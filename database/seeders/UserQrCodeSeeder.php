<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class UserQrCodeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::with('profile')->get();

        foreach ($users as $user) {

            // Plain text encoded into QR
            $qrData = implode("\n", [
                'User ID  : ' . str_pad($user->user_id, 5, '0', STR_PAD_LEFT),
                'Username : ' . $user->username,
                'Profile  : ' . ($user->profile->full_name ?? 'N/A'),
                'Coop ID  : ' . ($user->coop_id ?? 'N/A'),
                'Status   : Active',
            ]);

            // Generate QR Code as SVG
            $qrCode = QrCode::format('svg')
                            ->size(200)
                            ->margin(1)
                            ->generate($qrData);

            // Save to storage
            $filename = 'qrcodes/user_' . $user->user_id . '.svg';
            Storage::disk('public')->put($filename, $qrCode);

            // Update user record
            $user->update(['qr_code' => $filename]);

            $this->command->info("Regenerated for User #{$user->user_id} — {$user->username}");
        }

        $this->command->info('All QR Codes regenerated successfully.');
    }
}
