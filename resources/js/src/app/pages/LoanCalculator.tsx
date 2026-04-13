import { useState, useEffect, useRef } from 'react';
import { Calculator, ChevronLeft, ChevronRight, TrendingDown, Wallet, Clock, AlertCircle, Lock } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../components/ui/select';

const formatPeso = (amount: number) =>
  '₱' + amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const ROWS_PER_PAGE = 12;

const LOAN_TYPE_CONFIG: Record<string, {
  label: string;
  rate: number;
  maxMonths: number;
  maxAmount: number | null;
  amountSliderMax: number;
  amountStep: number;
  note: string;
}> = {
  ecash: {
    label: 'E-Cash Loan',
    rate: 3.0,
    maxMonths: 24,
    maxAmount: null,
    amountSliderMax: 500000,
    amountStep: 5000,
    note: 'No fixed loan limit. Collateral required for loans above ₱15,000.',
  },
  guaranteed: {
    label: 'Guaranteed Loan',
    rate: 2.0,
    maxMonths: 24,
    maxAmount: null,
    amountSliderMax: 500000,
    amountStep: 5000,
    note: 'Maximum loanable amount is Share Capital × 2.',
  },
  emergency: {
    label: 'Instant / Emergency Loan',
    rate: 3.0,
    maxMonths: 3,
    maxAmount: 15000,
    amountSliderMax: 15000,
    amountStep: 500,
    note: 'Maximum ₱15,000. Term up to 3 months only. Maximum 3% interest.',
  },
};

/* ─── Particles (reused from MembershipSteps) ─── */
function Particles() {
  const colorClasses = [
    'bg-green-300 dark:bg-green-600',
    'bg-green-400 dark:bg-green-500',
    'bg-green-200 dark:bg-green-700',
    'bg-green-500 dark:bg-green-800',
  ];
  const particles = Array.from({ length: 24 }, (_, i) => ({
    id: i,
    size: Math.random() * 5 + 3,
    x: Math.random() * 100,
    delay: Math.random() * 8,
    duration: Math.random() * 10 + 12,
    opacity: Math.random() * 0.5 + 0.2,
    colorClass: colorClasses[Math.floor(Math.random() * colorClasses.length)],
  }));
  return (
    <div className="absolute inset-0 overflow-hidden pointer-events-none">
      {particles.map((p) => (
        <div
          key={p.id}
          className={`absolute rounded-full ${p.colorClass}`}
          style={{
            width: p.size,
            height: p.size,
            left: `${p.x}%`,
            bottom: '-10px',
            opacity: p.opacity,
            animation: `floatUp ${p.duration}s ${p.delay}s infinite linear`,
          }}
        />
      ))}
    </div>
  );
}

function useInView() {
  const ref = useRef<HTMLElement | null>(null);
  const [inView, setInView] = useState(false);
  useEffect(() => {
    const el = ref.current;
    if (!el) return;
    const obs = new IntersectionObserver(([e]) => {
      if (e.isIntersecting) { setInView(true); obs.disconnect(); }
    }, { threshold: 0.1 });
    obs.observe(el);
    return () => obs.disconnect();
  }, []);
  return [ref, inView] as const;
}

