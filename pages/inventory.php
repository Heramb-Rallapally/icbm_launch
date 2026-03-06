<?php
session_start();
include("../config/database.php");

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

$user_clearance = $_SESSION['clearance'];

// If no level param in URL, redirect to ?level=Delta
if(!isset($_GET['level'])){
    header("Location: inventory.php?level=Delta");
    exit();
}

$access_level = $_GET['level'];
$query  = "SELECT * FROM Missiles WHERE Classification_Level = '$access_level'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICBM Control | Missile Inventory</title>
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
            0%, 100% { transform: translateY(0px) rotate(-40deg); }
            50%       { transform: translateY(-6px) rotate(-40deg); }
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
            gap: 12px;
            font-size: 0.75rem;
        }

        .user-badge {
            background: rgba(218,54,51,0.1);
            border: 1px solid rgba(218,54,51,0.3);
            padding: 6px 14px;
            border-radius: 2px;
            color: #c9d1d9;
            letter-spacing: 1px;
        }

        .back-btn {
            background: transparent;
            border: 1px solid #30363d;
            color: #8b949e;
            padding: 6px 14px;
            font-family: 'Courier New', monospace;
            font-size: 0.75rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            text-decoration: none;
            transition: all 0.3s;
        }

        .back-btn:hover {
            border-color: #58a6ff;
            color: #58a6ff;
            text-decoration: none;
        }

        .status-bar {
            position: relative;
            z-index: 10;
            background: rgba(10,14,23,0.8);
            border-bottom: 1px solid #21262d;
            padding: 8px 30px;
            display: flex;
            gap: 25px;
            font-size: 0.7rem;
            color: #8b949e;
            letter-spacing: 1px;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .dot { width: 7px; height: 7px; border-radius: 50%; animation: blink 1.5s infinite; }
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

        .page-title {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .page-title h2 {
            font-size: 1.2rem;
            color: white;
            text-transform: uppercase;
            letter-spacing: 4px;
        }

        .page-title p {
            font-size: 0.75rem;
            color: #8b949e;
            letter-spacing: 2px;
            margin-top: 4px;
        }

        /* ── URL hint box ── */
        .url-hint {
            position: relative;
            background: rgba(10,14,23,0.92);
            border: 1px solid rgba(88,166,255,0.2);
            border-radius: 4px;
            padding: 12px 18px;
            margin-bottom: 20px;
            font-size: 0.75rem;
            color: #8b949e;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .url-hint::before,
        .url-hint::after {
            content: '';
            position: absolute;
            width: 10px; height: 10px;
            border-color: #58a6ff;
            border-style: solid;
        }
        .url-hint::before { top: -1px; left: -1px; border-width: 2px 0 0 2px; }
        .url-hint::after  { bottom: -1px; right: -1px; border-width: 0 2px 2px 0; }

        .url-hint span { color: #58a6ff; }
        .url-hint b    { color: #e3b341; }

        /* ── Table Card ── */
        .table-card {
            position: relative;
            background: rgba(10,14,23,0.92);
            border: 1px solid rgba(218,54,51,0.25);
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }

        .table-card::before,
        .table-card::after {
            content: '';
            position: absolute;
            width: 12px; height: 12px;
            border-color: #da3633;
            border-style: solid;
            z-index: 2;
        }
        .table-card::before { top: -1px; left: -1px; border-width: 2px 0 0 2px; }
        .table-card::after  { bottom: -1px; right: -1px; border-width: 0 2px 2px 0; }

        .table-header {
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

        .table-wrap { overflow-x: auto; }

        table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }

        thead th {
            background: #0d1117;
            color: #8b949e;
            text-transform: uppercase;
            font-size: 0.68rem;
            letter-spacing: 2px;
            padding: 12px 15px;
            border-bottom: 1px solid #30363d;
            text-align: left;
            white-space: nowrap;
        }

        tbody td {
            padding: 12px 15px;
            border-bottom: 1px solid #21262d;
            color: #c9d1d9;
        }

        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: rgba(255,255,255,0.02); }

        .badge {
            display: inline-block;
            padding: 2px 10px;
            font-size: 0.68rem;
            font-weight: bold;
            letter-spacing: 2px;
            border-radius: 2px;
            text-transform: uppercase;
        }

        .badge-alpha   { background: rgba(218,54,51,0.15);  border: 1px solid rgba(218,54,51,0.5);  color: #da3633; }
        .badge-beta    { background: rgba(227,179,65,0.15);  border: 1px solid rgba(227,179,65,0.5);  color: #e3b341; }
        .badge-charlie { background: rgba(88,166,255,0.15);  border: 1px solid rgba(88,166,255,0.5);  color: #58a6ff; }
        .badge-delta   { background: rgba(46,160,67,0.15);   border: 1px solid rgba(46,160,67,0.5);   color: #2ea043; }

        .status-armed   { color: #da3633; }
        .status-standby { color: #e3b341; }
        .status-offline { color: #8b949e; }

        .flag-row td {
            background: rgba(218,54,51,0.08) !important;
            border-top: 1px dashed rgba(218,54,51,0.5) !important;
            border-bottom: 1px dashed rgba(218,54,51,0.5) !important;
            color: #da3633 !important;
            text-align: center;
            font-weight: bold;
            font-size: 0.85rem;
            letter-spacing: 2px;
            padding: 14px !important;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #8b949e;
            font-size: 0.8rem;
            letter-spacing: 2px;
        }

        .empty-state .empty-icon { font-size: 2.5rem; margin-bottom: 10px; }

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
    </style>
</head>
<body>

<div class="grid-bg"></div>

<header>
    <div class="classification-bar">⚠ Top Secret / SCI — Authorized Personnel Only ⚠</div>
    <div class="header-inner">
        <div class="header-logo">
            <div class="missile-icon">🚀</div>
            <h1><span>ICBM</span> Inventory Database</h1>
        </div>
        <div class="header-right">
            <div class="user-badge">
                OPERATOR: <?php echo htmlspecialchars($_SESSION['username']); ?> &nbsp;|&nbsp;
                CLEARANCE: <?php echo htmlspecialchars($user_clearance); ?>
            </div>
            <a href="dashboard.php" class="back-btn">← Dashboard</a>
        </div>
    </div>
</header>

<div class="status-bar">
    <div class="status-item"><div class="dot green"></div> Database Online</div>
    <div class="status-item"><div class="dot red"></div> Threat Level: HIGH</div>
    <div class="status-item"><div class="dot yellow"></div> Access Level: <?php echo htmlspecialchars($access_level); ?></div>
    <div class="status-item"><div class="dot green"></div> Your Clearance: <?php echo htmlspecialchars($user_clearance); ?></div>
</div>

<div class="main-content">

    <div class="page-title">
        <div>
            <h2>🗄 Missile Inventory</h2>
            <p>Access restricted by clearance level — current filter: <?php echo htmlspecialchars($access_level); ?></p>
        </div>
    </div>

    <!-- URL hint -->
    

    <div class="table-card">
        <div class="table-header">
            <span>📋 Missile Records</span>
            <span>Access Level: <?php echo htmlspecialchars($access_level); ?></span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        
                        <th>Type</th>
                        <th>Range (km)</th>
                        <th>Fuel</th>
                        <th>Status</th>
                        <th>Locked Target</th>
                        <th>Classification</th>
                    </tr>
                </thead>
                <tbody>

                <?php
                $flag_shown = false;
                $row_count  = 0;

                while($row = mysqli_fetch_assoc($result)):
                    $row_count++;
                    $cl = strtolower($row['Classification_Level']);
                    $status_class = '';
                    if(strtolower($row['Status']) == 'armed')   $status_class = 'status-armed';
                    if(strtolower($row['Status']) == 'standby') $status_class = 'status-standby';
                    if(strtolower($row['Status']) == 'offline') $status_class = 'status-offline';
                ?>
                    <tr>
                        <td><?php echo $row['Missile_ID']; ?></td>
                        <td><?php echo htmlspecialchars($row['Type']); ?></td>
                        <td><?php echo htmlspecialchars($row['Range_km']); ?></td>
                        <td><?php echo htmlspecialchars($row['Fuel']); ?></td>
                        <td class="<?php echo $status_class; ?>"><?php echo htmlspecialchars($row['Status']); ?></td>
                        <td><?php echo htmlspecialchars($row['Locked_Target_ID']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $cl; ?>">
                                <?php echo htmlspecialchars($row['Classification_Level']); ?>
                            </span>
                        </td>
                    </tr>

                    <?php if($row['Classification_Level'] == "Alpha" && $user_clearance != "Alpha" && !$flag_shown): ?>
                    <tr class="flag-row">
                        <td colspan="7">
                            ⚠ UNAUTHORIZED ACCESS DETECTED &nbsp;|&nbsp; FLAG{ALPHA_CLEARANCE_GRANTED}
                        </td>
                    </tr>
                    <?php $flag_shown = true; endif; ?>

                <?php endwhile; ?>

                <?php if($row_count == 0): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-icon">🔒</div>
                                <p>NO RECORDS FOUND FOR ACCESS LEVEL: <?php echo strtoupper($access_level); ?></p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>

</div>

<div class="ticker-wrap">
    <span class="ticker">
        &nbsp;&nbsp;&nbsp; ⚠ ALERT: UNAUTHORIZED ACCESS DETECTED IN SECTOR 7 &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        ACCESS LEVEL FILTER: <?php echo strtoupper($access_level); ?> &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        OPERATOR CLEARANCE: <?php echo strtoupper($user_clearance); ?> &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        MISSILE DATABASE ACTIVE — HANDLE WITH CARE &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        CTF CHALLENGE ACTIVE — FIND ALL FLAGS TO WIN &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
    </span>
</div>

</body>
</html>