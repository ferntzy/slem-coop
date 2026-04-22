<?php

namespace App\Console\Commands;

use App\Services\SavingsDormancyService;
use Illuminate\Console\Command;

class ProcessSavingsDormancy extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:process-savings-dormancy';

    /**
     * @var string
     */
    protected $description = 'Apply monthly savings interest and dormant account fees based on inactivity settings';

    public function __construct(
        protected SavingsDormancyService $savingsDormancyService,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $summary = $this->savingsDormancyService->processMonthly();

        $this->info('Savings dormancy processing completed.');
        $this->line('Evaluated accounts: '.$summary['evaluated_accounts']);
        $this->line('Dormant accounts: '.$summary['dormant_accounts']);
        $this->line('Interest credits posted: '.$summary['interest_posted']);
        $this->line('Dormancy fees posted: '.$summary['dormancy_fees_posted']);

        return self::SUCCESS;
    }
}
