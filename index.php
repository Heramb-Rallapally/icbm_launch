<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICBM Control | Classified Access</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #020408;
            overflow-y: auto;
            overflow-x: hidden;
            font-family: 'Courier New', monospace;
            padding: 20px 0 60px 0;
        }

        /* ── Animated star background ── */
        .stars {
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            z-index: 0;
            overflow: hidden;
        }

        .stars span {
            position: absolute;
            width: 2px; height: 2px;
            background: white;
            border-radius: 50%;
            animation: twinkle 3s infinite alternate;
        }

        @keyframes twinkle {
            0%   { opacity: 0.1; }
            100% { opacity: 1; }
        }

        /* ── Grid overlay ── */
        .grid-bg {
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            background-image:
                linear-gradient(rgba(88,166,255,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(88,166,255,0.05) 1px, transparent 1px);
            background-size: 60px 60px;
            z-index: 1;
        }

        /* ── Red pulsing glow at bottom ── */
        .ground-glow {
            position: fixed;
            bottom: -100px; left: 50%;
            transform: translateX(-50%);
            width: 800px; height: 300px;
            background: radial-gradient(ellipse, rgba(218,54,51,0.25) 0%, transparent 70%);
            z-index: 1;
            animation: pulse-glow 3s ease-in-out infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { opacity: 0.5; transform: translateX(-50%) scale(1);   }
            50%       { opacity: 1;   transform: translateX(-50%) scale(1.1); }
        }

        /* ── Main card ── */
        .landing-container {
            position: relative;
            z-index: 10;
            text-align: center;
            max-width: 700px;
            width: 90%;
            padding: 50px 40px;
            background: rgba(10, 14, 23, 0.92);
            border: 1px solid rgba(218,54,51,0.4);
            border-radius: 4px;
            box-shadow:
                0 0 40px rgba(218,54,51,0.15),
                0 0 80px rgba(0,0,0,0.8),
                inset 0 0 40px rgba(0,0,0,0.5);
        }

        /* Corner accents */
        .landing-container::before,
        .landing-container::after {
            content: '';
            position: absolute;
            width: 20px; height: 20px;
            border-color: #da3633;
            border-style: solid;
        }

        .landing-container::before {
            top: -1px; left: -1px;
            border-width: 2px 0 0 2px;
        }

        .landing-container::after {
            bottom: -1px; right: -1px;
            border-width: 0 2px 2px 0;
        }

        /* ── Classification bar ── */
        .classification-bar {
            background: #da3633;
            color: white;
            font-size: 0.7rem;
            font-weight: bold;
            letter-spacing: 4px;
            padding: 5px 0;
            margin: -50px -40px 35px;
            text-transform: uppercase;
        }

        /* ── ICBM SVG missile ── */
        .missile-wrap {
            position: relative;
            display: inline-block;
            margin: 10px 0 25px;
            animation: launch 5s ease-in-out infinite;
        }

        @keyframes launch {
            0%   { transform: translateY(0px)   rotate(-40deg); }
            50%  { transform: translateY(-25px) rotate(-40deg); }
            100% { transform: translateY(0px)   rotate(-40deg); }
        }

        .missile-svg {
            width: 120px;
            filter: drop-shadow(0 0 12px rgba(218,54,51,0.8));
        }

        /* Flame trail */
        .flame {
            position: absolute;
            bottom: -5px; right: 10px;
            width: 18px; height: 30px;
            background: linear-gradient(to bottom, #ff4500, #ff8c00, transparent);
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            animation: flicker 0.2s infinite alternate;
            transform: rotate(-40deg);
            transform-origin: top center;
        }

        @keyframes flicker {
            0%   { height: 25px; opacity: 0.9; }
            100% { height: 35px; opacity: 1;   }
        }

        /* ── Title ── */
        .title-top {
            font-size: 0.85rem;
            color: #58a6ff;
            letter-spacing: 6px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        h1 {
            font-size: 3.2rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 6px;
            line-height: 1.1;
            margin-bottom: 5px;
            color: white;
        }

        h1 span { color: #da3633; }

        .subtitle {
            font-size: 0.8rem;
            color: #8b949e;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 25px;
            border-top: 1px solid #30363d;
            border-bottom: 1px solid #30363d;
            padding: 8px 0;
        }

        /* ── Status indicators ── */
        .status-row {
            display: flex;
            justify-content: center;
            gap: 25px;
            margin-bottom: 30px;
        }

        .status-item {
            font-size: 0.75rem;
            color: #8b949e;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            animation: blink 1.5s infinite;
        }

        .dot.green  { background: #2ea043; }
        .dot.red    { background: #da3633; animation-delay: 0.5s; }
        .dot.yellow { background: #e3b341; animation-delay: 1s;   }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.2; }
        }

        /* ── Warning box ── */
        .warning-box {
            border: 1px solid rgba(218,54,51,0.4);
            background: rgba(218,54,51,0.05);
            padding: 12px 20px;
            margin-bottom: 30px;
            font-size: 0.78rem;
            color: #8b949e;
            line-height: 1.7;
        }

        .warning-box b { color: #da3633; }

        /* ── CTA button ── */
        .cta-button {
            display: inline-block;
            padding: 14px 50px;
            font-size: 1rem;
            font-family: 'Courier New', monospace;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 3px;
            background: transparent;
            color: white;
            border: 1px solid #da3633;
            border-radius: 2px;
            transition: all 0.3s ease;
            box-shadow: 0 0 15px rgba(218,54,51,0.2), inset 0 0 15px rgba(218,54,51,0.05);
            position: relative;
            z-index: 3;
            text-decoration: none;
        }

        .cta-button:hover {
            background: rgba(218,54,51,0.15);
            box-shadow: 0 0 30px rgba(218,54,51,0.5), inset 0 0 20px rgba(218,54,51,0.1);
            letter-spacing: 5px;
            text-decoration: none;
            color: white;
        }

        /* ── Login Button ── */
        .login-button {
            display: inline-block;
            padding: 14px 50px;
            font-size: 1rem;
            font-family: 'Courier New', monospace;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 3px;
            background: #da3633;
            color: white;
            border: 1px solid #da3633;
            border-radius: 2px;
            transition: all 0.3s ease;
            box-shadow: 0 0 20px rgba(218,54,51,0.4);
            text-decoration: none;
        }

        .login-button:hover {
            background: #b62324;
            box-shadow: 0 0 40px rgba(218,54,51,0.8);
            letter-spacing: 5px;
            text-decoration: none;
            color: white;
        }

        /* ── Footer ── */
        .footer-text {
            margin-top: 25px;
            font-size: 0.7rem;
            color: #30363d;
            letter-spacing: 2px;
        }

        /* ── Scrolling ticker ── */
        .ticker-wrap {
            position: fixed;
            bottom: 0; left: 0;
            width: 100%;
            background: rgba(218,54,51,0.1);
            border-top: 1px solid rgba(218,54,51,0.3);
            overflow: hidden;
            z-index: 20;
            padding: 6px 0;
        }

        .ticker {
            display: inline-block;
            white-space: nowrap;
            animation: ticker 25s linear infinite;
            font-size: 0.75rem;
            color: #da3633;
            letter-spacing: 2px;
        }

        @keyframes ticker {
            0%   { transform: translateX(100vw); }
            100% { transform: translateX(-100%); }
        }

    </style>
</head>
<body>

    <!-- Stars -->
    <div class="stars" id="stars"></div>

    <!-- Grid -->
    <div class="grid-bg"></div>

    <!-- Ground Glow -->
    <div class="ground-glow"></div>

    <!-- Main Card -->
    <div class="landing-container">

        <div class="classification-bar">⚠ Top Secret / SCI — Authorized Personnel Only ⚠</div>

        <!-- ICBM Missile SVG -->
        <div class="missile-wrap">
            <svg class="missile-svg" viewBox="0 0 80 200" xmlns="http://www.w3.org/2000/svg">
                <!-- Body -->
                <rect x="28" y="50" width="24" height="110" rx="4" fill="#c9d1d9"/>
                <!-- Nose cone -->
                <polygon points="40,5 28,50 52,50" fill="#da3633"/>
                <!-- Window -->
                <circle cx="40" cy="90" r="6" fill="#161b22" stroke="#58a6ff" stroke-width="2"/>
                <circle cx="40" cy="90" r="3" fill="#58a6ff" opacity="0.6"/>
                <!-- Fins left -->
                <polygon points="28,130 10,165 28,155" fill="#8b949e"/>
                <!-- Fins right -->
                <polygon points="52,130 70,165 52,155" fill="#8b949e"/>
                <!-- Nozzle -->
                <rect x="32" y="160" width="16" height="15" rx="2" fill="#8b949e"/>
                <!-- Stripe -->
                <rect x="28" y="105" width="24" height="6" fill="#da3633" opacity="0.7"/>
                <!-- USA text area -->
                <rect x="28" y="115" width="24" height="12" fill="#21262d"/>
            </svg>
            <div class="flame"></div>
        </div>

        <div class="title-top">ICBM Defense Network</div>
        <h1><span>Doomsday</span><br>Protocol</h1>
        <div class="subtitle">Intercontinental Ballistic Command System v4.2</div>

        <!-- Status Row -->
        <div class="status-row">
            <div class="status-item"><div class="dot green"></div> Systems Online</div>
            <div class="status-item"><div class="dot red"></div> Threat Level: HIGH</div>
            <div class="status-item"><div class="dot yellow"></div> 4 Missiles Armed</div>
        </div>

        <!-- Warning -->
        <div class="warning-box">
            <b>WARNING:</b> This system is restricted to authorized operators only.<br>
            All access attempts are monitored and recorded.<br>
            Unauthorized access will be prosecuted under federal law.
        </div>

        <a href="pages/login.php" class="cta-button">⚡ Initialize Secure Uplink</a>

        <br><br>

        <a href="pages/login.php" class="login-button">🔐 LOGIN</a>

        <div class="footer-text">CRYPTO KEY: AES-256 &nbsp;|&nbsp; CLEARANCE REQUIRED: DELTA+</div>

    </div>

    <!-- Scrolling Ticker -->
    <div class="ticker-wrap">
        <span class="ticker">
            &nbsp;&nbsp;&nbsp; ⚠ ALERT: UNAUTHORIZED ACCESS DETECTED IN SECTOR 7 &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
            MISSILE STATUS: 4 UNITS ARMED AND READY &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
            SYSTEM INTEGRITY: 100% &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
            LAST LOGIN: OFFICER1 — CLEARANCE DELTA &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
            CTF CHALLENGE ACTIVE — FIND ALL FLAGS TO WIN &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        </span>
    </div>

    <!-- Generate random stars -->
    <script>
        const starsContainer = document.getElementById('stars');
        for(let i = 0; i < 150; i++){
            const star = document.createElement('span');
            star.style.left   = Math.random() * 100 + 'vw';
            star.style.top    = Math.random() * 100 + 'vh';
            star.style.animationDelay    = Math.random() * 3 + 's';
            star.style.animationDuration = (2 + Math.random() * 3) + 's';
            star.style.width  = (Math.random() > 0.8 ? '3px' : '2px');
            star.style.height = star.style.width;
            starsContainer.appendChild(star);
        }
    </script>

</body>
</html>