import { useNavigate } from "react-router";
import { useTheme } from "next-themes";
import { ArrowLeft, Home, AlertTriangle } from "lucide-react";
import { useEffect, useState } from "react";

export function NotFound() {
    const navigate = useNavigate();
    const { theme, resolvedTheme } = useTheme();
    const [mounted, setMounted] = useState(false);
    const [pageLoaded, setPageLoaded] = useState(false);

    useEffect(() => {
        setMounted(true);
        const timer = setTimeout(() => setPageLoaded(true), 50);
        return () => clearTimeout(timer);
    }, []);

    const isDarkTheme = mounted
        ? resolvedTheme === "dark" || theme === "dark"
        : false;

    return (
        <>
            <style>{`
                @keyframes slideDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
                @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
                @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
                .animate-slide-down { animation: slideDown 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
                .animate-slide-up { animation: slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
                .animate-bounce-slow { animation: bounce 3s ease-in-out infinite; }

                @media (max-width: 640px) {
                    .error-container { padding: 2rem 1rem; }
                    .error-title { font-size: 3.5rem; }
                    .error-heading { font-size: 1.75rem; }
                }
            `}</style>

            <div className="min-h-screen bg-white dark:bg-[#000000] text-gray-900 dark:text-white flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
                <div className="w-full max-w-6xl">
                    {/* Main Grid - Responsive Layout */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                        {/* Left Side - Mascot Image */}
                        <div
                            className={`flex justify-center order-first lg:order-none ${pageLoaded ? "animate-slide-up" : "opacity-0"}`}
                        >
                            <div className="w-full max-w-sm lg:max-w-lg xl:max-w-xl">
                                {/* Mascot Image Card (Invisible) */}
                                <div className="relative aspect-square">
                                    <img
                                        src="/images/mascot.png"
                                        alt="SLEM COOP Mascot"
                                        className="w-full h-full object-cover"
                                        onError={(e) => {
                                            // Fallback to placeholder if image fails to load
                                            e.currentTarget.style.display =
                                                "none";
                                            const parent =
                                                e.currentTarget.parentElement;
                                            if (parent) {
                                                parent.innerHTML = `
                                                    <div class="text-center space-y-3 w-full h-64 flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900 rounded-lg">
                                                        <div>
                                                            <div class="flex justify-center">
                                                                <div class="bg-gray-300 dark:bg-gray-600 rounded-2xl p-6">
                                                                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                            <p class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                                Mascot Image
                                                            </p>
                                                        </div>
                                                    </div>
                                                `;
                                            }
                                        }}
                                    />

                                    {/* Subtle Background Elements */}
                                    <div className="absolute inset-0 bg-gradient-to-br from-green-500/5 to-orange-500/5 dark:opacity-0 pointer-events-none rounded-lg"></div>
                                </div>
                            </div>
                        </div>

                        {/* Right Side - Content */}
                        <div
                            className={`text-center lg:text-left space-y-6 lg:space-y-8 ${pageLoaded ? "animate-slide-down" : "opacity-0"}`}
                        >
                            {/* Icon for Mobile Only */}
                            <div className="flex justify-center lg:hidden">
                                <div className="relative">
                                    <div className="absolute inset-0 bg-red-500/20 blur-2xl rounded-full"></div>
                                    <div className="relative bg-gradient-to-br from-red-500 to-orange-600 p-4 rounded-2xl shadow-2xl animate-bounce-slow">
                                        <AlertTriangle className="w-10 h-10 text-white" />
                                    </div>
                                </div>
                            </div>

                            {/* Error Code */}
                            <div>
                                <h1 className="error-title text-6xl sm:text-7xl lg:text-8xl font-black text-transparent bg-gradient-to-r from-red-600 to-orange-600 dark:from-red-400 dark:to-orange-400 bg-clip-text mb-2 leading-none">
                                    404
                                </h1>
                                <p className="text-gray-500 dark:text-gray-400 text-xs sm:text-sm uppercase tracking-widest font-bold">
                                    Page Not Found
                                </p>
                            </div>

                            {/* Message */}
                            <div className="space-y-3 lg:space-y-4">
                                <h2 className="error-heading text-2xl sm:text-3xl lg:text-4xl font-black text-gray-900 dark:text-white leading-tight">
                                    Oops! We can't find that page.
                                </h2>
                                <p className="text-sm sm:text-base text-gray-600 dark:text-gray-400 leading-relaxed max-w-sm lg:max-w-none">
                                    The page you're looking for doesn't exist or
                                    has been moved. Don't worry, let's get you
                                    back on track!
                                </p>
                            </div>

                            {/* Decorative Elements */}
                            <div className="flex justify-center lg:justify-start gap-3">
                                <div className="w-2 h-2 bg-green-600 dark:bg-green-400 rounded-full"></div>
                                <div className="w-2 h-2 bg-green-600/50 dark:bg-green-400/50 rounded-full"></div>
                                <div className="w-2 h-2 bg-green-600/25 dark:bg-green-400/25 rounded-full"></div>
                            </div>

                            {/* Buttons */}
                            <div className="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start pt-4">
                                <button
                                    onClick={() => navigate(-1)}
                                    className="inline-flex items-center justify-center gap-2 px-6 sm:px-8 py-3 rounded-full font-bold text-xs sm:text-sm uppercase tracking-wider border-2 border-gray-300 dark:border-white/20 text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-white/5 transition-all duration-300 hover:scale-105"
                                >
                                    <ArrowLeft className="w-4 h-4 flex-shrink-0" />
                                    <span>Go Back</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
