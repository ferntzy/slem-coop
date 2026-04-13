import { Link } from 'react-router-dom';
import { Wallet, ShieldCheck, Zap, ArrowRight, CheckCircle2, ChevronDown } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Badge } from '../components/ui/badge';
import { useEffect, useRef, useState } from 'react';

/* ─── Reuseable Hooks & Components ───────────────────────────── */
function useInView(options = {}) {
  const ref = useRef(null);
  const [inView, setInView] = useState(false);
  useEffect(() => {
    const el = ref.current;
    if (!el) return;
    const obs = new IntersectionObserver(([entry]) => {
      if (entry.isIntersecting) {
        setInView(true);
        obs.disconnect();
      }
    }, { threshold: 0.1, ...options });
    obs.observe(el);
    return () => obs.disconnect();
  }, [options]);
  return [ref, inView];
}

function Particles() {
  const colorClasses = ['bg-blue-300 dark:bg-blue-600', 'bg-blue-400 dark:bg-blue-500', 'bg-green-200 dark:bg-green-700', 'bg-green-500 dark:bg-green-800'];
  const particles = Array.from({ length: 20 }, (_, i) => ({
    id: i,
    size: Math.random() * 5 + 3,
    x: Math.random() * 100,
    delay: Math.random() * 8,
    duration: Math.random() * 10 + 12,
    opacity: Math.random() * 0.4 + 0.1,
    colorClass: colorClasses[Math.floor(Math.random() * colorClasses.length)],
  }));
  return (
    <div className="absolute inset-0 overflow-hidden pointer-events-none">
      {particles.map(p => (
        <div key={p.id} className={`absolute rounded-full ${p.colorClass}`}
          style={{ width: p.size, height: p.size, left: `${p.x}%`, bottom: '-10px', opacity: p.opacity, animation: `floatUp ${p.duration}s ${p.delay}s infinite linear` }}
        />
      ))}
    </div>
  );
}

