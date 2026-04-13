import { Link } from "react-router-dom";
import {
    ArrowRight,
    FileText,
    Users,
    Video,
    UserCheck,
    ChevronDown,
} from "lucide-react";
import { Card, CardContent } from "../components/ui/card";
import { Button } from "../components/ui/button";
import { useEffect, useState } from "react";

/* ─── Particles Component ─── */
function Particles() {
    const colorClasses = [
        "bg-green-300 dark:bg-green-600",
        "bg-green-400 dark:bg-green-500",
        "bg-green-200 dark:bg-green-700",
        "bg-green-500 dark:bg-green-800",
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
                        bottom: "-10px",
                        opacity: p.opacity,
                        animation: `floatUp ${p.duration}s ${p.delay}s infinite linear`,
                    }}
                />
            ))}
        </div>
    );
}

/* ─── Overflow Check Badge ─── */
function OverflowCheck({ size = "sm" }: { size?: "sm" | "md" }) {
    const dim = size === "md" ? "w-10 h-10 -top-4 -left-4 rounded-xl" : "w-8 h-8 -top-3 -left-3 rounded-lg";
    const icon = size === "md" ? "w-6 h-6" : "w-4 h-4";
    return (
        <div className={`absolute z-10 ${dim} bg-green-500 dark:bg-green-400 shadow-lg shadow-green-500/40 dark:shadow-green-400/20 flex items-center justify-center rotate-[-7deg] flex-shrink-0`}>
            <svg viewBox="0 0 24 24" className={icon} fill="none" stroke="white" strokeWidth="3.5" strokeLinecap="round" strokeLinejoin="round">
                <path d="M4 13l5 5L20 7" />
            </svg>
        </div>
    );
}

