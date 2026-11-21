<?php
declare(strict_types=1);

$wiseAppPath = getenv('WISE_APP_PATH') ?: dirname(__DIR__, 3);
require_once rtrim($wiseAppPath, DIRECTORY_SEPARATOR) . '/app/bootstrap.php';
require_once rtrim($wiseAppPath, DIRECTORY_SEPARATOR) . '/app/wise_verification_codes.php';

try {
    $actorId = wise_require_super_admin();
    $pdo = wise_db();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        $stmt = $pdo->query(
            "SELECT vc.id, vc.category, vc.target_person_id, sp.full_name, sp.school_reference,
                    vc.expected_google_email, vc.issued_at, vc.expires_at, vc.redeemed_at, vc.revoked_at,
                    vc.attempt_count, vc.max_attempts,
                    CASE
                      WHEN vc.revoked_at IS NOT NULL THEN 'revoked'
                      WHEN vc.redeemed_at IS NOT NULL THEN 'redeemed'
                      WHEN vc.expires_at <= UTC_TIMESTAMP() THEN 'expired'
                      ELSE 'issued'
                    END AS status
             FROM wise_verification_codes vc
             INNER JOIN wise_school_people sp ON sp.id = vc.target_person_id
             ORDER BY vc.issued_at DESC
             LIMIT 200"
        );
        wise_json_response(200, ['ok' => true, 'codes' => $stmt->fetchAll()]);
    }

    if ($method !== 'POST') {
        header('Allow: GET, POST');
        wise_json_response(405, ['ok' => false, 'error' => 'Method not allowed.']);
    }

    wise_require_csrf();
    $input = json_decode(file_get_contents('php://input') ?: '', true);
    if (!is_array($input)) {
        wise_json_response(400, ['ok' => false, 'error' => 'Invalid request.']);
    }

    $action = $input['action'] ?? '';
    if ($action === 'generate') {
        $personId = filter_var($input['person_id'] ?? null, FILTER_VALIDATE_INT);
        $category = (string)($input['category'] ?? '');
        $hours = filter_var($input['valid_hours'] ?? 48, FILTER_VALIDATE_INT);
        $categoryToPersonType = [
            'student' => 'student',
            'parent' => 'parent',
            'teacher' => 'staff',
            'non_teaching_staff' => 'staff',
            'administrator' => 'staff',
            'alumni' => 'alumni',
        ];
        if (!$personId || !isset($categoryToPersonType[$category]) || !$hours || $hours > 168) {
            wise_json_response(422, ['ok' => false, 'error' => 'Choose a valid existing school record, category and expiry (1–168 hours).']);
        }

        $personStmt = $pdo->prepare('SELECT id,person_type,full_name,expected_email,status,linked_user_id FROM wise_school_people WHERE id = :id');
        $personStmt->execute([':id' => $personId]);
        $person = $personStmt->fetch();
        if (!$person || $person['person_type'] !== $categoryToPersonType[$category] || $person['status'] !== 'active' || !empty($person['linked_user_id'])) {
            wise_json_response(422, ['ok' => false, 'error' => 'The selected school record is not eligible for code issuance.']);
        }

        $pdo->beginTransaction();
        // Reissue semantics: invalidate every previous unused code for this record/category.
        $revoke = $pdo->prepare('UPDATE wise_verification_codes SET revoked_at = UTC_TIMESTAMP() WHERE target_person_id = :person AND category = :category AND redeemed_at IS NULL AND revoked_at IS NULL');
        $revoke->execute([':person' => $personId, ':category' => $category]);

        $plainCode = wise_generate_verification_code();
        $insert = $pdo->prepare(
            'INSERT INTO wise_verification_codes (code_hash,category,target_person_id,expected_google_email,expires_at,max_attempts,issued_by_user_id)
             VALUES (:hash,:category,:person,:email,DATE_ADD(UTC_TIMESTAMP(), INTERVAL :hours HOUR),5,:issuer)'
        );
        $insert->bindValue(':hash', wise_code_hash($plainCode));
        $insert->bindValue(':category', $category);
        $insert->bindValue(':person', $personId, PDO::PARAM_INT);
        $insert->bindValue(':email', $person['expected_email']);
        $insert->bindValue(':hours', $hours, PDO::PARAM_INT);
        $insert->bindValue(':issuer', $actorId, PDO::PARAM_INT);
        $insert->execute();
        $codeId = (int)$pdo->lastInsertId();
        wise_audit($pdo, $actorId, 'verification_code.issued', 'verification_code', $codeId, [
            'category' => $category, 'target_person_id' => $personId, 'expires_in_hours' => $hours,
        ]);
        $pdo->commit();

        // Plaintext is returned once only. Never log it or store it.
        wise_json_response(201, [
            'ok' => true,
            'message' => 'Code generated. Copy it now; it will not be shown again.',
            'code' => $plainCode,
            'code_id' => $codeId,
            'expires_in_hours' => $hours,
        ]);
    }

    if ($action === 'revoke') {
        $codeId = filter_var($input['code_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$codeId) {
            wise_json_response(422, ['ok' => false, 'error' => 'A valid code record is required.']);
        }
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('UPDATE wise_verification_codes SET revoked_at = UTC_TIMESTAMP() WHERE id = :id AND redeemed_at IS NULL AND revoked_at IS NULL');
        $stmt->execute([':id' => $codeId]);
        if ($stmt->rowCount() === 1) {
            wise_audit($pdo, $actorId, 'verification_code.revoked', 'verification_code', $codeId);
        }
        $pdo->commit();
        wise_json_response(200, ['ok' => true, 'revoked' => $stmt->rowCount() === 1]);
    }

    wise_json_response(422, ['ok' => false, 'error' => 'Unsupported action.']);
} catch (Throwable $error) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('WISE API failure: ' . $error->getMessage());
    wise_json_response(500, ['ok' => false, 'error' => 'The request could not be completed.']);
}
