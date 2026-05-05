<?php

use App\Models\Branch;
use App\Models\CoopSetting;

it('resolves branch by municipality with trimmed and case-insensitive matching', function () {
    $branch = Branch::create([
        'name' => 'Hilongos',
        'code' => 'HIL',
        'address' => 'Test Address',
        'contact_no' => '09171234567',
        'is_active' => true,
    ]);

    CoopSetting::set('municipality_to_branch_mapping', [
        'HILONGOS' => ' Bato, Hilongos , Inopacan ',
    ], 'json');

    $response = $this->getJson('/api/resolve-branch-by-municipality?municipality=  hIlOnGoS  ');

    $response->assertOk()
        ->assertJson([
            'branch_id' => $branch->branch_id,
            'name' => $branch->name,
        ]);
});
