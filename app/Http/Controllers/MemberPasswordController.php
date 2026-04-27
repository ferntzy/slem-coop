<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class MemberPasswordController extends Controller
{
    public function show(): View
    {
        return view('member.change-password');
    }

    public function update(ChangePasswordRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $user = $request->user();

        $user->forceFill([
            'password' => $validated['password'],
            'must_change_password' => false,
        ])->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Password updated successfully.',
            ]);
        }

        return redirect()
            ->route('member.password.form')
            ->with('status', 'Password updated successfully.');
    }
}
