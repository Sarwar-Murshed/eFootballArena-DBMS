<?php
session_start();

if (!isset($_SESSION["Gamer_Tag"]) || !isset($_SESSION["Player_ID"])) {
    header("Location: login.php");
    exit();
}

require_once "db.php";

date_default_timezone_set('Asia/Dhaka');

$gamerTag = $_SESSION["Gamer_Tag"];
$playerID = $_SESSION["Player_ID"];

$wallet_sql = "SELECT Balance FROM Manager_Wallet WHERE Manager_ID = ?";
$stmt = $conn->prepare($wallet_sql);
$stmt->bind_param("i", $playerID);
$stmt->execute();
$wallet_data = $stmt->get_result()->fetch_assoc();
$wallet = $wallet_data ? $wallet_data["Balance"] : 0;

$notif_sql = "SELECT COUNT(*) as total FROM Notifications WHERE (Player_ID = ? OR Player_ID IS NULL) AND Is_Read = 0";
$stmt = $conn->prepare($notif_sql);
$stmt->bind_param("i", $playerID);
$stmt->execute();
$notificationCount = $stmt->get_result()->fetch_assoc()['total'];

$profilePic = null;
$stmt = $conn->prepare("SELECT Profile_Picture FROM Players WHERE Player_ID = ?");
$stmt->bind_param("i", $playerID);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!empty($result["Profile_Picture"]) && file_exists($result["Profile_Picture"])) {
    $profilePic = $result["Profile_Picture"];
}

$claimMessage = "";
$canClaim = false;
$remainingTime = "";

$claim_sql = "SELECT Last_Daily_Claim FROM Players WHERE Player_ID = ?";
$stmt = $conn->prepare($claim_sql);
$stmt->bind_param("i", $playerID);
$stmt->execute();
$claimData = $stmt->get_result()->fetch_assoc();
$lastClaim = $claimData["Last_Daily_Claim"];

