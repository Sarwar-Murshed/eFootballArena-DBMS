<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["Gamer_Tag"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: matches.php");
    exit();
}

$match_id = (int)$_GET["id"];
$gamerTag = $_SESSION["Gamer_Tag"];

$sql = "SELECT 
            m.*,
            t.Tournament_Name,
            p1.Gamer_Tag AS Player1_Name,
            p2.Gamer_Tag AS Player2_Name,
            w.Gamer_Tag AS Winner_Name
        FROM Matches m
        LEFT JOIN Tournaments t ON m.Tournament_ID = t.Tournament_ID
        LEFT JOIN Players p1 ON m.Player1_ID = p1.Player_ID
        LEFT JOIN Players p2 ON m.Player2_ID = p2.Player_ID
        LEFT JOIN Players w ON m.Winner_ID = w.Player_ID
        WHERE m.Match_ID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $match_id);
$stmt->execute();
$match = $stmt->get_result()->fetch_assoc();

if (!$match) {
    echo "Match not found.";
    exit();
}

$stats_sql = "SELECT * FROM Match_Statistics WHERE Match_ID = ?";
$stmt = $conn->prepare($stats_sql);
$stmt->bind_param("i", $match_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

$today = date("Y-m-d");
$isUpcoming = ($match["Match_Date"] > $today);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Details - eFootball Arena</title>
    <link rel="stylesheet" href="match_details.css">
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
        <a href="matches.php" class="active">MATCHES</a>
        <a href="teams.php">MY TEAM</a>
        <a href="tournament.php">TOURNAMENTS</a>
    </nav>
    <div class="profile"><?php echo htmlspecialchars($gamerTag); ?></div>
</header>

<main class="details-page">

    <div class="page-header">
        <a href="matches.php" class="back-link">← Back to Matches</a>
        <h1>MATCH DETAILS</h1>
        <p><?php echo htmlspecialchars($match["Tournament_Name"] ?? "Friendly"); ?> • <?php echo htmlspecialchars($match["Match_Round"]); ?></p>
    </div>

    <!-- Scoreboard -->
    <div class="scoreboard">

        <div class="team team-left">
            <h2 class="club-name"><?php echo htmlspecialchars($match["Club_Player1"]); ?></h2>
            <span class="gamer-tag"><?php echo htmlspecialchars($match["Player1_Name"]); ?></span>
        </div>

        <div class="score-center">
            <div class="final-score">
                <?php 
                if ($isUpcoming) {
                    echo "vs";
                } else {
                    echo htmlspecialchars($match["Final_Score"] ?? "0-0");
                }
                ?>
            </div>
            <div class="match-date">
                <?php echo $match["Match_Date"]; ?> • <?php echo date("H:i", strtotime($match["Match_Time"])); ?>
            </div>
            <?php if (!$isUpcoming && !empty($match["Winner_Name"])): ?>
                <div class="winner">Winner: <?php echo htmlspecialchars($match["Winner_Name"]); ?></div>
            <?php endif; ?>
        </div>

        <div class="team team-right">
            <h2 class="club-name"><?php echo htmlspecialchars($match["Club_Player2"]); ?></h2>
            <span class="gamer-tag"><?php echo htmlspecialchars($match["Player2_Name"]); ?></span>
        </div>

    </div>

    <!-- Match Statistics -->
    <div class="stats-section">
        <h2>MATCH STATISTICS</h2>

        <?php if ($isUpcoming): ?>
            <div class="stats-grid">
                <div class="stat-row">
                    <div class="stat-label">Goals</div>
                    <div class="stat-value">0</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Possession</div>
                    <div class="stat-value">0%</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Shots</div>
                    <div class="stat-value">0</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Shots on Target</div>
                    <div class="stat-value">0</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Pass Accuracy</div>
                    <div class="stat-value">0%</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Fouls</div>
                    <div class="stat-value">0</div>
                </div>
                <div class="stat-row yellow">
                    <div class="stat-label">Yellow Cards</div>
                    <div class="stat-value">0</div>
                </div>
                <div class="stat-row red">
                    <div class="stat-label">Red Cards</div>
                    <div class="stat-value">0</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Corners</div>
                    <div class="stat-value">0</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Saves</div>
                    <div class="stat-value">0</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Offsides</div>
                    <div class="stat-value">0</div>
                </div>
            </div>

        <?php elseif ($stats): ?>
            <div class="stats-grid">
                <div class="stat-row">
                    <div class="stat-label">Goals</div>
                    <div class="stat-value"><?php echo $stats["Goals"]; ?></div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Possession</div>
                    <div class="stat-value"><?php echo $stats["Possession_Percentage"]; ?>%</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Shots</div>
                    <div class="stat-value"><?php echo $stats["Shots"]; ?></div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Shots on Target</div>
                    <div class="stat-value"><?php echo $stats["Shots_On_Target"]; ?></div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Pass Accuracy</div>
                    <div class="stat-value"><?php echo $stats["Pass_Accuracy"]; ?>%</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Fouls</div>
                    <div class="stat-value"><?php echo $stats["Fouls"]; ?></div>
                </div>
                <div class="stat-row yellow">
                    <div class="stat-label">Yellow Cards</div>
                    <div class="stat-value"><?php echo $stats["Yellow_Cards"]; ?></div>
                </div>
                <div class="stat-row red">
                    <div class="stat-label">Red Cards</div>
                    <div class="stat-value"><?php echo $stats["Red_Cards"]; ?></div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Corners</div>
                    <div class="stat-value"><?php echo $stats["Corners"]; ?></div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Saves</div>
                    <div class="stat-value"><?php echo $stats["Saves"]; ?></div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Offsides</div>
                    <div class="stat-value"><?php echo $stats["Offsides"]; ?></div>
                </div>
            </div>
        <?php else: ?>
            <div class="no-stats">No detailed statistics available for this match.</div>
        <?php endif; ?>
    </div>

</main>

<script src="music.js"></script>
</body>
</html>