export function LoanCalculator() {
  const [loanAmount, setLoanAmount] = useState<number>(0);
  const [loanAmountInput, setLoanAmountInput] = useState<string>('0');
  const [loanTerm, setLoanTerm] = useState<number>(12);
  const [loanType, setLoanType] = useState<string>('ecash');
  const [heroVisible, setHeroVisible] = useState(false);
  const [schedPage, setSchedPage] = useState(1);
  const [calcRef, calcInView] = useInView();
  const [schedRef, schedInView] = useInView();

  useEffect(() => {
    const t = setTimeout(() => setHeroVisible(true), 100);
    return () => clearTimeout(t);
  }, []);

  useEffect(() => { setSchedPage(1); }, [loanAmount, loanTerm, loanType]);

  const config = LOAN_TYPE_CONFIG[loanType];
  const interestRate = config.rate;

  const handleLoanTypeChange = (value: string) => {
    setLoanType(value);
    const cfg = LOAN_TYPE_CONFIG[value];
    setLoanTerm(prev => Math.min(prev, cfg.maxMonths));
    if (cfg.maxAmount !== null && loanAmount > cfg.maxAmount) {
      setLoanAmount(cfg.maxAmount);
      setLoanAmountInput(String(cfg.maxAmount));
    }
  };

  const handleAmountChange = (raw: string) => {
    if (raw === '' || raw === undefined) {
      setLoanAmountInput('');
      setLoanAmount(0);
      return;
    }
    const stripped = raw.replace(/^0+(\d)/, '$1');
    const val = parseInt(stripped, 10);
    if (isNaN(val)) return;
    const clamped = config.maxAmount !== null ? Math.min(val, config.maxAmount) : val;
    setLoanAmountInput(String(clamped));
    setLoanAmount(clamped);
  };

  const handleTermChange = (raw: string) => {
    const val = parseInt(raw, 10);
    if (!isNaN(val)) setLoanTerm(Math.min(Math.max(1, val), config.maxMonths));
  };

  const generateSchedule = () => {
    if (loanAmount <= 0 || loanTerm <= 0) return { schedule: [], firstPayment: 0, totalInterest: 0 };
    const schedule: Array<{
      month: number; payment: number; principal: number; interest: number; balance: number;
    }> = [];
    const principalPayment = loanAmount / loanTerm;
    let balance = loanAmount;
    let totalInterest = 0;
    for (let month = 1; month <= loanTerm; month++) {
      const interest = balance * (interestRate / 100);
      const payment = principalPayment + interest;
      totalInterest += interest;
      balance = Math.max(balance - principalPayment, 0);
      schedule.push({ month, payment, principal: principalPayment, interest, balance });
    }
    return { schedule, firstPayment: schedule[0]?.payment ?? 0, totalInterest };
  };

  const { schedule, firstPayment, totalInterest } = generateSchedule();
  const totalPayment = loanAmount + totalInterest;
  const totalPages = Math.ceil(schedule.length / ROWS_PER_PAGE);
  const pagedRows = schedule.slice((schedPage - 1) * ROWS_PER_PAGE, schedPage * ROWS_PER_PAGE);
  const startMonth = (schedPage - 1) * ROWS_PER_PAGE + 1;
  const endMonth = Math.min(schedPage * ROWS_PER_PAGE, loanTerm);
  const interestPct = totalPayment > 0 ? (totalInterest / totalPayment) * 100 : 0;

  return (
    <div className="flex flex-col bg-white dark:bg-[#0a0f0c] text-gray-900 dark:text-white transition-colors duration-500">
      <style>{`
        @keyframes floatUp { 0% { transform: translateY(0) scale(1); opacity: 0.2; } 100% { transform: translateY(-100vh) scale(0.5); opacity: 0; } }
        @keyframes bounceSlow { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(10px); } }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(32px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes countUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .stat-card { transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.3s ease; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -10px rgba(0,0,0,0.15); }
        .amount-display { animation: countUp 0.3s ease both; }
        .locked-field { background: repeating-linear-gradient(45deg, transparent, transparent 4px, rgba(0,0,0,0.02) 4px, rgba(0,0,0,0.02) 8px); }
        .row-hover { transition: background 0.15s ease; }
        .row-hover:hover { background: rgba(34,197,94,0.06); }
        .progress-bar { height: 8px; border-radius: 99px; overflow: hidden; }
        .accent-green { accent-color: #16a34a; }
        @media (max-width: 640px) { .hero-amount { font-size: 2.25rem; } }
      `}</style>

      {/* ── HERO — matches MembershipSteps hero exactly and fills the viewport ── */}
      <section className="relative min-h-screen flex items-center justify-center overflow-hidden">
        <div
          className="absolute inset-0 bg-[url('/src/images/bghd.jpg')] bg-cover bg-center"
          style={{
            transition: 'transform 20s linear',
            transform: heroVisible ? 'scale(1)' : 'scale(1.05)',
          }}
        />
        <div className="absolute inset-0 bg-gradient-to-br from-white/90 via-green-50/80 to-green-100/90 dark:from-[#022c22]/95 dark:via-[#064e3b]/95 dark:to-[#065f46]/95 transition-colors duration-500" />
        <Particles />

        <div className={`relative z-10 max-w-7xl mx-auto px-6 text-center transition-all duration-1000 ${heroVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'}`}>
          <div className="inline-flex items-center gap-2 mb-6 px-4 py-2 rounded-full bg-green-200/50 dark:bg-white/10 border border-green-300 dark:border-white/20 backdrop-blur-md">
            <div className="w-2.5 h-2.5 bg-green-600 dark:bg-green-400 rounded-full animate-pulse" />
            <span className="text-xs sm:text-sm text-green-900 dark:text-white/90 font-medium uppercase tracking-widest">Financial Planning Tool</span>
          </div>
          <h1 className="text-5xl sm:text-8xl font-extrabold mb-6 uppercase tracking-tight text-gray-900 dark:text-white leading-[0.9]">
            <span className="bg-clip-text text-transparent bg-gradient-to-r from-green-700 to-green-500 dark:from-green-400 dark:to-green-200">
              Loan
            </span>
            <br className="sm:hidden" /> Calculator
          </h1>
          <p className="text-lg sm:text-xl text-gray-700 dark:text-white/80 max-w-2xl mx-auto mb-10 font-medium leading-relaxed">
            Estimate your payments using our <strong className="text-green-700 dark:text-green-300">diminishing balance</strong> method — your interest decreases every month as you pay down the principal.
          </p>
        </div>
      </section>

      {/* ── CALCULATOR ── */}
      <section className="py-24 bg-white dark:bg-[#0a0f0c]" ref={calcRef}>
        <div className="max-w-7xl mx-auto px-6">

          <div className="text-center mb-16">
            <span className="text-xs font-bold uppercase tracking-widest text-green-600 dark:text-green-400">Loan Estimation</span>
            <h2 className="text-3xl sm:text-4xl font-bold mt-3 text-gray-900 dark:text-white">Calculate Your Payments</h2>
          </div>

          <div className="grid lg:grid-cols-[1fr_1.1fr] gap-8 items-start">

            {/* ── LEFT: Inputs ── */}
            <Card
              className="rounded-[2.5rem] border border-green-100 dark:border-white/10 shadow-sm bg-white dark:bg-[#111b17]"
              style={{ animation: calcInView ? 'fadeUp 0.65s 0.05s both ease-out' : 'none', opacity: calcInView ? undefined : 0 }}
            >
              <CardHeader className="pb-4 border-b border-green-100 dark:border-white/10 px-10 pt-10">
                <CardTitle className="flex items-center gap-2.5 text-lg font-black uppercase tracking-tight text-gray-900 dark:text-white">
                  <div className="w-10 h-10 rounded-[1rem] bg-green-600 dark:bg-green-500 flex items-center justify-center shadow-lg shadow-green-900/20">
                    <Calculator className="w-5 h-5 text-white" />
                  </div>
                  Loan Details
                </CardTitle>
                <p className="text-xs text-gray-500 dark:text-gray-400 mt-2 font-bold uppercase tracking-wide">{config.note}</p>
              </CardHeader>

              <CardContent className="space-y-6 px-10 pt-8 pb-10">

                {/* Loan Type */}
                <div
                  style={{ animation: calcInView ? 'fadeUp 0.6s both ease-out' : 'none', opacity: calcInView ? undefined : 0 }}
                >
                  <Label className="text-xs font-black uppercase tracking-widest text-gray-700 dark:text-gray-300 mb-3 block">Loan Type</Label>
                  <Select value={loanType} onValueChange={handleLoanTypeChange}>
                    <SelectTrigger className="w-full rounded-2xl border-green-200 dark:border-white/10 h-12 text-sm font-bold bg-green-50/50 dark:bg-black/20 text-gray-900 dark:text-white">
                      <SelectValue placeholder="Select loan type" />
                    </SelectTrigger>
                    <SelectContent className="rounded-2xl border-green-100 dark:border-white/10">
                      {Object.entries(LOAN_TYPE_CONFIG).map(([key, cfg]) => (
                        <SelectItem key={key} value={key} className="text-sm font-bold cursor-pointer">
                          {cfg.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                {/* Amount */}
                <div className="rounded-2xl border border-green-100 dark:border-white/5 p-5 bg-green-50/50 dark:bg-black/20">
                  <div className="flex items-center justify-between mb-3">
                    <Label className="text-xs font-black uppercase tracking-widest text-gray-700 dark:text-gray-300">Loan Amount</Label>
                    <span className="text-xs font-bold text-gray-500 dark:text-gray-400">
                      {config.maxAmount ? `Max: ${formatPeso(config.maxAmount)}` : 'No limit'}
                    </span>
                  </div>
                  <div className="relative mb-3">
                    <span className="absolute left-3 top-1/2 -translate-y-1/2 text-green-700 dark:text-green-400 font-black text-sm">₱</span>
                    <Input
                      type="number"
                      value={loanAmountInput}
                      placeholder="Enter amount"
                      onChange={e => handleAmountChange(e.target.value)}
                      onBlur={() => {
                        if (loanAmountInput === '') { setLoanAmountInput('0'); setLoanAmount(0); }
                      }}
                      min={0}
                      max={config.maxAmount ?? undefined}
                      step={config.amountStep}
                      className="pl-7 text-base font-black border-green-200 dark:border-white/10 bg-white dark:bg-[#111b17] text-gray-900 dark:text-white rounded-xl"
                    />
                  </div>
                  <input
                    type="range" min={0} max={config.amountSliderMax} step={config.amountStep} value={loanAmount}
                    onChange={e => { const val = Number(e.target.value); setLoanAmount(val); setLoanAmountInput(String(val)); }}
                    className="w-full h-2 accent-green cursor-pointer mt-1"
                    style={{ accentColor: '#16a34a' }}
                  />
                  <div className="flex justify-between text-xs font-bold text-gray-500 dark:text-gray-400 mt-1">
                    <span>₱0</span><span>{formatPeso(config.amountSliderMax)}</span>
                  </div>
                </div>

                {/* Term */}
                <div className="rounded-2xl border border-green-100 dark:border-white/5 p-5 bg-green-50/50 dark:bg-black/20">
                  <div className="flex items-center justify-between mb-3">
                    <Label className="text-xs font-black uppercase tracking-widest text-gray-700 dark:text-gray-300">Loan Term</Label>
                    <span className="text-xs font-bold text-gray-500 dark:text-gray-400">Max: {config.maxMonths} months</span>
                  </div>
                  <div className="flex items-center gap-3 mb-3">
                    <Input
                      type="number" value={loanTerm}
                      onChange={e => handleTermChange(e.target.value)}
                      min={1} max={config.maxMonths}
                      className="w-24 text-base font-black border-green-200 dark:border-white/10 bg-white dark:bg-[#111b17] text-gray-900 dark:text-white rounded-xl"
                    />
                    <span className="text-sm font-bold text-gray-500 dark:text-gray-400">months</span>
                  </div>
                  <input
                    type="range" min={1} max={config.maxMonths} step={1} value={loanTerm}
                    onChange={e => handleTermChange(e.target.value)}
                    className="w-full h-2 cursor-pointer"
                    style={{ accentColor: '#16a34a' }}
                  />
                  <div className="flex justify-between text-xs font-bold text-gray-500 dark:text-gray-400 mt-1">
                    <span>1 mo</span><span>{config.maxMonths} mo</span>
                  </div>
                </div>

                {/* Interest Rate — locked */}
                <div className="rounded-2xl border border-green-100 dark:border-white/5 p-5 locked-field">
                  <Label className="text-xs font-black uppercase tracking-widest text-gray-700 dark:text-gray-300 flex items-center gap-1.5 mb-3">
                    <Lock className="w-3.5 h-3.5 text-gray-400" />
                    Interest Rate (monthly)
                  </Label>
                  <div className="flex items-center gap-2">
                    <span className="text-2xl font-black text-green-600 dark:text-green-400">{interestRate}%</span>
                    <span className="text-sm font-bold text-gray-500 dark:text-gray-400">per month</span>
                  </div>
                </div>

              </CardContent>
            </Card>

            {/* ── RIGHT: Results ── */}
            <div
              className="space-y-5"
              style={{ animation: calcInView ? 'fadeUp 0.65s 0.15s both ease-out' : 'none', opacity: calcInView ? undefined : 0 }}
            >

              {/* First Month Payment — green gradient hero card */}
              <Card className="stat-card rounded-[2.5rem] border-none shadow-xl overflow-hidden bg-gradient-to-br from-green-600 to-green-800 dark:from-green-700 dark:to-green-900 text-white">
                <CardContent className="p-8">
                  <div className="flex items-start justify-between mb-3">
                    <p className="text-xs font-black uppercase tracking-widest text-white/75">First Month Payment</p>
                    <div className="w-10 h-10 rounded-[1rem] bg-white/20 flex items-center justify-center">
                      <Wallet className="w-5 h-5 text-white" />
                    </div>
                  </div>
                  <p className="hero-amount text-4xl sm:text-5xl font-black tracking-tight amount-display" key={firstPayment}>
                    {formatPeso(firstPayment)}
                  </p>
                  <p className="text-white/60 text-xs mt-2 font-bold uppercase tracking-wide">
                    Payments decrease monthly as principal reduces
                  </p>
                </CardContent>
              </Card>

              {/* Stats row */}
              <div className="grid grid-cols-2 gap-4">
                <Card className="stat-card rounded-[2rem] border border-green-100 dark:border-white/10 shadow-sm bg-white dark:bg-[#111b17]">
                  <CardContent className="p-6">
                    <div className="flex items-center gap-2 mb-3">
                      <div className="w-8 h-8 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                        <TrendingDown className="w-4 h-4 text-orange-600 dark:text-orange-400" />
                      </div>
                      <span className="text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Total Interest</span>
                    </div>
                    <p className="text-xl sm:text-2xl font-black text-orange-600 dark:text-orange-400" key={totalInterest}>
                      {formatPeso(totalInterest)}
                    </p>
                  </CardContent>
                </Card>
                <Card className="stat-card rounded-[2rem] border border-green-100 dark:border-white/10 shadow-sm bg-white dark:bg-[#111b17]">
                  <CardContent className="p-6">
                    <div className="flex items-center gap-2 mb-3">
                      <div className="w-8 h-8 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                        <Clock className="w-4 h-4 text-green-600 dark:text-green-400" />
                      </div>
                      <span className="text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Total Payment</span>
                    </div>
                    <p className="text-xl sm:text-2xl font-black text-gray-900 dark:text-white" key={totalPayment}>
                      {formatPeso(totalPayment)}
                    </p>
                  </CardContent>
                </Card>
              </div>

              {/* Breakdown card */}
              <Card className="rounded-[2.5rem] border border-green-100 dark:border-white/10 shadow-sm bg-white dark:bg-[#111b17]">
                <CardHeader className="pb-4 pt-8 px-8 border-b border-green-100 dark:border-white/10">
                  <CardTitle className="text-base font-black uppercase tracking-widest text-gray-900 dark:text-white">Payment Breakdown</CardTitle>
                </CardHeader>
                <CardContent className="px-8 pt-6 pb-8 space-y-5">
                  {/* Progress bar */}
                  <div className="space-y-2">
                    <div className="flex justify-between text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">
                      <span>Principal</span><span>Interest</span>
                    </div>
                    <div className="progress-bar bg-green-100 dark:bg-white/10">
                      <div style={{ display: 'flex', height: '100%' }}>
                        <div className="rounded-l-full bg-green-600 dark:bg-green-500" style={{ width: `${100 - interestPct}%` }} />
                        <div className="rounded-r-full bg-orange-400 dark:bg-orange-500" style={{ width: `${interestPct}%` }} />
                      </div>
                    </div>
                    <div className="flex justify-between text-xs font-bold text-gray-500 dark:text-gray-400">
                      <span>{formatPeso(loanAmount)} ({(100 - interestPct).toFixed(1)}%)</span>
                      <span>{interestPct.toFixed(1)}%</span>
                    </div>
                  </div>

                  {/* Detail rows */}
                  <div className="space-y-0 pt-2">
                    {[
                      { label: 'Loan Amount',        value: formatPeso(loanAmount),     className: 'text-gray-900 dark:text-white font-black' },
                      { label: 'Interest Rate',       value: `${interestRate}% / month`, className: 'text-gray-500 dark:text-gray-400 font-bold' },
                      { label: 'Loan Term',           value: `${loanTerm} months`,       className: 'text-gray-500 dark:text-gray-400 font-bold' },
                      { label: 'Total Interest',      value: formatPeso(totalInterest),  className: 'text-orange-600 dark:text-orange-400 font-black' },
                      { label: 'First Month Payment', value: formatPeso(firstPayment),   className: 'text-green-600 dark:text-green-400 font-black' },
                    ].map(row => (
                      <div key={row.label} className="flex justify-between items-center py-3 border-b border-green-50 dark:border-white/5 last:border-0">
                        <span className="text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">{row.label}</span>
                        <span className={`text-sm ${row.className}`}>{row.value}</span>
                      </div>
                    ))}
                    <div className="flex justify-between items-center pt-4">
                      <span className="text-sm font-black uppercase tracking-widest text-gray-900 dark:text-white">Total Payment</span>
                      <span className="text-base font-black text-gray-900 dark:text-white">{formatPeso(totalPayment)}</span>
                    </div>
                  </div>

                  {loanAmount > 15000 && loanType !== 'emergency' && (
                    <div className="flex gap-2.5 items-start p-4 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/30 mt-2">
                      <AlertCircle className="w-4 h-4 text-amber-600 dark:text-amber-400 mt-0.5 shrink-0" />
                      <p className="text-xs font-bold text-amber-700 dark:text-amber-300 uppercase tracking-wide">
                        Loans above ₱15,000 require collateral. Please prepare supporting documents.
                      </p>
                    </div>
                  )}
                </CardContent>
              </Card>

            </div>
          </div>
        </div>
      </section>

      {/* ── AMORTIZATION SCHEDULE — dark section like MembershipSteps CTA ── */}
      <section className="relative py-24 overflow-hidden" ref={schedRef}>
        <div className="absolute inset-0 bg-[url('/src/images/bghd.jpg')] bg-cover bg-center" />
        <div className="absolute inset-0 bg-gradient-to-br from-[#022c22]/97 via-[#064e3b]/97 to-[#065f46]/97" />

        <div className="relative z-10 max-w-7xl mx-auto px-6">

          <div
            className="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-5 mb-10"
            style={{ animation: schedInView ? 'fadeUp 0.7s both ease-out' : 'none', opacity: schedInView ? undefined : 0 }}
          >
            <div>
              <span className="text-xs font-bold uppercase tracking-widest text-green-400">Full Payment Plan</span>
              <h2 className="text-3xl sm:text-4xl font-extrabold text-white mt-3 uppercase tracking-tight">Amortization Schedule</h2>
              <p className="text-white/50 text-sm mt-1.5 font-bold uppercase tracking-wide">
                {loanTerm} month{loanTerm !== 1 ? 's' : ''} total &mdash; showing months {startMonth}–{endMonth}
              </p>
            </div>

            {totalPages > 1 && (
              <div className="flex items-center gap-2 flex-wrap">
                <Button
                  variant="outline" size="sm"
                  onClick={() => setSchedPage(p => Math.max(1, p - 1))}
                  disabled={schedPage === 1}
                  className="bg-white/10 border-white/20 text-white hover:bg-white/20 disabled:opacity-30 rounded-xl h-10"
                >
                  <ChevronLeft className="w-4 h-4 mr-1" /> Prev
                </Button>
                <div className="flex items-center gap-1">
                  {Array.from({ length: totalPages }, (_, i) => i + 1).map(page => (
                    <button
                      key={page}
                      onClick={() => setSchedPage(page)}
                      className={`w-10 h-10 rounded-xl text-sm font-black transition-all ${
                        page === schedPage
                          ? 'bg-green-500 text-white shadow-md shadow-green-900/30'
                          : 'bg-white/10 text-white hover:bg-white/20'
                      }`}
                    >
                      {page}
                    </button>
                  ))}
                </div>
                <Button
                  variant="outline" size="sm"
                  onClick={() => setSchedPage(p => Math.min(totalPages, p + 1))}
                  disabled={schedPage === totalPages}
                  className="bg-white/10 border-white/20 text-white hover:bg-white/20 disabled:opacity-30 rounded-xl h-10"
                >
                  Next <ChevronRight className="w-4 h-4 ml-1" />
                </Button>
              </div>
            )}
          </div>

          <Card
            className="rounded-[2.5rem] overflow-hidden border-white/10 shadow-2xl"
            style={{
              background: 'rgba(255,255,255,0.06)',
              backdropFilter: 'blur(16px)',
              animation: schedInView ? 'fadeUp 0.7s 0.1s both ease-out' : 'none',
              opacity: schedInView ? undefined : 0,
            }}
          >
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b border-white/10" style={{ background: 'rgba(255,255,255,0.08)' }}>
                    {['Month', 'Payment', 'Principal', 'Interest', 'Balance'].map((h, i) => (
                      <th key={h} className={`p-4 text-xs font-black uppercase tracking-widest text-white/60 ${i === 0 ? 'text-left' : 'text-right'}`}>
                        {h}
                      </th>
                    ))}
                  </tr>
                </thead>
                <tbody className="divide-y divide-white/[0.06]">
                  {pagedRows.map((row, i) => (
                    <tr
                      key={row.month} className="row-hover"
                      style={{
                        animation: schedInView ? `fadeUp 0.4s ${0.05 + i * 0.03}s both ease-out` : 'none',
                        opacity: schedInView ? undefined : 0,
                      }}
                    >
                      <td className="p-4">
                        <span className="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-green-500/20 text-green-300 text-sm font-black">
                          {row.month}
                        </span>
                      </td>
                      <td className="text-right p-4 text-white font-black">{formatPeso(row.payment)}</td>
                      <td className="text-right p-4"><span className="text-green-400 font-black">{formatPeso(row.principal)}</span></td>
                      <td className="text-right p-4"><span className="text-orange-400 font-black">{formatPeso(row.interest)}</span></td>
                      <td className="text-right p-4 text-white/80 font-black">{formatPeso(row.balance)}</td>
                    </tr>
                  ))}
                </tbody>
                <tfoot>
                  <tr className="border-t-2 border-white/20" style={{ background: 'rgba(255,255,255,0.08)' }}>
                    <td className="p-4 text-white/60 text-xs font-black uppercase tracking-widest" colSpan={1}>Total</td>
                    <td className="text-right p-4 text-white font-black">{formatPeso(totalPayment)}</td>
                    <td className="text-right p-4 text-green-400 font-black">{formatPeso(loanAmount)}</td>
                    <td className="text-right p-4 text-orange-400 font-black">{formatPeso(totalInterest)}</td>
                    <td className="text-right p-4 text-white/40 font-black">—</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </Card>

          {totalPages > 1 && (
            <div className="flex items-center justify-center gap-3 mt-6">
              <Button
                variant="outline" size="sm"
                onClick={() => setSchedPage(p => Math.max(1, p - 1))}
                disabled={schedPage === 1}
                className="bg-white/10 border-white/20 text-white hover:bg-white/20 disabled:opacity-30 rounded-xl"
              >
                <ChevronLeft className="w-4 h-4 mr-1" /> Prev
              </Button>
              <span className="text-white/50 text-sm font-bold uppercase tracking-widest">Page {schedPage} of {totalPages}</span>
              <Button
                variant="outline" size="sm"
                onClick={() => setSchedPage(p => Math.min(totalPages, p + 1))}
                disabled={schedPage === totalPages}
                className="bg-white/10 border-white/20 text-white hover:bg-white/20 disabled:opacity-30 rounded-xl"
              >
                Next <ChevronRight className="w-4 h-4 ml-1" />
              </Button>
            </div>
          )}

        </div>
      </section>

    </div>
  );
}
