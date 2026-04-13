<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $user = static::getModel()::create($data);

        $qrData = json_encode([
            'user_id'  => $user->user_id,
            'username' => $user->username,
        ]);

        $qrCode = QrCode::format('png')
                        ->size(200)
                        ->generate($qrData);

        $filename = 'qrcodes/user_' . $user->user_id . '.png';
        Storage::disk('public')->put($filename, $qrCode);

        // 1. Create the user
        $user = static::getModel()::create($data);

        // 2. Load profile relationship
        $user->load('profile');

        // 3. Build plain text QR data
        $qrData = implode("\n", [
            'User ID  : ' . str_pad($user->user_id, 5, '0', STR_PAD_LEFT),
            'Username : ' . $user->username,
            'Profile  : ' . ($user->profile->full_name ?? 'N/A'),
            'Coop ID  : ' . ($user->coop_id ?? 'N/A'),
            'Status   : Active',
        ]);

        // 4. Generate QR Code as SVG
        $qrCode = QrCode::format('svg')
                        ->size(200)
                        ->margin(1)
                        ->generate($qrData);

        // 5. Save to storage
        $filename = 'qrcodes/user_' . $user->user_id . '.svg';
        Storage::disk('public')->put($filename, $qrCode);

        // 6. Update user record with QR path
        $user->update(['qr_code' => $filename]);

        return $user;
    }
}