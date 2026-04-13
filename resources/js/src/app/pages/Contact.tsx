import { useState, useEffect, useRef } from 'react';
import { useForm } from 'react-hook-form';
import { toast } from 'sonner';
import { Send, MapPin, Phone, Clock, Loader2, Facebook, Instagram, Youtube, ChevronDown } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { Textarea } from '../components/ui/textarea';

declare global { interface Window { L: any } }

const DEFAULT_COORDS = "10.37,124.75";

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

/* ─── Reveal on scroll ─── */
function Reveal({ children, direction = 'up', delay = 0 }: {
  children: React.ReactNode;
  direction?: 'up' | 'left' | 'right';
  delay?: number;
}) {
  const ref = useRef<HTMLDivElement>(null);
  const [visible, setVisible] = useState(false);
  useEffect(() => {
    const obs = new IntersectionObserver(([e]) => { if (e.isIntersecting) setVisible(true); }, { threshold: 0.1 });
    if (ref.current) obs.observe(ref.current);
    return () => obs.disconnect();
  }, []);
  const initial = { up: 'translate-y-10', left: '-translate-x-10', right: 'translate-x-10' }[direction];
  return (
    <div
      ref={ref}
      className={`transition-all duration-700 ease-out ${visible ? 'opacity-100 translate-x-0 translate-y-0' : `opacity-0 ${initial}`}`}
      style={{ transitionDelay: `${delay}ms` }}
    >
      {children}
    </div>
  );
}

/* ─── Leaflet Map ─── */
function BranchMap({ value }: { value: string }) {
  const ref = useRef<HTMLDivElement>(null);
  const mapObj = useRef<any>(null);
  const coordsStr = value?.trim() || DEFAULT_COORDS;
  const isCoords = /^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/.test(coordsStr);

  const loadLeaflet = (cb: () => void) => {
    if (!document.getElementById('leaflet-css')) {
      const link = document.createElement('link');
      link.id = 'leaflet-css'; link.rel = 'stylesheet';
      link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
      document.head.appendChild(link);
    }
    if (window.L) { cb(); return; }
    const script = document.createElement('script');
    script.id = 'leaflet-js';
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = cb;
    document.head.appendChild(script);
  };

  useEffect(() => {
    if (!isCoords) return;
    const [lat, lng] = coordsStr.split(',').map(Number);
    loadLeaflet(() => {
      if (!ref.current) return;
      if (mapObj.current) mapObj.current.remove();
      mapObj.current = window.L.map(ref.current, { scrollWheelZoom: false }).setView([lat, lng], 15);
      window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(mapObj.current);
      window.L.marker([lat, lng]).addTo(mapObj.current).bindPopup('<b>Main Branch</b><br>Our Primary Location').openPopup();
    });
    return () => { if (mapObj.current) mapObj.current.remove(); };
  }, [coordsStr]);

  return (
    <div
      ref={ref}
      className="w-full h-full min-h-[360px] contrast-125 transition-all duration-700 dark:invert dark:hue-rotate-180"
    />
  );
}

