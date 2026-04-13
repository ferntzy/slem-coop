<?php

namespace App\Observers;

use App\Models\DailyCollectionEntry;
use App\Services\NotificationService;

class DailyCollectionEntryObserver
{
    public function updated(DailyCollectionEntry $entry): void
    {
        // Check if status changed to "Submitted" or submitted_at was just set
        if ($entry->wasChanged('submitted_at') && $entry->submitted_at !== null) {
            $aoName = $entry->ao?->profile?->first_name.' '.$entry->ao?->profile?->last_name;
            $totalAmount = $entry->system_total;

            app(NotificationService::class)->notifyDailyCollectionSubmitted(
                $aoName,
                $totalAmount
            );
        }
    }
}
