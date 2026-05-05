<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\MemberCoMaker;
use App\Models\MemberDetail;
use App\Models\MembershipApplication;
use App\Models\MembershipType;
use App\Models\MemberSpouse;
use App\Models\Profile;
use App\Models\User;
use App\Services\MunicipalityToBranchService;
use App\Services\NotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MembershipApplicationController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    public function membershipTypes()
    {
        return response()->json(
            MembershipType::select('membership_type_id', 'name')->get()
        );
    }

    public function branches()
    {
        return response()->json(
            Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['branch_id', 'name'])
        );
    }

    public function zoomScheduleToday()
    {
        $tz = 'Asia/Manila';
        $now = \Carbon\Carbon::now($tz);
        $today = $now->toDateString();

        $raw = \App\Models\CoopSetting::get('zoom_orientation_schedules', []);
        $schedules = is_array($raw) ? $raw : (json_decode($raw ?: '[]', true) ?: []);

        $globalLink = (string) \App\Models\CoopSetting::get('zoom_link', '');

        $todays = [];
        $upcoming = [];
        foreach ($schedules as $s) {
            $date = (string) ($s['date'] ?? '');
            $start = (string) ($s['start_time'] ?? '');
            $end = (string) ($s['end_time'] ?? '');
            if ($date === '' || $start === '') {
                continue;
            }
            $startAt = \Carbon\Carbon::parse("$date $start", $tz);
            $endAt = $end !== '' ? \Carbon\Carbon::parse("$date $end", $tz) : $startAt->copy()->addHour();
            $link = (string) ($s['zoom_link'] ?? '') ?: $globalLink;

            if ($date === $today && $endAt->gt($now)) {
                $todays[] = compact('startAt', 'endAt', 'link') + ['date' => $date, 'start_time' => $start, 'end_time' => $end];
            } elseif ($startAt->gt($now)) {
                $upcoming[] = compact('startAt') + ['date' => $date, 'start_time' => $start];
            }
        }

        usort($todays, fn ($a, $b) => $a['startAt'] <=> $b['startAt']);
        usort($upcoming, fn ($a, $b) => $a['startAt'] <=> $b['startAt']);

        if (! empty($todays)) {
            $t = $todays[0];

            return response()->json([
                'available' => true,
                'date' => $t['date'],
                'start_time' => $t['start_time'],
                'end_time' => $t['end_time'],
                'zoom_link' => $t['link'],
            ]);
        }

        return response()->json([
            'available' => false,
            'next' => empty($upcoming) ? null : [
                'date' => $upcoming[0]['date'],
                'start_time' => $upcoming[0]['start_time'],
            ],
        ]);
    }

    public function municipalities()
    {
        $mapping = MunicipalityToBranchService::getMunicipalitiesToBranchesMapping();
        $activeBranchNames = Branch::where('is_active', true)->pluck('name')->all();

        $list = [];
        foreach ($mapping as $branchName => $municipalities) {
            if (! in_array($branchName, $activeBranchNames, true)) {
                continue;
            }
            foreach ((array) $municipalities as $municipality) {
                $name = trim((string) $municipality);
                if ($name !== '') {
                    $list[] = $name;
                }
            }
        }

        $list = array_values(array_unique($list));
        sort($list, SORT_NATURAL | SORT_FLAG_CASE);

        return response()->json($list);
    }

    public function resolveBranchByMunicipality(Request $request)
    {
        $municipality = $request->query('municipality');

        if (! $municipality) {
            return response()->json(['error' => 'Municipality parameter is required.'], 400);
        }

        $branchId = MunicipalityToBranchService::getBranchIdByMunicipality($municipality);

        if (! $branchId) {
            return response()->json(['error' => 'Municipality not mapped to any branch.'], 404);
        }

        $branch = Branch::where('branch_id', $branchId)->where('is_active', true)->first();

        if (! $branch) {
            return response()->json(['error' => 'Branch not found or inactive.'], 404);
        }

        return response()->json(['branch_id' => $branch->branch_id, 'name' => $branch->name]);
    }

    public function store(Request $request)
    {
        // Validation block
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:100',
                'middle_name' => 'nullable|string|max:45',
                'last_name' => 'required|string|max:45',
                'email' => 'required|email',
                'mobile_number' => 'required|string|max:45',
                'birthdate' => 'required|date',
                'sex' => 'nullable|in:Male,Female',
                'civil_status' => 'nullable|string|max:50',
                'id_type' => 'nullable|string|max:50',
                'id_number' => 'nullable|string|max:100',
                'house_no' => 'nullable|string|max:255',
                'street_barangay' => 'nullable|string|max:255',
                'municipality' => 'required|string|max:255',
                'province' => 'nullable|string|max:255',
                'zip_code' => 'nullable|string|max:10',
                'occupation' => 'nullable|string|max:100',
                'employer_name' => 'nullable|string|max:150',
                'monthly_income_range' => 'nullable|string|max:50',
                'source_of_income' => 'nullable|string|max:50',
                'monthly_income' => 'nullable|numeric',
                'years_in_business' => 'nullable|integer|min:0',
                'emergency_full_name' => 'nullable|string|max:150',
                'emergency_phone' => 'nullable|string|max:50',
                'emergency_relationship' => 'nullable|string|max:50',
                'dependents_count' => 'nullable|integer|min:0',
                'children_in_school_count' => 'nullable|integer|min:0',
                'membership_type_id' => 'required|exists:membership_types,membership_type_id',
                'branch_id' => 'nullable|exists:branches,branch_id',
                'application_date' => 'required|date',
                'remarks' => 'nullable|string',
                'orientation_zoom_attended' => 'required|in:true,false,1,0',
                'orientation_video_completed' => 'required|in:true,false,1,0',
                'orientation_assessment_passed' => 'required|in:true,false,1,0',
                'orientation_certificate_generated' => 'nullable|in:true,false,1,0',
                'orientation_score' => 'nullable|integer|min:0|max:100',
                'id_document_front' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
                'id_document_back' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
                // Spouse data
                'spouse_full_name' => 'nullable|string|max:255',
                'spouse_birthdate' => 'nullable|date',
                'spouse_occupation' => 'nullable|string|max:255',
                'spouse_employer_name' => 'nullable|string|max:255',
                'spouse_source_of_income' => 'nullable|string|max:255',
                'spouse_monthly_income' => 'nullable|numeric',
                // Co-makers data
                'co_makers' => 'nullable|json',
            ]);

            // Auto-resolve branch from municipality if not provided
            if (! $validated['branch_id']) {
                $resolvedBranchId = MunicipalityToBranchService::getBranchIdByMunicipality($validated['municipality']);
                if (! $resolvedBranchId) {
                    throw ValidationException::withMessages([
                        'municipality' => 'The municipality is not mapped to any branch. Please contact support.',
                    ]);
                }
                $validated['branch_id'] = $resolvedBranchId;
            }

            $zoomAttended = filter_var($request->input('orientation_zoom_attended'), FILTER_VALIDATE_BOOLEAN);
            $videoCompleted = filter_var($request->input('orientation_video_completed'), FILTER_VALIDATE_BOOLEAN);
            $assessmentPassed = filter_var($request->input('orientation_assessment_passed'), FILTER_VALIDATE_BOOLEAN);
            $certificateGenerated = filter_var($request->input('orientation_certificate_generated'), FILTER_VALIDATE_BOOLEAN);

            if (! ($zoomAttended && $videoCompleted && $assessmentPassed)) {
                return response()->json([
                    'message' => 'Orientation must be completed before submitting the application.',
                ], 422);
            }

            return DB::transaction(function () use (
                $validated,
                $request,
                $zoomAttended,
                $videoCompleted,
                $assessmentPassed,
                $certificateGenerated
            ) {
                $fullAddress = collect([
                    $validated['house_no'] ?? null,
                    $validated['street_barangay'] ?? null,
                    $validated['municipality'] ?? null,
                    $validated['province'] ?? null,
                    $validated['zip_code'] ?? null,
                ])->filter()->implode(', ');

                // Use user_id (your custom PK), fall back to system user 1 for guests
                $userId = auth()->user()?->user_id ?? 1;

                // Create or get profile using firstOrCreate to avoid race conditions
                $profile = Profile::firstOrCreate(
                    ['email' => $validated['email']],
                    [
                        'first_name' => $validated['first_name'],
                        'middle_name' => $validated['middle_name'] ?? null,
                        'last_name' => $validated['last_name'],
                        'mobile_number' => $validated['mobile_number'],
                        'birthdate' => $validated['birthdate'],
                    ]
                );

                // Create application
                $application = MembershipApplication::create([
                    'profile_id' => $profile->profile_id,
                    'first_name' => $validated['first_name'],
                    'middle_name' => $validated['middle_name'] ?? null,
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'mobile_number' => $validated['mobile_number'],
                    'birthdate' => $validated['birthdate'],
                    'sex' => $validated['sex'] ?? null,
                    'civil_status' => $validated['civil_status'] ?? null,
                    'address' => $fullAddress ?: null,
                    'house_no' => $validated['house_no'] ?? null,
                    'street_barangay' => $validated['street_barangay'] ?? null,
                    'municipality' => $validated['municipality'] ?? null,
                    'province' => $validated['province'] ?? null,
                    'zip_code' => $validated['zip_code'] ?? null,
                    'id_type' => $validated['id_type'] ?? null,
                    'id_number' => $validated['id_number'] ?? null,
                    'occupation' => $validated['occupation'] ?? null,
                    'employer_name' => $validated['employer_name'] ?? null,
                    'monthly_income_range' => $validated['monthly_income_range'] ?? null,
                    'source_of_income' => $validated['source_of_income'] ?? null,
                    'monthly_income' => $validated['monthly_income'] ?? null,
                    'years_in_business' => $validated['years_in_business'] ?? null,
                    'emergency_full_name' => $validated['emergency_full_name'] ?? null,
                    'emergency_phone' => $validated['emergency_phone'] ?? null,
                    'emergency_relationship' => $validated['emergency_relationship'] ?? null,
                    'dependents_count' => $validated['dependents_count'] ?? null,
                    'children_in_school_count' => $validated['children_in_school_count'] ?? null,
                    'membership_type_id' => $validated['membership_type_id'],
                    'application_date' => $validated['application_date'],
                    'status' => 'pending',
                    'remarks' => $validated['remarks'] ?? null,
                    'id_document_front' => $request->file('id_document_front')?->store('applications/id_documents', 'public'),
                    'id_document_back' => $request->file('id_document_back')?->store('applications/id_documents', 'public'),
                    'orientation_zoom_attended' => $zoomAttended,
                    'orientation_video_completed' => $videoCompleted,
                    'orientation_assessment_passed' => $assessmentPassed,
                    'orientation_certificate_generated' => $certificateGenerated,
                    'orientation_score' => $request->input('orientation_score') ?? null,
                    'created_by' => $userId,
                ]);

                // Create member detail
                $memberDetail = MemberDetail::create([
                    'profile_id' => $profile->profile_id,
                    'membership_type_id' => $validated['membership_type_id'],
                    'branch_id' => (int) $validated['branch_id'],
                    'status' => 'Active',
                    'occupation' => $validated['occupation'] ?? null,
                    'employer_name' => $validated['employer_name'] ?? null,
                    'monthly_income_range' => $validated['monthly_income_range'] ?? null,
                    'monthly_income' => $validated['monthly_income'] ?? null,
                    'id_type' => $validated['id_type'] ?? null,
                    'id_number' => $validated['id_number'] ?? null,
                    'emergency_full_name' => $validated['emergency_full_name'] ?? null,
                    'emergency_phone' => $validated['emergency_phone'] ?? null,
                    'emergency_relationship' => $validated['emergency_relationship'] ?? null,
                    'dependents_count' => $validated['dependents_count'] ?? null,
                    'children_in_school_count' => $validated['children_in_school_count'] ?? null,
                    'source_of_income' => $validated['source_of_income'] ?? null,
                    'house_no' => $validated['house_no'] ?? null,
                    'street_barangay' => $validated['street_barangay'] ?? null,
                    'municipality' => $validated['municipality'] ?? null,
                    'province' => $validated['province'] ?? null,
                    'zip_code' => $validated['zip_code'] ?? null,
                    'years_in_business' => $validated['years_in_business'] ?? null,
                ]);

                // Save spouse data if provided
                if (! empty($validated['spouse_full_name'])) {
                    MemberSpouse::create([
                        'member_detail_id' => $memberDetail->id,
                        'full_name' => $validated['spouse_full_name'],
                        'birthdate' => $validated['spouse_birthdate'] ?? null,
                        'occupation' => $validated['spouse_occupation'] ?? null,
                        'employer_name' => $validated['spouse_employer_name'] ?? null,
                        'source_of_income' => $validated['spouse_source_of_income'] ?? null,
                        'monthly_income' => $validated['spouse_monthly_income'] ?? null,
                    ]);
                }

                // Save co-makers data if provided
                if (! empty($validated['co_makers'])) {
                    $coMakers = is_string($validated['co_makers'])
                        ? json_decode($validated['co_makers'], true)
                        : $validated['co_makers'];

                    if (is_array($coMakers)) {
                        foreach ($coMakers as $coMaker) {
                            if (! empty($coMaker['full_name'])) {
                                MemberCoMaker::create([
                                    'member_detail_id' => $memberDetail->id,
                                    'full_name' => $coMaker['full_name'],
                                    'relationship' => $coMaker['relationship'] ?? null,
                                    'contact_number' => $coMaker['contact_number'] ?? null,
                                    'address' => $coMaker['address'] ?? null,
                                    'occupation' => $coMaker['occupation'] ?? null,
                                    'employer_name' => $coMaker['employer_name'] ?? null,
                                    'monthly_income' => $coMaker['monthly_income'] ?? null,
                                ]);
                            }
                        }
                    }
                }

                // Notify the applicant if they have an existing user account
                $user = User::where('profile_id', $profile->profile_id)->first();

                if ($user) {
                    $this->notificationService->notifyUserWithPush(
                        $user->user_id,
                        'Application received',
                        'Your membership application has been received and is pending review.',
                        notifiableType: 'membership_application',
                        notifiableId: $application->id
                    );
                }

                return response()->json([
                    'message' => 'Application submitted successfully.',
                    'application_id' => $application->id,
                    'member_detail_id' => $memberDetail->id,
                ], 201);
            });

        } catch (ValidationException $e) {
            // Return validation errors clearly to the frontend
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (QueryException $e) {
            $errorMsg = $e->getMessage();
            \Log::error('Database error during membership application: '.$errorMsg, [
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Database error. Please contact support.',
                'error' => $errorMsg,
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Membership application error: '.$e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to submit application.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