export function MembershipSteps() {
    const [heroVisible, setHeroVisible] = useState(false);

    useEffect(() => {
        const t = setTimeout(() => setHeroVisible(true), 100);
        return () => clearTimeout(t);
    }, []);

    const steps = [
        {
            number: "01",
            icon: FileText,
            title: "Personal Details",
            description: "Complete the core identity section of your membership profile.",
            details: ["Full Legal Name", "Residential Address", "Employment Details", "Emergency Contacts"],
        },
        {
            number: "02",
            icon: Users,
            title: "Documentation",
            description: "Upload verification and choose your membership classification.",
            details: ["Valid Government ID", "Proof of Income", "Digital Signature", "Photo Attachment"],
        },
        {
            number: "03",
            icon: Video,
            title: "Orientation",
            description: "Attend the Pre-Membership Education Seminar (PMES) virtually.",
            details: ["Video Seminar Modules", "Interactive Assessment", "Certificate Generation", "Governance Overview"],
        },
        {
            number: "04",
            icon: UserCheck,
            title: "Board Approval",
            description: "Final submission for review by the board of directors.",
            details: ["Eligibility Check", "Review Period", "Fee Instructions", "Welcome Packet"],
        },
    ];

    const checklistGroups = [
        {
            title: "Documents Ready",
            items: ["Accomplished Application Form", "Certificate of Orientation", "Passed Assessment Quiz", "Valid Government ID"],
        },
        {
            title: "Membership Readiness",
            items: ["Initial Share Capital (₱500)", "Membership Fee Paid", "Digital Signature Ready", "Recent 2x2 Photo"],
        },
    ];

    return (
        <>
            <style>{`
                @keyframes floatUp { 0% { transform: translateY(0) scale(1); opacity: 0.2; } 100% { transform: translateY(-100vh) scale(0.5); opacity: 0; } }
                @keyframes bounceSlow { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(10px); } }
            `}</style>

            <div className="flex flex-col bg-white dark:bg-[#0a0f0c] text-gray-900 dark:text-white transition-colors duration-500">

                {/* ── Hero Section ── */}
                <section className="relative min-h-[100dvh] flex items-center justify-center overflow-hidden">
                    <div
                        className="absolute inset-0 bg-[url('/src/images/bghd.jpg')] bg-cover bg-center"
                        style={{
                            transition: "transform 20s linear",
                            transform: heroVisible ? "scale(1)" : "scale(1.05)",
                        }}
                    />
                    <div className="absolute inset-0 bg-gradient-to-br from-white/90 via-green-50/80 to-green-100/90 dark:from-[#022c22]/95 dark:via-[#064e3b]/95 dark:to-[#065f46]/95 transition-colors duration-500" />
                    <Particles />

                    <div className={`relative z-10 max-w-7xl mx-auto px-6 text-center transition-all duration-1000 ${heroVisible ? "opacity-100 translate-y-0" : "opacity-0 translate-y-10"}`}>
                        <div className="inline-flex items-center gap-2 mb-6 px-4 py-2 rounded-full bg-green-200/50 dark:bg-white/10 border border-green-300 dark:border-white/20 backdrop-blur-md">
                            <div className="w-2.5 h-2.5 bg-green-600 dark:bg-green-400 rounded-full animate-pulse" />
                            <span className="text-xs sm:text-sm text-green-900 dark:text-white/90 font-medium uppercase tracking-widest">Step-by-Step Guide</span>
                        </div>
                        <h1 className="text-5xl sm:text-8xl font-extrabold mb-6 uppercase tracking-tight text-gray-900 dark:text-white leading-[0.9]">
                            How to <br className="sm:hidden" />
                            <span className="bg-clip-text text-transparent bg-gradient-to-r from-green-700 to-green-500 dark:from-green-400 dark:to-green-200">Join</span>
                        </h1>
                        <p className="text-lg sm:text-xl text-gray-700 dark:text-white/80 max-w-2xl mx-auto mb-10 font-medium leading-relaxed">
                            Our application process is streamlined for the digital age. Your progress is saved every step of the way.
                        </p>
                        <div className="flex flex-col sm:flex-row gap-4 justify-center">
                            <Link
                                to="/membership/info"
                                className="px-10 py-3.5 rounded-full bg-green-600 dark:bg-white text-white dark:text-green-900 font-bold shadow-xl hover:-translate-y-1 transition-all text-center uppercase tracking-widest"
                            >
                                Start Application
                            </Link>
                        </div>
                    </div>
                    <div className="absolute bottom-10 left-1/2 -translate-x-1/2 text-green-600 dark:text-white/40 animate-[bounceSlow_2s_infinite] flex flex-col items-center gap-2">
                        <ChevronDown className="w-8 h-8" />
                    </div>
                </section>

                {/* ── Steps Timeline ── */}
                <section className="relative py-24 bg-white dark:bg-[#0a0f0c]">
                    <div className="max-w-5xl mx-auto px-6">
                        <div className="text-center mb-16">
                            <span className="text-xs font-bold uppercase tracking-widest text-green-600 dark:text-green-400">Membership Process</span>
                            <h2 className="text-3xl sm:text-4xl font-bold mt-3 text-gray-900 dark:text-white">Four Simple Steps</h2>
                        </div>

                        <div className="space-y-16">
                            {steps.map((step, index) => (
                                <div key={index} className="group relative flex flex-col md:flex-row gap-8 items-start">
                                    <div className="flex-shrink-0 relative">
                                        <div className="w-20 h-20 rounded-[2rem] bg-green-600 dark:bg-green-500 flex items-center justify-center shadow-2xl shadow-green-900/20 z-10 relative transform group-hover:rotate-12 transition-transform duration-500">
                                            <step.icon className="w-10 h-10 text-white" />
                                        </div>
                                        <div className="absolute -top-6 -left-6 text-7xl font-black text-green-900/5 dark:text-white/5 select-none tracking-tighter">
                                            {step.number}
                                        </div>
                                        {index !== steps.length - 1 && (
                                            <div className="absolute left-10 top-24 w-0.5 h-24 bg-gradient-to-b from-green-500/50 to-transparent hidden md:block" />
                                        )}
                                    </div>

                                    {/* overflow-visible so check badges can escape */}
                                    <Card className="flex-1 bg-white dark:bg-[#111b17] border border-green-100 dark:border-white/10 rounded-[2.5rem] shadow-sm hover:shadow-xl transition-all duration-500 group-hover:-translate-y-2 overflow-visible">
                                        <CardContent className="p-10">
                                            <h3 className="text-3xl font-black text-gray-900 dark:text-white mb-3 uppercase tracking-tighter">
                                                {step.title}
                                            </h3>
                                            <p className="text-gray-600 dark:text-gray-400 font-bold mb-8 leading-relaxed text-sm uppercase tracking-wide">
                                                {step.description}
                                            </p>
                                            <div className="grid sm:grid-cols-2 gap-6">
                                                {step.details.map((detail, idx) => (
                                                    <div key={idx} className="relative flex items-center bg-green-50/50 dark:bg-black/20 pt-5 pb-4 pr-4 pl-8 rounded-2xl border border-green-100 dark:border-white/5 overflow-visible">
                                                        <OverflowCheck size="md" />
                                                        <span className="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-tight">
                                                            {detail}
                                                        </span>
                                                    </div>
                                                ))}
                                            </div>
                                        </CardContent>
                                    </Card>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* ── Submission Checklist ── */}
                <section className="py-24 bg-green-50/30 dark:bg-[#0d1410] border-y border-green-100 dark:border-white/5 relative transition-colors duration-500">
                    <div className="relative z-10 max-w-7xl mx-auto px-6">
                        <div className="text-center mb-16">
                            <span className="text-xs font-bold uppercase tracking-widest text-green-600 dark:text-green-400">Application Readiness</span>
                            <h2 className="text-3xl sm:text-4xl font-bold mt-3 text-gray-900 dark:text-white">
                                Submission{" "}
                                <span className="bg-clip-text text-transparent bg-gradient-to-r from-green-700 to-green-500 dark:from-green-400 dark:to-green-200">
                                    Checklist
                                </span>
                            </h2>
                            <p className="text-gray-500 dark:text-white/60 mt-4 text-sm max-w-md mx-auto">
                                Make sure you have everything ready before you begin your application.
                            </p>
                        </div>

                        <div className="grid lg:grid-cols-2 gap-8 max-w-5xl mx-auto">
                            {checklistGroups.map((group, i) => (
                                <div key={i} className="bg-white dark:bg-[#111b17] rounded-[2.5rem] p-8 md:p-10 border border-green-100 dark:border-white/10 shadow-sm dark:shadow-none transition-all duration-500">
                                    <h3 className="text-gray-900 dark:text-white font-black uppercase tracking-widest text-xs mb-8 flex items-center gap-3">
                                        <div className="w-2.5 h-2.5 rounded-full bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.5)]" />
                                        {group.title}
                                    </h3>
                                    <div className="space-y-4">
                                        {group.items.map((item) => (
                                            <div
                                                key={item}
                                                className="relative flex items-center pt-4 pb-4 pr-4 pl-9 rounded-2xl border bg-green-50/50 border-green-100 dark:bg-green-500/10 dark:border-green-500/20 overflow-visible"
                                            >
                                                <OverflowCheck size="sm" />
                                                <span className="text-[11px] font-black uppercase tracking-tight text-green-800 dark:text-green-400">
                                                    {item}
                                                </span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* ── Final Call to Action ── */}
                <section className="py-24 bg-white dark:bg-[#0a0f0c] px-6 transition-colors duration-500">
                    <Card className="max-w-4xl mx-auto rounded-3xl border-0 shadow-2xl bg-gradient-to-br from-green-100 via-green-50 to-green-200 dark:from-[#022c22] dark:via-[#047857] dark:to-[#064e3b] transition-colors duration-500">
                        <CardContent className="p-12 text-center">
                            <h2 className="text-3xl sm:text-4xl font-bold mb-4 text-green-950 dark:text-white">
                                Ready to Get Started?
                            </h2>
                            <p className="text-green-800 dark:text-white/80 text-lg mb-10">
                                Your journey toward financial cooperation starts now.
                            </p>
                            <div className="flex flex-col sm:flex-row gap-4 justify-center">
                                <Link
                                    to="/membership/info"
                                    className="px-10 py-4 rounded-full bg-green-600 dark:bg-white text-white dark:text-green-800 text-lg font-bold shadow-xl hover:scale-105 transition-all"
                                >
                                    Apply Now <ArrowRight className="inline ml-2 w-5 h-5" />
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </section>

            </div>
        </>
    );
}
