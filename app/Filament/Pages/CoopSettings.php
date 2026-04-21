<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BranchCrudWidget;
use App\Filament\Widgets\CoopFeesTableWidget;
use App\Filament\Widgets\CoopFeesWidget;
use App\Filament\Widgets\CoopFeeTypesTableWidget;
use App\Filament\Widgets\LoanTypeCrudWidget;
use App\Filament\Widgets\MembershipTypeCrudWidget;
use App\Filament\Widgets\PaymentPriorityWidget;
use App\Filament\Widgets\PenaltyRulesWidget;
use App\Models\Branch;
use App\Models\CoopSetting;
use App\Models\LoanType;
use App\Models\MembershipType;
use App\Models\PaymentAllocationSetting;
use App\Models\PenaltyRule;
use App\Models\ShareCapitalTransaction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class CoopSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationLabel = 'Coop Settings';

    protected static ?string $title = 'Coop Settings';

    protected static ?string $slug = 'coop-settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.coop-settings';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:CoopSettings') ?? false;
    }

    public int $branch_max_members = 500;

    public bool $branch_allow_inter_branch_transfer = true;

    public float $branch_inter_branch_transfer_fee = 50.00;

    public bool $branch_require_branch_manager_approval = true;

    public string $branch_default_branch_code_prefix = 'BR';

    public int $branch_count = 0;

    public int $share_capital_transaction_count = 0;

    public float $share_capital_minimum_initial_subscription = 1000.00;

    public float $share_capital_par_value_per_share = 100.00;

    public float $share_capital_minimum_monthly_contribution = 200.00;

    public int $share_capital_maximum_shares_per_member = 5000;

    public bool $share_capital_allow_share_withdrawal = false;

    public int $share_capital_withdrawal_notice_days = 30;

    public float $share_capital_dividend_rate_percent = 8.00;

    public string $share_capital_dividend_payout_schedule = 'annual';

    public int $penalty_rule_count = 0;

    public float $penalty_late_payment_rate_percent = 2.00;

    public int $penalty_grace_period_days = 5;

    public float $penalty_maximum_penalty_cap_percent = 20.00;

    public bool $penalty_apply_compound_penalty = false;

    public float $penalty_missed_contribution_penalty = 50.00;

    public bool $penalty_waiver_allowed = true;

    public string $penalty_calculation_method = 'daily';

    public int $loan_type_count = 0;

    public float $loan_default_interest_rate_percent = 12.00;

    public int $loan_maximum_loan_multiplier = 3;

    public float $loan_minimum_loan_amount = 1000.00;

    public float $loan_maximum_loan_amount = 500000.00;

    public int $loan_maximum_term_months = 60;

    public float $loan_loan_processing_fee_percent = 1.00;

    public int $loan_minimum_membership_months_to_apply = 6;

    public bool $loan_require_co_maker = true;

    public float $loan_co_maker_minimum_share_capital = 5000.00;

    public string $loan_interest_calculation_method = 'diminishing';

    public bool $loan_allow_early_payment = true;

    public float $loan_early_payment_rebate_percent = 25.00;

    public float $loan_loan_officer_approval_limit = 20000.00;

    public int $membership_type_count = 0;

    public float $membership_registration_fee = 200.00;

    public float $membership_annual_membership_fee = 100.00;

    public int $membership_minimum_age = 18;

    public bool $membership_allow_associate_membership = true;

    public float $membership_associate_membership_fee = 150.00;

    public int $membership_probationary_period_months = 3;

    public bool $membership_require_id_verification = true;

    public int $membership_auto_suspend_on_missed_contributions = 3;

    public int $member_status_delinquent_months_threshold = 3;

    public bool $member_status_auto_mark_delinquent = true;

    public int $payment_allocation_rule_count = 0;

    public bool $payment_apply_to_oldest_loan_first = true;

    public bool $payment_allow_partial_payment = true;

    public float $payment_minimum_partial_payment_percent = 25.00;

    public string $payment_overpayment_action = 'apply_to_principal';

    public bool $payment_auto_debit_share_capital = true;

    public string $payment_share_capital_deduction_priority = 'after_loan';

    public string $payment_payment_receipt_prefix = 'OR';

    // ORIENTATION
    public string $orientation_zoom_link = '';

    public string $orientation_video_link = '';

    public int $orientation_passing_score = 75;

    public bool $orientation_require_for_loan = true;

    public array $orientation_questions = [];

    public function mount(): void
    {
        $this->branch_max_members = (int) CoopSetting::get('branch.max_members', 500);
        $this->branch_allow_inter_branch_transfer = (bool) CoopSetting::get('branch.allow_inter_branch_transfer', true);
        $this->branch_inter_branch_transfer_fee = (float) CoopSetting::get('branch.inter_branch_transfer_fee', 50.00);
        $this->branch_require_branch_manager_approval = (bool) CoopSetting::get('branch.require_branch_manager_approval', true);
        $this->branch_default_branch_code_prefix = CoopSetting::get('branch.default_branch_code_prefix', 'BR') ?? 'BR';
        $this->branch_count = Branch::count();

        $this->share_capital_transaction_count = ShareCapitalTransaction::count();
        $this->share_capital_minimum_initial_subscription = (float) CoopSetting::get('share_capital.minimum_initial_subscription', 1000.00);
        $this->share_capital_par_value_per_share = (float) CoopSetting::get('share_capital.par_value_per_share', 100.00);
        $this->share_capital_minimum_monthly_contribution = (float) CoopSetting::get('share_capital.minimum_monthly_contribution', 200.00);
        $this->share_capital_maximum_shares_per_member = (int) CoopSetting::get('share_capital.maximum_shares_per_member', 5000);
        $this->share_capital_allow_share_withdrawal = (bool) CoopSetting::get('share_capital.allow_share_withdrawal', false);
        $this->share_capital_withdrawal_notice_days = (int) CoopSetting::get('share_capital.withdrawal_notice_days', 30);
        $this->share_capital_dividend_rate_percent = (float) CoopSetting::get('share_capital.dividend_rate_percent', 8.00);
        $this->share_capital_dividend_payout_schedule = CoopSetting::get('share_capital.dividend_payout_schedule', 'annual') ?? 'annual';

        $this->penalty_late_payment_rate_percent = (float) CoopSetting::get('penalty.late_payment_rate_percent', 2.00);
        $this->penalty_grace_period_days = (int) CoopSetting::get('penalty.grace_period_days', 5);
        $this->penalty_maximum_penalty_cap_percent = (float) CoopSetting::get('penalty.maximum_penalty_cap_percent', 20.00);
        $this->penalty_apply_compound_penalty = (bool) CoopSetting::get('penalty.apply_compound_penalty', false);
        $this->penalty_missed_contribution_penalty = (float) CoopSetting::get('penalty.missed_contribution_penalty', 50.00);
        $this->penalty_waiver_allowed = (bool) CoopSetting::get('penalty.penalty_waiver_allowed', true);
        $this->penalty_calculation_method = CoopSetting::get('penalty.penalty_calculation_method', 'daily') ?? 'daily';
        $this->penalty_rule_count = PenaltyRule::count();

        $this->loan_type_count = LoanType::count();
        $this->loan_default_interest_rate_percent = (float) CoopSetting::get('loan.default_interest_rate_percent', 12.00);
        $this->loan_maximum_loan_multiplier = (int) CoopSetting::get('loan.maximum_loan_multiplier', 3);
        $this->loan_minimum_loan_amount = (float) CoopSetting::get('loan.minimum_loan_amount', 1000.00);
        $this->loan_maximum_loan_amount = (float) CoopSetting::get('loan.maximum_loan_amount', 500000.00);
        $this->loan_maximum_term_months = (int) CoopSetting::get('loan.maximum_term_months', 60);
        $this->loan_loan_processing_fee_percent = (float) CoopSetting::get('loan.loan_processing_fee_percent', 1.00);
        $this->loan_minimum_membership_months_to_apply = (int) CoopSetting::get('loan.minimum_membership_months_to_apply', 6);
        $this->loan_require_co_maker = (bool) CoopSetting::get('loan.require_co_maker', true);
        $this->loan_co_maker_minimum_share_capital = (float) CoopSetting::get('loan.co_maker_minimum_share_capital', 5000.00);
        $this->loan_interest_calculation_method = CoopSetting::get('loan.interest_calculation_method', 'diminishing') ?? 'diminishing';
        $this->loan_allow_early_payment = (bool) CoopSetting::get('loan.allow_early_payment', true);
        $this->loan_early_payment_rebate_percent = (float) CoopSetting::get('loan.early_payment_rebate_percent', 25.00);
        $this->loan_loan_officer_approval_limit = (float) CoopSetting::get('loan.loan_officer_approval_limit', 20000.00);

        $this->membership_registration_fee = (float) CoopSetting::get('membership.registration_fee', 200.00);
        $this->membership_annual_membership_fee = (float) CoopSetting::get('membership.annual_membership_fee', 100.00);
        $this->membership_minimum_age = (int) CoopSetting::get('membership.minimum_age', 18);
        $this->membership_allow_associate_membership = (bool) CoopSetting::get('membership.allow_associate_membership', true);
        $this->membership_associate_membership_fee = (float) CoopSetting::get('membership.associate_membership_fee', 150.00);
        $this->membership_probationary_period_months = (int) CoopSetting::get('membership.probationary_period_months', 3);
        $this->membership_require_id_verification = (bool) CoopSetting::get('membership.require_id_verification', true);
        $this->membership_auto_suspend_on_missed_contributions = (int) CoopSetting::get('membership.auto_suspend_on_missed_contributions', 3);
        $this->membership_type_count = MembershipType::count();

        $this->member_status_delinquent_months_threshold = (int) CoopSetting::get('member_status.delinquent_months_threshold', 3);
        $this->member_status_auto_mark_delinquent = (bool) CoopSetting::get('member_status.auto_mark_delinquent', true);

        $this->payment_allocation_rule_count = PaymentAllocationSetting::getSingleton()->allocationRules()->count();
        $this->payment_apply_to_oldest_loan_first = (bool) CoopSetting::get('payment_allocation.apply_to_oldest_loan_first', true);
        $this->payment_allow_partial_payment = (bool) CoopSetting::get('payment_allocation.allow_partial_payment', true);
        $this->payment_minimum_partial_payment_percent = (float) CoopSetting::get('payment_allocation.minimum_partial_payment_percent', 25.00);
        $this->payment_overpayment_action = CoopSetting::get('payment_allocation.overpayment_action', 'apply_to_principal') ?? 'apply_to_principal';
        $this->payment_auto_debit_share_capital = (bool) CoopSetting::get('payment_allocation.auto_debit_share_capital', true);
        $this->payment_share_capital_deduction_priority = CoopSetting::get('payment_allocation.share_capital_deduction_priority', 'after_loan') ?? 'after_loan';
        $this->payment_payment_receipt_prefix = CoopSetting::get('payment_allocation.payment_receipt_prefix', 'OR') ?? 'OR';

        // ORIENTATION
        $this->orientation_zoom_link = CoopSetting::get('orientation.zoom_link', '') ?? '';
        $this->orientation_video_link = CoopSetting::get('orientation.video_link', '') ?? '';
        $this->orientation_passing_score = (int) CoopSetting::get('orientation.passing_score', 75);
        $this->orientation_require_for_loan = (bool) CoopSetting::get('orientation.require_for_loan', true);

        $rawQuestions = CoopSetting::get('orientation.questions', []);
        $this->orientation_questions = is_array($rawQuestions)
            ? $rawQuestions
            : (json_decode($rawQuestions ?: '[]', true) ?: []);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('CoopSettingsTabs')
                ->tabs([
                    Tab::make('Branches')
                        ->icon('heroicon-o-building-storefront')
                        ->schema([
                            Section::make()->schema([])->columnSpanFull(),
                            Livewire::make(BranchCrudWidget::class)->columnSpanFull(),
                        ]),

                    Tab::make('Loan Types')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            Section::make()->schema([])->columnSpanFull(),
                            Livewire::make(LoanTypeCrudWidget::class)->columnSpanFull(),
                        ]),

                    Tab::make('Membership Types')
                        ->icon('heroicon-o-users')
                        ->schema([
                            Section::make()->schema([])->columnSpanFull(),
                            Livewire::make(MembershipTypeCrudWidget::class)->columnSpanFull(),
                        ]),

                    Tab::make('Coop Fees')
                        ->icon('heroicon-o-receipt-percent')
                        ->schema([
                            Section::make('Manage Fee Types')
                                ->schema([
                                    Livewire::make(CoopFeeTypesTableWidget::class)->columnSpanFull(),
                                ]),
                            Section::make('Manage Fees')
                                ->schema([
                                    Livewire::make(CoopFeesTableWidget::class)->columnSpanFull(),
                                ]),
                            Section::make('Fee Overview')
                                ->schema([
                                    Livewire::make(CoopFeesWidget::class)->columnSpanFull(),
                                ]),
                        ]),

                    Tab::make('Penalties')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->schema([
                            Livewire::make(PenaltyRulesWidget::class)->columnSpanFull(),
                        ]),

                    Tab::make('Payment Allocation')
                        ->icon('heroicon-o-arrow-trending-up')
                        ->schema([
                            Livewire::make(PaymentPriorityWidget::class)->columnSpanFull(),
                        ]),

                    Tab::make('Loan Approval')
                        ->icon('heroicon-o-check-badge')
                        ->schema([
                            Section::make('Loan Officer Approval Limit')
                                ->description('Loans above this amount cannot be finalized by a Loan Officer and require both Manager and Admin approvals.')
                                ->schema([
                                    TextInput::make('loan_loan_officer_approval_limit')
                                        ->label('Loan Officer Auto-Approval Limit')
                                        ->numeric()
                                        ->minValue(0)
                                        ->prefix('PHP')
                                        ->required(),
                                ])
                                ->columns(1),
                        ]),

                    Tab::make('Member Status')
                        ->icon('heroicon-o-user-minus')
                        ->schema([
                            Section::make('Delinquent Member Settings')
                                ->description('Configure when members are automatically marked as delinquent based on missed loan payments.')
                                ->schema([
                                    TextInput::make('member_status_delinquent_months_threshold')
                                        ->label('Delinquent Months Threshold')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(12)
                                        ->suffix('months')
                                        ->helperText('Number of consecutive months a member can miss loan payments before being marked as delinquent.')
                                        ->required(),

                                    Toggle::make('member_status_auto_mark_delinquent')
                                        ->label('Automatically Mark Members as Delinquent')
                                        ->helperText('When enabled, members will be automatically marked as delinquent after missing payments for the specified number of months.')
                                        ->default(true),
                                ])
                                ->columns(2),
                        ]),

                    Tab::make('Orientation')
                        ->icon('heroicon-o-play-circle')
                        ->schema([
                            Section::make('Orientation Links & Rules')
                                ->schema([
                                    TextInput::make('orientation_zoom_link')
                                        ->label('Zoom Link')
                                        ->placeholder('https://...')
                                        ->columnSpanFull(),

                                    TextInput::make('orientation_video_link')
                                        ->label('Video Embed Link')
                                        ->placeholder('https://www.youtube.com/embed/...')
                                        ->columnSpanFull(),

                                    TextInput::make('orientation_passing_score')
                                        ->label('Passing Score (%)')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(100)
                                        ->required(),

                                    Toggle::make('orientation_require_for_loan')
                                        ->label('Require orientation before loan application'),
                                ])
                                ->columns(2),

                            Section::make('Post-Orientation Assessment')
                                ->schema([
                                    Repeater::make('orientation_questions')
                                        ->label('Questions')
                                        ->itemLabel(function (array $state, ?string $key = null): ?string {
                                            static $questionIndex = 0;
                                            if ($key === '0') {
                                                $questionIndex = 0;
                                            }

                                            return 'Question '.(++$questionIndex);
                                        })
                                        ->schema([
                                            Textarea::make('question')
                                                ->label('Question')
                                                ->required()
                                                ->columnSpanFull(),

                                            Repeater::make('choices')
                                                ->label('Choices')
                                                ->schema([
                                                    TextInput::make('value')
                                                        ->label('Choice')
                                                        ->required(),

                                                    Toggle::make('correct')
                                                        ->label('Correct Answer')
                                                        ->inline(false),
                                                ])
                                                ->minItems(2)
                                                ->defaultItems(2)
                                                ->columnSpanFull(),
                                        ])
                                        ->defaultItems(0)
                                        ->collapsible()
                                        ->cloneable()
                                        ->columnSpanFull(),

                                    Placeholder::make('orientation_note')
                                        ->label('Note')
                                        ->content('The applicant must reach the passing score to become eligible for loan application.'),
                                ]),
                        ]),
                ]),
        ]);
    }

    public function save(): void
    {
        CoopSetting::set('branch.max_members', $this->branch_max_members);
        CoopSetting::set('branch.allow_inter_branch_transfer', $this->branch_allow_inter_branch_transfer ? 'true' : 'false');
        CoopSetting::set('branch.inter_branch_transfer_fee', $this->branch_inter_branch_transfer_fee);
        CoopSetting::set('branch.require_branch_manager_approval', $this->branch_require_branch_manager_approval ? 'true' : 'false');
        CoopSetting::set('branch.default_branch_code_prefix', $this->branch_default_branch_code_prefix);

        CoopSetting::set('share_capital.minimum_initial_subscription', $this->share_capital_minimum_initial_subscription);
        CoopSetting::set('share_capital.par_value_per_share', $this->share_capital_par_value_per_share);
        CoopSetting::set('share_capital.minimum_monthly_contribution', $this->share_capital_minimum_monthly_contribution);
        CoopSetting::set('share_capital.maximum_shares_per_member', $this->share_capital_maximum_shares_per_member);
        CoopSetting::set('share_capital.allow_share_withdrawal', $this->share_capital_allow_share_withdrawal ? 'true' : 'false');
        CoopSetting::set('share_capital.withdrawal_notice_days', $this->share_capital_withdrawal_notice_days);
        CoopSetting::set('share_capital.dividend_rate_percent', $this->share_capital_dividend_rate_percent);
        CoopSetting::set('share_capital.dividend_payout_schedule', $this->share_capital_dividend_payout_schedule);

        CoopSetting::set('penalty.late_payment_rate_percent', $this->penalty_late_payment_rate_percent);
        CoopSetting::set('penalty.grace_period_days', $this->penalty_grace_period_days);
        CoopSetting::set('penalty.maximum_penalty_cap_percent', $this->penalty_maximum_penalty_cap_percent);
        CoopSetting::set('penalty.apply_compound_penalty', $this->penalty_apply_compound_penalty ? 'true' : 'false');
        CoopSetting::set('penalty.missed_contribution_penalty', $this->penalty_missed_contribution_penalty);
        CoopSetting::set('penalty.penalty_waiver_allowed', $this->penalty_waiver_allowed ? 'true' : 'false');
        CoopSetting::set('penalty.penalty_calculation_method', $this->penalty_calculation_method);

        CoopSetting::set('loan.default_interest_rate_percent', $this->loan_default_interest_rate_percent);
        CoopSetting::set('loan.maximum_loan_multiplier', $this->loan_maximum_loan_multiplier);
        CoopSetting::set('loan.minimum_loan_amount', $this->loan_minimum_loan_amount);
        CoopSetting::set('loan.maximum_loan_amount', $this->loan_maximum_loan_amount);
        CoopSetting::set('loan.maximum_term_months', $this->loan_maximum_term_months);
        CoopSetting::set('loan.loan_processing_fee_percent', $this->loan_loan_processing_fee_percent);
        CoopSetting::set('loan.minimum_membership_months_to_apply', $this->loan_minimum_membership_months_to_apply);
        CoopSetting::set('loan.require_co_maker', $this->loan_require_co_maker ? 'true' : 'false');
        CoopSetting::set('loan.co_maker_minimum_share_capital', $this->loan_co_maker_minimum_share_capital);
        CoopSetting::set('loan.interest_calculation_method', $this->loan_interest_calculation_method);
        CoopSetting::set('loan.allow_early_payment', $this->loan_allow_early_payment ? 'true' : 'false');
        CoopSetting::set('loan.early_payment_rebate_percent', $this->loan_early_payment_rebate_percent);
        CoopSetting::set('loan.loan_officer_approval_limit', $this->loan_loan_officer_approval_limit);

        CoopSetting::set('membership.registration_fee', $this->membership_registration_fee);
        CoopSetting::set('membership.annual_membership_fee', $this->membership_annual_membership_fee);
        CoopSetting::set('membership.minimum_age', $this->membership_minimum_age);
        CoopSetting::set('membership.allow_associate_membership', $this->membership_allow_associate_membership ? 'true' : 'false');
        CoopSetting::set('membership.associate_membership_fee', $this->membership_associate_membership_fee);
        CoopSetting::set('membership.probationary_period_months', $this->membership_probationary_period_months);
        CoopSetting::set('membership.require_id_verification', $this->membership_require_id_verification ? 'true' : 'false');
        CoopSetting::set('membership.auto_suspend_on_missed_contributions', $this->membership_auto_suspend_on_missed_contributions);

        CoopSetting::set('member_status.delinquent_months_threshold', $this->member_status_delinquent_months_threshold);
        CoopSetting::set('member_status.auto_mark_delinquent', $this->member_status_auto_mark_delinquent ? 'true' : 'false');

        CoopSetting::set('payment_allocation.apply_to_oldest_loan_first', $this->payment_apply_to_oldest_loan_first ? 'true' : 'false');
        CoopSetting::set('payment_allocation.allow_partial_payment', $this->payment_allow_partial_payment ? 'true' : 'false');
        CoopSetting::set('payment_allocation.minimum_partial_payment_percent', $this->payment_minimum_partial_payment_percent);
        CoopSetting::set('payment_allocation.overpayment_action', $this->payment_overpayment_action);
        CoopSetting::set('payment_allocation.auto_debit_share_capital', $this->payment_auto_debit_share_capital ? 'true' : 'false');
        CoopSetting::set('payment_allocation.share_capital_deduction_priority', $this->payment_share_capital_deduction_priority);
        CoopSetting::set('payment_allocation.payment_receipt_prefix', $this->payment_payment_receipt_prefix);

        // ORIENTATION
        CoopSetting::set('orientation.zoom_link', $this->orientation_zoom_link, 'string');
        CoopSetting::set('orientation.video_link', $this->orientation_video_link, 'string');
        CoopSetting::set('orientation.passing_score', $this->orientation_passing_score, 'integer');
        CoopSetting::set('orientation.require_for_loan', $this->orientation_require_for_loan, 'boolean');
        CoopSetting::set('orientation.questions', $this->orientation_questions, 'json');

        Notification::make()
            ->title('Coop settings saved!')
            ->success()
            ->send();

        $this->redirect(static::getUrl(), navigate: false);
    }
}
