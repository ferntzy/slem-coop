import { useState, useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router';
import { useTheme } from 'next-themes';
import { AlertCircle, Loader, ArrowLeft, Eye, EyeOff, CheckCircle, XCircle } from 'lucide-react';

export function CompleteRegistration() {
    const { theme, resolvedTheme } = useTheme();
    const [mounted, setMounted] = useState(false);
    const [pageLoaded, setPageLoaded] = useState(false);
    const [searchParams] = useSearchParams();
    const [email] = useState(searchParams.get('email') ?? '');
    const [password, setPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirm, setShowConfirm] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);
    const [csrfToken, setCsrfToken] = useState('');
    const navigate = useNavigate();

    useEffect(() => {
        setMounted(true);
        const timer = setTimeout(() => setPageLoaded(true), 50);
        return () => clearTimeout(timer);
    }, []);

    useEffect(() => {
        fetch('/csrf-token')
            .then(res => res.json())
            .then(data => setCsrfToken(data.token))
            .catch(() => {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (token) setCsrfToken(token);
            });
    }, []);

    // ── Password rules ────────────────────────────────────────────────
    const rules = {
        length:   password.length >= 8,
        capital:  /[A-Z]/.test(password),
        special:  /[^A-Za-z0-9]/.test(password),
        match:    password.length > 0 && password === confirmPassword,
    };

    const allValid = Object.values(rules).every(Boolean);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');

        if (!allValid) {
            setError('Please make sure all password requirements are met.');
            return;
        }

        setIsLoading(true);

        try {
            const response = await fetch('/register/complete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'include',
                body: JSON.stringify({ email, password, password_confirmation: confirmPassword }),
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || 'Registration failed. Please try again.');
            }

            setSuccess(true);
            setTimeout(() => navigate('/coop'), 2000);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Something went wrong. Please try again.');
        } finally {
            setIsLoading(false);
        }
    };

    const isDarkTheme = mounted ? (resolvedTheme === 'dark' || theme === 'dark') : false;
    const logoSrc = isDarkTheme ? '/alt-logo.png' : '/logo.png';

    // ── Rule indicator ────────────────────────────────────────────────
    const RuleItem = ({ passed, label }: { passed: boolean; label: string }) => (
        <li className="flex items-center gap-2 text-sm">
            {passed
                ? <CheckCircle className="h-4 w-4 shrink-0 text-green-500" />
                : <XCircle className="h-4 w-4 shrink-0 text-gray-300 dark:text-gray-600" />
            }
            <span className={passed ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'}>
                {label}
            </span>
        </li>
    );

    return (
        <>
            <style>{`
                @keyframes slideInLeft  { from { opacity:0; transform:translateX(-40px); } to { opacity:1; transform:translateX(0); } }
                @keyframes slideInRight { from { opacity:0; transform:translateX(40px);  } to { opacity:1; transform:translateX(0); } }
                .animate-slide-in-left  { animation: slideInLeft  0.8s cubic-bezier(0.34,1.56,0.64,1) forwards; }
                .animate-slide-in-right { animation: slideInRight 0.8s cubic-bezier(0.34,1.56,0.64,1) forwards; }
            `}</style>

            <div className="min-h-screen bg-white dark:bg-[#090f0b] text-gray-900 dark:text-white">
                <div className="grid min-h-screen lg:grid-cols-2">

                    {/* ── Left panel ── */}
                    <section className={`hidden lg:flex relative min-h-screen flex-col justify-center px-8 py-12 overflow-hidden bg-green-900 text-white sm:px-12 lg:px-16 ${pageLoaded ? 'animate-slide-in-left' : 'opacity-0'}`}>
                        <button
                            onClick={() => navigate('/')}
                            className="absolute top-10 left-10 z-20 flex items-center gap-2 text-sm font-medium text-green-100/80 transition hover:text-white"
                        >
                            <ArrowLeft className="h-4 w-4" /> Back to Home
                        </button>

                        <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.14),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(16,185,129,0.25),_transparent_35%)]" />

                        <div className="relative z-10 max-w-xl">
                            <span className="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs uppercase tracking-[0.28em] text-white/80 shadow-sm">
                                Complete Registration
                            </span>
                            <h1 className="mt-8 text-4xl sm:text-5xl font-black tracking-tight text-white">
                                Set up your member account password.
                            </h1>
                            <p className="mt-5 max-w-xl text-base leading-8 text-green-100/65">
                                You're almost there. Create a secure password to activate your SLEM Coop membership account.
                            </p>

                            <div className="mt-12 space-y-4 text-sm text-green-200/90">
                                <p className="flex items-center gap-3">
                                    <span className="inline-flex h-8 w-8 items-center justify-center rounded-2xl bg-white/15 text-white">✓</span>
                                    Your email is pre-filled from your member application.
                                </p>
                                <p className="flex items-center gap-3">
                                    <span className="inline-flex h-8 w-8 items-center justify-center rounded-2xl bg-white/15 text-white">✓</span>
                                    Your password is encrypted and never stored in plain text.
                                </p>
                            </div>
                        </div>
                    </section>

                    {/* ── Right panel / form ── */}
                    <section className={`flex min-h-screen items-center justify-center px-6 py-12 bg-[#f7fcf8] dark:bg-[#050b08] sm:px-10 lg:px-16 ${pageLoaded ? 'animate-slide-in-right' : 'opacity-0'}`}>
                        <div className="w-full max-w-lg mx-auto flex flex-col">

                            <div className="mb-10 text-center">
                                <img src={logoSrc} alt="Logo" className="mx-auto mb-5 h-16 w-16 object-contain" />
                                <h2 className="text-3xl font-black text-gray-900 dark:text-white">Complete your registration</h2>
                                <p className="mt-3 text-sm text-gray-600 dark:text-gray-400">
                                    Create a password to activate your SLEM Coop account.
                                </p>
                            </div>

                            <div className="rounded-[2rem] border border-green-100/50 dark:border-white/10 bg-white dark:bg-[#111b17] p-8 shadow-lg">

                                {/* Success state */}
                                {success ? (
                                    <div className="flex flex-col items-center gap-4 py-8 text-center">
                                        <CheckCircle className="h-16 w-16 text-green-500" />
                                        <h3 className="text-xl font-bold text-gray-900 dark:text-white">Registration Complete!</h3>
                                        <p className="text-sm text-gray-500 dark:text-gray-400">Redirecting you to your dashboard…</p>
                                    </div>
                                ) : (
                                    <>
                                        {error && (
                                            <div className="mb-6 rounded-3xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-200">
                                                <div className="flex items-start gap-3">
                                                    <AlertCircle className="mt-0.5 h-5 w-5 shrink-0" />
                                                    <p className="font-semibold">{error}</p>
                                                </div>
                                            </div>
                                        )}

                                        <form onSubmit={handleSubmit} className="space-y-6">

                                            {/* Email — pre-filled, read-only */}
                                            <div>
                                                <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                                    Email address
                                                </label>
                                                <input
                                                    type="email"
                                                    value={email}
                                                    readOnly
                                                    className="w-full rounded-3xl border border-green-200 bg-green-50 px-5 py-3 text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-gray-400 cursor-not-allowed select-none outline-none"
                                                />
                                                <p className="mt-1.5 pl-2 text-xs text-gray-400 dark:text-gray-500">
                                                    This email is linked to your membership application.
                                                </p>
                                            </div>

                                            {/* Password */}
                                            <div>
                                                <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                                    Password
                                                </label>
                                                <div className="relative">
                                                    <input
                                                        type={showPassword ? 'text' : 'password'}
                                                        value={password}
                                                        onChange={e => setPassword(e.target.value)}
                                                        required
                                                        placeholder="••••••••"
                                                        className="w-full rounded-3xl border border-green-200 bg-white px-5 py-3 pr-12 text-gray-900 outline-none transition focus:border-green-500 disabled:opacity-60 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                                        disabled={isLoading}
                                                    />
                                                    <button
                                                        type="button"
                                                        onClick={() => setShowPassword(v => !v)}
                                                        className="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-green-600 dark:hover:text-green-400"
                                                    >
                                                        {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
                                                    </button>
                                                </div>
                                            </div>

                                            {/* Confirm Password */}
                                            <div>
                                                <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                                    Re-enter Password
                                                </label>
                                                <div className="relative">
                                                    <input
                                                        type={showConfirm ? 'text' : 'password'}
                                                        value={confirmPassword}
                                                        onChange={e => setConfirmPassword(e.target.value)}
                                                        required
                                                        placeholder="••••••••"
                                                        className="w-full rounded-3xl border border-green-200 bg-white px-5 py-3 pr-12 text-gray-900 outline-none transition focus:border-green-500 disabled:opacity-60 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                                        disabled={isLoading}
                                                    />
                                                    <button
                                                        type="button"
                                                        onClick={() => setShowConfirm(v => !v)}
                                                        className="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-green-600 dark:hover:text-green-400"
                                                    >
                                                        {showConfirm ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
                                                    </button>
                                                </div>
                                            </div>

                                            {/* Password rules checklist */}
                                            {password.length > 0 && (
                                                <ul className="rounded-2xl border border-green-100 dark:border-white/10 bg-green-50 dark:bg-white/5 px-5 py-4 space-y-2">
                                                    <RuleItem passed={rules.length}  label="At least 8 characters" />
                                                    <RuleItem passed={rules.capital} label="At least 1 uppercase letter (A–Z)" />
                                                    <RuleItem passed={rules.special} label="At least 1 special character (!@#$…)" />
                                                    <RuleItem passed={rules.match}   label="Passwords match" />
                                                </ul>
                                            )}

                                            <div className="pt-5 mt-2 border-t border-green-100 dark:border-white/10">
                                                <button
                                                    type="submit"
                                                    disabled={isLoading || !allValid}
                                                    className="flex w-full items-center justify-center gap-2 rounded-3xl bg-green-700 px-5 py-3 text-sm font-bold uppercase tracking-[0.12em] text-white shadow-xl transition hover:bg-green-800 disabled:cursor-not-allowed disabled:opacity-50"
                                                >
                                                    {isLoading && <Loader className="h-4 w-4 animate-spin" />}
                                                    {isLoading ? 'Completing registration…' : 'Complete Registration'}
                                                </button>
                                            </div>
                                        </form>

                                        <div className="mt-8 border-t border-green-100 pt-6 text-center text-sm text-gray-700 dark:text-gray-400">
                                            <p>Already have an account? <a href="/login" className="font-semibold text-green-700 dark:text-green-300 hover:underline">Sign in</a></p>
                                        </div>
                                    </>
                                )}
                            </div>

                            {/* Mobile back button */}
                            <div className="mt-12 flex justify-center lg:hidden">
                                <button
                                    type="button"
                                    onClick={() => navigate('/')}
                                    className="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                >
                                    <ArrowLeft className="h-4 w-4" /> Back to Home
                                </button>
                            </div>
                        </div>
                    </section>

                </div>
            </div>
        </>
    );
}