<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AboutPageSetting;
use Illuminate\Support\Facades\Storage;

class AboutPageController extends Controller
{
    public function show()
    {
        $setting = AboutPageSetting::getSetting();

        // Resolve photo URLs for board members
        $boardMembers = collect($setting->board_members ?? [])->map(function ($member) {
            if (!empty($member['photo'])) {
                $member['photo'] = Storage::url($member['photo']);
            }
            return $member;
        })->toArray();

        return response()->json([
            'hero' => [
                'badge'    => $setting->hero_badge,
                'title'    => $setting->hero_title,
                'subtitle' => $setting->hero_subtitle,
            ],
            'vision'       => $setting->vision,
            'mission'      => $setting->mission,
            'history'      => $setting->history ?? [],
            'core_values'  => $setting->core_values ?? [],
            'testimonials' => $setting->testimonials ?? [],
            'board_members'=> $boardMembers,
            'org_structure' => [
                'general_assembly'   => $setting->org_general_assembly,
                'board_of_directors' => $setting->org_board_of_directors,
                'management_team'    => $setting->org_management_team,
                'operational_staff'  => $setting->org_operational_staff,
            ],
        ]);
    }
}
