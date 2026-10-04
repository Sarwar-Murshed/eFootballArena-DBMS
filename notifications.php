<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["Player_ID"])) {
    header("Location: login.php");
    exit();
}

$managerID = $_SESSION["Player_ID"];
$gamerTag  = $_SESSION["Gamer_Tag"];

// Mark as read
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $id = (int)$_GET['read'];
    $conn->query("UPDATE Notifications SET Is_Read = 1 WHERE Notification_ID = $id AND (Player_ID = $managerID OR Player_ID IS NULL)");
    header("Location: notifications.php");
    exit();
}

// Mark all as read
if (isset($_GET['read_all'])) {
    $conn->query("UPDATE Notifications SET Is_Read = 1 WHERE Player_ID = $managerID OR Player_ID IS NULL");
    header("Location: notifications.php");
    exit();
}

$sql = "
    SELECT * FROM Notifications 
    WHERE Player_ID = ? OR Player_ID IS NULL 
    ORDER BY Created_At DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $managerID);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - eFootball Arena</title>
    <link rel="stylesheet" href="notifications.css">
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
        <a href="markets.php">MARKETS</a>
        <a href="notifications.php" class="active">NOTIFICATIONS</a>
    </nav>
    <div class="profile"><?php echo htmlspecialchars($gamerTag); ?></div>
</header>

<main class="notifications-page">

    <div class="page-header">
        <h1>NOTIFICATIONS</h1>
        <a href="?read_all=1" class="mark-all">Mark all as read</a>
    </div>

    <div class="notifications-list">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($n = $result->fetch_assoc()): ?>
                <div class="notification-item <?php echo $n['Is_Read'] ? 'read' : 'unread'; ?>">
                    <div class="notif-left">
                        <div class="notif-type"><?php echo $n['Type']; ?></div>
                        <h3><?php echo htmlspecialchars($n['Title']); ?></h3>
                        <p><?php echo htmlspecialchars($n['Message']); ?></p>
                        <span class="notif-time"><?php echo date("d M Y, h:i A", strtotime($n['Created_At'])); ?></span>
                    </div>
                    <?php if (!$n['Is_Read']): ?>
                        <a href="?read=<?php echo $n['Notification_ID']; ?>" class="mark-read">Mark as read</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty">No notifications yet.</div>
        <?php endif; ?>
    </div>

    <div class="back-section">
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>

</main>

<script src="music.js"></script>
</body>
</html>