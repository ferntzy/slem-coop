<?php

use App\Models\CoopSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add municipality-to-branch mapping coop setting
        CoopSetting::updateOrCreate(
            ['key' => 'municipality_to_branch_mapping'],
            [
                'value' => json_encode([
                    'Hilongos' => ['Bato', 'Hilongos', 'Hindang', 'Inopacan'],
                    'Baybay' => ['Baybay', 'Albuera'],
                    'Ormoc' => ['Ormoc', 'Merida', 'Isabel', 'Kananga'],
                ]),
                'type' => 'json',
                'group' => 'membership',
                'label' => 'Municipality to Branch Mapping',
                'description' => 'Maps municipalities to their assigned branches for automatic branch assignment during membership application',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        CoopSetting::where('key', 'municipality_to_branch_mapping')->delete();
    }
};
