import { Link } from 'react-router-dom';
import { ArrowRight, CheckCircle2, UserCheck, Users, ChevronDown } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { useEffect, useRef, useState } from 'react';

/* ─── useInView ─── */
function useInView(options = {}) {
  const ref = useRef<HTMLElement | null>(null);
  const [inView, setInView] = useState(false);
  useEffect(() => {
    const el = ref.current;
    if (!el) return;
    const obs = new IntersectionObserver(([entry]) => {
      if (entry.isIntersecting) { setInView(true); obs.disconnect(); }
    }, { threshold: 0.1, ...options });
    obs.observe(el);
    return () => obs.disconnect();
  }, [options]);
  return [ref, inView] as const;
}

/* ─── Particles ─── */
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
      {particles.map(p => (
        <div key={p.id} className={`absolute rounded-full ${p.colorClass}`}
          style={{ width: p.size, height: p.size, left: `${p.x}%`, bottom: '-10px', opacity: p.opacity, animation: `floatUp ${p.duration}s ${p.delay}s infinite linear` }}
        />
      ))}
    </div>
  );
}

/* ─── Overflow Check Badge ─── */
function OverflowCheck({ size = 'sm' }: { size?: 'sm' | 'md' }) {
  const dim = size === 'md'
    ? 'w-10 h-10 -top-4 -left-4 rounded-xl'
    : 'w-8 h-8 -top-3 -left-3 rounded-lg';
  const icon = size === 'md' ? 'w-6 h-6' : 'w-4 h-4';
  return (
    <div className={`absolute z-10 ${dim} bg-green-500 dark:bg-green-400 shadow-lg shadow-green-500/40 dark:shadow-green-400/20 flex items-center justify-center rotate-[-7deg] flex-shrink-0`}>
      <svg viewBox="0 0 24 24" className={icon} fill="none" stroke="white" strokeWidth="3.5" strokeLinecap="round" strokeLinejoin="round">
        <path d="M4 13l5 5L20 7" />
      </svg>
    </div>
  );
}