export function Loans() {
  const [heroVisible, setHeroVisible] = useState(false);
  const [gridRef, gridInView] = useInView();
  const [reqRef, reqInView] = useInView();
  const [ctaRef, ctaInView] = useInView();

  useEffect(() => {
    const t = setTimeout(() => setHeroVisible(true), 100);
    return () => clearTimeout(t);
  }, []);

  const loanTypes = [
    {
      icon: Wallet,
      title: 'E-Cash Loan',
      description: 'Flexible cash loan for any purpose. Large loan amounts may require collateral as security.',
      features: ['No fixed loan limit', 'Up to 3% interest rate', 'Collateral for large amounts', 'Up to 2 years term'],
      rate: 'Up to 3%',
      note: 'Rates & limits subject to change.',
    },
    {
      icon: ShieldCheck,
      title: 'Guaranteed Loan',
      description: 'Loan amount based on your fixed deposit. Borrow up to twice your share capital.',
      features: ['Max: Share Capital × 2', 'Based on fixed deposit', 'Up to 2% interest rate', 'Up to 2 years term'],
      rate: 'Up to 2%',
      note: 'Rates & limits subject to change.',
    },
    {
      icon: Zap,
      title: 'Instant Loan',
      description: 'Quick access to funds for urgent and unexpected expenses. Fast approval process.',
      features: ['Up to ₱15,000', 'Up to 3% interest rate', '3 months repayment', 'Minimal requirements'],
      rate: 'Short-Term',
      note: 'Rates & limits subject to change.',
    },
  ];

  return (
    <>
      <style>{`
        @keyframes floatUp { 0% { transform: translateY(0) scale(1); opacity: 0.2; } 100% { transform: translateY(-100vh) scale(0.5); opacity: 0; } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes bounceSlow { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(10px); } }
      `}</style>

      <div className="flex flex-col bg-white dark:bg-[#0a0f0c] transition-colors duration-500">

        {/* ── Full-Screen Hero Section ── */}
        <section className="relative min-h-screen flex items-center justify-center overflow-hidden">
          <div className="absolute inset-0 bg-[url('/src/images/bghd.jpg')] bg-cover bg-center"
               style={{ transition: 'transform 20s linear', transform: heroVisible ? 'scale(1)' : 'scale(1.05)' }} />

          <div className="absolute inset-0 bg-gradient-to-br from-white/95 via-blue-50/90 to-green-100/95 dark:from-[#022c22]/95 dark:via-[#064e3b]/95 dark:to-[#065f46]/95 transition-colors duration-500" />
          <Particles />

          <div className={`relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center transition-all duration-1000 ${heroVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'}`}>
            <Badge className="mb-6 bg-blue-600/10 dark:bg-white/10 text-blue-700 dark:text-white border-blue-200 dark:border-white/20 backdrop-blur-sm px-4 py-1 text-sm">
              Financial Solutions
            </Badge>
            <h1 className="text-5xl sm:text-7xl font-extrabold mb-8 leading-tight text-gray-900 dark:text-white uppercase tracking-tighter">
              Loan <span className="text-green-600 dark:text-green-400">Products</span>
            </h1>
            <p className="text-lg sm:text-2xl text-gray-700 dark:text-white/80 leading-relaxed max-w-3xl mx-auto mb-10">
              Flexible financing solutions tailored to your needs. From everyday cash needs
              to emergency funds, we have the right loan product for you.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Button asChild size="lg" className="bg-green-600 hover:bg-green-700 text-white rounded-full px-10 py-7 text-lg font-bold shadow-xl transition-transform hover:scale-105">
                <Link to="/calculator">Calculate Loan</Link>
              </Button>
              <Button asChild size="lg" variant="outline" className="border-green-600 text-green-700 dark:text-white dark:border-white/20 rounded-full px-10 py-7 text-lg font-bold backdrop-blur-sm">
                <Link to="#loans">Browse Loans</Link>
              </Button>
            </div>
          </div>

          <div className="absolute bottom-10 left-1/2 -translate-x-1/2 text-green-600 dark:text-white/40 animate-[bounceSlow_2s_infinite]">
            <ChevronDown className="w-10 h-10" />
          </div>
        </section>

        {/* ── Loan Types Grid ── */}
        <section id="loans" className="relative py-24 sm:py-32" ref={gridRef}>
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="text-center mb-16">
              <h2 className="text-3xl sm:text-5xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Available Loan Plans</h2>
              <p className="text-gray-500 dark:text-gray-400 font-bold mt-2">Competitive rates designed for cooperative members</p>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
              {loanTypes.map((loan, index) => (
                <Card
                  key={index}
                  className="hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 rounded-2xl border-green-100 dark:border-white/10 bg-white dark:bg-[#111b17] flex flex-col"
                  style={{ animation: gridInView ? `fadeInUp 0.6s ${index * 0.15}s both` : 'opacity-0' }}
                >
                  <CardHeader>
                    {/* Uniform Green Icon Container matching Home Section */}
                    <div className="w-14 h-14 rounded-xl bg-gradient-to-br from-green-500 to-green-600 dark:from-green-600 dark:to-green-800 flex items-center justify-center mb-4 shadow-lg shadow-green-500/20">
                      <loan.icon className="w-7 h-7 text-white" />
                    </div>
                    <CardTitle className="text-gray-900 dark:text-white text-xl font-bold uppercase tracking-tight">{loan.title}</CardTitle>
                    <Badge variant="secondary" className="w-fit mt-2 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 font-bold">{loan.rate}</Badge>
                  </CardHeader>
                  <CardContent className="space-y-4 flex flex-col flex-1">
                    <p className="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{loan.description}</p>
                    <div className="space-y-2">
                      <p className="text-sm font-bold text-gray-900 dark:text-white">Key Features:</p>
                      <ul className="space-y-1">
                        {loan.features.map((feature, idx) => (
                          <li key={idx} className="text-sm text-gray-600 dark:text-gray-400 flex items-start gap-2">
                            <span className="text-green-500 font-bold">•</span>
                            {feature}
                          </li>
                        ))}
                      </ul>
                    </div>
                    {loan.note && (
                      <p className="text-xs text-gray-500 italic mt-auto pt-4 border-t border-gray-100 dark:border-white/5">
                        {loan.note}
                      </p>
                    )}
                  </CardContent>
                </Card>
              ))}
            </div>
          </div>
        </section>

        {/* ── Requirements Section ── */}
        <section className="relative py-24 sm:py-32 overflow-hidden" ref={reqRef}>
          <div className="absolute inset-0 bg-green-50 dark:bg-[#0d1410] transition-colors duration-500" />
          <div className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="max-w-4xl mx-auto" style={{ animation: reqInView ? 'fadeInUp 0.8s ease both' : 'opacity-0' }}>
              <h2 className="text-3xl sm:text-5xl font-black mb-12 text-center text-gray-900 dark:text-white uppercase tracking-tight">Loan Requirements</h2>
              <Card className="rounded-3xl bg-white dark:bg-[#111b17] border-green-100 dark:border-white/10 shadow-xl overflow-hidden">
                <CardContent className="p-8 sm:p-12">
                  <div className="grid md:grid-cols-2 gap-12">
                    <div>
                      <h3 className="font-bold mb-6 text-xl text-green-700 dark:text-green-400 flex items-center gap-2 uppercase tracking-tight">
                        <CheckCircle2 className="w-6 h-6" /> Standard Docs
                      </h3>
                      <ul className="space-y-4 text-sm text-gray-600 dark:text-gray-400 font-medium">
                        {['Active membership (6+ months)', 'Valid Government ID', 'Proof of Income (Payslip/ITR)', 'Good credit standing', 'Completed application form'].map((item, i) => (
                          <li key={i} className="flex items-start gap-3">
                            <span className="text-green-500 font-bold">✓</span>
                            {item}
                          </li>
                        ))}
                      </ul>
                    </div>
                    <div>
                      <h3 className="font-bold mb-6 text-xl text-green-700 dark:text-green-400 flex items-center gap-2 uppercase tracking-tight">
                        <CheckCircle2 className="w-6 h-6" /> Supporting Docs
                      </h3>
                      <ul className="space-y-4 text-sm text-gray-600 dark:text-gray-400 font-medium">
                        {['Latest bank statements', 'Proof of billing address', 'Co-maker info (if required)', 'Collateral docs (secured loans)'].map((item, i) => (
                          <li key={i} className="flex items-start gap-3">
                            <span className="text-green-500 font-bold">✓</span>
                            {item}
                          </li>
                        ))}
                      </ul>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </section>

        {/* ── CTA Section ── */}
        {/* <section className="relative py-24 sm:py-32" ref={ctaRef}>
          <div className="relative z-10 max-w-5xl mx-auto px-4" style={{ animation: ctaInView ? 'fadeInUp 0.8s ease both' : 'opacity-0' }}>
            <Card className="bg-gradient-to-br from-green-600 to-green-800 dark:from-[#022c22] dark:to-[#064e3b] text-white border-none shadow-2xl rounded-[3rem] overflow-hidden transition-all">
              <CardContent className="p-12 sm:p-20 text-center">
                <h2 className="text-3xl sm:text-5xl font-bold mb-6 uppercase tracking-tight">Ready to apply?</h2>
                <p className="text-lg sm:text-xl text-green-50 dark:text-white/80 mb-10 max-w-2xl mx-auto font-medium">
                  Estimate your payments with our calculator or talk to our loan officers
                  to find the best plan for your financial goals.
                </p>
                <div className="flex flex-col sm:flex-row gap-4 justify-center">
                  <Button asChild size="lg" className="bg-white text-green-700 hover:bg-green-50 shadow-xl rounded-full font-bold px-12 py-7 text-lg transition-transform hover:scale-105">
                    <Link to="/calculator">
                      Calculate Monthly
                      <ArrowRight className="ml-2 w-5 h-5" />
                    </Link>
                  </Button>
                  <Button asChild size="lg" variant="outline" className="border-green-800/20 text-green-800 hover:bg-green-50 dark:border-white/40 dark:text-white dark:hover:bg-white/10 rounded-full font-bold px-12 py-7 text-lg backdrop-blur-sm transition-all duration-300 hover:scale-105">
                    <Link to="/contact">Contact Support</Link>
                  </Button>
                </div>
              </CardContent>
            </Card>
          </div>
        </section> */}

      </div>
    </>
  );
}
