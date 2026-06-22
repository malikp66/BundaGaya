export default function ApplicationLogo(props) {
    return (
        <svg
            {...props}
            viewBox="0 0 120 120"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
        >
            <circle cx="60" cy="60" r="56" stroke="currentColor" strokeWidth="3" />
            <path
                d="M60 20C45 20 33 32 33 47c0 10 5 18 13 23l-2 18c-.2 2 1.5 4 3.5 4h25c2 0 3.7-2 3.5-4l-2-18c8-5 13-13 13-23C87 32 75 20 60 20z"
                fill="currentColor"
                opacity="0.15"
            />
            <path
                d="M60 20C45 20 33 32 33 47c0 10 5 18 13 23l-2 18c-.2 2 1.5 4 3.5 4h25c2 0 3.7-2 3.5-4l-2-18c8-5 13-13 13-23C87 32 75 20 60 20z"
                stroke="currentColor"
                strokeWidth="2.5"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
            <path
                d="M48 55c0 0 4 6 12 6s12-6 12-6"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
            />
            <circle cx="48" cy="42" r="3" fill="currentColor" />
            <circle cx="72" cy="42" r="3" fill="currentColor" />
            <path
                d="M52 92h16M56 97h8"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
            />
        </svg>
    );
}
