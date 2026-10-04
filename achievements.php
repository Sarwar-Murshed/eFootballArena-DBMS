<?php
session_start();

if (!isset($_SESSION["Gamer_Tag"]) || !isset($_SESSION["Player_ID"])) {
    header("Location: login.php");
    exit();
}

require_once "db.php";

$playerID  = $_SESSION["Player_ID"];
$gamerTag  = $_SESSION["Gamer_Tag"];

// ===== Fetch Profile Picture =====
$profilePic = null;
$stmt = $conn->prepare("SELECT Profile_Picture FROM Players WHERE Player_ID = ?");
$stmt->bind_param("i", $playerID);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!empty($result["Profile_Picture"]) && file_exists($result["Profile_Picture"])) {
    $profilePic = $result["Profile_Picture"];
}

$allAchievements = [];
$sql = "SELECT * FROM Achievements ORDER BY Achievement_ID";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $allAchievements[$row["Achievement_ID"]] = $row;
}

$earned = [];
$sql = "SELECT Achievement_ID, Date_Earned 
        FROM Player_Achievements 
        WHERE Player_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $playerID);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $earned[$row["Achievement_ID"]] = $row["Date_Earned"];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achievements - eFootball Arena</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="achievements.css">
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
        <a href="transfers.php">TRANSFERS</a>
        <a href="auction.php">AUCTIONS</a>
        <a href="achievements.php" class="active">ACHIEVEMENTS</a>
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

<main class="achievements-page">

    <div class="page-header">
        <div>
            <span class="small-title">eFOOTBALL ARENA 2026</span>
            <h1>PLAYER <span>ACHIEVEMENTS</span></h1>
            <p>Track your milestones and unlock glory in the Arena.</p>
        </div>
        <div class="achievement-count">
            <?php echo count($earned); ?> / <?php echo count($allAchievements); ?> Unlocked
        </div>
    </div>

    <div class="achievements-grid">

        <?php foreach ($allAchievements as $id => $ach): ?>
            <?php
            $isUnlocked = isset($earned[$id]);
            $dateEarned = $isUnlocked ? date("d M Y", strtotime($earned[$id])) : null;
            ?>

            <div class="achievement-card <?php echo $isUnlocked ? 'unlocked' : 'locked'; ?>">

                <div class="ach-icon">
                    <?php if ($isUnlocked): ?>
                        🏅
                    <?php else: ?>
                        🔒
                    <?php endif; ?>
                </div>

                <div class="ach-content">
                    <h3><?php echo htmlspecialchars($ach["Achievement_Name"]); ?></h3>
                    <p><?php echo htmlspecialchars($ach["Description"]); ?></p>

                    <?php if ($isUnlocked): ?>
                        <div class="earned-date">
                            Unlocked on <?php echo $dateEarned; ?>
                        </div>
                    <?php else: ?>
                        <div class="locked-text">
                            Not yet unlocked
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        <?php endforeach; ?>

    </div>

    <div class="back-section">
        <a href="dashboard.php" class="back-button">← BACK TO DASHBOARD</a>
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