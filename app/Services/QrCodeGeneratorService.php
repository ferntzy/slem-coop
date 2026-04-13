<?php

namespace App\Services;

use App\Models\User;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class QrCodeGeneratorService
{
    /**
     * Generate raw QR PNG binary.
     */
    public function generate(string $data): string
    {
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($data)
            ->size(200)
            ->margin(10)
            ->build();

        return $result->getString();
    }

    /**
     * Generate and store QR for a user.
     */
    public function generateForUser(User $user): void
    {
        $qrContent = $user->user_id;

        $qrCodePng = $this->generate($qrContent);

        $fileName = 'qr_codes/user_' . $user->user_id . '.png';

        Storage::disk('public')->put($fileName, $qrCodePng);

        $user->qr_code = $fileName;
        $user->saveQuietly();
    }
}
