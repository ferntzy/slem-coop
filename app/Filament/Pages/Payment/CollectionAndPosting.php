<?php

namespace App\Filament\Pages\Payment;

use Filament\Pages\Page;

class CollectionAndPosting extends Page
{
   
    protected static bool $shouldRegisterNavigation = false;

    protected static string|\UnitEnum|null $navigationGroup = 'Payment Management';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Collection & Posting';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Collection & Posting';
    protected string $view = 'filament.pages.payment.collection-and-posting';
}