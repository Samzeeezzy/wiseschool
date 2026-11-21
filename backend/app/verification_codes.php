<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function wise_code_hash(string $code): string
{
    return hash_hmac('sha256', strtoupper(trim($code)), (string)wise_config()['app_secret']);
}

function wise_generate_verification_code(): string
{
    // 128 bits of cryptographic randomness; displayed once at issuance.
    return strtoupper(bin2hex(random_bytes(16)));
}

/**
 * Redeem a code within an existing transaction.
 * Caller must also validate the Google identity/token and activate/link the account
 * in this SAME transaction before committing.
 */
function wise_redeem_code(PDO $pdo, string $code, string $category, int $personId, ?string $verifiedGoogleEmail): array
{
    $allowed = ['student','parent','teacher','non_teaching_staff','administrator','alumni'];
    if (!in_array($category, $allowed, true) || strlen($code) < 20 || strlen($code) > 80) {
        throw new DomainException('Verification could not be completed.');
    }

    $hash = wise_code_hash($code);
    $stmt = $pdo->prepare('SELECT * FROM verification_codes WHERE code_hash = :hash FOR UPDATE');
    $stmt->execute([':hash' => $hash]);
    $row = $stmt->fetch();

    if (!$row) {
        throw new DomainException('Verification could not be completed.');
    }

    if ((int)$row['target_person_id'] !== $personId || $row['category'] !== $category ||
        $row['revoked_at'] !== null || $row['redeemed_at'] !== null ||
        strtotime($row['expires_at'] . ' UTC') <= time() ||
        (int)$row['attempt_count'] >= (int)$row['max_attempts']) {
        throw new DomainException('Verification could not be completed.');
    }

    $personStmt = $pdo->prepare('SELECT expected_email,status FROM school_people WHERE id = :id FOR UPDATE');
    $personStmt->execute([':id' => $personId]);
    $person = $personStmt->fetch();
    if (!$person || $person['status'] !== 'active') {
        throw new DomainException('Verification could not be completed.');
    }

    $expected = $row['expected_google_email'] ?: $person['expected_email'];
    if ($expected !== null && ($verifiedGoogleEmail === null || !hash_equals(strtolower($expected), strtolower($verifiedGoogleEmail)))) {
        $attempt = $pdo->prepare('UPDATE verification_codes SET attempt_count = attempt_count + 1 WHERE id = :id');
        $attempt->execute([':id' => $row['id']]);
        throw new DomainException('Verification could not be completed.');
    }

    $consume = $pdo->prepare('UPDATE verification_codes SET redeemed_at = UTC_TIMESTAMP() WHERE id = :id AND redeemed_at IS NULL AND revoked_at IS NULL');
    $consume->execute([':id' => $row['id']]);
    if ($consume->rowCount() !== 1) {
        throw new DomainException('Verification could not be completed.');
    }

    return ['code_id' => (int)$row['id'], 'person_id' => $personId, 'category' => $category];
}
