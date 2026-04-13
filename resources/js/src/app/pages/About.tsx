import { useEffect, useRef, useState } from 'react';
import { Target, Eye, Users, TrendingUp, Award, Building2, Star, Shield, Heart, Zap, ChevronDown, Loader2, CheckCircle2 } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Badge } from '../components/ui/badge';

const ICON_MAP: Record<string, any> = {
  Target, Eye, Users, TrendingUp, Award, Building2,
  Star, Shield, Heart, Zap,
};

/* ─── Reuseable Components (Synced with Loans) ───────────────── */
function Particles() {
  const colorClasses = ['bg-emerald-300 dark:bg-emerald-600', 'bg-blue-300 dark:bg-blue-600', 'bg-emerald-500 dark:bg-emerald-800'];
  const particles = Array.from({ length: 20 }, (_, i) => ({
    id: i,
    size: Math.random() * 5 + 3,
    x: Math.random() * 100,
    delay: Math.random() * 8,
    duration: Math.random() * 10 + 12,
    opacity: Math.random() * 0.3 + 0.1,
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

function Reveal({ children, direction = 'up', delay = 0 }: { children: React.ReactNode, direction?: 'up' | 'left' | 'right', delay?: number }) {
  const ref = useRef(null);
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    const observer = new IntersectionObserver(([entry]) => {
        if (entry.isIntersecting) setIsVisible(true);
        else setIsVisible(false);
      }, { threshold: 0.1 });
    if (ref.current) observer.observe(ref.current);
    return () => observer.disconnect();
  }, []);

  const variants = {
    up: 'translate-y-12',
    left: '-translate-x-12',
    right: 'translate-x-12',
  };

  return (
    <div ref={ref} className={`transition-all duration-1000 ease-out ${isVisible ? 'opacity-100 translate-x-0 translate-y-0' : `opacity-0 ${variants[direction]}`}`}
      style={{ transitionDelay: `${delay}ms` }}>
      {children}
    </div>
  );
}

export function About() {
  const [heroVisible, setHeroVisible] = useState(false);
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const t = setTimeout(() => setHeroVisible(true), 100);
    fetch('/api/about').then(res => res.json()).then(json => { setData(json); setLoading(false); }).catch(() => setLoading(false));
    return () => clearTimeout(t);
  }, []);

  if (loading || !data) {
    return (
      <div className="flex items-center justify-center min-h-screen bg-white dark:bg-[#0a0f0c]">
        <Loader2 className="animate-spin h-10 w-10 text-emerald-600" />
      </div>
    );
  }

  const { hero, vision, mission, history, core_values, board_members } = data;

  return (
    <div className="flex flex-col bg-white dark:bg-[#0a0f0c] transition-colors duration-500">
      <style>{`
        @keyframes floatUp { 0% { transform: translateY(0) scale(1); opacity: 0.2; } 100% { transform: translateY(-100vh) scale(0.5); opacity: 0; } }
      `}</style>

      {/* ── LOAN THEME HERO (SYNCED) ── */}
      <section className="relative min-h-screen flex items-center justify-center overflow-hidden">
        <div className="absolute inset-0 bg-[url('/src/images/bghd.jpg')] bg-cover bg-center"
             style={{ transition: 'transform 20s linear', transform: heroVisible ? 'scale(1)' : 'scale(1.05)' }} />

        {/* Synced Gradient Overlay: Emerald/Blue/Green Mix */}
        <div className="absolute inset-0 bg-gradient-to-br from-white/95 via-emerald-50/90 to-blue-100/95 dark:from-[#022c22]/95 dark:via-[#064e3b]/95 dark:to-[#065f46]/95 transition-colors duration-500" />
        <Particles />

        <div className={`relative z-10 max-w-7xl mx-auto px-6 text-center transition-all duration-1000 ${heroVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'}`}>
          <Badge className="mb-6 bg-emerald-600/10 dark:bg-white/10 text-emerald-700 dark:text-white border-emerald-200 dark:border-white/20 backdrop-blur-sm px-4 py-1 text-sm uppercase tracking-widest font-bold">
            {hero.badge}
          </Badge>
          <h1 className="text-5xl md:text-8xl font-black mb-8 leading-[1.1] text-slate-900 dark:text-white uppercase tracking-tighter">
            {hero.title.split(' ').map((word: string, i: number) =>
                i === hero.title.split(' ').length - 1
                ? <span key={i} className="text-emerald-600 dark:text-emerald-400"> {word}</span>
                : word + ' '
            )}
          </h1>
          <p className="text-lg md:text-2xl text-slate-700 dark:text-white/80 leading-relaxed max-w-3xl mx-auto font-medium">
            {hero.subtitle}
          </p>
        </div>

        <div className="absolute bottom-12 left-1/2 -translate-x-1/2 text-emerald-600 dark:text-emerald-400 animate-bounce">
          <ChevronDown size={40} />
        </div>
      </section>

      {/* ── VISION & MISSION (SYNCED TILES) ── */}
      <section className="py-32 px-6 max-w-7xl mx-auto w-full">
        <div className="grid md:grid-cols-2 gap-12">
          <Reveal direction="up">
            <div className="group p-12 rounded-[3rem] bg-white dark:bg-[#111b17] border border-emerald-100 dark:border-white/10 shadow-xl shadow-emerald-500/5 transition-all duration-500 hover:-translate-y-2">
                <div className="w-20 h-20 rounded-[2rem] bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center mb-10 shadow-lg shadow-emerald-500/30">
                    <Eye className="text-white" size={40} />
                </div>
                <h2 className="text-4xl font-black text-slate-900 dark:text-white uppercase mb-6 tracking-tight">Our Vision</h2>
                <p className="text-xl text-slate-600 dark:text-slate-400 leading-relaxed font-medium">{vision}</p>
            </div>
          </Reveal>
          <Reveal direction="up" delay={200}>
            <div className="group p-12 rounded-[3rem] bg-white dark:bg-[#111b17] border border-blue-100 dark:border-white/10 shadow-xl shadow-blue-500/5 transition-all duration-500 hover:-translate-y-2">
                <div className="w-20 h-20 rounded-[2rem] bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center mb-10 shadow-lg shadow-blue-500/30">
                    <Target className="text-white" size={40} />
                </div>
                <h2 className="text-4xl font-black text-slate-900 dark:text-white uppercase mb-6 tracking-tight">Our Mission</h2>
                <p className="text-xl text-slate-600 dark:text-slate-400 leading-relaxed font-medium">{mission}</p>
            </div>
          </Reveal>
        </div>
      </section>

      {/* ── IMMERSIVE HISTORY (SYNCED) ── */}
      <section className="py-32 bg-emerald-50 dark:bg-[#0d1410] transition-colors relative overflow-hidden">
        <div className="max-w-5xl mx-auto px-6">
          <Reveal direction="up">
            <div className="text-center mb-28">
              <h2 className="text-5xl md:text-6xl font-black text-slate-900 dark:text-white uppercase tracking-tighter">Our Journey</h2>
              <div className="w-24 h-2 bg-emerald-600 mx-auto mt-6 rounded-full" />
            </div>
          </Reveal>

          <div className="relative">
            {/* Synced Vertical Spine */}
            <div className="absolute left-4 md:left-1/2 top-0 bottom-0 w-1.5 bg-emerald-200 dark:bg-emerald-900/30 -translate-x-1/2" />

            <div className="space-y-40">
              {history.map((item: any, i: number) => {
                const isEven = i % 2 === 0;
                return (
                  <div key={i} className={`relative flex items-center justify-between flex-col md:flex-row ${isEven ? 'md:flex-row-reverse' : ''}`}>
                    {/* Pulsing Emerald Marker */}
                    <div className="absolute left-4 md:left-1/2 -translate-x-1/2 w-16 h-16 rounded-full bg-white dark:bg-[#0a0f0c] border-4 border-emerald-600 z-10 flex items-center justify-center shadow-2xl">
                        <div className="w-4 h-4 bg-emerald-600 rounded-full animate-pulse" />
                    </div>

                    <div className="w-full md:w-[45%] ml-16 md:ml-0">
                      <Reveal direction={isEven ? 'left' : 'right'}>
                        <div className="group p-10 rounded-[2.5rem] bg-white dark:bg-[#111b17] shadow-2xl dark:shadow-none border border-slate-100 dark:border-white/5 hover:border-emerald-500 transition-all duration-500">
                          <span className="text-6xl font-black text-emerald-600/10 dark:text-emerald-400/10 absolute top-4 right-8 italic">{item.year}</span>
                          <h3 className="text-3xl font-black text-slate-900 dark:text-white mb-4 uppercase tracking-tight">{item.title}</h3>
                          <p className="text-slate-500 dark:text-slate-400 leading-relaxed font-semibold">{item.desc}</p>
                        </div>
                      </Reveal>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </section>

      {/* ── CORE VALUES (SYNCED CARDS) ── */}
      <section className="py-32 px-6">
        <div className="max-w-7xl mx-auto">
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            {core_values.map((value: any, index: number) => {
              const Icon = ICON_MAP[value.icon] ?? Star;
              return (
                <Reveal key={index} direction="up" delay={index * 100}>
                  <div className="group h-full p-10 rounded-[2.5rem] bg-white dark:bg-[#111b17] border border-slate-200 dark:border-white/10 hover:bg-emerald-600 transition-all duration-500 shadow-lg hover:shadow-emerald-500/20">
                    <div className="w-16 h-16 bg-emerald-50 dark:bg-[#0a0f0c] rounded-2xl flex items-center justify-center mb-8 shadow-inner group-hover:scale-110 group-hover:rotate-12 transition-all">
                      <Icon className="text-emerald-600 group-hover:text-emerald-400 w-8 h-8" />
                    </div>
                    <h3 className="font-black text-xl text-slate-900 dark:text-white group-hover:text-white mb-4 uppercase tracking-tight transition-colors">{value.title}</h3>
                    <p className="text-slate-500 dark:text-slate-400 group-hover:text-white/80 font-medium leading-relaxed transition-colors">{value.description}</p>
                  </div>
                </Reveal>
              );
            })}
          </div>
        </div>
      </section>

      {/* ── BOARD MEMBERS (PREMIUM DARK SYNCED) ── */}
      <section className="py-32 bg-[#022c22] text-white">
        <div className="max-w-7xl mx-auto px-6">
          <Reveal direction="up">
            <div className="mb-24">
                <Badge className="bg-emerald-500/20 text-emerald-400 border-none px-4 py-1 mb-6 text-xs font-bold tracking-widest uppercase">Leadership</Badge>
                <h2 className="text-5xl md:text-7xl font-black uppercase tracking-tighter">The Board of Directors</h2>
            </div>
          </Reveal>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-12">
            {board_members.map((member: any, index: number) => (
              <Reveal key={index} direction="up" delay={index * 100}>
                <div className="group">
                  <div className="relative aspect-[3/4] rounded-[2.5rem] overflow-hidden mb-8 border border-white/10 shadow-2xl">
                    <img src={member.photo} alt={member.name} className="w-full h-full object-cover group-hover:grayscale-0 transition-all duration-700 group-hover:scale-110" />
                    <div className="absolute inset-0 bg-gradient-to-t from-[#022c22] via-transparent opacity-80" />
                  </div>
                  <h3 className="font-black text-2xl tracking-tight text-white mb-2">{member.name}</h3>
                  <p className="text-emerald-400 text-sm font-bold uppercase tracking-[0.2em]">{member.position}</p>
                </div>
              </Reveal>
            ))}
          </div>
        </div>
      </section>
    </div>
  );
}
