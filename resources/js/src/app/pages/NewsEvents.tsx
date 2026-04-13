import { Calendar, MapPin, ArrowRight, ChevronDown, Loader2 } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Badge } from '../components/ui/badge';
import { Button } from '../components/ui/button';
import { useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';

/* ─── Reuseable Hooks ────────────────────────────────────────── */
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

export function NewsEvents() {
    const [heroVisible, setHeroVisible] = useState(false);
    const [eventsRef, eventsInView] = useInView();
    const [newsRef, newsInView] = useInView();

    const [newsEvents, setNewsEvents] = useState<Array<any>>([]);
    const [heroNewsEvents, setHeroNewsEvents] = useState<Array<any>>([]);
    const [news, setNews] = useState<Array<any>>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const t = setTimeout(() => setHeroVisible(true), 100);

        const fetchData = async () => {
            try {
                const [eventsRes, heroRes, newsRes] = await Promise.all([
                    fetch('/api/newsevent'),
                    fetch('/api/newsevent/hero'),
                    fetch('/api/newsevent/news')
                ]);

                const eventsData = await eventsRes.json();
                const heroData = await heroRes.json();
                const newsData = await newsRes.json();

                setNewsEvents(eventsData.data || []);
                setHeroNewsEvents(heroData.data || []);
                setNews(newsData.data || []);
            } catch (error) {
                toast.error('Failed to load page content');
            } finally {
                setLoading(false);
            }
        };

        fetchData();
        return () => clearTimeout(t);
    }, []);

    const scrollToContent = () => {
        document.getElementById('events-grid')?.scrollIntoView({ behavior: 'smooth' });
    };

    return (
        <>
            <style>{`
                @keyframes floatUp { 0% { transform: translateY(0) scale(1); opacity: 0.2; } 100% { transform: translateY(-100vh) scale(0.5); opacity: 0; } }
                @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
                @keyframes bounceSlow { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(10px); } }
            `}</style>

            <div className="flex flex-col bg-white dark:bg-[#0a0f0c] transition-colors duration-500">

                {/* ── Full-Screen Hero Section (Loans Style) ── */}
                <section className="relative min-h-screen flex items-center justify-center overflow-hidden">
                    {/* Background Image with Scale Animation */}
                    <div className="absolute inset-0 bg-[url('/src/images/bghd.jpg')] bg-cover bg-center"
                         style={{ transition: 'transform 20s linear', transform: heroVisible ? 'scale(1)' : 'scale(1.05)' }} />

                    {/* Gradient Overlay matching Loans.tsx */}
                    <div className="absolute inset-0 bg-gradient-to-br from-white/95 via-blue-50/90 to-green-100/95 dark:from-[#022c22]/95 dark:via-[#064e3b]/95 dark:to-[#065f46]/95 transition-colors duration-500" />

                    {/* Content Container */}
                    <div className={`relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center transition-all duration-1000 ${heroVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'}`}>
                        {loading ? (
                            <div className="flex justify-center"><Loader2 className="w-10 h-10 animate-spin text-green-600" /></div>
                        ) : (
                            heroNewsEvents.map((hero, index) => (
                                <div key={index}>
                                    <Badge className="mb-6 bg-blue-600/10 dark:bg-white/10 text-blue-700 dark:text-white border-blue-200 dark:border-white/20 backdrop-blur-sm px-4 py-1 text-sm">
                                        {hero.hero_badge || 'Latest Updates'}
                                    </Badge>
                                    <h1 className="text-5xl sm:text-7xl font-extrabold mb-8 leading-tight text-gray-900 dark:text-white uppercase tracking-tighter">
                                        {hero.hero_header.split(' ').map((word: string, i: number) =>
                                            i === hero.hero_header.split(' ').length - 1
                                            ? <span key={i} className="text-green-600 dark:text-green-400"> {word}</span>
                                            : word + ' '
                                        )}
                                    </h1>
                                    <p className="text-lg sm:text-2xl text-gray-700 dark:text-white/80 leading-relaxed max-w-3xl mx-auto mb-10">
                                        {hero.hero_paragraph}
                                    </p>
                                    <div className="flex flex-col sm:flex-row gap-4 justify-center">
                                        <Button onClick={scrollToContent} size="lg" className="bg-green-600 hover:bg-green-700 text-white rounded-full px-10 py-7 text-lg font-bold shadow-xl transition-transform hover:scale-105">
                                            View Events
                                        </Button>
                                        <Button variant="outline" size="lg" className="border-green-600 text-green-700 dark:text-white dark:border-white/20 rounded-full px-10 py-7 text-lg font-bold backdrop-blur-sm">
                                            Latest News
                                        </Button>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>

                    {/* Animated Scroll Down Indicator */}
                    <div className="absolute bottom-10 left-1/2 -translate-x-1/2 text-green-600 dark:text-white/40 animate-[bounceSlow_2s_infinite] cursor-pointer" onClick={scrollToContent}>
                        <ChevronDown className="w-10 h-10" />
                    </div>
                </section>

                {/* ── Events Grid ── */}
                <section id="events-grid" className="py-24" ref={eventsRef}>
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="text-center mb-16">
                            <h2 className="text-3xl sm:text-5xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Upcoming Events</h2>
                            <p className="text-gray-500 dark:text-gray-400 font-bold mt-2">Join us in our community activities and assemblies</p>
                        </div>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            {newsEvents.map((event, index) => (
                                <Card key={index}
                                    className="hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 rounded-2xl border-green-100 dark:border-white/10 bg-white dark:bg-[#111b17] overflow-hidden"
                                    style={{ animation: eventsInView ? `fadeInUp 0.6s ${index * 0.15}s both` : 'opacity-0' }}
                                >
                                    <div className="aspect-video overflow-hidden">
                                        <img src={event.image} alt={event.title} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                    </div>
                                    <CardHeader>
                                        <Badge className="w-fit bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 font-bold">{event.category}</Badge>
                                        <CardTitle className="text-xl font-bold uppercase mt-2">{event.title}</CardTitle>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                            <Calendar className="w-4 h-4 text-green-600" />
                                            {new Date(event.date).toLocaleDateString()}
                                        </div>
                                        <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                            <MapPin className="w-4 h-4 text-green-600" />
                                            {event.location}
                                        </div>
                                        <p className="text-sm text-gray-600 dark:text-gray-400 line-clamp-3">{event.description}</p>
                                        <Button className="w-full rounded-full border-green-600 text-green-600 hover:bg-green-600 hover:text-white" variant="outline">Learn More</Button>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </div>
                </section>

                {/* ── LATEST NEWS ── */}
                <section className="py-24 bg-green-50/30 dark:bg-[#0d1410]" ref={newsRef}>
                    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="text-center mb-16">
                            <span className="text-xs font-bold uppercase tracking-widest text-green-600 dark:text-green-400">Stay Informed</span>
                            <h2 className="text-3xl sm:text-4xl font-bold mt-3 text-gray-900 dark:text-white">Latest Updates</h2>
                        </div>

                        <div className="space-y-4">
                            {loading ? (
                                <div className="flex justify-center py-10">
                                    <Loader2 className="w-8 h-8 text-green-600 dark:text-green-400 animate-spin" />
                                </div>
                            ) : (
                                news.map((item, index) => (
                                    <Card
                                        key={index}
                                        className="bg-white dark:bg-[#111b17] border-green-100 dark:border-white/10 hover:shadow-md transition-all group"
                                        style={{ animation: newsInView ? `fadeInUp 0.6s ${index * 0.15}s both` : 'opacity-0' }}
                                    >
                                        <CardContent className="p-6 sm:p-8">
                                            <div className="flex flex-col sm:flex-row items-start justify-between gap-6">
                                                <div className="flex-1">
                                                    <p className="text-xs font-semibold text-green-600 dark:text-green-400 mb-2">
                                                        {new Date(item.date).toLocaleDateString('en-US', {
                                                            year: 'numeric', month: 'long', day: 'numeric'
                                                        })}
                                                    </p>
                                                    <h3 className="text-xl font-bold mb-3 text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">
                                                        {item.title}
                                                    </h3>
                                                    <p className="text-gray-600 dark:text-gray-400 leading-relaxed">
                                                        {item.excerpt}
                                                    </p>
                                                </div>
                                                <Button variant="ghost" className="shrink-0 text-green-700 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-white/5 rounded-full px-6 flex items-center gap-2">
                                                    Read More <ArrowRight className="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))
                            )}
                        </div>
                    </div>
                </section>
            </div>
        </>
    );
}
