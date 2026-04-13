<?php

namespace App\Filament\Resources\MembershipApplications\Pages;

use App\Filament\Resources\MembershipApplications\MembershipApplicationResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListMembershipApplications extends ListRecords
{
    protected static string $resource = MembershipApplicationResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Membership Applications';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\MembershipApplicationsStats::class,
        ];
    }
}
