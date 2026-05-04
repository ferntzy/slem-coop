<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MemberDetail;
use App\Models\Spouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberDetailsController extends Controller
{
    /**
     * GET /api/member-details
     */
    public function index(Request $request)
    {
        $query = MemberDetail::with([
            'profile',
            'membershipType',
            'branch',
            'spouse',
            'coMakers',
        ]);

        // 🔍 SEARCH
        if ($request->search) {
            $search = $request->search;

            $query->whereHas('profile', function ($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        // 🔃 SORT
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');

        $query->orderBy($sortBy, $sortOrder);

        // 📄 PAGINATION
        $perPage = $request->get('per_page', 10);

        return response()->json([
            'status' => true,
            'data' => $query->paginate($perPage),
        ]);
    }

    /**
     * GET /api/member-details/{id}
     */
    public function show($id)
    {
        $member = MemberDetail::with([
            'profile',
            'membershipType',
            'branch',
            'spouse',
            'coMakers',
        ])->find($id);

        if (! $member) {
            return response()->json([
                'status' => false,
                'message' => 'Member not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $member,
        ]);
    }

    /**
     * POST /api/member-details
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // ✅ CREATE MEMBER
            $member = MemberDetail::create([
                'profile_id' => $request->profile_id,
                'membership_type_id' => $request->membership_type_id,
                'branch_id' => $request->branch_id,
                'status' => $request->status,

                'employment_info' => $request->employment_info,
                'monthly_income' => $request->monthly_income,
                'occupation' => $request->occupation,
                'employer_name' => $request->employer_name,
                'monthly_income_range' => $request->monthly_income_range,

                'id_type' => $request->id_type,
                'id_number' => $request->id_number,

                'emergency_full_name' => $request->emergency_full_name,
                'emergency_phone' => $request->emergency_phone,
                'emergency_relationship' => $request->emergency_relationship,

                'years_in_coop' => $request->years_in_coop,
                'dependents_count' => $request->dependents_count,
                'children_in_school_count' => $request->children_in_school_count,
            ]);

            // ✅ SPOUSE (if naa)
            if ($request->spouse) {
                $member->spouse()->create($request->spouse);
            }

            // ✅ CO-MAKERS
            if ($request->coMakers && is_array($request->coMakers)) {
                foreach ($request->coMakers as $coMaker) {
                    $member->coMakers()->create($coMaker);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Member created successfully',
                'data' => $member->load(['spouse', 'coMakers']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /api/member-details/{id}
     */
   public function update(Request $request, $id)
{
    DB::beginTransaction();

    try {
        $member = MemberDetail::with(['profile','spouse','coMakers'])->find($id);

        if (! $member) {
            return response()->json([
                'status' => false,
                'message' => 'Member not found',
            ], 404);
        }

        /* =========================
           PROFILE (FIXED)
        ========================= */
        if ($request->profile) {
            $member->profile()->updateOrCreate(
                ['profile_id' => $member->profile_id], // depende sa FK nimo
                [
                    'first_name' => $request->profile['first_name'] ?? null,
                    'last_name' => $request->profile['last_name'] ?? null,
                    'email' => $request->profile['email'] ?? null,
                    'mobile_number' => $request->profile['mobile_number'] ?? null,
                ]
            );
        }

        /* =========================
           MEMBER DETAILS
        ========================= */
        if ($request->member) {
            $member->update([
                'occupation' => $request->member['occupation'] ?? null,
                'employer_name' => $request->member['employer_name'] ?? null,
                'monthly_income' => $request->member['monthly_income'] ?? 0,
            ]);
        }

        /* =========================
           SPOUSE
        ========================= */
        if ($request->spouse) {
            $member->spouse()->updateOrCreate(
                ['member_detail_id' => $member->id],
                [
                    'full_name' => $request->spouse['full_name'] ?? null,
                    'occupation' => $request->spouse['occupation'] ?? null,
                    'monthly_income' => $request->spouse['monthly_income'] ?? 0,
                ]
            );
        }

        /* =========================
           CO-MAKERS
        ========================= */
        if ($request->co_makers) {
            $member->coMakers()->delete();
    
            foreach ($request->co_makers as $coMaker) {
                $member->coMakers()->create([
                    'full_name' => $coMaker['full_name'] ?? null,
                    'relationship' => $coMaker['relationship'] ?? null,
                    'contact_number' => $coMaker['contact_number'] ?? null,
                ]);
            }
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Member updated successfully',
            'data' => $member->load(['profile','spouse','coMakers']),
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}
    /**
     * DELETE /api/member-details/{id}
     */
    public function destroy($id)
    {
        $member = MemberDetail::find($id);

        if (! $member) {
            return response()->json([
                'status' => false,
                'message' => 'Member not found',
            ], 404);
        }

        // delete relations first
        $member->spouse()->delete();
        $member->coMakers()->delete();

        $member->delete();

        return response()->json([
            'status' => true,
            'message' => 'Member deleted successfully',
        ]);
    }
}
