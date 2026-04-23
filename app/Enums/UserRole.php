<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Librarian = 'librarian';
    case Employee = 'employee';
    case Member = 'member';
    case HQManager = 'hq_manager';
    case HQTeller = 'hq_teller';
    case HQCashier = 'hq_cashier';
    case HQLoanOfficer = 'hq_loan_officer';
    case HQAccountOfficer = 'hq_account_officer';
}
