# WISE Website & School Management System — Implementation Plan

This repository is the existing WISE public website. Development is staged against the SamKonceptz Enterprise proposal. The current website is static HTML/CSS/JavaScript; adding a visible login screen does not create secure authentication or a working school management system.

## Phase 1 — Public website (this branch)
- Preserve WISE's royal blue, golden yellow and fresh green identity.
- Keep existing school overview, curriculum, school levels, achievements, contact details and official image assets.
- Add a BTS Scholarship information and enquiry section.
- Add basic FAQs and a document-request route. Only approved school documents should be published.
- Keep beneficiary identities and sensitive student information private.

## Phase 2 — Platform foundation
- Confirm the school's chosen backend/hosting and database provider.
- Configure separate development and production environments.
- Design relational records for academic sessions/terms, classes/arms, subjects, students, guardians, staff, admissions, attendance, assessments, fees, announcements, learning resources and alumni.
- Implement secure authentication, role-based permissions, audit logging, backups and recovery before loading real student data.

## Phase 3 — School operations
- Admin dashboard and content management.
- Student and guardian records, staff records, class/subject allocation and academic calendar.
- Online admissions, application reference, document uploads, review/status tracking and admission letters.
- Attendance, fee configuration, invoices, part-payments, balances, receipts and financial reports. Payment gateway integration is optional and requires the school's provider approval.

## Phase 4 — Academic and portals
- Continuous assessment and examination entry, automatic totals/grades, teacher comments, review and principal approval.
- Secure result publishing and downloadable PDF report cards.
- Parent, teacher and student portals with permissions scoped to the correct child, class or assigned subjects.
- **No student ranking or position** is to appear on report cards, as specified in the proposal.

## Phase 5 — Learning, AI and communication
- Lesson notes, videos, assignments, submissions, quizzes and practice tests.
- AI school assistant, student tutor, teacher assistant, result-comment suggestions and academic analytics.
- AI suggestions must remain reviewable and editable by authorised staff; they must not autonomously publish results.
- Email notifications first; SMS and WhatsApp are optional third-party integrations with separate provider costs.

## Phase 6 — Additional modules and delivery
- Library, inventory/assets, document management and school calendar.
- Transport management and online payments only if confirmed in scope.
- Security, accessibility, mobile, performance and workflow testing; deployment, staff training and documentation.
- Hosting, monitoring, backups, security updates and agreed maintenance plan.

## Required decisions and school-supplied materials
Before production data or sensitive workflows are enabled, obtain:
- Written approval of the system owner and named administrators.
- Backend/database/hosting choice and access controlled by the school.
- Approved admission rules, fee schedules, grading scale, assessment weights, term/session structure and result approval workflow.
- Staff roles, class/subject assignments and verified student/guardian records through a secure onboarding process.
- Approved privacy notice, consent process, retention rules and student-photo permissions.
- Approved BTS Scholarship eligibility, application dates and sponsorship instructions.
- Approved policies, calendar, prospectus, forms, staff/leadership biographies and facility photographs.
- Email/SMS/WhatsApp/payment/AI provider choices and API credentials stored only as server-side secrets.

## Safety and release rules
- Never put database service-role keys, AI keys, payment secrets or SMTP passwords in browser JavaScript or a public repository.
- Do not use demo credentials or browser-only checks as authentication.
- Enforce authorisation on the server/database for every student, parent, teacher and administrator action.
- Validate and restrict uploads; protect against SQL injection, XSS, CSRF and unauthorised record access.
- Use HTTPS, least-privilege access, audit logs, routine backups and tested recovery.
- Do not publish real student data, private results, scholarship beneficiary details or unapproved documents.
- Test in a staging environment before merging production changes.

## Delivery timing
The proposal estimates 21 days: 14 days for development and 7–14 days for testing/quality assurance, subject to timely school materials, decisions, feedback and approvals. This is a target, not a guaranteed completion date. Third-party AI, messaging and payment usage may incur separate charges.
