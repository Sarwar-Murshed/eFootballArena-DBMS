<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["Admin_ID"])) {
    header("Location: login.php");
    exit();
}

$Admin_ID = $_SESSION["Admin_ID"];

$perm = mysqli_query($conn, "SELECT * FROM Admin_Permissions WHERE Admin_ID = $Admin_ID AND Table_Name = 'Players'");
if (mysqli_num_rows($perm) == 0) {
    die("You do not have permission to manage players.");
}
$permission = mysqli_fetch_assoc($perm);

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'ban' && $permission['Can_Update']) {
        mysqli_query($conn, "UPDATE Players SET Status = 'Banned' WHERE Player_ID = $id");
    }
    if ($_GET['action'] === 'unban' && $permission['Can_Update']) {
        mysqli_query($conn, "UPDATE Players SET Status = 'Active' WHERE Player_ID = $id");
    }
    header("Location: manage_players.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM Players ORDER BY Player_ID");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Players - Admin</title>
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
        <a href="manage_players.php" class="active">Manage Players</a>
        <a href="manage_tournaments.php">Manage Tournaments</a>
        <a href="manage_matches.php">Manage Matches</a>
        <a href="manage_auctions.php">Manage Auctions</a>
        <a href="logout.php">Logout</a>
    </aside>

    <main class="main-content">
        <div class="top-actions">
            <h1>Manage Players</h1>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Gamer Tag</th>
                        <th>Real Name</th>
                        <th>Country</th>
                        <th>Platform</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['Player_ID']; ?></td>
                        <td><?php echo htmlspecialchars($row['Gamer_Tag']); ?></td>
                        <td><?php echo htmlspecialchars($row['Real_Name']); ?></td>
                        <td><?php echo htmlspecialchars($row['Country'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['Platform'] ?? '-'); ?></td>
                        <td class="<?php echo ($row['Status'] ?? 'Active') === 'Banned' ? 'status-banned' : 'status-active'; ?>">
                            <?php echo $row['Status'] ?? 'Active'; ?>
                        </td>
                        <td>
                            <?php if ($permission['Can_Update']): ?>
                                <?php if (($row['Status'] ?? 'Active') === 'Banned'): ?>
                                    <a href="?action=unban&id=<?php echo $row['Player_ID']; ?>" class="btn btn-unban">Unban</a>
                                <?php else: ?>
                                    <a href="?action=ban&id=<?php echo $row['Player_ID']; ?>" class="btn btn-ban">Ban</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
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