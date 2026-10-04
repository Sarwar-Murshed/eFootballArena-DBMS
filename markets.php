<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["Player_ID"])) {
    header("Location: login.php");
    exit();
}

$gamerTag = $_SESSION["Gamer_Tag"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Markets - eFootball Arena</title>
    <link rel="stylesheet" href="markets.css">
</head>
<body>

<audio id="backgroundMusic" loop>
    <source src="Audio/background.mp3" type="audio/mpeg">
</audio>
<audio id="clickSound">
    <source src="Audio/click.mp3" type="audio/mpeg">
</audio>

<header class="topbar">
    <div class="logo">eFootball <span>ARENA</span></div>
    <nav class="navigation">
        <a href="dashboard.php">HOME</a>
        <a href="tournament.php">TOURNAMENTS</a>
        <a href="teams.php">MY TEAM</a>
        <a href="auction.php">AUCTIONS</a>
        <a href="markets.php" class="active">MARKETS</a>
    </nav>
    <div class="profile"><?php echo htmlspecialchars($gamerTag); ?></div>
</header>

<main class="markets-page">

    <div class="page-header">
        <h1>PLAYER & COACH MARKET</h1>
        <p>Buy and sell players & coaches</p>
    </div>

    <div class="market-buttons">

        <a href="buy_players.php" class="market-card buy-card">
            <div class="card-icon">🛒</div>
            <h2>BUY PLAYERS</h2>
            <p>Purchase players from the market using your coins</p>
            <span class="card-btn">ENTER MARKET →</span>
        </a>

        <a href="sell_players.php" class="market-card sell-card">
            <div class="card-icon">💰</div>
            <h2>SELL PLAYERS</h2>
            <p>Sell your players and earn coins</p>
            <span class="card-btn">SELL NOW →</span>
        </a>

        <a href="buy_coaches.php" class="market-card buy-card">
            <div class="card-icon">👔</div>
            <h2>BUY COACHES</h2>
            <p>Hire legendary coaches for your team</p>
            <span class="card-btn">ENTER →</span>
        </a>

        <a href="sell_coaches.php" class="market-card sell-card">
            <div class="card-icon">🔄</div>
            <h2>SELL COACH</h2>
            <p>Release your current coach and get coins back</p>
            <span class="card-btn">SELL →</span>
        </a>

    </div>

    <div class="back-section">
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>

</main>

<script src="music.js"></script>
</body>
</html>