<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LoanApplication;
use App\Models\MemberDetail;
use App\Models\LoanType;
use Carbon\Carbon;

class AccountLoanEditController extends Controller
{
    // GET ALL LOANS WITH COMPLETE INFO
    public function index()
    {
        $loans = LoanApplication::with([
            'member.profile',
            'member.membershipType',
            'member.branch',
            'member.spouse',
            'member.coMakers',
            'type',
        ])->get();

        $response = $loans->map(function ($loan) {
            $member = $loan->member;

            return [
                'loan_application_id' => $loan->id,

                'member' => [
                    'id' => $member->id,
                    'full_name' => $member->profile?->full_name,
                    'first_name' => $member->profile?->first_name,
                    'middle_name' => $member->profile?->middle_name,
                    'last_name' => $member->profile?->last_name,
                    'birthdate' => $member->profile?->birthdate,
                    'age' => $member->profile?->birthdate ? Carbon::parse($member->profile->birthdate)->age : null,
                    'sex' => $member->profile?->sex,
                    'civil_status' => $member->profile?->civil_status,
                    'tin' => $member->profile?->tin,
                    'mobile_number' => $member->profile?->mobile_number,
                    'email' => $member->profile?->email,
                    'address' => $member->profile?->address,

                    'member_no' => $member->member_no,
                    'membership_type' => $member->membershipType?->name,
                    'branch' => $member->branch?->name,
                    'status' => $member->status,

                    'years_in_coop' => $member->years_in_coop,
                    'dependents_count' => $member->dependents_count,
                    'children_in_school_count' => $member->children_in_school_count,

                    // ✅ EMPLOYMENT & IDENTIFICATION
                    'occupation' => $member->occupation,
                    'employer' => $member->employer_name,
                    'employment_info' => $member->employment_info,
                    'monthly_income' => $member->monthly_income,
                    'monthly_income_range' => $member->monthly_income_range,

                    'id_type' => $member->id_type,
                    'id_number' => $member->id_number,

                    'emergency_contact' => [
                        'full_name' => $member->emergency_full_name,
                        'phone' => $member->emergency_phone,
                        'relationship' => $member->emergency_relationship,
                    ],

                    'spouse' => $member->spouse ? [
                        'full_name' => $member->spouse->full_name,
                        'birthdate' => $member->spouse->birthdate,
                        'occupation' => $member->spouse->occupation,
                        'employer' => $member->spouse->employer_name,
                        'employer_address' => $member->spouse->employer_address,
                        'income_source' => $member->spouse->source_of_income,
                        'tin' => $member->spouse->tin,
                    ] : null,

                    'co_makers' => $member->coMakers->map(function ($co) {
                        return [
                            'full_name' => $co->full_name,
                            'relationship' => $co->relationship,
                            'contact_number' => $co->contact_number,
                            'occupation' => $co->occupation,
                            'employer' => $co->employer_name,
                            'monthly_income' => $co->monthly_income,
                            'address' => $co->address,
                        ];
                    }),
                ],

                'loan_type' => [
                    'id' => $loan->type?->id,
                    'name' => $loan->type?->name,
                    'requires_collateral' => $loan->type?->requires_collateral,
                    'collateral_threshold' => $loan->type?->collateral_threshold,
                    'max_interest_rate' => $loan->type?->max_interest_rate,
                    'max_term_months' => $loan->type?->max_term_months,
                    'min_amount' => $loan->type?->min_amount,
                    'max_amount' => $loan->type?->max_amount,
                ],

                'loan_details' => [
                    'amount_requested' => $loan->amount_requested,
                    'coop_fee_total' => $loan->coop_fee_total,
                    'net_release_amount' => $loan->net_release_amount,
                    'term_months' => $loan->term_months,
                    'interest_rate_display' => $loan->interest_rate_display,
                    'status' => $loan->status,
                    'collateral_type' => $loan->collateral_type,
                    'collateral_status' => $loan->collateral_status,
                    'evaluation_notes' => $loan->evaluation_notes,
                    'bici_notes' => $loan->bici_notes,
                ],

                // ✅ CASHFLOW FULL
                'cashflow' => [
                    'salary' => $loan->salary,
                    'business_income' => $loan->business_income,
                    'remittances' => $loan->remittances,
                    'other_income' => $loan->other_income,

                    'living_expenses' => $loan->living_expenses,
                    'business_expenses' => $loan->business_expenses,
                    'existing_loan_payments' => $loan->existing_loan_payments,
                    'other_expenses' => $loan->other_expenses,

                    'cashflow_documents' => $loan->cashflow_documents,
                ],
            ];
        });

        return response()->json($response);
    }

    // GET SINGLE LOAN
    public function show($id)
    {
        $loan = LoanApplication::with([
            'member.profile',
            'member.membershipType',
            'member.branch',
            'member.spouse',
            'member.coMakers',
            'type',
        ])->find($id);

        if (!$loan) {
            return response()->json(['message' => 'Loan not found'], 404);
        }

        $member = $loan->member;

        return response()->json([
            'loan_application_id' => $loan->id,

            'member' => [
                'id' => $member->id,
                'full_name' => $member->profile?->full_name,
                'first_name' => $member->profile?->first_name,
                'middle_name' => $member->profile?->middle_name,
                'last_name' => $member->profile?->last_name,
                'birthdate' => $member->profile?->birthdate,
                'age' => $member->profile?->birthdate ? Carbon::parse($member->profile->birthdate)->age : null,
                'sex' => $member->profile?->sex,
                'civil_status' => $member->profile?->civil_status,
                'tin' => $member->profile?->tin,
                'mobile_number' => $member->profile?->mobile_number,
                'email' => $member->profile?->email,
                'address' => $member->profile?->address,

                'member_no' => $member->member_no,
                'membership_type' => $member->membershipType?->name,
                'branch' => $member->branch?->name,
                'status' => $member->status,

                'years_in_coop' => $member->years_in_coop,
                'dependents_count' => $member->dependents_count,
                'children_in_school_count' => $member->children_in_school_count,

                // ✅ EMPLOYMENT & IDENTIFICATION
                'occupation' => $member->occupation,
                'employer' => $member->employer_name,
                'employment_info' => $member->employment_info,
                'monthly_income' => $member->monthly_income,
                'monthly_income_range' => $member->monthly_income_range,

                'id_type' => $member->id_type,
                'id_number' => $member->id_number,
            ],

            'loan_type' => [
                'id' => $loan->type?->id,
                'name' => $loan->type?->name,
                'max_interest_rate' => $loan->type?->max_interest_rate,
                'max_term_months' => $loan->type?->max_term_months,
            ],

            'loan_details' => [
                'amount_requested' => $loan->amount_requested,
                'coop_fee_total' => $loan->coop_fee_total,
                'net_release_amount' => $loan->net_release_amount,
                'term_months' => $loan->term_months,
                'status' => $loan->status,
                'collateral_type' => $loan->collateral_type,
                'collateral_status' => $loan->collateral_status,
                'evaluation_notes' => $loan->evaluation_notes,
                'bici_notes' => $loan->bici_notes,
            ],

            'cashflow' => [
                'salary' => $loan->salary,
                'business_income' => $loan->business_income,
                'remittances' => $loan->remittances,
                'other_income' => $loan->other_income,

                'living_expenses' => $loan->living_expenses,
                'business_expenses' => $loan->business_expenses,
                'existing_loan_payments' => $loan->existing_loan_payments,
                'other_expenses' => $loan->other_expenses,

                'cashflow_documents' => $loan->cashflow_documents,
            ],
        ]);
    }
}
