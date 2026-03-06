
<?php
include("../config/database.php");

/*
VULNERABILITY:
No authentication or authorization check.
Anyone who discovers this endpoint
can delete the missile database.
*/

$query = "DELETE FROM Missiles";
mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICBM Control | Database Wiped</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: #020408;
            font-family: 'Courier New', monospace;
            color: #c9d1d9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .grid-bg {
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            background-image:
                linear-gradient(rgba(218,54,51,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(218,54,51,0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            z-index: 0;
            pointer-events: none;
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
            position: relative;
            z-index: 10;
        }

        /* ── Center Container ── */
        .center-wrap {
            position: relative;
            z-index: 5;
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .wipe-card {
            position: relative;
            background: rgba(10,14,23,0.97);
            border: 1px solid rgba(218,54,51,0.5);
            border-radius: 4px;
            width: 100%;
            max-width: 560px;
            box-shadow:
                0 0 60px rgba(218,54,51,0.2),
                0 0 120px rgba(0,0,0,0.9);
            overflow: hidden;
        }

        .wipe-card::before,
        .wipe-card::after {
            content: '';
            position: absolute;
            width: 18px; height: 18px;
            border-color: #da3633;
            border-style: solid;
            z-index: 2;
        }
        .wipe-card::before { top: -1px; left: -1px; border-width: 2px 0 0 2px; }
        .wipe-card::after  { bottom: -1px; right: -1px; border-width: 0 2px 2px 0; }

        /* ── Card Header ── */
        .wipe-header {
            background: rgba(218,54,51,0.12);
            border-bottom: 1px solid rgba(218,54,51,0.4);
            padding: 14px 25px;
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #da3633;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ── Card Body ── */
        .wipe-body {
            padding: 35px 30px;
            text-align: center;
        }

        .skull-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            filter: drop-shadow(0 0 15px rgba(218,54,51,0.8));
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1);    filter: drop-shadow(0 0 15px rgba(218,54,51,0.8)); }
            50%       { transform: scale(1.08); filter: drop-shadow(0 0 30px rgba(218,54,51,1));   }
        }

        .wipe-title {
            font-size: 1.4rem;
            color: #da3633;
            text-transform: uppercase;
            letter-spacing: 5px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .wipe-subtitle {
            font-size: 0.75rem;
            color: #8b949e;
            letter-spacing: 2px;
            margin-bottom: 30px;
        }

        /* ── Divider ── */
        .divider {
            border: none;
            border-top: 1px solid #21262d;
            margin: 0 0 25px;
        }

        /* ── Info Rows ── */
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #21262d;
            font-size: 0.78rem;
        }

        .info-row:last-of-type { border-bottom: none; }

        .info-label {
            color: #8b949e;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: 0.7rem;
        }

        .info-value { color: #c9d1d9; }
        .info-value.red { color: #da3633; font-weight: bold; }

        /* ── Flag Box ── */
        .flag-box {
            margin-top: 25px;
            padding: 16px;
            background: rgba(218,54,51,0.08);
            border: 1px dashed #da3633;
            border-radius: 2px;
        }

        .flag-box .flag-label {
            font-size: 0.65rem;
            color: #8b949e;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .flag-box .flag-value {
            font-size: 1rem;
            color: #da3633;
            font-weight: bold;
            letter-spacing: 3px;
        }

        /* ── Back Button ── */
        .back-btn {
            display: inline-block;
            margin-top: 25px;
            padding: 11px 30px;
            background: transparent;
            border: 1px solid #30363d;
            color: #8b949e;
            font-family: 'Courier New', monospace;
            font-size: 0.8rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-decoration: none;
            transition: all 0.3s;
        }

        .back-btn:hover {
            border-color: #da3633;
            color: #da3633;
            text-decoration: none;
        }

        /* ── Ticker ── */
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

<div class="grid-bg"></div>

<div class="classification-bar">⚠ Top Secret / SCI — Authorized Personnel Only ⚠</div>

<div class="center-wrap">
    <div class="wipe-card">

        <div class="wipe-header">
            ☠ &nbsp; DESTRUCTIVE COMMAND EXECUTED
        </div>

        <div class="wipe-body">

            <div class="skull-icon">☠</div>

            <div class="wipe-title">Database Wiped</div>
            <div class="wipe-subtitle">All missile records have been permanently removed from the system</div>

            <hr class="divider">

            <div class="info-row">
                <span class="info-label">Command Executed</span>
                <span class="info-value red">DELETE FROM Missiles</span>
            </div>
            <div class="info-row">
                <span class="info-label">Records Destroyed</span>
                <span class="info-value red">ALL</span>
            </div>
            <div class="info-row">
                <span class="info-label">Authorization Check</span>
                <span class="info-value red">NONE</span>
            </div>
            <div class="info-row">
                <span class="info-label">Endpoint</span>
                <span class="info-value">/pages/delete_missiles.php</span>
            </div>
            <div class="info-row">
                <span class="info-label">Timestamp</span>
                <span class="info-value"><?php echo date('Y-m-d H:i:s'); ?></span>
            </div>

            <div class="flag-box">
                <div class="flag-label">🚩 Flag Captured</div>
                <div class="flag-value">FLAG{DATABASE_DESTROYED}</div>
            </div>

            <a href="dashboard.php" class="back-btn">← Return to Dashboard</a>

        </div>
    </div>
</div>

<div class="ticker-wrap">
    <span class="ticker">
        &nbsp;&nbsp;&nbsp; ☠ CRITICAL: MISSILE DATABASE HAS BEEN WIPED &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        UNAUTHORIZED ENDPOINT ACCESS DETECTED: /pages/delete_missiles.php &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        ALL MISSILE RECORDS DESTROYED &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        SYSTEM INTEGRITY COMPROMISED &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        CTF CHALLENGE ACTIVE — FIND ALL FLAGS TO WIN &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
    </span>
</div>

</body>
</html>