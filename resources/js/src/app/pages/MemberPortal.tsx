import { useEffect, useState, type FormEvent } from 'react';
import { Link } from 'react-router-dom';
import { toast } from 'sonner';
import { CheckCircle2, Eye, EyeOff, Loader2, LockKeyhole, ShieldCheck, User } from 'lucide-react';
import { Card, CardContent } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';

export function MemberPortal() {
  const [authenticated, setAuthenticated] = useState(false);
  const [loadingAuth, setLoadingAuth] = useState(true);
  const [mustChangePassword, setMustChangePassword] = useState(false);
  const [saving, setSaving] = useState(false);
  const [csrfToken, setCsrfToken] = useState('');
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showCurrentPassword, setShowCurrentPassword] = useState(false);
  const [showNewPassword, setShowNewPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);

  useEffect(() => {
    fetch('/csrf-token')
      .then((res) => res.json())
      .then((data) => setCsrfToken(data.token ?? ''))
      .catch(() => {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) {
          setCsrfToken(token);
        }
      });

    fetch('/api/auth-status', { credentials: 'include' })
      .then((res) => res.json())
      .then((data) => {
        setAuthenticated(Boolean(data.authenticated));
        setMustChangePassword(Boolean(data.must_change_password));
      })
      .catch(() => setAuthenticated(false))
      .finally(() => setLoadingAuth(false));
  }, []);

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setSaving(true);

    try {
      const response = await fetch('/coop/change-password', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken,
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
        },
        credentials: 'include',
        body: JSON.stringify({
          current_password: currentPassword,
          password: newPassword,
          password_confirmation: confirmPassword,
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Unable to update password.');
      }

      toast.success(data.message || 'Password updated successfully.');
      setCurrentPassword('');
      setNewPassword('');
      setConfirmPassword('');
    } catch (error) {
      const message = error instanceof Error ? error.message : 'Password update failed.';
      toast.error(message);
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="flex flex-col bg-white dark:bg-[#0a0f0c] text-gray-900 dark:text-white transition-colors duration-500">
      <section className="relative min-h-[60dvh] flex items-center justify-center overflow-hidden">
        <div className="absolute inset-0 bg-[url('/src/images/bghd.jpg')] bg-cover bg-center" />
        <div className="absolute inset-0 bg-gradient-to-br from-white/90 via-green-50/85 to-green-100/95 dark:from-[#022c22]/95 dark:via-[#064e3b]/95 dark:to-[#065f46]/95" />
        <div className="absolute inset-0 overflow-hidden pointer-events-none">
          {Array.from({ length: 18 }, (_, index) => (
            <div
              key={index}
              className="absolute rounded-full bg-green-300/40 dark:bg-green-400/20"
              style={{
                width: `${6 + (index % 4) * 3}px`,
                height: `${6 + (index % 4) * 3}px`,
                left: `${(index * 13) % 100}%`,
                bottom: '-10px',
                animation: `floatUp ${12 + (index % 5)}s ${index * 0.3}s infinite linear`,
              }}
            />
          ))}
        </div>

        <div className="relative z-10 max-w-6xl mx-auto px-6 text-center">
          <div className="inline-flex items-center gap-2 mb-6 px-4 py-2 rounded-full bg-green-200/50 dark:bg-white/10 border border-green-300 dark:border-white/20 backdrop-blur-md">
            <ShieldCheck className="w-4 h-4 text-green-700 dark:text-green-300" />
            <span className="text-xs sm:text-sm text-green-900 dark:text-white/90 font-medium uppercase tracking-widest">
              {mustChangePassword ? 'Password Change Required' : 'Member Portal'}
            </span>
          </div>
          <h1 className="text-5xl sm:text-7xl font-extrabold mb-6 uppercase tracking-tight text-gray-900 dark:text-white leading-[0.9]">
            Secure Your <br className="sm:hidden" />
            <span className="bg-clip-text text-transparent bg-gradient-to-r from-green-700 to-green-500 dark:from-green-400 dark:to-green-200">Account</span>
          </h1>
          <p className="text-lg sm:text-xl text-gray-700 dark:text-white/80 max-w-2xl mx-auto font-medium leading-relaxed">
            {mustChangePassword
              ? 'Your approval email included a temporary password. You must change it now before continuing.'
              : 'Your approval email included a temporary password. Change it here after your first login to keep your account secure.'}
          </p>
        </div>
      </section>

      <section className="py-20 bg-green-50/30 dark:bg-[#0d1410] px-6 transition-colors duration-500">
        <div className="max-w-6xl mx-auto grid lg:grid-cols-[1.1fr_0.9fr] gap-8 items-start">
          <Card className="rounded-[2.5rem] border border-green-100 dark:border-white/10 bg-white dark:bg-[#111b17] shadow-sm overflow-hidden">
            <CardContent className="p-8 md:p-10">
              <div className="flex items-center gap-3 mb-6">
                <div className="w-12 h-12 rounded-2xl bg-green-600 text-white flex items-center justify-center shadow-lg shadow-green-500/20">
                  <User className="w-6 h-6" />
                </div>
                <div>
                  <h2 className="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Account Security</h2>
                  <p className="text-sm text-gray-500 dark:text-gray-400">Update the password sent during approval.</p>
                </div>
              </div>

              <div className="grid sm:grid-cols-2 gap-4 mb-8">
                <div className="rounded-2xl border border-green-100 dark:border-green-900/40 bg-green-50/50 dark:bg-green-500/10 p-4">
                  <div className="flex items-center gap-2 text-green-700 dark:text-green-300 font-black uppercase tracking-widest text-[11px] mb-2">
                    <CheckCircle2 className="w-4 h-4" />
                    Why this matters
                  </div>
                  <p className="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    Temporary passwords should only be used once. Changing it right away protects your member account and personal data.
                  </p>
                </div>
                <div className="rounded-2xl border border-green-100 dark:border-green-900/40 bg-green-50/50 dark:bg-green-500/10 p-4">
                  <div className="flex items-center gap-2 text-green-700 dark:text-green-300 font-black uppercase tracking-widest text-[11px] mb-2">
                    <LockKeyhole className="w-4 h-4" />
                    After approval
                  </div>
                  <p className="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    Use the email-linked temporary password for your first login, then replace it here with a password only you know.
                  </p>
                </div>
              </div>

              {loadingAuth ? (
                <div className="rounded-3xl border border-dashed border-green-200 dark:border-green-900/60 bg-green-50/30 dark:bg-[#0d1410] p-10 text-center text-gray-500 dark:text-gray-400">
                  Checking your session...
                </div>
              ) : !authenticated ? (
                <div className="rounded-3xl border border-green-200 dark:border-green-900/60 bg-green-50/30 dark:bg-[#0d1410] p-10 text-center">
                  <p className="text-lg font-bold text-gray-900 dark:text-white mb-3">Sign in to access your member portal.</p>
                  <p className="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Once you log in with your approved account, you can change your temporary password here.
                  </p>
                  <Button asChild className="rounded-full bg-green-700 hover:bg-green-800 text-white font-black uppercase tracking-widest text-xs px-8 py-3">
                    <Link to="/login">Go to Login</Link>
                  </Button>
                </div>
              ) : (
                <form className="space-y-6" onSubmit={handleSubmit}>
                  <div className="space-y-2">
                    <Label className="text-xs font-black uppercase tracking-widest text-gray-600 dark:text-gray-400">Temporary Password / Current Password</Label>
                    <div className="relative">
                      <Input
                        type={showCurrentPassword ? 'text' : 'password'}
                        value={currentPassword}
                        onChange={(event) => setCurrentPassword(event.target.value)}
                        placeholder="Enter your current password"
                        className="h-12 rounded-2xl border-green-200 dark:border-green-900/50 bg-white dark:bg-[#0d1410] pr-12"
                        required
                      />
                      <button
                        type="button"
                        onClick={() => setShowCurrentPassword((current) => !current)}
                        className="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-green-600 dark:hover:text-green-400"
                      >
                        {showCurrentPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                      </button>
                    </div>
                  </div>

                  <div className="space-y-2">
                    <Label className="text-xs font-black uppercase tracking-widest text-gray-600 dark:text-gray-400">New Password</Label>
                    <div className="relative">
                      <Input
                        type={showNewPassword ? 'text' : 'password'}
                        value={newPassword}
                        onChange={(event) => setNewPassword(event.target.value)}
                        placeholder="Create a new password"
                        className="h-12 rounded-2xl border-green-200 dark:border-green-900/50 bg-white dark:bg-[#0d1410] pr-12"
                        required
                        minLength={8}
                      />
                      <button
                        type="button"
                        onClick={() => setShowNewPassword((current) => !current)}
                        className="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-green-600 dark:hover:text-green-400"
                      >
                        {showNewPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                      </button>
                    </div>
                  </div>

                  <div className="space-y-2">
                    <Label className="text-xs font-black uppercase tracking-widest text-gray-600 dark:text-gray-400">Confirm New Password</Label>
                    <div className="relative">
                      <Input
                        type={showConfirmPassword ? 'text' : 'password'}
                        value={confirmPassword}
                        onChange={(event) => setConfirmPassword(event.target.value)}
                        placeholder="Repeat the new password"
                        className="h-12 rounded-2xl border-green-200 dark:border-green-900/50 bg-white dark:bg-[#0d1410] pr-12"
                        required
                        minLength={8}
                      />
                      <button
                        type="button"
                        onClick={() => setShowConfirmPassword((current) => !current)}
                        className="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-green-600 dark:hover:text-green-400"
                      >
                        {showConfirmPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                      </button>
                    </div>
                  </div>

                  <div className="pt-2 flex items-center gap-3">
                    <Button
                      type="submit"
                      disabled={saving}
                      className="rounded-full bg-green-700 hover:bg-green-800 text-white font-black uppercase tracking-widest text-xs px-8 py-3 shadow-lg shadow-green-500/20"
                    >
                      {saving && <Loader2 className="w-4 h-4 animate-spin" />}
                      {saving ? 'Updating...' : 'Change Password'}
                    </Button>
                    <p className="text-[11px] uppercase tracking-widest text-gray-400 dark:text-gray-500 font-bold">
                      Use at least 8 characters.
                    </p>
                  </div>
                </form>
              )}
            </CardContent>
          </Card>

          <div className="space-y-6">
            <Card className="rounded-[2.5rem] border border-green-100 dark:border-white/10 bg-gradient-to-br from-green-100 via-green-50 to-green-200 dark:from-[#022c22] dark:via-[#047857] dark:to-[#064e3b] shadow-xl overflow-hidden">
              <CardContent className="p-8 md:p-10">
                <div className="inline-flex items-center gap-2 mb-5 px-3 py-1.5 rounded-full bg-white/25 backdrop-blur-md text-white text-[10px] font-black uppercase tracking-widest">
                  <ShieldCheck className="w-4 h-4" />
                  Security Reminder
                </div>
                <h2 className="text-2xl font-black text-green-950 dark:text-white uppercase tracking-tight mb-4">
                  Protect your member login.
                </h2>
                <p className="text-green-900/80 dark:text-white/80 leading-relaxed">
                  If you received a temporary password by email, that password should only be used for your first sign-in. Update it immediately and keep it private.
                </p>
              </CardContent>
            </Card>

            <Card className="rounded-[2.5rem] border border-green-100 dark:border-white/10 bg-white dark:bg-[#111b17] shadow-sm">
              <CardContent className="p-8 md:p-10 space-y-4">
                <h3 className="text-xs font-black uppercase tracking-widest text-green-600 dark:text-green-400">Member Account Tips</h3>
                <div className="space-y-4 text-sm text-gray-600 dark:text-gray-400">
                  <p>Use a password you have not used on other services.</p>
                  <p>Keep your temporary password only until the first successful login.</p>
                  <p>After changing your password, sign out on shared devices.</p>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </section>

      <style>{`
        @keyframes floatUp { 0% { transform: translateY(0) scale(1); opacity: 0.2; } 100% { transform: translateY(-100vh) scale(0.5); opacity: 0; } }
      `}</style>
    </div>
  );
}