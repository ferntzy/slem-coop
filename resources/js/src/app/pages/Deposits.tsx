import { Link } from 'react-router-dom';
import { Wallet, PiggyBank, TrendingUp, Shield, Gift, ArrowRight, CheckCircle2, ChevronDown } from 'lucide-react';
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

export function Deposits() {
  const [heroVisible, setHeroVisible] = useState(false);
  const [gridRef, gridInView] = useInView();
  const [tableRef, tableInView] = useInView();
  const [ctaRef, ctaInView] = useInView();

  useEffect(() => {
    const t = setTimeout(() => setHeroVisible(true), 100);
    return () => clearTimeout(t);
  }, []);

  const savingsProducts = [
    {
      icon: Wallet,
      title: 'Regular Savings',
      description: 'Build your savings with our flexible regular savings account designed for everyday needs.',
      features: ['Minimum deposit: ₱100', 'Withdrawable anytime', 'Monthly interest', 'No maintaining balance'],
      rate: '2.5% P.A.',
    },
    {
      icon: PiggyBank,
      title: 'Time Deposit',
      description: 'Earn higher returns with our time deposit accounts with fixed terms and guaranteed rates.',
      features: ['Minimum deposit: ₱5,000', '6, 12, or 24 month terms', 'Higher interest rates', 'Auto-renewal option'],
      rate: 'Up to 6.0%',
    },
    {
      icon: Gift,
      title: 'Junior Savers',
      description: "Start your child's financial journey early with our junior savings account for minors.",
      features: ['Ages 0-17 years old', 'Low opening: ₱50', 'Financial literacy', 'Parental monitoring'],
      rate: 'Special Rate',
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
              Secure Your Future
            </Badge>
            <h1 className="text-5xl sm:text-7xl font-extrabold mb-8 leading-tight text-gray-900 dark:text-white uppercase tracking-tighter">
              Savings & <span className="text-green-600 dark:text-green-400">Deposits</span>
            </h1>
            <p className="text-lg sm:text-2xl text-gray-700 dark:text-white/80 leading-relaxed max-w-3xl mx-auto mb-10">
              Grow your wealth with high-interest savings plans and secure deposit options
              designed to give you financial peace of mind.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Button asChild size="lg" className="bg-green-600 hover:bg-green-700 text-white rounded-full px-10 py-7 text-lg font-bold shadow-xl transition-transform hover:scale-105">
                <Link to="/membership/apply">Open An Account</Link>
              </Button>
              {/* <Button asChild size="lg" variant="outline" className="border-green-600 text-green-700 dark:text-white dark:border-white/20 rounded-full px-10 py-7 text-lg font-bold backdrop-blur-sm">
                <Link to="#products">View All Products</Link>
              </Button> */}
            </div>
          </div>

          <div className="absolute bottom-10 left-1/2 -translate-x-1/2 text-green-600 dark:text-white/40 animate-[bounceSlow_2s_infinite]">
            <ChevronDown className="w-10 h-10" />
          </div>
        </section>

        {/* ── Savings Grid ── */}
        <section id="products" className="relative py-24 sm:py-32" ref={gridRef}>
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
             <div className="text-center mb-16">
                <h2 className="text-3xl sm:text-5xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Our Savings Solutions</h2>
                <p className="text-gray-500 dark:text-gray-400 font-bold mt-2">Tailored accounts for every stage of your life</p>
             </div>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
              {savingsProducts.map((product, index) => (
                <Card
                  key={index}
                  className="hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 rounded-2xl border-green-100 dark:border-white/10 bg-white dark:bg-[#111b17] flex flex-col"
                  style={{ animation: gridInView ? `fadeInUp 0.6s ${index * 0.15}s both` : 'opacity-0' }}
                >
                  <CardHeader>
                    {/* Consistent Green Icon Container matching Loans/Home section */}
                    <div className="w-14 h-14 rounded-xl bg-gradient-to-br from-green-500 to-green-600 dark:from-green-600 dark:to-green-800 flex items-center justify-center mb-4 shadow-lg shadow-green-500/20">
                      <product.icon className="w-7 h-7 text-white" />
                    </div>
                    <CardTitle className="text-gray-900 dark:text-white text-xl font-bold uppercase tracking-tight">{product.title}</CardTitle>
                    <Badge variant="secondary" className="w-fit mt-2 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 font-bold">{product.rate}</Badge>
                  </CardHeader>
                  <CardContent className="space-y-4 flex flex-col flex-1">
                    <p className="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{product.description}</p>
                    <div className="space-y-2">
                      <p className="text-sm font-bold text-gray-900 dark:text-white">Product Features:</p>
                      <ul className="space-y-1">
                        {product.features.map((feature, idx) => (
                          <li key={idx} className="text-sm text-gray-600 dark:text-gray-400 flex items-start gap-2">
                            <CheckCircle2 className="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                            {feature}
                          </li>
                        ))}
                      </ul>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </div>
        </section>

        {/* ── Rates Table Section ── */}
        <section className="relative py-24 sm:py-32 overflow-hidden" ref={tableRef}>
          <div className="absolute inset-0 bg-green-50 dark:bg-[#0d1410] transition-colors duration-500" />
          <div className="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div style={{ animation: tableInView ? 'fadeInUp 0.8s ease both' : 'opacity-0' }}>
              <h2 className="text-3xl sm:text-4xl font-bold mb-10 text-center text-gray-900 dark:text-white uppercase tracking-tight">Dividend & Interest Rates</h2>
              <Card className="rounded-3xl bg-white dark:bg-[#111b17] border-green-100 dark:border-white/10 shadow-xl overflow-hidden">
                <div className="overflow-x-auto">
                  <table className="w-full text-sm font-bold">
                    <thead>
                      <tr className="bg-green-600 text-white dark:bg-green-900/50">
                        <th className="p-6 text-left uppercase tracking-wider">Account Type</th>
                        <th className="p-6 text-left uppercase tracking-wider">Min. Deposit</th>
                        <th className="p-6 text-left uppercase tracking-wider">Interest Rate</th>
                        <th className="p-6 text-left uppercase tracking-wider">Term</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100 dark:divide-white/5">
                      {[
                        ['Regular Savings', '₱100', '2.5% p.a.', 'No lock-in'],
                        ['Time Deposit (6 mo.)', '₱5,000', '4.5% p.a.', '6 months'],
                        ['Time Deposit (12 mo.)', '₱5,000', '5.5% p.a.', '12 months'],
                        ['Time Deposit (24 mo.)', '₱5,000', '6.0% p.a.', '24 months'],
                        ['High-Yield Savings', '₱10,000', '3.5% - 5.0%', 'No lock-in'],
                        ['Junior Savers', '₱50', '2.5% p.a.', 'No lock-in'],
                      ].map(([type, min, rate, term], i) => (
                        <tr key={i} className="hover:bg-green-50/50 dark:hover:bg-white/5 transition-colors text-gray-700 dark:text-gray-300">
                          <td className="p-6 font-bold">{type}</td>
                          <td className="p-6">{min}</td>
                          <td className="p-6 text-green-600 dark:text-green-400 font-black">{rate}</td>
                          <td className="p-6 font-medium text-gray-400">{term}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </Card>
            </div>
          </div>
        </section>

        {/* ── CTA Section ── */}
        {/* <section className="relative py-24 sm:py-32" ref={ctaRef}>
          <div className="relative z-10 max-w-5xl mx-auto px-4" style={{ animation: ctaInView ? 'fadeInUp 0.8s ease both' : 'opacity-0' }}>
            <Card className="bg-gradient-to-br from-green-600 to-green-800 dark:from-[#022c22] dark:to-[#064e3b] text-white border-none shadow-2xl rounded-[3rem] overflow-hidden transition-all">
              <CardContent className="p-12 sm:p-20 text-center">
                <h2 className="text-3xl sm:text-5xl font-bold mb-6 uppercase tracking-tight">Start Growing Today</h2>
                <p className="text-lg sm:text-xl text-green-50 dark:text-white/80 mb-10 max-w-2xl mx-auto font-medium">
                  Experience the power of cooperative saving. Join our family today
                  and enjoy high dividends and competitive interest rates.
                </p>
                <div className="flex flex-col sm:flex-row gap-4 justify-center">
                  <Button asChild size="lg" className="bg-white text-green-700 hover:bg-green-50 shadow-xl rounded-full font-bold px-12 py-7 text-lg transition-transform hover:scale-105">
                    <Link to="/membership/apply">
                      Apply Now
                      <ArrowRight className="ml-2 w-5 h-5" />
                    </Link>
                  </Button>
                  <Button asChild size="lg" variant="outline" className="border-green-800/20 text-green-800 hover:bg-green-50 dark:border-white/40 dark:text-white dark:hover:bg-white/10 rounded-full font-bold px-12 py-7 text-lg backdrop-blur-sm transition-all duration-300 hover:scale-105">
                    <Link to="/contact">Get Assistance</Link>
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
