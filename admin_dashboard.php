<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["Admin_ID"])) {
    header("Location: login.php");
    exit();
}

$Admin_ID = $_SESSION["Admin_ID"];
$Admin_Name = $_SESSION["Admin_Name"];
$Role = $_SESSION["Role"];

$total_players = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Players"))['total'];
$total_tournaments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Tournaments"))['total'];
$upcoming = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Tournaments WHERE Tournament_Status = 'Upcoming'"))['total'];
$live_streams = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Live_Streams WHERE Status = 'Live'"))['total'];
$total_matches = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Matches"))['total'];
$banned = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM Players WHERE Status = 'Banned'"))['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - eFootball Arena</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<audio id="backgroundMusic" loop>
    <source src="Audio/background.mp3" type="audio/mpeg">
</audio>
<audio id="clickSound">
    <source src="Audio/click.mp3" type="audio/mpeg">
</audio>

<header class="topbar">
    <div class="logo">eFootball <span>ADMIN</span></div>
    <div class="admin-info">
        <span><?php echo htmlspecialchars($Admin_Name); ?> (<?php echo $Role; ?>)</span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<div class="admin-layout">
    <aside class="sidebar">
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        <a href="manage_players.php">Manage Players</a>
        <a href="manage_tournaments.php">Manage Tournaments</a>
        <a href="manage_matches.php">Manage Matches</a>
        <a href="manage_auctions.php">Manage Auctions</a>
        <a href="logout.php">Logout</a>
    </aside>

    <main class="main-content">
        <h1>Admin Dashboard</h1>
        <p class="subtitle">Welcome back, <?php echo htmlspecialchars($Admin_Name); ?></p>

        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $total_players; ?></h3>
                <p>Total Players</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $total_tournaments; ?></h3>
                <p>Tournaments</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $upcoming; ?></h3>
                <p>Upcoming</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $live_streams; ?></h3>
                <p>Live Streams</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $total_matches; ?></h3>
                <p>Total Matches</p>
            </div>
            <div class="stat-card danger">
                <h3><?php echo $banned; ?></h3>
                <p>Banned Players</p>
            </div>
        </div>
    </main>
</div>

<script src="music.js"></script>
</body>
</html>