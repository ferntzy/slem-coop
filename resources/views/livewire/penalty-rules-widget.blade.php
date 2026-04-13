<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\PenaltyRule;

class PenaltyRulesWidget extends Component
{
    public $penaltyRules;

    public function mount()
    {
        $this->penaltyRules = PenaltyRule::orderBy('sort_order')->orderBy('id')->get();
    }

    public function render()
    {
        return view('livewire.penalty-rules-widget');
    }
}sasadasdsa