<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["Gamer_Tag"]) || !isset($_SESSION["Player_ID"])) {
    header("Location: login.php");
    exit();
}

$gamerTag = $_SESSION["Gamer_Tag"];
$playerID = $_SESSION["Player_ID"];

$profilePic = null;
$stmt = $conn->prepare("SELECT Profile_Picture FROM Players WHERE Player_ID = ?");
$stmt->bind_param("i", $playerID);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!empty($result["Profile_Picture"]) && file_exists($result["Profile_Picture"])) {
    $profilePic = $result["Profile_Picture"];
}

$sql = "SELECT
            m.Match_ID,
            t.Tournament_Name,
            p1.Gamer_Tag AS Player1,
            p2.Gamer_Tag AS Player2,
            m.Club_Player1,
            m.Club_Player2,
            m.Match_Date,
            m.Match_Time,
            m.Match_Round,
            m.Final_Score,
            w.Gamer_Tag AS Winner
        FROM Matches m
        LEFT JOIN Tournaments t ON m.Tournament_ID = t.Tournament_ID
        LEFT JOIN Players p1 ON m.Player1_ID = p1.Player_ID
        LEFT JOIN Players p2 ON m.Player2_ID = p2.Player_ID
        LEFT JOIN Players w ON m.Winner_ID = w.Player_ID
        ORDER BY m.Match_Date ASC, m.Match_Time ASC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matches - eFootball Arena</title>
    <link rel="stylesheet" href="matches.css">
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

    <nav class="navigation">
        <a href="dashboard.php">HOME</a>
        <a href="tournament.php">TOURNAMENTS</a>
        <a href="matches.php" class="active">MATCHES</a>
        <a href="teams.php">MY TEAM</a>
        <a href="rankings.php">RANKINGS</a>
    </nav>

    <div class="top-right">
        <div class="profile">
            <div class="profile-picture">
                <?php if ($profilePic): ?>
                    <img src="<?php echo htmlspecialchars($profilePic); ?>" 
                         alt="Profile" 
                         style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                <?php else: ?>
                    <?php echo strtoupper(substr($gamerTag, 0, 1)); ?>
                <?php endif; ?>
            </div>
            <span class="profile-name"><?php echo htmlspecialchars($gamerTag); ?></span>
        </div>
        <a href="logout.php" class="logout-button">LOGOUT</a>
    </div>
</header>

<main class="matches-page">

    <div class="page-header">
        <div>
            <span class="small-title">eFOOTBALL ARENA 2026</span>
            <h1>MATCH <span>CENTER</span></h1>
            <p>All fixtures, results and live matches in one place.</p>
        </div>
    </div>

    <div class="matches-container">

        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($match = mysqli_fetch_assoc($result)): ?>

                <?php
                $today = date("Y-m-d");
                if ($match["Match_Date"] > $today) {
                    $status = "UPCOMING";
                    $statusClass = "status-upcoming";
                } elseif ($match["Match_Date"] == $today) {
                    $status = "LIVE";
                    $statusClass = "status-live";
                } else {
                    $status = "COMPLETED";
                    $statusClass = "status-completed";
                }
                ?>

     <a href="match_details.php?id=<?php echo $match['Match_ID']; ?>" class="match-card-link">
          <div class="match-card">
                    <div class="match-left">
                        <div class="match-id">Match #<?php echo $match["Match_ID"]; ?></div>
                        <div class="status-badge <?php echo $statusClass; ?>">
                            <?php echo $status; ?>
                        </div>
                    </div>

                    <div class="tournament-name">
                        <?php echo htmlspecialchars($match["Tournament_Name"] ?? 'Friendly'); ?>
                    </div>

                    <div class="players-section">
                        <div class="player">
                            <h3><?php echo htmlspecialchars($match["Player1"]); ?></h3>
                            <span><?php echo htmlspecialchars($match["Club_Player1"]); ?></span>
                        </div>
                        <div class="vs">VS</div>
                        <div class="player">
                            <h3><?php echo htmlspecialchars($match["Player2"]); ?></h3>
                            <span><?php echo htmlspecialchars($match["Club_Player2"]); ?></span>
                        </div>
                    </div>

                    <div class="score-box">
                        <?php
                        if ($status === "UPCOMING") {
                            echo "—";
                        } else {
                            echo htmlspecialchars($match["Final_Score"] ?? "0-0");
                        }
                        ?>
                    </div>

                    <div class="match-meta">
                        <div><strong>Date:</strong> <?php echo $match["Match_Date"]; ?></div>
                        <div><strong>Time:</strong> <?php echo date("H:i", strtotime($match["Match_Time"])); ?></div>
                        <div><strong>Round:</strong> <?php echo htmlspecialchars($match["Match_Round"]); ?></div>
                    </div>

                    <?php if ($status !== "UPCOMING" && !empty($match["Winner"])): ?>
                        <div class="winner-badge">
                            Winner: <?php echo htmlspecialchars($match["Winner"]); ?>
                        </div>
                    <?php endif; ?>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-matches">No matches found.</div>
        <?php endif; ?>

    </div>

    <div class="back-section">
        <a href="dashboard.php" class="back-btn">← BACK TO DASHBOARD</a>
    </div>

</main>

<audio id="backgroundMusic" loop>
    <source src="Audio/background.mp3" type="audio/mpeg">
</audio>
<audio id="clickSound">
    <source src="Audio/click.mp3" type="audio/mpeg">
</audio>
<script src="music.js"></script>

</body>
</html>
