import { Link, Outlet, useLocation } from "react-router";
import { Sun, Moon, Menu, X, Home, Building2, Users, Newspaper, Info, Phone, Calculator, LogIn, MapPin, Facebook, ChevronDown, Mail, LogOut } from "lucide-react";
import { useTheme } from "next-themes";
import { useState, useEffect } from "react";
import { Button } from "./ui/button";

export function Layout() {
    const { theme, resolvedTheme, setTheme } = useTheme();
    const location = useLocation();
    const [mounted, setMounted] = useState(false);
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const [isAuthenticated, setIsAuthenticated] = useState(false);

    useEffect(() => setMounted(true), []);

    useEffect(() => {
        // Check authentication status
        const checkAuthStatus = async () => {
            try {
                const res = await fetch('/api/auth-status', {
                    credentials: 'include',
                });
                const data = await res.json();
                setIsAuthenticated(data.authenticated);
            } catch (error) {
                setIsAuthenticated(false);
            }
        };

        checkAuthStatus();
    }, [location.pathname]); // Re-check when location changes

    useEffect(() => {
        window.scrollTo({ top: 0, behavior: "instant" });
    }, [location.pathname]);

    const handleLogout = async () => {
        try {
            await fetch('/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'include',
            });
            // Redirect to home after logout
            window.location.href = '/';
        } catch (error) {
            console.error('Logout failed:', error);
        }
    };

    const isDarkTheme = mounted ? (resolvedTheme === 'dark' || theme === 'dark') : false;
    const logoSrc = isDarkTheme ? '/logo-dark.png' : '/logo.png';

    const hideShell = location.pathname === '/login';

    if (!mounted) return null;

    if (hideShell) {
        return (
            <main className="min-h-screen">
                <Outlet />
            </main>
        );
    }

    const navigation = [
        { name: "Home", href: "/", icon: Home },
        {
            name: "Products",
            href: "#",
            icon: Building2,
            submenu: [
                { name: "Loan Types", href: "/products/loans" },
                { name: "Deposits & Savings", href: "/products/deposits" },
            ],
        },
        {
            name: "Membership",
            href: "#",
            icon: Users,
            submenu: [
                { name: "How to Join", href: "/membership/steps" },
                { name: "Types of Membership", href: "/membership/info" },

            ],
        },
        { name: "What's New", href: "/news", icon: Newspaper },
        { name: "About Us", href: "/about", icon: Info },
        { name: "Contact", href: "/contact", icon: Phone },
        { name: "Calculator", href: "/calculator", icon: Calculator },
    ];

    return (
        <div className="min-h-screen flex flex-col bg-white dark:bg-[#0a0f0c] text-gray-900 dark:text-gray-100 transition-colors duration-300 font-sans">

            {/* ── HEADER ── */}
            <header className="fixed top-0 left-0 right-0 z-[100] bg-white/95 dark:bg-[#0a0f0c]/95 backdrop-blur-md border-b border-green-100 dark:border-white/5 h-20 flex items-center shadow-sm">
                <div className="max-w-7xl mx-auto px-4 w-full">
                    <div className="flex justify-between items-center gap-2">

                        {/* Logo & Brand */}
                        <Link to="/" className="flex items-center gap-2 sm:gap-3 group flex-shrink-0">
                            <img
                                src={logoSrc}
                                alt="Logo"
                                className="w-9 h-9 sm:w-11 sm:h-11 object-contain bg-transparent rounded-none p-0 shadow-none"
                            />
                            <div className="flex flex-col">
                                <span className="text-base sm:text-lg font-black tracking-tight bg-gradient-to-r from-green-800 to-green-600 dark:from-green-500 dark:to-green-300 bg-clip-text text-transparent uppercase leading-none">
                                    SLEM COOP
                                </span>
                                <span className="text-[9px] font-bold text-gray-400 dark:text-gray-500 tracking-tighter uppercase">Est. since 1965</span>
                            </div>
                        </Link>

                        {/* Desktop Nav */}
                        <nav className="hidden lg:flex items-center gap-1">
                            {navigation.map((item) => (
                                <div key={item.name} className="relative group h-20 flex items-center">
                                    {item.submenu ? (
                                        <button className="flex items-center gap-1 px-3 py-2 rounded-full text-[13px] font-bold text-gray-700 dark:text-gray-300 hover:text-green-700 transition-colors">
                                            {item.name}
                                            <ChevronDown className="w-3.5 h-3.5 opacity-50 group-hover:rotate-180 transition-transform" />
                                        </button>
                                    ) : (
                                        <Link to={item.href} className={`px-4 py-2 rounded-full text-[13px] font-bold transition-all ${location.pathname === item.href ? "bg-green-600 text-white" : "text-gray-700 dark:text-gray-300 hover:bg-green-50 dark:hover:bg-white/5"}`}>
                                            {item.name}
                                        </Link>
                                    )}
                                    {item.submenu && (
                                        <div className="absolute top-[75%] left-0 w-48 bg-white dark:bg-[#111b17] border border-green-100 dark:border-white/10 shadow-xl rounded-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all py-2 z-50">
                                            {item.submenu.map(sub => (
                                                <Link key={sub.name} to={sub.href} className="block px-5 py-2.5 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-green-600 hover:text-white">
                                                    {sub.name}
                                                </Link>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            ))}
                        </nav>

                        {/* Actions */}
                        <div className="flex items-center gap-1.5 sm:gap-3">
                            <Button
                                variant="ghost"
                                size="icon"
                                onClick={() => setTheme(theme === "dark" ? "light" : "dark")}
                                className="rounded-full w-9 h-9 hover:bg-green-50 dark:hover:bg-white/5"
                            >
                                {theme === "dark" ? <Sun className="w-4 h-4 text-white-400" /> : <Moon className="w-4 h-4 text-green-800" />}
                            </Button>

                            {isAuthenticated ? (
                                <Button
                                    onClick={handleLogout}
                                    className="rounded-full bg-red-600 hover:bg-red-700 text-white font-bold h-9 px-4 sm:px-6 text-xs uppercase tracking-wider flex items-center gap-2"
                                >
                                    <LogOut className="w-4 h-4" />
                                    Logout
                                </Button>
                            ) : (
                                <Link to="/login">
                                    <Button className="rounded-full bg-green-700 hover:bg-green-800 text-white font-bold h-9 px-4 sm:px-6 text-xs uppercase tracking-wider flex items-center gap-2">

                                        Login
                                    </Button>
                                </Link>
                            )}

                            <Button
                                variant="ghost"
                                size="icon"
                                className="lg:hidden rounded-full w-9 h-9"
                                onClick={() => setIsMenuOpen(!isMenuOpen)}
                            >
                                {isMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5 text-green-800 dark:text-green-500" />}
                            </Button>
                        </div>
                    </div>
                </div>

                {/* Mobile Menu */}
                {isMenuOpen && (
                    <div className="lg:hidden absolute top-20 left-0 right-0 bg-white dark:bg-[#0a0f0c] border-b border-green-100 dark:border-white/5 p-4 shadow-2xl animate-in slide-in-from-top duration-300">
                        {navigation.map((item) => (
                            <div key={item.name} className="py-1">
                                {item.submenu ? (
                                    <>
                                        <div className="px-4 py-2 text-[10px] font-black text-green-700/50 dark:text-green-400/50 uppercase tracking-widest">{item.name}</div>
                                        {item.submenu.map(sub => (
                                            <Link key={sub.name} to={sub.href} onClick={() => setIsMenuOpen(false)} className="block px-8 py-3 text-sm font-bold text-gray-600 dark:text-gray-400">{sub.name}</Link>
                                        ))}
                                    </>
                                ) : (
                                    <Link to={item.href} onClick={() => setIsMenuOpen(false)} className="flex items-center gap-3 px-4 py-3 font-bold text-gray-800 dark:text-gray-200"><item.icon className="w-4 h-4 opacity-40"/>{item.name}</Link>
                                )}
                            </div>
                        ))}
                    </div>
                )}
            </header>

            <main className="flex-1 pt-20">
                <Outlet />
            </main>

            {/* ── FOOTER ── */}
            <footer className="bg-[#f2f6f4] dark:bg-[#021410] border-t border-green-100 dark:border-none">
                <div className="max-w-7xl mx-auto px-6 py-16">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
                        <div className="space-y-6">
                            <div className="flex items-center gap-3">
                                <img src={logoSrc} className="w-12 h-12 object-contain bg-transparent rounded-none p-0 shadow-none" alt="logo" />
                                <h2 className="font-black text-lg leading-tight uppercase text-green-900 dark:text-green-400">SLEM COOP</h2>
                            </div>
                            <p className="text-gray-600 dark:text-green-100/60 text-sm font-medium leading-relaxed">Supporting the local community with reliable financial services and sustainable cooperative programs.</p>
                            <div className="flex gap-2">
                                <a href="#" className="p-2.5 bg-green-800 text-white rounded-full hover:scale-110 transition-transform shadow-md"><Facebook className="w-4 h-4" /></a>
                                <a href="#" className="p-2.5 bg-green-800 text-white rounded-full hover:scale-110 transition-transform shadow-md"><Mail className="w-4 h-4" /></a>
                            </div>
                        </div>

                        <div>
                            <h3 className="text-green-800 dark:text-green-500 font-black text-xs uppercase tracking-widest mb-6">Services</h3>
                            <ul className="space-y-3 font-bold text-sm text-gray-600 dark:text-gray-400">
                                <li><Link to="/products/loans" className="hover:text-green-700 dark:hover:text-white transition-colors">Loan Products</Link></li>
                                <li><Link to="/products/deposits" className="hover:text-green-700 dark:hover:text-white transition-colors">Savings Accounts</Link></li>
                                <li><Link to="/calculator" className="hover:text-green-700 dark:hover:text-white transition-colors">Loan Calculator</Link></li>
                            </ul>
                        </div>

                        <div>
                            <h3 className="text-green-800 dark:text-green-500 font-black text-xs uppercase tracking-widest mb-6">Information</h3>
                            <ul className="space-y-3 font-bold text-sm text-gray-600 dark:text-gray-400">
                                <li><Link to="/about" className="hover:text-green-700 dark:hover:text-white transition-colors">About the Coop</Link></li>
                                <li><Link to="/news" className="hover:text-green-700 dark:hover:text-white transition-colors">Latest Updates</Link></li>
                                <li><Link to="/contact" className="hover:text-green-700 dark:hover:text-white transition-colors">Support Center</Link></li>
                            </ul>
                        </div>

                            <div className="bg-white dark:bg-white/5 rounded-3xl p-6 border border-green-200 dark:border-white/10 shadow-sm">
                            <h3 className="font-black text-xs text-green-900 dark:text-white uppercase mb-4 tracking-wider">Contact Us</h3>
                            <div className="space-y-4 text-xs font-bold text-gray-600 dark:text-gray-300">
                                <div className="flex gap-3"><MapPin className="w-4 h-4 text-green-700 flex-shrink-0" /><span>RV Villaflores St. Hilongos, Leyte</span></div>
                                <div className="flex gap-3"><Phone className="w-4 h-4 text-green-700 flex-shrink-0" /><span>(053) 567-8901</span></div>
                                <div className="flex gap-3"><Mail className="w-4 h-4 text-green-700 flex-shrink-0" /><span>info@hilongosmpc.com</span></div>
                            </div>
                        </div>
                    </div>
                    <div className="mt-16 pt-8 border-t border-green-200 dark:border-white/5 text-center text-[10px] font-black text-gray-400 dark:text-gray-600 uppercase tracking-widest">
                        © 2026 Hilongos Multi-Purpose Cooperative. All rights reserved.
                    </div>
                </div>
            </footer>
        </div>
    );
}
