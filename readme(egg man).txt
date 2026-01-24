
DatabaseSeeder.php
Create Roles: admin, doctor, patient.
Create Users:
Doctor:  doctor@example.com(User ID 1), with Doctor Profile (ID 1).
Patient: patient@example.com (User ID 2), with Patient Profile (ID 1).
Admin: admin@example.com (User ID 3).
Assign Roles to Users.
Create related data:
Diet Plan for Patient 1.
Measurement for Patient 1.
Consultation for Doctor 1 / Patient 1.
Subscription for Doctor 1 / Patient 1.
Verification Plan
Automated Verification

Run php artisan db:seed.
