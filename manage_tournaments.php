<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["Admin_ID"])) {
    header("Location: login.php");
    exit();
}

$Admin_ID = $_SESSION["Admin_ID"];

$perm = mysqli_query($conn, "SELECT * FROM Admin_Permissions WHERE Admin_ID = $Admin_ID AND Table_Name = 'Tournaments'");
if (mysqli_num_rows($perm) == 0) {
    die("You do not have permission to manage tournaments.");
}
$permission = mysqli_fetch_assoc($perm);

$result = mysqli_query($conn, "SELECT * FROM Tournaments ORDER BY Tournament_ID");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tournaments - Admin</title>
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
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<div class="admin-layout">
    <aside class="sidebar">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_players.php">Manage Players</a>
        <a href="manage_tournaments.php" class="active">Manage Tournaments</a>
        <a href="manage_matches.php">Manage Matches</a>
        <a href="manage_auctions.php">Manage Auctions</a>
        <a href="logout.php">Logout</a>
    </aside>

    <main class="main-content">
        <div class="top-actions">
            <h1>Manage Tournaments</h1>
            <?php if ($permission['Can_Insert']): ?>
                <a href="add_tournament.php" class="action-btn">+ Add Tournament</a>
            <?php endif; ?>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Prize Pool</th>
                        <th>Max Players</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['Tournament_ID']; ?></td>
                        <td><?php echo htmlspecialchars($row['Tournament_Name']); ?></td>
                        <td><?php echo $row['Tournament_Type']; ?></td>
                        <td>$<?php echo number_format($row['Prize_Pool'], 2); ?></td>
                        <td><?php echo $row['Maximum_Players']; ?></td>
                        <td><?php echo $row['Tournament_Status']; ?></td>
                        <td><?php echo $row['Start_Date']; ?></td>
                        <td><?php echo $row['End_Date']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script src="music.js"></script>
</body>
</html>