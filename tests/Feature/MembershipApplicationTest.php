<?php

use App\Models\Branch;
use App\Models\CoopSetting;
use App\Models\MembershipType;
use App\Models\Profile;

test('resolve branch by municipality returns correct branch', function () {
    // Create test branches
    $batoBranch = Branch::factory()->create(['name' => 'Bato']);
    $hilongosBranch = Branch::factory()->create(['name' => 'Hilongos']);

    // Ensure municipality mapping is set in coop settings
    CoopSetting::set('municipality_to_branch_mapping', [
        'Hilongos' => ['Bato', 'Hilongos', 'Hindang', 'Inopacan'],
        'Baybay' => ['Baybay', 'Albuera'],
        'Ormoc' => ['Ormoc', 'Merida', 'Isabel', 'Kananga'],
    ]);

    $response = $this->get('/api/resolve-branch-by-municipality?municipality=Hilongos');

    $response->assertStatus(200);
    $response->assertJson([
        'branch_id' => $hilongosBranch->branch_id,
        'name' => 'Hilongos',
    ]);
});

test('resolve branch by municipality returns 400 for missing municipality', function () {
    $response = $this->get('/api/resolve-branch-by-municipality');

    $response->assertStatus(400);
    $response->assertJson([
        'error' => 'Municipality parameter is required.',
    ]);
});

test('resolve branch by municipality returns 404 for unmapped municipality', function () {
    CoopSetting::set('municipality_to_branch_mapping', [
        'Hilongos' => ['Hilongos'],
    ]);

    $response = $this->get('/api/resolve-branch-by-municipality?municipality=UnknownCity');

    $response->assertStatus(404);
    $response->assertJson([
        'error' => 'Municipality not mapped to any branch.',
    ]);
});
