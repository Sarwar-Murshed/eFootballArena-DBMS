<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["Admin_ID"])) {
    header("Location: login.php");
    exit();
}

$message = "";

// ========== DELETE ==========
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM Bids WHERE Auction_ID = $id");
    mysqli_query($conn, "DELETE FROM Auction_Giveups WHERE Auction_ID = $id");
    mysqli_query($conn, "DELETE FROM Auctions WHERE Auction_ID = $id");
    $message = "Auction deleted successfully.";
}

// ========== UPDATE AUCTION (with Auto Transfer) ==========
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_auction"])) {
    $auction_id  = (int)$_POST["auction_id"];
    $status      = mysqli_real_escape_string($conn, $_POST["status"]);
    $current_bid = (float)$_POST["current_bid"];
    $winner_id   = $_POST["winner_id"] !== "" ? (int)$_POST["winner_id"] : null;

    // Get old data
    $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM Auctions WHERE Auction_ID = $auction_id"));

    // Update auction
    if ($winner_id) {
        mysqli_query($conn, "UPDATE Auctions SET 
            Auction_Status = '$status',
            Current_Bid = $current_bid,
            Winner_Manager_ID = $winner_id
            WHERE Auction_ID = $auction_id");
    } else {
        mysqli_query($conn, "UPDATE Auctions SET 
            Auction_Status = '$status',
            Current_Bid = $current_bid,
            Winner_Manager_ID = NULL
            WHERE Auction_ID = $auction_id");
    }

    // ===== AUTOMATIC TRANSFER WHEN SET TO SOLD =====
    if ($status === "Sold" && $winner_id && $old["Auction_Status"] !== "Sold") {
        $player_id   = $old["Football_Player_ID"];
        $final_price = $current_bid;

        // 1. Deduct money
        mysqli_query($conn, "UPDATE Manager_Wallet SET Balance = Balance - $final_price WHERE Manager_ID = $winner_id");

        // 2. Give player to winner
        mysqli_query($conn, "INSERT IGNORE INTO Team_Players (Manager_ID, Football_Player_ID, Acquired_Date, Acquisition_Type) 
                             VALUES ($winner_id, $player_id, CURDATE(), 'Auction')");

        // 3. Get names
        $player_name = mysqli_fetch_assoc(mysqli_query($conn, "SELECT Player_Name FROM Football_Players WHERE Football_Player_ID = $player_id"))["Player_Name"];
        $winner_name = mysqli_fetch_assoc(mysqli_query($conn, "SELECT Gamer_Tag FROM Players WHERE Player_ID = $winner_id"))["Gamer_Tag"];

        // 4. Notify everyone
        $all_users = mysqli_query($conn, "SELECT Player_ID FROM Players");
        while ($u = mysqli_fetch_assoc($all_users)) {
            $title = "Auction Ended!";
            $msg = "$player_name has been won by $winner_name for " . number_format($final_price) . " coins.";
            mysqli_query($conn, "INSERT INTO Notifications (Player_ID, Title, Message, Type, Is_Read) 
                                 VALUES ({$u['Player_ID']}, '$title', '$msg', 'Market', 0)");
        }

        $message = "Auction ended! $player_name transferred to $winner_name. Money deducted.";
    } else {
        $message = "Auction updated successfully.";
    }
}

// ========== CREATE NEW AUCTION ==========
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_auction"])) {
    $player_id = (int)$_POST["football_player_id"];
    $starting_price = (float)$_POST["starting_price"];
    $auction_date = $_POST["auction_date"];
    $status = mysqli_real_escape_string($conn, $_POST["status"]);

    mysqli_query($conn, "INSERT INTO Auctions 
        (Football_Player_ID, Starting_Price, Current_Bid, Auction_Status, Auction_Date)
        VALUES ($player_id, $starting_price, $starting_price, '$status', '$auction_date')");
    
    $message = "New auction created successfully.";
}

// ========== GET DATA ==========
$auctions = mysqli_query($conn, "
    SELECT a.*, 
           fp.Player_Name, fp.Position, fp.Overall_Rating,
           p.Gamer_Tag AS Winner_Name
    FROM Auctions a
    LEFT JOIN Football_Players fp ON a.Football_Player_ID = fp.Football_Player_ID
    LEFT JOIN Players p ON a.Winner_Manager_ID = p.Player_ID
    ORDER BY a.Auction_ID DESC
");

$football_players = mysqli_query($conn, "SELECT Football_Player_ID, Player_Name, Position, Overall_Rating FROM Football_Players ORDER BY Overall_Rating DESC");
$managers = mysqli_query($conn, "SELECT Player_ID, Gamer_Tag FROM Players");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Auctions - Admin</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="manage_auctions.css">
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
        <a href="manage_tournaments.php">Manage Tournaments</a>
        <a href="manage_matches.php">Manage Matches</a>
        <a href="manage_auctions.php" class="active">Manage Auctions</a>
        <a href="logout.php">Logout</a>
    </aside>

    <main class="main-content">
        <h1>Manage Auctions</h1>
        <p class="subtitle">Create and control player auctions</p>

        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="form-box">
            <h2>+ Create New Auction</h2>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Football Player</label>
                        <select name="football_player_id" required>
                            <option value="">Select Player</option>
                            <?php while ($fp = mysqli_fetch_assoc($football_players)): ?>
                                <option value="<?php echo $fp['Football_Player_ID']; ?>">
                                    <?php echo htmlspecialchars($fp['Player_Name']); ?> 
                                    (<?php echo $fp['Position']; ?> - <?php echo $fp['Overall_Rating']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Starting Price</label>
                        <input type="number" name="starting_price" step="1000" placeholder="e.g. 300000" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Auction Date & Time</label>
                        <input type="datetime-local" name="auction_date" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" required>
                            <option value="Upcoming">Upcoming</option>
                            <option value="Live">Live</option>
                            <option value="Sold">Sold</option>
                            <option value="Unsold">Unsold</option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="add_auction" class="btn-primary">Create Auction</button>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Player</th>
                        <th>Position</th>
                        <th>Starting Price</th>
                        <th>Current Bid</th>
                        <th>Status</th>
                        <th>Winner</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($a = mysqli_fetch_assoc($auctions)): ?>
                    <tr>
                        <td><?php echo $a['Auction_ID']; ?></td>
                        <td><?php echo htmlspecialchars($a['Player_Name']); ?></td>
                        <td><?php echo $a['Position']; ?></td>
                        <td><?php echo number_format($a['Starting_Price']); ?></td>
                        <td>
                            <form method="POST" class="update-form">
                                <input type="hidden" name="auction_id" value="<?php echo $a['Auction_ID']; ?>">
                                <input type="number" name="current_bid" value="<?php echo $a['Current_Bid']; ?>" step="1000">
                                
                                <select name="status">
                                    <option value="Upcoming" <?php echo $a['Auction_Status']=='Upcoming'?'selected':''; ?>>Upcoming</option>
                                    <option value="Live" <?php echo $a['Auction_Status']=='Live'?'selected':''; ?>>Live</option>
                                    <option value="Sold" <?php echo $a['Auction_Status']=='Sold'?'selected':''; ?>>Sold</option>
                                    <option value="Unsold" <?php echo $a['Auction_Status']=='Unsold'?'selected':''; ?>>Unsold</option>
                                </select>

                                <select name="winner_id">
                                    <option value="">No Winner</option>
                                    <?php 
                                    mysqli_data_seek($managers, 0);
                                    while ($m = mysqli_fetch_assoc($managers)): ?>
                                        <option value="<?php echo $m['Player_ID']; ?>" 
                                            <?php echo $a['Winner_Manager_ID'] == $m['Player_ID'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($m['Gamer_Tag']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>

                                <button type="submit" name="update_auction" class="btn-sm">Update</button>
                            </form>
                        </td>
                        <td class="status-<?php echo strtolower($a['Auction_Status']); ?>">
                            <?php echo $a['Auction_Status']; ?>
                        </td>
                        <td><?php echo htmlspecialchars($a['Winner_Name'] ?? '-'); ?></td>
                        <td><?php echo $a['Auction_Date']; ?></td>
                        <td>
                            <a href="?delete=<?php echo $a['Auction_ID']; ?>" class="btn-delete" 
                               onclick="return confirm('Delete this auction?')">Delete</a>
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