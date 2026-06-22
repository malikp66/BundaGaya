import { useEffect, useState, Component } from "react";
import { motion, AnimatePresence } from "framer-motion";

class SafeTextReveal extends Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false };
    }
    static getDerivedStateFromError() {
        return { hasError: true };
    }
    componentDidCatch(error) {
        if (typeof console !== "undefined") {
            console.warn("[Splashscreen] Text reveal failed, falling back to plain text.", error);
        }
    }
    render() {
        if (this.state.hasError) {
            return <span style={{ color: this.props.textColor || "#ffffff" }}>{this.props.text}</span>;
        }
        const { DiaTextReveal } = this.props.moduleRef.current || {};
        if (!DiaTextReveal) {
            return <span style={{ color: this.props.textColor || "#ffffff" }}>{this.props.text}</span>;
        }
        return (
            <DiaTextReveal
                text={this.props.text}
                textColor={this.props.textColor}
                colors={this.props.colors}
                duration={this.props.duration}
                delay={this.props.delay}
                startOnView={false}
                once={true}
            />
        );
    }
}

export default function Splashscreen({ onComplete }) {
    const [isVisible, setIsVisible] = useState(true);
    const [textRevealModule, setTextRevealModule] = useState(null);

    useEffect(() => {
        let cancelled = false;
        import("./DiaTextReveal")
            .then((mod) => {
                if (!cancelled) {
                    setTextRevealModule({ current: mod });
                }
            })
            .catch((err) => {
                if (typeof console !== "undefined") {
                    console.warn("[Splashscreen] Could not load DiaTextReveal, using plain text fallback.", err);
                }
            });
        return () => {
            cancelled = true;
        };
    }, []);

    useEffect(() => {
        const timer = setTimeout(() => {
            setIsVisible(false);
            if (onComplete) {
                setTimeout(onComplete, 800);
            }
        }, 2800);
        return () => clearTimeout(timer);
    }, [onComplete]);

    return (
        <AnimatePresence>
            {isVisible && (
                <motion.div
                    initial={{ opacity: 1 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0, filter: "blur(10px)" }}
                    transition={{ duration: 0.8, ease: [0.4, 0, 0.2, 1] }}
                    className="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-neutral-950 text-white"
                    aria-hidden="true"
                    role="presentation"
                >
                    <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[350px] w-[350px] rounded-full bg-rose-500/10 blur-[130px] pointer-events-none" />
                    <div className="absolute top-1/4 left-1/3 h-[200px] w-[200px] rounded-full bg-amber-500/5 blur-[100px] pointer-events-none" />

                    <div className="relative z-10 flex flex-col items-center text-center px-4">
                        <motion.span
                            initial={{ opacity: 0, y: 10 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.5, delay: 0.2 }}
                            className="mb-4 text-xs font-semibold uppercase tracking-[0.25em] text-rose-500/80"
                        >
                            Sewa Busana Branded
                        </motion.span>

                        <h1 className="font-display text-5xl md:text-7xl font-bold tracking-wider mb-5 min-h-[1.2em]">
                            <SafeTextReveal
                                moduleRef={{ current: textRevealModule }}
                                text="BundaGaya"
                                textColor="#ffffff"
                                colors={["#fda4af", "#f43f5e", "#be123c", "#fca5a5", "#fb7185"]}
                                duration={1.8}
                                delay={0.4}
                            />
                        </h1>

                        <motion.p
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            transition={{ duration: 0.8, delay: 1.4 }}
                            className="text-sm md:text-base text-neutral-400 tracking-wide font-light max-w-xs md:max-w-md leading-relaxed"
                        >
                            Tampil Elegan di Setiap Acara • Sewa Baju Kondangan Branded
                        </motion.p>

                        <div className="mt-12 h-[1px] w-32 bg-neutral-800 overflow-hidden relative rounded-full">
                            <motion.div
                                initial={{ left: "-100%" }}
                                animate={{ left: "100%" }}
                                transition={{ duration: 2.2, ease: "easeInOut", repeat: 0 }}
                                className="absolute inset-y-0 w-1/2 bg-gradient-to-r from-rose-500 to-pink-500"
                            />
                        </div>
                    </div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
