<?php

namespace App\Observers;

use App\Models\ShareCapitalTransaction;
use App\Support\CoopSettings;

class ShareCapitalTransactionObserver
{
    public function created(ShareCapitalTransaction $tx): void
    {
        $this->recalculate($tx);
    }

    public function updated(ShareCapitalTransaction $tx): void
    {
        $this->recalculate($tx);
    }

    private function recalculate(ShareCapitalTransaction $tx): void
    {
        $profile = $tx->profile()->with('memberDetail')->first();
        if (! $profile || ! $profile->memberDetail) return;

        $balance = ShareCapitalTransaction::query()
            ->where('profile_id', $tx->profile_id)
            ->selectRaw("SUM(CASE WHEN direction = 'credit' THEN amount ELSE -amount END) as bal")
            ->value('bal') ?? 0;

        $member = $profile->memberDetail;
        $member->share_capital_balance = $balance;

        // threshold
        $threshold = (float) CoopSettings::get('share_capital_regular_threshold', 5000);

        // MVP: once regular, stays regular
        if (is_null($member->regular_at) && $balance >= $threshold) {
            $member->regular_at = now();
        }

        $member->save();
    }
}