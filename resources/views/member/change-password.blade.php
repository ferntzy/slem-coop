<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(20,184,166,0.18),_transparent_32%),linear-gradient(180deg,_#f8fafc_0%,_#eff6ff_100%)] text-slate-900">
    <main class="relative mx-auto flex min-h-screen max-w-6xl items-center px-4 py-10 sm:px-6 lg:px-8">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -left-24 top-10 h-64 w-64 rounded-full bg-teal-300/20 blur-3xl"></div>
            <div class="absolute -right-16 bottom-0 h-72 w-72 rounded-full bg-cyan-300/20 blur-3xl"></div>
        </div>

        <section class="relative grid w-full overflow-hidden rounded-[2rem] bg-white/85 shadow-[0_30px_90px_-30px_rgba(15,23,42,0.35)] ring-1 ring-white/60 backdrop-blur xl:grid-cols-[1.05fr_0.95fr]">
            <div class="bg-gradient-to-br from-teal-700 via-teal-600 to-cyan-600 px-7 py-10 sm:px-10 sm:py-12">
                <div class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-white/80">
                    Member Account
                </div>
                <p class="mt-4 text-xs font-semibold uppercase tracking-[0.28em] text-white/70">Member Password Update</p>
                <h1 class="mt-6 text-4xl font-black tracking-tight text-white sm:text-5xl">
                    Change Password
                </h1>
                <p class="mt-4 max-w-xl text-sm leading-7 text-white/82 sm:text-base">
                    Replace the temporary password from your approval email with a new one you can use going forward.
                </p>

                <div class="mt-10 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white shadow-lg shadow-teal-950/10">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/60">Step 1</p>
                        <p class="mt-2 text-sm font-medium">Enter your temporary password.</p>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white shadow-lg shadow-teal-950/10">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/60">Step 2</p>
                        <p class="mt-2 text-sm font-medium">Choose a stronger password.</p>
                    </div>
                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white shadow-lg shadow-teal-950/10">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/60">Step 3</p>
                        <p class="mt-2 text-sm font-medium">Save and continue to your account.</p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-8 sm:px-10 sm:py-12">
                @if (session('status'))
                    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-800">
                        <p class="font-semibold">Please review the highlighted fields.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('member.password.update') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="current_password" class="block text-sm font-semibold text-slate-700">Current Password</label>
                        <p class="mt-1 text-xs text-slate-500">Use the temporary password sent to your email.</p>
                        <input
                            id="current_password"
                            name="current_password"
                            type="password"
                            autocomplete="current-password"
                            required
                            value="{{ old('current_password') }}"
                            class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                        >
                        @error('current_password')
                            <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700">New Password</label>
                        <p class="mt-1 text-xs text-slate-500">Use at least 8 characters and make it different from the current one.</p>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                        >
                        @error('password')
                            <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Confirm New Password</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                        >
                        @error('password_confirmation')
                            <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/15 transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-200"
                    >
                        Save New Password
                    </button>
                </form>

                <div class="mt-8 grid gap-4 rounded-3xl bg-slate-50 p-5 ring-1 ring-slate-200 sm:grid-cols-2">
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-teal-700">Security note</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Once updated, your temporary password is no longer valid and the first-login flag is cleared.
                        </p>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-teal-700">Need help?</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            If you did not receive the temporary password email, contact the coop office before trying again.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>