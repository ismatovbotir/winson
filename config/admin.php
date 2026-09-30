<?php

/*
| Credentials for the /admin login created by AdminUserSeeder. Set these in
| .env — never commit real values. Re-running the seeder updates the password.
*/
return [
    'name' => env('ADMIN_NAME', 'Admin'),
    'email' => env('ADMIN_EMAIL'),
    'password' => env('ADMIN_PASSWORD'),
];
