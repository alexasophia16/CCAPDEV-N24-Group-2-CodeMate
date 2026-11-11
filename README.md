# CodeMate

A peer-tutoring web app for DLSU CCS students: find a tutor, book a consultation, ask questions in course discussions, and share study resources. Admins verify tutors and moderate content.

**CCAPDEV N24 · Group 2**

- **Phase 1 (frontent):** https://alexasophia16.github.io/CCAPDEV-N24-Group-2-CodeMate/ (`main` branch)
- **Phase 2 (backend):** this branch, `phase2`

Built with PHP, MySQL/MariaDB and Apache (LAMP) 

## Demo accounts


## Setting up on the CCS Cloud server

The CCS Cloud machine runs Ubuntu 22. 



## Account type selection and Google sign-in

- New email/password registrations must choose either `tutee` or `tutor`.
- New Google registrations choose the same role after Google authentication.
- Existing Google-linked accounts sign in with their existing database role; they are not asked to choose again.
- New tutor accounts automatically receive a `tutor_profiles` row with `verification_status = 'pending'`.
- New passwords are stored using PHP `password_hash()`. Existing seeded plaintext demo passwords are accepted once and upgraded to a password hash after successful login.
- `backend/config/google.php` is a local demo-mode file in this package. It simulates Google sign-in and does not verify email ownership. Never deploy demo mode publicly.
- To enable real Google OAuth, replace the local `google.php` with the settings shown in `backend/config/google.example.php`, and register the exact callback URI in Google Cloud Console.
