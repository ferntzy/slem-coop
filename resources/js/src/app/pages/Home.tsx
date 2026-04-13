import { Link } from 'react-router-dom';
import { ArrowRight, Shield, TrendingUp, Users, Heart, Building2, Calculator, CreditCard, Star, CheckCircle2 } from 'lucide-react';
import { Card, CardContent } from '../components/ui/card';
import { useEffect, useRef, useState } from 'react';
import { useTheme } from 'next-themes';

/* ─── Hooks ───────────────────────────────────────────────────── */
function useInView(options = {}) {
  const ref = useRef<HTMLElement | null>(null);
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
  return [ref, inView] as const;
}

function AnimatedCounter({ value, inView }: { value: string; inView: boolean }) {
  const [display, setDisplay] = useState('0');
  useEffect(() => {
    if (!inView) return;
    const numeric = parseFloat(value.replace(/[^0-9.]/g, ''));
    const suffix = value.replace(/[0-9.]/g, '');
    const duration = 1600;
    const start = performance.now();
    const tick = (now: number) => {
      const progress = Math.min((now - start) / duration, 1);
      const ease = 1 - Math.pow(1 - progress, 3);
      setDisplay(Math.round(ease * numeric) + suffix);
      if (progress < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  }, [inView, value]);
  return <span>{display}</span>;
}

function Particles() {
  const colorClasses = [
    'bg-green-300 dark:bg-green-600',
    'bg-green-400 dark:bg-green-500',
    'bg-green-200 dark:bg-green-700',
    'bg-green-500 dark:bg-green-800'
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
      {particles.map(p => (
        <div
          key={p.id}
          className={`absolute rounded-full ${p.colorClass}`}
          style={{
            width: p.size, height: p.size,
            left: `${p.x}%`, bottom: '-10px',
            opacity: p.opacity,
            animation: `floatUp ${p.duration}s ${p.delay}s infinite linear`,
          }}
        />
      ))}
    </div>
  );
}

export function Home() {
  const { theme, resolvedTheme } = useTheme();
  const [mounted, setMounted] = useState(false);
  const [heroVisible, setHeroVisible] = useState(false);
  const [statsRef, statsInView] = useInView();
  const [featuresRef, featuresInView] = useInView();
  const [servicesRef, servicesInView] = useInView();
  const [ctaRef, ctaInView] = useInView();
  const whySectionRef = useRef<HTMLElement | null>(null);

  useEffect(() => setMounted(true), []);

  const isDarkTheme = mounted ? (resolvedTheme === 'dark' || theme === 'dark') : false;

  const scrollToWhy = () => whySectionRef.current?.scrollIntoView({ behavior: 'smooth' });

  useEffect(() => {
    const t = setTimeout(() => setHeroVisible(true), 100);
    return () => clearTimeout(t);
  }, []);

  const features = [
    { icon: Shield, title: 'Secure & Trusted', description: 'Your financial security is our top priority.', bgClass: 'bg-green-100 dark:bg-green-900', iconClass: 'text-green-600 dark:text-green-400' },
    { icon: TrendingUp, title: 'Competitive Rates', description: 'Attractive interest rates designed for members.', bgClass: 'bg-green-50 dark:bg-green-800', iconClass: 'text-green-500 dark:text-green-300' },
    { icon: Users, title: 'Member-Focused', description: 'Built by the community, for the community.', bgClass: 'bg-green-100 dark:bg-green-950', iconClass: 'text-green-700 dark:text-green-500' },
    { icon: Heart, title: 'Community Impact', description: 'Supporting local development and prosperity.', bgClass: 'bg-green-200 dark:bg-green-900', iconClass: 'text-green-800 dark:text-green-200' },
  ];

  const services = [
    { icon: CreditCard, title: 'Flexible Loans', description: 'Personal and business loans with flexible terms.', link: '/products/loans', gradient: 'from-green-300 to-green-400 dark:from-green-800 dark:to-green-900', shadowClass: 'shadow-green-300 dark:shadow-green-900/50' },
    { icon: Building2, title: 'Savings & Deposits', description: 'Grow your wealth with competitive rates.', link: '/products/deposits', gradient: 'from-green-200 to-green-300 dark:from-green-700 dark:to-green-800', shadowClass: 'shadow-green-200 dark:shadow-green-800/50' },
    { icon: Calculator, title: 'Loan Calculator', description: 'Calculate monthly payments and plan ahead.', link: '/calculator', gradient: 'from-green-400 to-green-500 dark:from-green-600 dark:to-green-700', shadowClass: 'shadow-green-400 dark:shadow-green-700/50' },
    { icon: Users, title: 'Become a Member', description: 'Join our community and unlock benefits.', link: '/membership/apply', gradient: 'from-green-100 to-green-200 dark:from-green-900 dark:to-green-950', shadowClass: 'shadow-green-100 dark:shadow-green-900/50' },
  ];

  const stats = [
    { value: '10K+', label: 'Active Members', color: 'text-green-700 dark:text-green-400' },
    { value: '50M+', label: 'Total Assets', color: 'text-green-600 dark:text-green-500' },
    { value: '25+', label: 'Years of Service', color: 'text-green-500 dark:text-green-300' },
    { value: '99%', label: 'Member Satisfaction', color: 'text-green-800 dark:text-green-200' },
  ];


  return (
    <>
      <style>{`
        @keyframes floatUp { 0% { transform: translateY(0) scale(1); opacity: 0.2; } 100% { transform: translateY(-100vh) scale(0.5); opacity: 0; } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes statReveal { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes logoFloat { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-15px); } }
        .animate-logo { animation: logoFloat 6s ease-in-out infinite; }
      `}</style>

      <div className="flex flex-col bg-white dark:bg-[#0a0f0c] text-gray-900 dark:text-white transition-colors duration-500">

        {/* ── HERO ── */}
        <section className="relative overflow-hidden min-h-screen flex items-center">
          <div className="absolute inset-0 bg-[url('/src/images/bghd.jpg')] bg-cover bg-center"
               style={{ transition: 'transform 20s linear', transform: heroVisible ? 'scale(1)' : 'scale(1.05)' }} />
          <div className="absolute inset-0 bg-gradient-to-br from-white/90 via-green-50/80 to-green-100/90 dark:from-[#022c22]/95 dark:via-[#064e3b]/95 dark:to-[#065f46]/95 transition-colors duration-500" />
          <Particles />

          <div className={`relative z-10 flex flex-col lg:flex-row items-center justify-between w-full px-5 sm:px-6 lg:px-16 pt-32 pb-20 lg:pt-24 lg:pb-32 transition-all duration-1000 ${heroVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'}`}>

            {/* Logo: Appears first on mobile, right side on desktop */}
            <div className="flex flex-1 justify-center lg:justify-end order-first lg:order-last mb-10 lg:mb-0 lg:pl-10">
              <img
                src={isDarkTheme ? '/logo-dark.png' : '/logo.png'}
                alt="Hilongos MPC Logo"
                className="w-40 sm:w-56 md:w-72 lg:w-full max-w-lg h-auto drop-shadow-2xl animate-logo"
              />
            </div>

            {/* Hero Text */}
            <div className="max-w-2xl w-full text-center lg:text-left flex flex-col items-center lg:items-start">
              <div className="inline-flex items-center gap-2 mb-6 px-4 py-2 rounded-full bg-green-200/50 dark:bg-white/10 border border-green-300 dark:border-white/20 backdrop-blur-md">
                <div className="w-2.5 h-2.5 bg-green-600 dark:bg-green-400 rounded-full animate-pulse" />
                <span className="text-xs sm:text-sm text-green-900 dark:text-white/90 font-medium">Trusted by over 10,000 members</span>
              </div>

              <h1 className="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold mb-6 leading-tight tracking-tight text-gray-900 dark:text-white">
                Southern Leyte Employees<br/>Multi-Purpose<br />
                <span className="bg-clip-text text-transparent bg-gradient-to-r from-green-700 to-green-500 dark:from-green-400 dark:to-green-200">Cooperative</span>
              </h1>

              <p className="text-base sm:text-lg md:text-xl text-gray-700 dark:text-white/80 mb-8 max-w-xl">
                Transforming lives through community-driven financial solutions.
              </p>

              <div className="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                <Link to="/membership/apply" className="px-8 py-3.5 rounded-full bg-green-600 dark:bg-white text-white dark:text-green-900 font-bold shadow-xl hover:-translate-y-1 transition-all text-center">
                  Become a Member
                </Link>
                <button onClick={scrollToWhy} className="px-8 py-3.5 rounded-full bg-white/50 dark:bg-white/10 border border-green-200 dark:border-white/30 font-semibold backdrop-blur-sm text-gray-800 dark:text-white text-center">
                  Learn More
                </button>
              </div>
            </div>
          </div>
        </section>

        {/* ── STATS ── */}
        <section className="relative -mt-8 sm:-mt-16 z-20 mb-8" ref={statsRef}>
          <div className="max-w-6xl mx-auto px-4">
            <Card className="bg-white/80 dark:bg-[#111b17]/90 border-green-100 dark:border-white/10 shadow-2xl backdrop-blur-xl">
              <CardContent className="p-0 grid grid-cols-2 lg:grid-cols-4 divide-x divide-green-50 dark:divide-white/10">
                {stats.map((stat, i) => (
                  <div key={i} className="text-center py-8 px-4" style={{ animation: statsInView ? `statReveal 0.6s ${i * 0.1}s both` : 'opacity-0' }}>
                    <div className={`text-3xl sm:text-4xl font-black ${stat.color}`}><AnimatedCounter value={stat.value} inView={statsInView} /></div>
                    <div className="text-sm text-gray-500 dark:text-white/60">{stat.label}</div>
                  </div>
                ))}
              </CardContent>
            </Card>
          </div>
        </section>

        {/* ── FEATURES ── */}
        <section className="py-24 bg-white dark:bg-[#0a0f0c]" ref={featuresRef} id="why-us">
          <div className="max-w-7xl mx-auto px-4" ref={whySectionRef}>
            <div className="text-center mb-16">
              <span className="text-xs font-bold uppercase tracking-widest text-green-600 dark:text-green-400">Why Choose Us</span>
              <h2 className="text-3xl sm:text-4xl font-bold mt-3 text-gray-900 dark:text-white">More Than a Cooperative</h2>
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {features.map((f, i) => (
                <Card key={i} className="bg-white dark:bg-[#111b17] border-green-100 dark:border-white/10 p-7 hover:-translate-y-2 transition-all shadow-sm"
                      style={{ animation: featuresInView ? `fadeInUp 0.6s ${i * 0.15}s both` : 'opacity-0' }}>
                  <div className={`w-12 h-12 rounded-2xl flex items-center justify-center mb-5 ${f.bgClass}`}><f.icon className={`w-6 h-6 ${f.iconClass}`} /></div>
                  <h3 className="font-bold text-lg mb-2 text-gray-900 dark:text-white">{f.title}</h3>
                  <p className="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">{f.description}</p>
                </Card>
              ))}
            </div>
          </div>
        </section>

        {/* ── SERVICES ── */}
        <section className="py-24 bg-green-50/30 dark:bg-[#0d1410]" ref={servicesRef}>
          <div className="max-w-7xl mx-auto px-4">
            <div className="text-center mb-16">
              <span className="text-xs font-bold uppercase tracking-widest text-green-600 dark:text-green-400">Our Services</span>
              <h2 className="text-3xl sm:text-4xl font-bold mt-3 text-gray-900 dark:text-white">What We Offer</h2>
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {services.map((s, i) => (
                <Link key={i} to={s.link} style={{ animation: servicesInView ? `fadeInUp 0.6s ${i * 0.15}s both` : 'opacity-0' }}>
                  <Card className="bg-white dark:bg-[#111b17] border-green-100 dark:border-white/10 p-7 h-full hover:shadow-xl transition-all group">
                    <div className={`w-12 h-12 rounded-2xl bg-gradient-to-br ${s.gradient} flex items-center justify-center mb-5 shadow-lg ${s.shadowClass}`}>
                      <s.icon className="w-6 h-6 text-green-900 dark:text-white" />
                    </div>
                    <h3 className="font-bold text-lg mb-2 text-gray-900 dark:text-white">{s.title}</h3>
                    <p className="text-gray-600 dark:text-gray-400 text-sm mb-6">{s.description}</p>
                    <div className="text-green-600 dark:text-green-400 font-medium flex items-center gap-2 group-hover:gap-3 transition-all">Learn more <ArrowRight className="w-4 h-4" /></div>
                  </Card>
                </Link>
              ))}
            </div>
          </div>
        </section>

        {/* ── CTA ── */}
        <section className="py-24 bg-white dark:bg-[#0a0f0c]" ref={ctaRef}>
          <div className="max-w-4xl mx-auto px-6" style={{ animation: ctaInView ? 'fadeInUp 0.8s ease both' : 'opacity-0' }}>
            <Card className="rounded-3xl border-0 shadow-2xl bg-gradient-to-br from-green-100 via-green-50 to-green-200 dark:from-[#022c22] dark:via-[#047857] dark:to-[#064e3b] transition-colors duration-500">
              <div className="p-12 text-center">
                <h2 className="text-3xl sm:text-4xl font-bold mb-4 text-green-950 dark:text-white">Ready to Get Started?</h2>
                <p className="text-green-800 dark:text-white/80 text-lg mb-10">Join our community today and experience the benefits of cooperative banking.</p>
                <div className="flex flex-col sm:flex-row gap-4 justify-center">
                  <Link to="/membership/apply" className="px-10 py-4 rounded-full bg-green-600 dark:bg-white text-white dark:text-green-800 text-lg font-bold shadow-xl hover:scale-105 transition-all">Become a Member</Link>
                  <Link to="/contact" className="px-10 py-4 rounded-full border-2 border-green-600 dark:border-white/40 text-green-800 dark:text-white font-bold hover:bg-white/20 transition-all">Contact Us</Link>
                </div>
              </div>
            </Card>
          </div>
        </section>
      </div>
    </>
  );
}