export function MembershipInfo() {
  const [heroVisible, setHeroVisible] = useState(false);
  const [selectedType, setSelectedType] = useState<string | null>(null);
  const [flippedCard, setFlippedCard] = useState<string | null>(null);
  const [typesRef, typesInView] = useInView();
  const [rightsRef] = useInView();

  useEffect(() => {
    const t = setTimeout(() => setHeroVisible(true), 100);
    return () => clearTimeout(t);
  }, []);

  const membershipTypes = [
    {
      id: '2',
      title: 'Regular Member',
      icon: UserCheck,
      description: 'Full membership with voting rights and access to all cooperative products and services.',
      requirements: ['Resident of Service Area', 'Good moral character', 'Completed PMES'],
      shareCapital: '₱500 minimum',
      gradient: 'from-green-600 to-green-800',
    },
    {
      id: '1',
      title: 'Associate Member',
      icon: Users,
      description: 'Limited membership for those who cannot meet regular membership requirements.',
      requirements: ['Students or Minors', 'Non-residents of area', 'Immediate family of members', 'Completed PMES'],
      shareCapital: '₱250 minimum',
      gradient: 'from-emerald-600 to-emerald-800',
    },
  ];

  const handleSelect = (id: string) => {
    setSelectedType(id);
    if (window.innerWidth < 1024) {
      setFlippedCard(flippedCard === id ? null : id);
    }
  };

  return (
    <>
      <style>{`
        @keyframes floatUp { 0% { transform: translateY(0) scale(1); opacity: 0.2; } 100% { transform: translateY(-100vh) scale(0.5); opacity: 0; } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes bounceSlow { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(10px); } }
        .preserve-3d { transform-style: preserve-3d; -webkit-transform-style: preserve-3d; }
        .backface-hidden { backface-visibility: hidden; -webkit-backface-visibility: hidden; }
        .rotate-y-180 { transform: rotateY(180deg); }
        @keyframes bounceIn { 0% { transform: translateX(-50%) scale(0.5); opacity: 0; } 60% { transform: translateX(-50%) scale(1.15); opacity: 1; } 100% { transform: translateX(-50%) scale(1); opacity: 1; } }
        .animate-bounce-once { animation: bounceIn 0.4s cubic-bezier(0.34,1.56,0.64,1) both; }
      `}</style>

      <div className="flex flex-col bg-white dark:bg-[#0a0f0c] text-gray-900 dark:text-white transition-colors duration-500">

        {/* ── Hero ── */}
        <section className="relative min-h-[100dvh] flex items-center justify-center overflow-hidden">
          <div className="absolute inset-0 bg-[url('/src/images/bghd.jpg')] bg-cover bg-center"
            style={{ transition: 'transform 20s linear', transform: heroVisible ? 'scale(1)' : 'scale(1.05)' }} />
          <div className="absolute inset-0 bg-gradient-to-br from-white/90 via-green-50/80 to-green-100/90 dark:from-[#022c22]/95 dark:via-[#064e3b]/95 dark:to-[#065f46]/95 transition-colors duration-500" />
          <Particles />

          <div className={`relative z-10 max-w-7xl mx-auto px-6 text-center transition-all duration-1000 ${heroVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'}`}>
            <div className="inline-flex items-center gap-2 mb-6 px-4 py-2 rounded-full bg-green-200/50 dark:bg-white/10 border border-green-300 dark:border-white/20 backdrop-blur-md">
              <div className="w-2.5 h-2.5 bg-green-600 dark:bg-green-400 rounded-full animate-pulse" />
              <span className="text-xs sm:text-sm text-green-900 dark:text-white/90 font-medium uppercase tracking-widest">Join Our Community</span>
            </div>
            <h1 className="text-5xl sm:text-7xl font-extrabold mb-6 uppercase tracking-tight text-gray-900 dark:text-white leading-[0.9]">
              Membership{' '}
              <span className="bg-clip-text text-transparent bg-gradient-to-r from-green-700 to-green-500 dark:from-green-400 dark:to-green-200">
                Info
              </span>
            </h1>
            <p className="text-lg sm:text-xl text-gray-700 dark:text-white/80 max-w-2xl mx-auto mb-10 font-medium leading-relaxed">
              Your gateway to financial empowerment and cooperative growth.
            </p>
            <a
              href="#types"
              className="inline-block px-10 py-3.5 rounded-full bg-green-600 dark:bg-white text-white dark:text-green-900 font-bold shadow-xl hover:-translate-y-1 transition-all uppercase tracking-widest"
            >
              Choose Membership Type
            </a>
          </div>

          <div className="absolute bottom-8 left-1/2 -translate-x-1/2 text-green-600 dark:text-white/40 animate-[bounceSlow_2s_infinite]">
            <ChevronDown className="w-8 h-8" />
          </div>
        </section>

        {/* ── Membership Types ── */}
        <section id="types" className="py-24 bg-green-50/30 dark:bg-[#0d1410]" ref={typesRef}>
          <div className="max-w-7xl mx-auto px-6">
            <div className="text-center mb-16">
              <span className="text-xs font-bold uppercase tracking-widest text-green-600 dark:text-green-400">Choose Your Path</span>
              <h2 className="text-3xl sm:text-4xl font-bold mt-3 text-gray-900 dark:text-white">Membership Types</h2>
              <p className="text-gray-500 dark:text-gray-400 mt-2 text-sm">Hover to explore, click to select your membership type</p>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-10 max-w-5xl mx-auto mb-16">
              {membershipTypes.map((type) => (
                <div
                  key={type.id}
                  className="group relative"
                  style={{ perspective: '1500px', height: '520px' }}
                  onClick={() => handleSelect(type.id)}
                >
                  {/* ── Floating "✓ Selected" badge — always on top, outside flip ── */}
                  {selectedType === type.id && (
                    <div className="absolute -top-4 left-1/2 -translate-x-1/2 z-50 flex items-center gap-2 px-5 py-2 rounded-full bg-green-500 shadow-lg shadow-green-500/50 dark:shadow-green-400/30 animate-bounce-once">
                      <svg viewBox="0 0 24 24" className="w-4 h-4" fill="none" stroke="white" strokeWidth="3.5" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M4 13l5 5L20 7" />
                      </svg>
                      <span className="text-white text-xs font-black uppercase tracking-widest">Selected</span>
                    </div>
                  )}

                  <div className={`relative w-full h-full transition-all duration-500 preserve-3d cursor-pointer rounded-[2.5rem]
                    ${selectedType === type.id
                      ? 'scale-[1.04]'
                      : selectedType !== null
                        ? 'scale-[0.97]'
                        : 'hover:scale-[1.02]'}
                    ${flippedCard === type.id ? 'rotate-y-180' : 'lg:group-hover:rotate-y-180'}`}
                  >
                    {/* Front */}
                    <div className={`absolute inset-0 backface-hidden rounded-[2.5rem] bg-gradient-to-br ${type.gradient} p-10 flex flex-col shadow-2xl border border-white/10 z-20 transition-all duration-500 ${selectedType !== null && selectedType !== type.id ? 'opacity-40 saturate-0' : ''}`}>
                      <div className="flex justify-between items-start mb-8">
                        <span className="bg-white/20 text-white border border-white/30 backdrop-blur-sm px-4 py-1 rounded-full text-xs font-bold">
                          Capital: {type.shareCapital}
                        </span>
                      </div>
                      <h3 className="text-3xl font-black text-white mb-4 uppercase tracking-tighter">{type.title}</h3>
                      <p className="text-base text-white/80 mb-8 font-medium leading-relaxed">{type.description}</p>
                      <ul className="space-y-3">
                        {type.requirements.map((req, idx) => (
                          <li key={idx} className="flex items-start gap-3 text-sm text-white/90 font-bold">
                            <CheckCircle2 className="w-5 h-5 text-green-300 flex-shrink-0 mt-0.5" />
                            {req}
                          </li>
                        ))}
                      </ul>
                      <div className="mt-auto flex items-center text-white/50 text-[10px] font-bold uppercase tracking-widest pt-6">
                        Hover to view more
                        <ArrowRight className="ml-2 w-4 h-4" />
                      </div>

                      {/* Selected ribbon at the bottom of front face */}
                      {selectedType === type.id && (
                        <div className="absolute bottom-0 left-0 right-0 bg-white/20 backdrop-blur-sm rounded-b-[2.5rem] py-3 flex items-center justify-center gap-2">
                          <CheckCircle2 className="w-4 h-4 text-white" />
                          <span className="text-white text-[11px] font-black uppercase tracking-[0.2em]">Currently Selected</span>
                        </div>
                      )}
                    </div>

                    {/* Back */}
                    <div className="absolute inset-0 backface-hidden rotate-y-180 rounded-[2.5rem] bg-white dark:bg-[#111b17] p-10 flex flex-col items-center justify-center text-center shadow-2xl border border-green-100 dark:border-white/10 z-10">
                      <div className="w-16 h-16 rounded-2xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center mb-6">
                        <type.icon className="w-8 h-8 text-green-600 dark:text-green-400" />
                      </div>
                      <h3 className="text-2xl font-black text-gray-900 dark:text-white mb-2 uppercase">{type.title}</h3>
                      <p className="bg-clip-text text-transparent bg-gradient-to-r from-green-700 to-green-500 dark:from-green-400 dark:to-green-200 font-black text-4xl mb-6">
                        {type.shareCapital}
                      </p>
                      <p className="text-sm text-gray-500 dark:text-gray-400 mb-8 leading-relaxed font-bold">
                        Click this card to select this membership for your application.
                      </p>
                      <div className={`px-8 py-3 rounded-full font-black uppercase tracking-widest text-xs transition-all duration-300
                        ${selectedType === type.id
                          ? 'bg-green-600 dark:bg-green-500 text-white scale-105 shadow-lg shadow-green-600/30'
                          : 'bg-gray-100 dark:bg-white/10 text-gray-400'}`}>
                        {selectedType === type.id ? '✓ Membership Selected' : 'Select Membership'}
                      </div>

                      {/* Selected ribbon at the bottom of back face */}
                      {selectedType === type.id && (
                        <div className="absolute bottom-0 left-0 right-0 bg-green-600/10 dark:bg-green-500/20 rounded-b-[2.5rem] py-3 flex items-center justify-center gap-2 border-t border-green-200 dark:border-green-500/30">
                          <CheckCircle2 className="w-4 h-4 text-green-600 dark:text-green-400" />
                          <span className="text-green-700 dark:text-green-400 text-[11px] font-black uppercase tracking-[0.2em]">Currently Selected</span>
                        </div>
                      )}
                    </div>
                  </div>
                </div>
              ))}
            </div>

            {/* Apply Button */}
            <div className={`max-w-md mx-auto transition-all duration-500 ${typesInView ? 'translate-y-0 opacity-100' : 'translate-y-10 opacity-0'}`}>
              <Button
                asChild
                disabled={!selectedType}
                size="lg"
                className={`w-full rounded-full py-8 text-xl font-black shadow-2xl transition-all duration-300 uppercase tracking-wide
                  ${selectedType
                    ? 'bg-green-600 hover:bg-green-700 dark:bg-white dark:text-green-900 dark:hover:bg-green-50 text-white scale-105'
                    : 'bg-gray-200 dark:bg-white/10 text-gray-400 cursor-not-allowed'}`}
              >
                {selectedType ? (
                  <Link to={`/membership/apply?type=${selectedType}`}>
                    Apply Now <ArrowRight className="ml-3 w-6 h-6" />
                  </Link>
                ) : (
                  <span>Select a Membership Type Above</span>
                )}
              </Button>
              {selectedType && (
                <p className="text-center mt-4 text-green-600 dark:text-green-400 font-black uppercase tracking-widest text-[10px] animate-pulse">
                  Ready to proceed with {membershipTypes.find(t => t.id === selectedType)?.title}
                </p>
              )}
            </div>
          </div>
        </section>

        {/* ── Rights & Privileges ── */}
        <section className="py-24 px-6 bg-white dark:bg-[#0a0f0c] border-t border-green-100 dark:border-white/5" ref={rightsRef}>
          <div className="max-w-7xl mx-auto">
            <div className="text-center mb-16">
              <span className="text-xs font-bold uppercase tracking-widest text-green-600 dark:text-green-400">What You Get</span>
              <h2 className="text-3xl sm:text-4xl font-bold mt-3 text-gray-900 dark:text-white">Rights & Privileges</h2>
            </div>

            <div className="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
              {[
                {
                  id: '2',
                  title: 'Regular Member',
                  list: [
                    'Vote in annual general assemblies',
                    'Run for board positions',
                    'Access all loan products',
                    'Receive annual dividends',
                    'Full share in coop governance',
                  ],
                },
                {
                  id: '1',
                  title: 'Associate Member',
                  list: [
                    'Access selected loan products',
                    'Open savings accounts',
                    'Attend member seminars',
                    'Receive patronage refunds',
                    'No voting rights (until upgrade)',
                  ],
                },
              ].map((role) => (
                <Card
                  key={role.id}
                  className={`rounded-[2.5rem] border border-green-100 dark:border-white/10 transition-all duration-500 overflow-visible
                    ${selectedType === role.id
                      ? 'scale-[1.02] shadow-[0_8px_40px_rgba(34,197,94,0.2)] dark:shadow-[0_8px_50px_rgba(74,222,128,0.15)] bg-green-50/40 dark:bg-green-900/10'
                      : 'bg-white dark:bg-[#111b17]'}`}
                >
                  <CardHeader className="bg-green-600 dark:bg-green-900/40 p-6 rounded-t-[2.3rem]">
                    <CardTitle className="text-white uppercase tracking-wider text-sm font-bold">
                      As a {role.title}, you can:
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="p-8">
                    <ul className="space-y-4">
                      {role.list.map((item, idx) => (
                        <li key={idx} className="relative flex items-center pt-3 pb-3 pr-3 pl-8 rounded-2xl bg-green-50/50 dark:bg-green-500/10 border border-green-100 dark:border-green-500/20 overflow-visible">
                          <OverflowCheck size="sm" />
                          <span className="text-sm font-bold text-gray-700 dark:text-gray-300">{item}</span>
                        </li>
                      ))}
                    </ul>
                  </CardContent>
                </Card>
              ))}
            </div>
          </div>
        </section>

        {/* ── CTA ── */}
        <section className="py-24 bg-green-50/30 dark:bg-[#0d1410] px-6 transition-colors duration-500">
          <Card className="max-w-4xl mx-auto rounded-3xl border-0 shadow-2xl bg-gradient-to-br from-green-100 via-green-50 to-green-200 dark:from-[#022c22] dark:via-[#047857] dark:to-[#064e3b] transition-colors duration-500">
            <CardContent className="p-12 text-center">
              <h2 className="text-3xl sm:text-4xl font-bold mb-4 text-green-950 dark:text-white">Ready to Get Started?</h2>
              <p className="text-green-800 dark:text-white/80 text-lg mb-10">
                Join our cooperative and start your journey toward financial empowerment.
              </p>
              <div className="flex flex-col sm:flex-row gap-4 justify-center">
                <Link
                  to="/membership/apply"
                  className="px-10 py-4 rounded-full bg-green-600 dark:bg-white text-white dark:text-green-800 text-lg font-bold shadow-xl hover:scale-105 transition-all"
                >
                  Apply Now <ArrowRight className="inline ml-2 w-5 h-5" />
                </Link>
                <Link
                  to="/membership/steps"
                  className="px-10 py-4 rounded-full border-2 border-green-600 dark:border-white/40 text-green-800 dark:text-white font-bold hover:bg-white/20 transition-all"
                >
                  View Steps
                </Link>
              </div>
            </CardContent>
          </Card>
        </section>

      </div>
    </>
  );
}