if ($lastClaim === null) {
    $canClaim = true;
} else {
    $lastClaimTime = strtotime($lastClaim);
    $nextClaimTime = $lastClaimTime + 86400;
    $now = time();

    if ($now >= $nextClaimTime) {
        $canClaim = true;
    } else {
        $diff = $nextClaimTime - $now;
        $hours = floor($diff / 3600);
        $minutes = floor(($diff % 3600) / 60);
        $seconds = $diff % 60;
        $remainingTime = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["claim_daily"])) {
    if ($canClaim) {
        $conn->query("UPDATE Manager_Wallet SET Balance = Balance + 100000 WHERE Manager_ID = $playerID");
        $conn->query("UPDATE Players SET Last_Daily_Claim = NOW() WHERE Player_ID = $playerID");

        $wallet_sql = "SELECT Balance FROM Manager_Wallet WHERE Manager_ID = ?";
        $stmt = $conn->prepare($wallet_sql);
        $stmt->bind_param("i", $playerID);
        $stmt->execute();
        $wallet = $stmt->get_result()->fetch_assoc()["Balance"];

        $claimMessage = "You received 100,000 coins!";
        $canClaim = false;
        $remainingTime = "23:59:59";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eFootball Arena</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<header class="topbar">
    <div class="brand">
        <div class="brand-logo">⚽</div>
        <div>
            <div class="brand-title">eFootball</div>
            <div class="brand-subtitle">ARENA</div>
        </div>
    </div>

    <div class="wallet">
        <span class="wallet-coin">●</span>
        <span><?php echo number_format($wallet); ?></span>
    </div>

    <nav class="navigation">
        <a href="dashboard.php" class="active">HOME</a>
        <a href="tournament.php">TOURNAMENTS</a>
        <a href="transfers.php">TRANSFERS</a>
        <a href="auction.php">AUCTIONS</a>
        <a href="markets.php">MARKETS</a>
    </nav>

    <div class="top-right">
        <a href="notifications.php" class="notification">
            <span class="notification-bell">🔔</span>
            <span class="notification-count"><?php echo $notificationCount; ?></span>
        </a>

        <a href="profile.php" class="profile" style="text-decoration: none; color: inherit;">
            <div class="profile-picture">
                <?php if ($profilePic): ?>
                    <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                <?php else: ?>
                    <?php echo strtoupper(substr($gamerTag, 0, 1)); ?>
                <?php endif; ?>
            </div>
            <span class="profile-name"><?php echo htmlspecialchars($gamerTag); ?></span>
        </a>

        <a href="logout.php" class="logout-button">LOGOUT</a>
    </div>
</header>

<main class="dashboard">
    <section class="hero">
        <div class="hero-content">
            <div class="season">eFOOTBALL ARENA 2026</div>
            <h1>WELCOME BACK,</h1>
            <h2><?php echo htmlspecialchars($gamerTag); ?></h2>
            <p>Compete. Build your squad. Rise through the Arena.</p>
        </div>
    </section>

    <div class="daily-reward">
        <?php if ($claimMessage): ?>
            <div class="claim-success"><?php echo $claimMessage; ?></div>
        <?php endif; ?>

        <?php if ($canClaim): ?>
            <form method="POST">
                <button type="submit" name="claim_daily" class="claim-btn">
                    🎁 Claim Daily Reward (100,000 Coins)
                </button>
            </form>
        <?php else: ?>
            <div class="claim-timer">
                Next reward in: <span id="countdown"><?php echo $remainingTime; ?></span>
            </div>
        <?php endif; ?>
    </div>

    <section class="dashboard-content">
        <div class="feature-grid">
            <a href="matches.php" class="feature-card">
                <div class="feature-icon">⚽</div>
                <div class="feature-text">
                    <h3>MATCHES</h3>
                    <p>View your matches, results and match history.</p>
                    <span>OPEN →</span>
                </div>
            </a>

            <a href="teams.php" class="feature-card">
                <div class="feature-icon">👥</div>
                <div class="feature-text">
                    <h3>MY TEAM</h3>
                    <p>Manage your squad, formation and team players.</p>
                    <span>OPEN →</span>
                </div>
            </a>

            <a href="rankings.php" class="feature-card">
                <div class="feature-icon">♛</div>
                <div class="feature-text">
                    <h3>RANKINGS</h3>
                    <p>Track your position and compete for the top.</p>
                    <span>OPEN →</span>
                </div>
            </a>

            <a href="achievements.php" class="feature-card">
                <div class="feature-icon">🏅</div>
                <div class="feature-text">
                    <h3>ACHIEVEMENTS</h3>
                    <p>View your achievements, awards and milestones.</p>
                    <span>OPEN →</span>
                </div>
            </a>

            <a href="player_features.php" class="feature-card">
                <div class="feature-icon">👤</div>
                <div class="feature-text">
                    <h3>PLAYER FEATURES</h3>
                    <p>Explore player stats, skills and performance.</p>
                    <span>OPEN →</span>
                </div>
            </a>

            <a href="matches.php" class="feature-card">
                <div class="feature-icon">📅</div>
                <div class="feature-text">
                    <h3>UPCOMING MATCHES</h3>
                    <p>Check your next matches and scheduled fixtures.</p>
                    <span>OPEN →</span>
                </div>
            </a>
        </div>

        <aside class="live-section">
            <div class="live-label">LIVE</div>
            <div class="live-icon">▶️</div>
            <h2>LIVE MATCHES</h2>
            <p>Watch the action live from the Arena.</p>
            <a href="live.php" class="live-button">WATCH LIVE →</a>
        </aside>
    </section>
</main>

<div id="adOverlay" class="ad-overlay hidden">
    <div class="ad-box">
        <button id="adClose" class="ad-close">×</button>
        <img id="adImage" src="Images/fifa-collaboration.jpg" alt="Advertisement">
    </div>
</div>

<audio id="backgroundMusic" loop>
    <source src="Audio/background.mp3" type="audio/mpeg">
</audio>

<audio id="clickSound">
    <source src="Audio/click.mp3" type="audio/mpeg">
</audio>

<script src="dashboard.js"></script>
<script src="music.js"></script>

</body>
</html>