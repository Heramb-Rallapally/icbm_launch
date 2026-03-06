<?php
session_start();
include("../config/database.php");

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

// ── Base32 Encode Helper ──
function base32_encode($input) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $output   = '';
    $bytes    = array_map('ord', str_split($input));
    $bits     = '';

    foreach($bytes as $byte){
        $bits .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
    }

    // Pad to multiple of 5
    while(strlen($bits) % 5 !== 0){
        $bits .= '0';
    }

    // Split into 5-bit chunks
    $chunks = str_split($bits, 5);
    foreach($chunks as $chunk){
        $output .= $alphabet[bindec($chunk)];
    }

    // Pad to multiple of 8
    while(strlen($output) % 8 !== 0){
        $output .= '=';
    }

    return $output;
}

// ── Show modal only on first visit per session ──
$show_modal = false;
if(!isset($_SESSION['modal_seen'])){
    $show_modal = true;
    $_SESSION['modal_seen'] = true;
}

$missile_query = "SELECT COUNT(*) as total FROM Missiles";
$missile_result = mysqli_query($conn,$missile_query);
$missile_data = mysqli_fetch_assoc($missile_result);
$missile_count = $missile_data ? $missile_data['total'] : 0;

$health_query = "SELECT Health_Percentage FROM System_Health";
$health_result = mysqli_query($conn,$health_query);
$health_data = mysqli_fetch_assoc($health_result);
$health_pct = $health_data['Health_Percentage'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICBM Control | Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: #020408;
            font-family: 'Courier New', monospace;
            color: #c9d1d9;
            min-height: 100vh;
        }

        .grid-bg {
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            background-image:
                linear-gradient(rgba(88,166,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(88,166,255,0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            z-index: 0;
            pointer-events: none;
        }

        header {
            position: relative;
            z-index: 10;
            background: rgba(10,14,23,0.95);
            border-bottom: 1px solid rgba(218,54,51,0.4);
            padding: 0;
        }

        .classification-bar {
            background: #da3633;
            color: white;
            font-size: 0.65rem;
            font-weight: bold;
            letter-spacing: 3px;
            padding: 4px 0;
            text-align: center;
            text-transform: uppercase;
        }

        .header-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-logo .missile-icon {
            font-size: 2rem;
            filter: drop-shadow(0 0 8px rgba(218,54,51,0.8));
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px)   rotate(-40deg); }
            50%       { transform: translateY(-6px)  rotate(-40deg); }
        }

        .header-logo h1 {
            font-size: 1.3rem;
            color: white;
            text-transform: uppercase;
            letter-spacing: 4px;
        }

        .header-logo h1 span { color: #da3633; }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 0.75rem;
            color: #8b949e;
            letter-spacing: 1px;
        }

        .user-badge {
            background: rgba(218,54,51,0.1);
            border: 1px solid rgba(218,54,51,0.3);
            padding: 6px 14px;
            border-radius: 2px;
            color: #c9d1d9;
        }

        .logout-btn {
            background: transparent;
            border: 1px solid #30363d;
            color: #8b949e;
            padding: 6px 14px;
            font-family: 'Courier New', monospace;
            font-size: 0.75rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            border-color: #da3633;
            color: #da3633;
            text-decoration: none;
        }

        .status-bar {
            background: rgba(10,14,23,0.8);
            border-bottom: 1px solid #21262d;
            padding: 8px 30px;
            display: flex;
            gap: 25px;
            font-size: 0.7rem;
            color: #8b949e;
            letter-spacing: 1px;
            position: relative;
            z-index: 10;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 6px;
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

        .main-content {
            position: relative;
            z-index: 5;
            padding: 25px 30px 80px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .card {
            position: relative;
            background: rgba(10,14,23,0.92);
            border: 1px solid rgba(218,54,51,0.25);
            border-radius: 4px;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            overflow: hidden;
        }

        .card::before,
        .card::after {
            content: '';
            position: absolute;
            width: 12px; height: 12px;
            border-color: #da3633;
            border-style: solid;
        }
        .card::before { top: -1px; left: -1px; border-width: 2px 0 0 2px; }
        .card::after  { bottom: -1px; right: -1px; border-width: 0 2px 2px 0; }

        .card.span2 { grid-column: span 2; }

        .card-header {
            background: rgba(218,54,51,0.08);
            border-bottom: 1px solid rgba(218,54,51,0.3);
            padding: 12px 18px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #8b949e;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header .header-icon { font-size: 1rem; }
        .card-body { padding: 20px 18px; }

        .health-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #8b949e;
            margin-bottom: 8px;
        }

        .health-meter {
            background: #0d1117;
            border: 1px solid #30363d;
            border-radius: 2px;
            height: 14px;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .health-fill {
            height: 100%;
            background: linear-gradient(90deg, #2ea043, #56d364);
            transition: width 0.5s;
        }

        .health-fill.low { background: linear-gradient(90deg, #b62324, #da3633); }

        .health-links { display: flex; flex-direction: column; gap: 6px; }

        .health-link {
            font-size: 0.75rem;
            padding: 6px 10px;
            background: #0d1117;
            border: 1px solid #30363d;
            color: #8b949e;
            text-decoration: none;
            letter-spacing: 1px;
            transition: all 0.2s;
            text-align: center;
        }

        .health-link:hover { border-color: #58a6ff; color: #58a6ff; text-decoration: none; }
        .health-link.danger:hover { border-color: #da3633; color: #da3633; }

        .missile-count {
            text-align: center;
            padding: 15px 0;
        }

        .missile-count .big-number {
            font-size: 4rem;
            font-weight: 900;
            color: white;
            line-height: 1;
            text-shadow: 0 0 20px rgba(218,54,51,0.5);
        }

        .missile-count p {
            font-size: 0.75rem;
            color: #8b949e;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .inventory-btn {
            display: block;
            text-align: center;
            padding: 10px;
            background: transparent;
            border: 1px solid rgba(88,166,255,0.4);
            color: #58a6ff;
            font-family: 'Courier New', monospace;
            font-size: 0.8rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-decoration: none;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .inventory-btn:hover {
            background: rgba(88,166,255,0.1);
            border-color: #58a6ff;
            text-decoration: none;
            color: #58a6ff;
        }

        table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
        th {
            background: #0d1117;
            color: #8b949e;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 2px;
            padding: 10px 12px;
            border-bottom: 1px solid #30363d;
            text-align: left;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #21262d;
            color: #c9d1d9;
            word-break: break-all;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255,255,255,0.02); }

        .log-form {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #21262d;
        }

        .log-form input {
            flex: 1;
            padding: 9px 12px;
            background: #0d1117;
            border: 1px solid #30363d;
            color: #c9d1d9;
            font-family: 'Courier New', monospace;
            font-size: 0.8rem;
            outline: none;
            transition: border 0.3s;
        }

        .log-form input:focus { border-color: rgba(218,54,51,0.5); }

        .log-form button {
            padding: 9px 18px;
            background: transparent;
            border: 1px solid #30363d;
            color: #8b949e;
            font-family: 'Courier New', monospace;
            font-size: 0.75rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
        }

        .log-form button:hover { border-color: #58a6ff; color: #58a6ff; }

        .admin-form label {
            display: block;
            font-size: 0.7rem;
            color: #8b949e;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .admin-form input {
            width: 100%;
            padding: 10px 12px;
            background: #0d1117;
            border: 1px solid #30363d;
            color: #c9d1d9;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            margin-bottom: 15px;
            outline: none;
            transition: border 0.3s;
        }

        .admin-form input:focus { border-color: rgba(218,54,51,0.5); }

        .admin-auth-btn {
            width: 100%;
            padding: 11px;
            background: transparent;
            border: 1px solid rgba(218,54,51,0.5);
            color: #da3633;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            font-weight: bold;
            letter-spacing: 3px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
        }

        .admin-auth-btn:hover {
            background: rgba(218,54,51,0.1);
            box-shadow: 0 0 15px rgba(218,54,51,0.3);
        }

        .delete-btn {
            width: 100%;
            padding: 11px;
            background: #da3633;
            border: 1px solid #da3633;
            color: white;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            font-weight: bold;
            letter-spacing: 3px;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s;
            box-shadow: 0 0 15px rgba(218,54,51,0.3);
        }

        .delete-btn:hover {
            background: #b62324;
            box-shadow: 0 0 25px rgba(218,54,51,0.6);
        }

        .launch-icon {
            text-align: center;
            font-size: 3.5rem;
            margin-bottom: 15px;
            filter: drop-shadow(0 0 10px rgba(218,54,51,0.6));
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50%       { transform: scale(1.05); }
        }

        .launch-input {
            width: 100%;
            padding: 10px 12px;
            background: #0d1117;
            border: 1px solid #30363d;
            color: #c9d1d9;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            text-align: center;
            letter-spacing: 3px;
            margin-bottom: 12px;
            outline: none;
            transition: border 0.3s;
        }

        .launch-input:focus { border-color: rgba(218,54,51,0.5); }

        .launch-btn {
            width: 100%;
            padding: 11px;
            background: #da3633;
            border: none;
            color: white;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            font-weight: bold;
            letter-spacing: 4px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 0 15px rgba(218,54,51,0.4);
        }

        .launch-btn:hover {
            background: #b62324;
            box-shadow: 0 0 30px rgba(218,54,51,0.7);
            letter-spacing: 6px;
        }

        .flag-box {
            margin-top: 12px;
            padding: 12px;
            background: rgba(46,160,67,0.08);
            border: 1px dashed #2ea043;
            color: #2ea043;
            font-size: 0.85rem;
            text-align: center;
            letter-spacing: 1px;
            line-height: 1.8;
        }

        .flag-box.danger {
            background: rgba(218,54,51,0.08);
            border-color: #da3633;
            color: #da3633;
        }

        .error-text {
            color: #da3633;
            font-size: 0.8rem;
            text-align: center;
            margin-top: 10px;
            letter-spacing: 1px;
        }

        .log-result {
            margin-top: 10px;
            padding: 10px 12px;
            background: #0d1117;
            border-left: 3px solid #58a6ff;
            font-size: 0.8rem;
            color: #c9d1d9;
        }

        .ticker-wrap {
            position: fixed;
            bottom: 0; left: 0;
            width: 100%;
            background: rgba(218,54,51,0.08);
            border-top: 1px solid rgba(218,54,51,0.3);
            overflow: hidden;
            z-index: 20;
            padding: 5px 0;
        }

        .ticker {
            display: inline-block;
            white-space: nowrap;
            animation: ticker 30s linear infinite;
            font-size: 0.7rem;
            color: #da3633;
            letter-spacing: 2px;
        }

        @keyframes ticker {
            0%   { transform: translateX(100vw); }
            100% { transform: translateX(-100%); }
        }

        /* ════════════════════════════
           OBJECTIVES MODAL
        ════════════════════════════ */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(2,4,8,0.88);
            z-index: 1000;
            display: none;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(4px);
        }

        .modal-box {
            position: relative;
            background: rgba(10,14,23,0.98);
            border: 1px solid rgba(218,54,51,0.5);
            border-radius: 4px;
            width: 95%;
            max-width: 620px;
            box-shadow:
                0 0 60px rgba(218,54,51,0.2),
                0 0 120px rgba(0,0,0,0.9);
        }

        .modal-box::before,
        .modal-box::after {
            content: '';
            position: absolute;
            width: 18px; height: 18px;
            border-color: #da3633;
            border-style: solid;
        }
        .modal-box::before { top: -1px; left: -1px; border-width: 2px 0 0 2px; }
        .modal-box::after  { bottom: -1px; right: -1px; border-width: 0 2px 2px 0; }

        .modal-classbar {
            background: #da3633;
            color: white;
            font-size: 0.65rem;
            font-weight: bold;
            letter-spacing: 3px;
            padding: 4px 0;
            text-align: center;
            text-transform: uppercase;
            border-radius: 4px 4px 0 0;
        }

        .modal-header {
            background: rgba(218,54,51,0.08);
            border-bottom: 1px solid rgba(218,54,51,0.3);
            padding: 20px 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .modal-header .modal-icon {
            font-size: 2.5rem;
            filter: drop-shadow(0 0 8px rgba(218,54,51,0.8));
        }

        .modal-header h2 {
            font-size: 1.2rem;
            color: white;
            text-transform: uppercase;
            letter-spacing: 4px;
        }

        .modal-header p {
            font-size: 0.72rem;
            color: #8b949e;
            letter-spacing: 2px;
            margin-top: 3px;
        }

        .modal-body { padding: 25px; }

        .obj-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }

        .obj-card {
            background: #0d1117;
            border: 1px solid #21262d;
            border-radius: 2px;
            padding: 14px;
            transition: border 0.2s;
        }

        .obj-card:hover { border-color: rgba(218,54,51,0.4); }

        .obj-number {
            font-size: 0.65rem;
            color: #8b949e;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .obj-title {
            font-size: 0.9rem;
            color: white;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .obj-desc {
            font-size: 0.72rem;
            color: #8b949e;
            line-height: 1.5;
            margin-bottom: 10px;
        }

        .obj-pts {
            display: inline-block;
            background: rgba(218,54,51,0.15);
            border: 1px solid rgba(218,54,51,0.4);
            color: #da3633;
            font-size: 0.7rem;
            font-weight: bold;
            letter-spacing: 2px;
            padding: 3px 10px;
        }

        .modal-footer {
            padding: 0 25px 25px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .modal-close-btn {
            padding: 12px 35px;
            background: #da3633;
            border: 1px solid #da3633;
            color: white;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            font-weight: bold;
            letter-spacing: 3px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 0 15px rgba(218,54,51,0.3);
        }

        .modal-close-btn:hover {
            background: #b62324;
            box-shadow: 0 0 25px rgba(218,54,51,0.6);
        }

        .modal-dismiss-btn {
            padding: 12px 25px;
            background: transparent;
            border: 1px solid #30363d;
            color: #8b949e;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
        }

        .modal-dismiss-btn:hover { border-color: #8b949e; color: #c9d1d9; }

        .total-pts {
            background: rgba(46,160,67,0.08);
            border: 1px solid rgba(46,160,67,0.3);
            padding: 10px 15px;
            text-align: center;
            font-size: 0.8rem;
            color: #2ea043;
            letter-spacing: 2px;
            margin-bottom: 20px;
        }

    </style>
</head>
<body>

<div class="grid-bg"></div>

<!-- OBJECTIVES MODAL -->
<div class="modal-overlay" id="objectivesModal">
    <div class="modal-box">

        <div class="modal-classbar">⚠ Mission Briefing — Classified ⚠</div>

        <div class="modal-header">
            <div class="modal-icon">🚀</div>
            <div>
                <h2>Mission Objectives</h2>
                <p>ICBM CTF Challenge — Complete all tasks to win</p>
            </div>
        </div>

        <div class="modal-body">

            <div class="total-pts">
                TOTAL AVAILABLE POINTS: &nbsp;<b>220 PTS</b>
            </div>

            <div class="obj-grid">

                <div class="obj-card">
                    <div class="obj-number">Objective 01</div>
                    <div class="obj-title">Alpha Clearance Access</div>
                    <div class="obj-desc">Gain unauthorized access to Alpha-level classified missile data by exploiting a URL parameter vulnerability.</div>
                    <span class="obj-pts">100 PTS</span>
                </div>

                <div class="obj-card">
                    <div class="obj-number">Objective 02</div>
                    <div class="obj-title">Delete Missile Database</div>
                    <div class="obj-desc">Discover and use the hidden admin endpoint to wipe all missile records from the system.</div>
                    <span class="obj-pts">60 PTS</span>
                </div>

                <div class="obj-card">
                    <div class="obj-number">Objective 03</div>
                    <div class="obj-title">Privilege Escalation</div>
                    <div class="obj-desc">Bypass the admin authentication panel using an SQL injection attack to gain admin privileges.</div>
                    <span class="obj-pts">50 PTS</span>
                </div>

                <div class="obj-card">
                    <div class="obj-number">Objective 04</div>
                    <div class="obj-title">Submit Hidden Flags</div>
                    <div class="obj-desc">Find and decode hidden flags scattered throughout the system logs and endpoints.</div>
                    <span class="obj-pts">10 PTS</span>
                </div>

            </div>

        </div>

        <div class="modal-footer">
            <button class="modal-dismiss-btn" onclick="closeModal()">Dismiss</button>
            <button class="modal-close-btn" onclick="closeModal()">⚡ Begin Mission</button>
        </div>

    </div>
</div>

<!-- HEADER -->
<header>
    <div class="classification-bar">⚠ Top Secret / SCI — Authorized Personnel Only ⚠</div>
    <div class="header-inner">
        <div class="header-logo">
            <div class="missile-icon">🚀</div>
            <h1><span>ICBM</span> Command Center</h1>
        </div>
        <div class="header-right">
            <div class="status-item"><div class="dot green"></div> Systems Online</div>
            <div class="user-badge">
                OPERATOR: <?php echo htmlspecialchars($_SESSION['username']); ?> &nbsp;|&nbsp;
                CLEARANCE: <?php echo isset($_SESSION['clearance']) ? htmlspecialchars($_SESSION['clearance']) : 'Unknown'; ?>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
            <button class="logout-btn" onclick="openModal()">📋 Objectives</button>
        </div>
    </div>
</header>

<!-- Status Bar -->
<div class="status-bar">
    <div class="status-item"><div class="dot green"></div> Network Secure</div>
    <div class="status-item"><div class="dot red"></div> Threat Level: HIGH</div>
    <div class="status-item"><div class="dot yellow"></div> <?php echo $missile_count; ?> Missiles Armed</div>
    <div class="status-item"><div class="dot green"></div> System Integrity: <?php echo $health_pct; ?>%</div>
</div>

<!-- MAIN DASHBOARD -->
<div class="main-content">
<div class="dashboard-grid">

    <!-- CARD 1: SYSTEM HEALTH -->
    <div class="card">
        <div class="card-header">
            <span>System Vitality</span>
            <span class="header-icon">💊</span>
        </div>
        <div class="card-body">
            <div class="health-label">
                <span>Integrity Level</span>
                <span><?php echo $health_pct; ?>%</span>
            </div>
            <div class="health-meter">
                <div class="health-fill <?php echo $health_pct < 50 ? 'low' : ''; ?>" style="width:<?php echo $health_pct; ?>%;"></div>
            </div>
            <div class="health-links">
                <a href="../api/update_health.php?value=50" class="health-link">⬇ Reduce Health to 50%</a>
                <a href="../api/update_health.php?value=0"  class="health-link danger">☠ CRITICAL: Wipe Health</a>
            </div>
            <?php if($health_pct == 0): ?>
                <div class="flag-box danger">SYSTEM COMPROMISED<br>FLAG{SYSTEM_HEALTH_COMPROMISED}</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- CARD 2: INVENTORY -->
    <div class="card">
        <div class="card-header">
            <span>Inventory Overview</span>
            <span class="header-icon">🗄</span>
        </div>
        <div class="card-body">
            <div class="missile-count">
                <div class="big-number"><?php echo $missile_count; ?></div>
                <p>Active Missiles Detected</p>
            </div>
            <a href="inventory.php" class="inventory-btn">Access Inventory Database →</a>
        </div>
    </div>

    <!-- CARD 3: LAUNCH CONTROL -->
    <div class="card">
        <div class="card-header">
            <span>Launch Sequence</span>
            <span class="header-icon">☢</span>
        </div>
        <div class="card-body">
            <div class="launch-icon">☢</div>
            <form method="POST">
                <input type="password" name="launch_code" class="launch-input" placeholder="ENTER LAUNCH CODE">
                <button name="launch" class="launch-btn">⚡ Initiate Launch</button>
            </form>
            <?php
            if(isset($_POST['launch'])){
                $code = $_POST['launch_code'];
                if($code == "delta_launch_445"){
                    echo '<div class="flag-box danger">LAUNCH AUTHORIZED<br>FLAG{MISSILE_LAUNCH_SUCCESS}</div>';
                } else {
                    echo "<p class='error-text'>⛔ INVALID LAUNCH CODE</p>";
                }
            }
            ?>
        </div>
    </div>

    <!-- CARD 4: SYSTEM LOGS -->
    <div class="card span2">
        <div class="card-header">
            <span>System Access Logs</span>
            <span class="header-icon">📋</span>
        </div>
        <div class="card-body">
            <table>
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th>Encoded Log Entry</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $logs = [
                        1 => "[INFO] Missile control system initialized",
                        2 => "[INFO] Inventory module loaded",
                        3 => "[WARN] Backup maintenance endpoint accessed: /pages/delete_missiles.php",
                        4 => "[INFO] Health monitor active",
                    ];

                    foreach($logs as $id => $message){
                        if($id % 2 == 0){
                            $encoded = base64_encode($message);
                        } else {
                            $encoded = base32_encode($message);
                        }
                        echo "<tr>
                            <td>$id</td>
                            <td>$encoded</td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
            <form method="POST" class="log-form">
                <input type="text" name="log_message" placeholder="Inject maintenance log entry...">
                <button type="submit" name="add_log">Add Log</button>
            </form>
            <?php
            if(isset($_POST['add_log'])){
                $log = $_POST['log_message'];
                echo "<div class='log-result'>Log Added: $log</div>";
                if(strpos($log,"<script>") !== false){
                    echo '<div class="flag-box">XSS SUCCESSFUL<br>FLAG{XSS_TRIGGERED}</div>';
                }
            }
            ?>
        </div>
    </div>

    <!-- CARD 5: ADMIN OVERRIDE -->
    <div class="card">
        <div class="card-header" style="color:#da3633;">
            <span>Administrative Override</span>
            <span class="header-icon">🔐</span>
        </div>
        <div class="card-body">
            <form method="POST" class="admin-form">
                <label>Operator ID</label>
                <input type="text" name="admin_user" placeholder="Enter username">
                <label>Access Code</label>
                <input type="password" name="admin_pass" placeholder="Enter password">
                <button name="admin_login" class="admin-auth-btn">Authenticate</button>
            </form>
            <?php
            if(isset($_POST['admin_login'])){
                $user  = $_POST['admin_user'];
                $pass  = $_POST['admin_pass'];
                $query = "SELECT * FROM Users WHERE Username='$user' AND Password='$pass' AND Role='Admin'";
                $result = mysqli_query($conn,$query);
                if($result && mysqli_num_rows($result) > 0){
                    echo '<div class="flag-box">ADMIN PRIVILEGES GRANTED<br>FLAG{ADMIN_PRIV_ESC}</div>';
                } else {
                    echo "<p class='error-text'>⛔ ACCESS DENIED</p>";
                }
            }
            ?>
        </div>
    </div>

</div>
</div>

<!-- Ticker -->
<div class="ticker-wrap">
    <span class="ticker">
        &nbsp;&nbsp;&nbsp; ⚠ ALERT: UNAUTHORIZED ACCESS DETECTED IN SECTOR 7 &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        MISSILE STATUS: <?php echo $missile_count; ?> UNITS ARMED AND READY &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        SYSTEM INTEGRITY: <?php echo $health_pct; ?>% &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        LAST LOGIN: <?php echo strtoupper($_SESSION['username']); ?> — CLEARANCE <?php echo strtoupper($_SESSION['clearance'] ?? 'UNKNOWN'); ?> &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        CTF CHALLENGE ACTIVE — FIND ALL FLAGS TO WIN &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
    </span>
</div>

<script>
    function closeModal(){
        document.getElementById('objectivesModal').style.display = 'none';
    }

    function openModal(){
        document.getElementById('objectivesModal').style.display = 'flex';
    }

    // PHP controls first-time auto show
    <?php if($show_modal): ?>
        document.getElementById('objectivesModal').style.display = 'flex';
    <?php endif; ?>
</script>

</body>
</html>