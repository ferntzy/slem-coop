<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\Role;
use App\Services\QrCodeGeneratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Admin','Manager','Staff','Member',
            'Cashier','Loan Officer','Account Officer','Teller'
        ];

        $roleMap = [];
        foreach ($roles as $name) {
            $roleMap[$name] = Role::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        $upsertUser = function (
            string $email,
            string $username,
            string $roleName,
            string $first,
            string $last,
            ?string $civilStatus = null,
            ?string $tin = null,
            string $password = 'password'
        ) use ($roleMap) {

            $profile = Profile::updateOrCreate(
                ['email' => $email],
                [
                    'first_name'   => $first,
                    'middle_name'  => null,
                    'last_name'    => $last,
                    'roles_id'     => $roleMap[$roleName]->id,
                    'civil_status' => $civilStatus,
                    'tin'          => $tin,
                ]
            );

            $avatar = 'https://ui-avatars.com/api/?name=' .
                urlencode($first . ' ' . $last) .
                '&background=random';

            $user = User::updateOrCreate(
                ['username' => $username],
                [
                    'password'   => Hash::make($password),
                    'profile_id' => $profile->profile_id,
                    'avatar'     => $avatar,
                    'image_path' => 'images/user-profile-seed.jpg',
                ]
            );

            // Ensure coop_id exists
            if (empty($user->coop_id)) {
                $user->coop_id = User::generateCoopId();
                $user->save(); // triggers observer
            }

            // Generate QR via service (single source of truth)
            app(QrCodeGeneratorService::class)
                ->generateForUser($user);
        };

        $upsertUser('admin@example.com',          'admin',          'Admin',           'Admin',   'User');
        $upsertUser('manager@example.com',        'manager',        'Manager',         'Manager', 'User');
        $upsertUser('member@example.com',         'member',         'Member',          'Member',  'User');
        $upsertUser('loanofficer@example.com',    'loanofficer',    'Loan Officer',    'Loan',    'Officer');
        $upsertUser('accountofficer@example.com', 'accountofficer', 'Account Officer', 'Account', 'Officer');
    }
}
