<?php

namespace App\Filament\Resources\PaymentAllocationLogics\Pages;

use App\Filament\Resources\PaymentAllocationLogics\PaymentAllocationLogicResource;
use Filament\Resources\Pages\Page;

class PaymentAllocationDashboard extends Page
{
    protected static string $resource = PaymentAllocationLogicResource::class;

    protected string $view = 'filament.resources.payment-allocation-logics.pages.payment-allocation-dashboard';
}
