<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\CoopSetting;

class MunicipalityToBranchService
{
    /**
     * Get branch by municipality name.
     */
    public static function getBranchIdByMunicipality(?string $municipality): ?int
    {
        if (! $municipality) {
            return null;
        }

        $needle = mb_strtolower(trim($municipality));
        $mapping = self::getMunicipalitiesToBranchesMapping();

        foreach ($mapping as $branchName => $municipalities) {
            $normalized = array_map(fn ($m) => mb_strtolower(trim((string) $m)), (array) $municipalities);
            if (in_array($needle, $normalized, true)) {
                $branch = Branch::where('name', $branchName)
                    ->where('is_active', true)
                    ->first();

                return $branch?->branch_id;
            }
        }

        return null;
    }

    /**
     * Get the complete municipality-to-branch mapping.
     */
    public static function getMunicipalitiesToBranchesMapping(): array
    {
        return CoopSetting::get('municipality_to_branch_mapping', []);
    }

    /**
     * Update the municipality-to-branch mapping.
     */
    public static function updateMapping(array $mapping): void
    {
        CoopSetting::set('municipality_to_branch_mapping', $mapping, 'json');
    }

    /**
     * Get all municipalities and their assigned branches.
     */
    public static function getAllMunicipalitiesWithBranches(): array
    {
        $mapping = self::getMunicipalitiesToBranchesMapping();
        $result = [];

        foreach ($mapping as $branchName => $municipalities) {
            $branch = Branch::where('name', $branchName)
                ->where('is_active', true)
                ->first();

            if ($branch) {
                foreach ($municipalities as $municipality) {
                    $result[$municipality] = [
                        'municipality' => $municipality,
                        'branch_name' => $branchName,
                        'branch_id' => $branch->branch_id,
                    ];
                }
            }
        }

        return $result;
    }
}