/* ─── Main Contact Component ─── */
export function Contact() {
  const { register, handleSubmit, reset } = useForm<any>();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [heroVisible, setHeroVisible] = useState(false);

  useEffect(() => {
    const t = setTimeout(() => setHeroVisible(true), 100);
    fetch('/api/contact')
      .then(r => r.json())
      .then(json => { setData(json); setLoading(false); })
      .catch(() => setLoading(false));
    return () => clearTimeout(t);
  }, []);

  const onSubmit = async (formData: any) => {
    setIsSubmitting(true);
    try {
      const res = await fetch('/api/contact/submit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData),
      });
      if (res.ok) { toast.success('Message sent successfully!'); reset(); }
      else toast.error('Failed to send message.');
    } catch { toast.error('Network error.'); }
    finally { setIsSubmitting(false); }
  };

  if (loading || !data) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-white dark:bg-[#0a0f0c]">
        <Loader2 className="animate-spin h-10 w-10 text-green-600 dark:text-green-400" />
      </div>
    );
  }

  return (
    <>
      <style>{`
        @keyframes floatUp { 0% { transform: translateY(0) scale(1); opacity: 0.2; } 100% { transform: translateY(-100vh) scale(0.5); opacity: 0; } }
        @keyframes bounceSlow { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(10px); } }
      `}</style>

      <div className="flex flex-col bg-white dark:bg-[#0a0f0c] text-gray-900 dark:text-white transition-colors duration-500">

        {/* ── Hero + Form ── */}
        <section className="relative min-h-[100dvh] flex items-center justify-center overflow-hidden py-28">
          <div
            className="absolute inset-0 bg-[url('/src/images/bghd.jpg')] bg-cover bg-center"
            style={{ transition: 'transform 20s linear', transform: heroVisible ? 'scale(1)' : 'scale(1.05)' }}
          />
          <div className="absolute inset-0 bg-gradient-to-br from-white/90 via-green-50/80 to-green-100/90 dark:from-[#022c22]/95 dark:via-[#064e3b]/95 dark:to-[#065f46]/95 transition-colors duration-500" />
          <Particles />

          <div className={`relative z-10 w-full max-w-7xl mx-auto px-6 transition-all duration-1000 ${heroVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'}`}>

            {/* Section header */}
            <div className="text-center mb-14">
              <div className="inline-flex items-center gap-2 mb-6 px-4 py-2 rounded-full bg-green-200/50 dark:bg-white/10 border border-green-300 dark:border-white/20 backdrop-blur-md">
                <div className="w-2.5 h-2.5 bg-green-600 dark:bg-green-400 rounded-full animate-pulse" />
                <span className="text-xs sm:text-sm text-green-900 dark:text-white/90 font-medium uppercase tracking-widest">Get in Touch</span>
              </div>
              <h1 className="text-5xl sm:text-7xl font-extrabold uppercase tracking-tight text-gray-900 dark:text-white leading-[0.9]">
                Contact{' '}
                <span className="bg-clip-text text-transparent bg-gradient-to-r from-green-700 to-green-500 dark:from-green-400 dark:to-green-200">
                  Us
                </span>
              </h1>
              <p className="mt-4 text-lg text-gray-700 dark:text-white/80 max-w-xl mx-auto font-medium leading-relaxed">
                Reach out and we'll get back to you as soon as possible.
              </p>
            </div>

            {/* Form + Map grid */}
            <div className="grid lg:grid-cols-5 gap-8 items-start">

              {/* Contact Form */}
              <div className="lg:col-span-3">
                <Reveal direction="up">
                  <div className="p-8 md:p-12 rounded-[2.5rem] bg-white/80 dark:bg-[#111b17]/80 backdrop-blur-xl border border-green-100 dark:border-white/10 shadow-2xl">
                    <h2 className="text-xl font-black uppercase tracking-tighter text-gray-900 dark:text-white mb-8">
                      Send a Message
                    </h2>
                    <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
                      <div className="grid md:grid-cols-2 gap-6">
                        <div className="space-y-2">
                          <Label className="text-[10px] uppercase tracking-widest font-black text-gray-500 dark:text-green-400">Full Name</Label>
                          <Input
                            {...register('name', { required: true })}
                            className="h-12 rounded-2xl border-green-100 dark:border-white/10 bg-white dark:bg-white/5 focus:ring-green-500 focus:border-green-500 transition-colors"
                            placeholder="Juan dela Cruz"
                          />
                        </div>
                        <div className="space-y-2">
                          <Label className="text-[10px] uppercase tracking-widest font-black text-gray-500 dark:text-green-400">Email Address</Label>
                          <Input
                            {...register('email', { required: true })}
                            type="email"
                            className="h-12 rounded-2xl border-green-100 dark:border-white/10 bg-white dark:bg-white/5 focus:ring-green-500 focus:border-green-500 transition-colors"
                            placeholder="juan@example.com"
                          />
                        </div>
                      </div>
                      <div className="space-y-2">
                        <Label className="text-[10px] uppercase tracking-widest font-black text-gray-500 dark:text-green-400">Subject</Label>
                        <Input
                          {...register('subject', { required: true })}
                          className="h-12 rounded-2xl border-green-100 dark:border-white/10 bg-white dark:bg-white/5 focus:ring-green-500 focus:border-green-500 transition-colors"
                          placeholder="How can we help?"
                        />
                      </div>
                      <div className="space-y-2">
                        <Label className="text-[10px] uppercase tracking-widest font-black text-gray-500 dark:text-green-400">Message</Label>
                        <Textarea
                          {...register('message', { required: true })}
                          rows={6}
                          className="rounded-2xl border-green-100 dark:border-white/10 bg-white dark:bg-white/5 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                          placeholder="Tell us more about your inquiry..."
                        />
                      </div>
                      <button
                        type="submit"
                        disabled={isSubmitting}
                        className="w-full py-4 rounded-2xl bg-green-600 dark:bg-white text-white dark:text-green-900 font-black uppercase tracking-widest text-sm shadow-xl hover:-translate-y-1 hover:bg-green-700 dark:hover:bg-green-50 transition-all flex items-center justify-center gap-3 disabled:opacity-60 disabled:pointer-events-none"
                      >
                        {isSubmitting ? <Loader2 className="animate-spin h-5 w-5" /> : <Send className="h-5 w-5" />}
                        {isSubmitting ? 'Sending...' : 'Send Message'}
                      </button>
                    </form>
                  </div>
                </Reveal>
              </div>

              {/* Map + Socials */}
              <div className="lg:col-span-2 space-y-6">
                <Reveal direction="right" delay={150}>
                  <div className="rounded-[2.5rem] overflow-hidden border border-green-100 dark:border-white/10 shadow-2xl h-[360px] bg-green-50 dark:bg-[#111b17]">
                    <BranchMap value={data.maps_embed_url} />
                  </div>
                </Reveal>

                <Reveal direction="right" delay={300}>
                  <div className="p-8 rounded-[2.5rem] bg-gradient-to-br from-green-100 via-green-50 to-green-200 dark:from-[#022c22] dark:via-[#047857] dark:to-[#064e3b] border border-green-200 dark:border-white/10 shadow-xl">
                    <h3 className="text-lg font-black uppercase tracking-tighter text-green-950 dark:text-white mb-2">Connect With Us</h3>
                    <p className="text-green-800 dark:text-white/60 mb-6 text-sm font-medium">
                      Follow us for the latest updates, news, and community stories.
                    </p>
                    <div className="flex gap-3">
                      {[
                        { Icon: Facebook, url: data.social?.facebook },
                        { Icon: Instagram, url: data.social?.instagram },
                        { Icon: Youtube, url: data.social?.youtube },
                      ].map((soc, i) => soc.url && (
                        <a
                          key={i}
                          href={soc.url}
                          target="_blank"
                          rel="noreferrer"
                          className="h-12 w-12 rounded-2xl bg-green-600 dark:bg-white/10 flex items-center justify-center text-white hover:bg-green-700 dark:hover:bg-green-500 hover:-translate-y-1 transition-all shadow-lg"
                        >
                          <soc.Icon className="h-5 w-5" />
                        </a>
                      ))}
                    </div>
                  </div>
                </Reveal>
              </div>
            </div>
          </div>

          <div className="absolute bottom-8 left-1/2 -translate-x-1/2 text-green-600 dark:text-white/40 animate-[bounceSlow_2s_infinite]">
            <ChevronDown className="w-8 h-8" />
          </div>
        </section>

        {/* ── Branches ── */}
        {data.branches?.length > 0 && (
          <section className="py-24 bg-green-50/30 dark:bg-[#0d1410] px-6">
            <div className="max-w-7xl mx-auto">
              <div className="text-center mb-16">
                <span className="text-xs font-bold uppercase tracking-widest text-green-600 dark:text-green-400">Locations</span>
                <h2 className="text-3xl sm:text-4xl font-bold mt-3 text-gray-900 dark:text-white">Our Branches</h2>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                {data.branches.map((branch: any, i: number) => (
                  <Reveal key={i} direction="up" delay={i * 100}>
                    <Card className="group h-full rounded-[2rem] bg-white dark:bg-[#111b17] border border-green-100 dark:border-white/10 hover:shadow-xl hover:-translate-y-2 transition-all duration-500 shadow-sm overflow-hidden">
                      <CardHeader className="p-8 pb-4">
                        <div className="w-14 h-14 bg-green-100 dark:bg-green-500/10 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-green-200 dark:group-hover:bg-green-500/20 transition-all">
                          <MapPin className="text-green-600 dark:text-green-400 w-7 h-7" />
                        </div>
                        <CardTitle className="text-xl font-black uppercase tracking-tighter text-gray-900 dark:text-white">
                          {branch.name}
                        </CardTitle>
                      </CardHeader>
                      <CardContent className="p-8 pt-0 space-y-4">
                        <div className="flex gap-3 items-start text-gray-600 dark:text-gray-400">
                          <MapPin className="h-4 w-4 shrink-0 text-green-500 mt-0.5" />
                          <span className="text-sm font-medium leading-relaxed">{branch.address}</span>
                        </div>
                        <div className="flex gap-3 items-center text-gray-600 dark:text-gray-400">
                          <Phone className="h-4 w-4 shrink-0 text-green-500" />
                          <span className="text-sm font-medium">{branch.phone}</span>
                        </div>
                        <div className="flex gap-3 items-center text-gray-600 dark:text-gray-400">
                          <Clock className="h-4 w-4 shrink-0 text-green-500" />
                          <span className="text-sm font-medium">{branch.hours}</span>
                        </div>
                      </CardContent>
                    </Card>
                  </Reveal>
                ))}
              </div>
            </div>
          </section>
        )}

        {/* ── CTA ── */}
        <section className="py-24 bg-white dark:bg-[#0a0f0c] px-6 transition-colors duration-500">
          <Card className="max-w-4xl mx-auto rounded-3xl border-0 shadow-2xl bg-gradient-to-br from-green-100 via-green-50 to-green-200 dark:from-[#022c22] dark:via-[#047857] dark:to-[#064e3b] transition-colors duration-500">
            <CardContent className="p-12 text-center">
              <h2 className="text-3xl sm:text-4xl font-bold mb-4 text-green-950 dark:text-white">Ready to Join?</h2>
              <p className="text-green-800 dark:text-white/80 text-lg mb-10">
                Become a member today and experience the benefits of cooperative banking.
              </p>
              <div className="flex flex-col sm:flex-row gap-4 justify-center">
                <a
                  href="/membership/apply"
                  className="px-10 py-4 rounded-full bg-green-600 dark:bg-white text-white dark:text-green-800 text-lg font-bold shadow-xl hover:scale-105 transition-all"
                >
                  Become a Member
                </a>
                <a
                  href="/membership/steps"
                  className="px-10 py-4 rounded-full border-2 border-green-600 dark:border-white/40 text-green-800 dark:text-white font-bold hover:bg-white/20 transition-all"
                >
                  View Steps
                </a>
              </div>
            </CardContent>
          </Card>
        </section>

      </div>
    </>
  );
}
