<?php
session_start();

if (!isset($_SESSION["Gamer_Tag"]) || !isset($_SESSION["Player_ID"])) {
    header("Location: login.php");
    exit();
}

require_once "db.php";

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

$sql = "SELECT Rankings.*, Players.Gamer_Tag, Players.Real_Name
        FROM Rankings
        JOIN Players ON Rankings.Player_ID = Players.Player_ID
        ORDER BY Rankings.Player_Rank ASC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rankings - eFootball Arena</title>
    <link rel="stylesheet" href="rankings.css">
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
        <a href="matches.php">MATCHES</a>
        <a href="teams.php">MY TEAM</a>
        <a href="rankings.php" class="active">RANKINGS</a>
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

<main class="ranking-page">

    <section class="ranking-header">
        <div class="header-content">
            <span class="small-title">eFOOTBALL ARENA 2026</span>
            <h1>PLAYER <span>RANKINGS</span></h1>
            <p>Track performances, points and your position in the Arena.</p>
        </div>
        <div class="ranking-icon">♛</div>
    </section>

    <section class="ranking-content">
        <div class="table-heading">
            <div>
                <span>COMPETITIVE LADDER</span>
                <h2>ARENA RANKINGS</h2>
            </div>
            <div class="player-status">
                PLAYER: <strong><?php echo htmlspecialchars($gamerTag); ?></strong>
            </div>
        </div>

        <div class="ranking-table-container">
            <table class="ranking-table">
                <thead>
                    <tr>
                        <th>RANK</th>
                        <th>PLAYER</th>
                        <th>MATCHES</th>
                        <th>WINS</th>
                        <th>DRAWS</th>
                        <th>LOSSES</th>
                        <th>POINTS</th>
                        <th>GD</th>
                        <th>WIN RATE</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($ranking = mysqli_fetch_assoc($result)) {
                        $isCurrentPlayer = $ranking["Gamer_Tag"] == $gamerTag;
                        $rank = $ranking["Player_Rank"];
                ?>
                    <tr class="<?php
                        if ($rank == 1) echo 'first-place';
                        elseif ($rank == 2) echo 'second-place';
                        elseif ($rank == 3) echo 'third-place';
                        if ($isCurrentPlayer) echo ' current-player';
                    ?>">
                        <td>
                            <div class="rank-number">
                                <?php
                                if ($rank == 1) echo "🥇";
                                elseif ($rank == 2) echo "🥈";
                                elseif ($rank == 3) echo "🥉";
                                else echo $rank;
                                ?>
                            </div>
                        </td>
                        <td>
                            <div class="player-info">
                                <div class="player-avatar">
                                    <?php echo strtoupper(substr($ranking["Gamer_Tag"], 0, 1)); ?>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($ranking["Gamer_Tag"]); ?></strong>
                                    <small><?php echo htmlspecialchars($ranking["Real_Name"]); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo $ranking["Matches"]; ?></td>
                        <td class="wins"><?php echo $ranking["Wins"]; ?></td>
                        <td><?php echo $ranking["Draws"]; ?></td>
                        <td class="losses"><?php echo $ranking["Losses"]; ?></td>
                        <td><span class="points"><?php echo $ranking["Points"]; ?></span></td>
                        <td>
                            <?php
                            if ($ranking["Goal_Difference"] > 0) {
                                echo "+" . $ranking["Goal_Difference"];
                            } else {
                                echo $ranking["Goal_Difference"];
                            }
                            ?>
                        </td>
                        <td>
                            <div class="win-rate">
                                <div class="rate-bar">
                                    <span style="width: <?php echo $ranking["Win_Rate"]; ?>%"></span>
                                </div>
                                <small><?php echo $ranking["Win_Rate"]; ?>%</small>
                            </div>
                        </td>
                    </tr>
                <?php
                    }
                } else {
                ?>
                    <tr>
                        <td colspan="9" class="no-ranking">No rankings available.</td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="bottom-area">
            <a href="dashboard.php" class="back-button">← BACK TO DASHBOARD</a>
            <div class="ranking-note">🏆 Keep competing to climb the Arena rankings.</div>
        </div>
    </section>
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