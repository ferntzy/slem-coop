<?php

use App\Models\CollectionAndPosting;
use App\Models\User;

test('receipt download returns a pdf attachment', function () {
    $user = User::query()->create([
        'username' => 'Receipt Tester',
        'password' => 'password',
        'is_active' => true,
        'profile_id' => null,
        'avatar' => null,
        'image_path' => null,
    ]);

    $record = CollectionAndPosting::query()->create([
        'loan_number' => 'LA-1001',
        'member_name' => 'Juan Dela Cruz',
        'amount_paid' => 1250.50,
        'payment_date' => now()->toDateString(),
        'payment_method' => 'Cash',
        'reference_number' => 'OR-1001',
        'notes' => 'Test receipt download',
        'file_path' => null,
        'original_file_name' => null,
        'mime_type' => null,
        'file_size' => null,
        'document_type' => 'Official Receipt',
        'status' => 'Posted',
        'posted_by_user_id' => $user->getKey(),
        'audit_trail' => [
            'action' => 'Posted',
            'user_id' => $user->getKey(),
            'timestamp' => now()->toISOString(),
        ],
    ]);

    $response = $this->actingAs($user)->get(route('receipt.download', $record));

    $response->assertOk();
    $response->assertDownload('receipt-OR-1001.pdf');
    $response->assertHeader('content-type', 'application/pdf');
});
