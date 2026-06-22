import { cn } from "@/lib/utils";

function BentoGrid({ children, className, ...props }) {
    return (
        <div
            className={cn(
                "grid w-full grid-cols-2 gap-3 md:grid-cols-4 md:gap-4",
                "auto-rows-[9rem] md:auto-rows-[11rem]",
                className
            )}
            {...props}
        >
            {children}
        </div>
    );
}

function BentoCard({
    name,
    className,
    background,
    Icon,
    description,
    href,
    cta,
    ...props
}) {
    return (
        <div
            key={name}
            className={cn(
                "group relative col-span-1 flex flex-col justify-center overflow-hidden rounded-xl",
                "bg-white [box-shadow:0_0_0_1px_rgba(0,0,0,.03),0_2px_4px_rgba(0,0,0,.05)]",
                "transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md",
                className
            )}
            {...props}
        >
            {background}

            <div className="relative z-10 px-3 py-3 md:px-4 md:py-3">
                <div className="pointer-events-none flex transform-gpu flex-col transition-all duration-300">
                    {Icon && (
                        <Icon className="mb-1.5 h-6 w-6 origin-left transform-gpu transition-all duration-300 ease-in-out group-hover:scale-110 md:h-7 md:w-7" />
                    )}
                    <h3 className="text-sm font-semibold leading-tight md:text-base">
                        {name}
                    </h3>
                    {description && (
                        <p className="mt-1 text-xs text-current opacity-70 md:text-sm">
                            {description}
                        </p>
                    )}
                    {href && cta && (
                        <span className="mt-1.5 inline-flex items-center gap-0.5 text-xs font-medium text-rose-600 opacity-0 transition-all duration-300 group-hover:opacity-100">
                            {cta}
                            <svg className="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    )}
                </div>
            </div>

            <div className="pointer-events-none absolute inset-0 transform-gpu transition-all duration-300 group-hover:bg-black/[0.03]" />
        </div>
    );
}

export { BentoGrid, BentoCard };
