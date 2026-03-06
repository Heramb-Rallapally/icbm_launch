<?php
include("../config/database.php");
session_start();

$error = "";

if(isset($_POST['login'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM Users WHERE Username='$username' AND Password='$password'";
    $result = mysqli_query($conn,$query);

    if(mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);
        $_SESSION['username'] = $user['Username'];
        $_SESSION['role']     = $user['Role'];
        $_SESSION['clearance']= $user['Security_Clearance'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "ACCESS DENIED — Invalid credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICBM Control | Secure Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #020408;
            font-family: 'Courier New', monospace;
            overflow: hidden;
        }

        /* ── Stars ── */
        .stars { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 0; }
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

        /* ── Ground glow ── */
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

        /* ── Login Card — matches index.php landing-container ── */
        .login-card {
            position: relative;
            z-index: 10;
            text-align: center;
            max-width: 500px;
            width: 90%;
            padding: 0;
            background: rgba(10, 14, 23, 0.92);
            border: 1px solid rgba(218,54,51,0.4);
            border-radius: 4px;
            box-shadow:
                0 0 40px rgba(218,54,51,0.15),
                0 0 80px rgba(0,0,0,0.8),
                inset 0 0 40px rgba(0,0,0,0.5);
        }

        /* Corner accents — same as index.php */
        .login-card::before,
        .login-card::after {
            content: '';
            position: absolute;
            width: 20px; height: 20px;
            border-color: #da3633;
            border-style: solid;
        }
        .login-card::before { top: -1px; left: -1px; border-width: 2px 0 0 2px; }
        .login-card::after  { bottom: -1px; right: -1px; border-width: 0 2px 2px 0; }

        /* ── Classification bar ── */
        .classification-bar {
            background: #da3633;
            color: white;
            font-size: 0.65rem;
            font-weight: bold;
            letter-spacing: 3px;
            padding: 5px 0;
            text-align: center;
            text-transform: uppercase;
            border-radius: 4px 4px 0 0;
        }

        /* ── Card Header ── */
        .card-top {
            background: rgba(218,54,51,0.08);
            border-bottom: 1px solid rgba(218,54,51,0.4);
            padding: 25px 30px 20px;
            text-align: center;
        }

        .missile-icon {
            font-size: 3rem;
            display: inline-block;
            animation: float 3s ease-in-out infinite;
            filter: drop-shadow(0 0 12px rgba(218,54,51,0.8));
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px)   rotate(-40deg); }
            50%       { transform: translateY(-12px) rotate(-40deg); }
        }

        .card-top h1 {
            font-size: 1.5rem;
            color: white;
            text-transform: uppercase;
            letter-spacing: 5px;
            margin-top: 12px;
            font-weight: 900;
        }

        .card-top h1 span { color: #da3633; }

        .card-top p {
            font-size: 0.75rem;
            color: #8b949e;
            letter-spacing: 2px;
            margin-top: 5px;
            text-transform: uppercase;
            border-top: 1px solid #30363d;
            border-bottom: 1px solid #30363d;
            padding: 6px 0;
        }

        /* ── Form Body ── */
        .card-body { padding: 30px 35px; text-align: left; }

        .form-group { margin-bottom: 20px; }

        .form-group label {
            display: block;
            font-size: 0.75rem;
            color: #8b949e;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            background: #0d1117;
            border: 1px solid #30363d;
            color: #c9d1d9;
            font-family: 'Courier New', monospace;
            font-size: 0.95rem;
            border-radius: 2px;
            transition: border 0.3s, box-shadow 0.3s;
            outline: none;
        }

        .form-group input:focus {
            border-color: rgba(218,54,51,0.6);
            box-shadow: 0 0 10px rgba(218,54,51,0.15);
        }

        /* ── Error message ── */
        .error-box {
            background: rgba(218,54,51,0.1);
            border: 1px solid rgba(218,54,51,0.5);
            color: #da3633;
            padding: 10px 15px;
            font-size: 0.8rem;
            letter-spacing: 1px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* ── Login Button — matches index.php login-button ── */
        .login-btn {
            width: 100%;
            padding: 14px;
            background: #da3633;
            color: white;
            border: 1px solid #da3633;
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 0 20px rgba(218,54,51,0.4);
            border-radius: 2px;
        }

        .login-btn:hover {
            background: #b62324;
            box-shadow: 0 0 40px rgba(218,54,51,0.8);
            letter-spacing: 6px;
        }

        /* ── Status row ── */
        .status-row {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            padding-top: 18px;
            border-top: 1px solid #21262d;
        }

        .status-item {
            font-size: 0.7rem;
            color: #8b949e;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            animation: blink 1.5s infinite;
        }
        .dot.green  { background: #2ea043; }
        .dot.red    { background: #da3633; animation-delay: 0.5s; }
        .dot.yellow { background: #e3b341; animation-delay: 1s; }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.2; }
        }

        /* ── Back link ── */
        .back-link {
            position: relative;
            z-index: 10;
            display: block;
            text-align: center;
            margin-top: 18px;
            font-size: 0.75rem;
            color: #8b949e;
            letter-spacing: 2px;
            text-decoration: none;
            text-transform: uppercase;
            transition: color 0.3s;
        }
        .back-link:hover { color: #58a6ff; text-decoration: none; }

        /* ── Ticker ── */
        .ticker-wrap {
            position: fixed;
            bottom: 0; left: 0;
            width: 100%;
            background: rgba(218,54,51,0.1);
            border-top: 1px solid rgba(218,54,51,0.3);
            overflow: hidden;
            z-index: 20;
            padding: 5px 0;
        }
        .ticker {
            display: inline-block;
            white-space: nowrap;
            animation: ticker 25s linear infinite;
            font-size: 0.7rem;
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

    <div class="stars" id="stars"></div>
    <div class="grid-bg"></div>
    <div class="ground-glow"></div>

    <!-- Login Card -->
    <div class="login-card">

        <div class="classification-bar">⚠ Top Secret / SCI — Authorized Personnel Only ⚠</div>

        <div class="card-top">
            <div class="missile-icon">🚀</div>
            <h1><span>Secure</span> Access</h1>
            <p>ICBM Command &amp; Control Network v4.2</p>
        </div>

        <div class="card-body">

            <?php if($error): ?>
                <div class="error-box">⛔ <?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label>Operator ID</label>
                    <input type="text" name="username" placeholder="Enter username" autocomplete="off">
                </div>

                <div class="form-group">
                    <label>Access Code</label>
                    <input type="password" name="password" placeholder="Enter password">
                </div>

                <button type="submit" name="login" class="login-btn">🔐 Authenticate</button>

            </form>

            <div class="status-row">
                <div class="status-item"><div class="dot green"></div> Network Online</div>
                <div class="status-item"><div class="dot red"></div> Threat: HIGH</div>
                <div class="status-item"><div class="dot yellow"></div> Encrypted</div>
            </div>

        </div>

    </div>

    <a href="../index.php" class="back-link">← Return to Command Center</a>

    <!-- Ticker -->
    <div class="ticker-wrap">
        <span class="ticker">
            &nbsp;&nbsp;&nbsp; ⚠ ALERT: UNAUTHORIZED ACCESS DETECTED IN SECTOR 7 &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
            MISSILE STATUS: 4 UNITS ARMED AND READY &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
            SYSTEM INTEGRITY: 100% &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
            CLEARANCE REQUIRED: DELTA OR ABOVE &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
            CTF CHALLENGE ACTIVE — FIND ALL FLAGS TO WIN &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        </span>
    </div>

    <script>
        const starsContainer = document.getElementById('stars');
        for(let i = 0; i < 150; i++){
            const star = document.createElement('span');
            star.style.left              = Math.random() * 100 + 'vw';
            star.style.top               = Math.random() * 100 + 'vh';
            star.style.animationDelay    = Math.random() * 3 + 's';
            star.style.animationDuration = (2 + Math.random() * 3) + 's';
            star.style.width             = (Math.random() > 0.8 ? '3px' : '2px');
            star.style.height            = star.style.width;
            starsContainer.appendChild(star);
        }
    </script>

</body>
</html>