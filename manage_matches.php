<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["Admin_ID"])) {
    header("Location: login.php");
    exit();
}

$Admin_ID = $_SESSION["Admin_ID"];
$message = "";

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM Matches WHERE Match_ID = $id");
    $message = "Match deleted successfully.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_score"])) {
    $match_id = (int)$_POST["match_id"];
    $score = mysqli_real_escape_string($conn, $_POST["final_score"]);
    $winner_id = $_POST["winner_id"] !== "" ? (int)$_POST["winner_id"] : "NULL";

    mysqli_query($conn, "UPDATE Matches SET Final_Score = '$score', Winner_ID = $winner_id WHERE Match_ID = $match_id");
    $message = "Match score updated successfully.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_match"])) {
    $tournament_id = (int)$_POST["tournament_id"];
    $player1_id = (int)$_POST["player1_id"];
    $player2_id = (int)$_POST["player2_id"];
    $club1 = mysqli_real_escape_string($conn, $_POST["club1"]);
    $club2 = mysqli_real_escape_string($conn, $_POST["club2"]);
    $match_date = $_POST["match_date"];
    $match_time = $_POST["match_time"];
    $match_round = mysqli_real_escape_string($conn, $_POST["match_round"]);

    mysqli_query($conn, "INSERT INTO Matches 
        (Tournament_ID, Player1_ID, Player2_ID, Club_Player1, Club_Player2, Match_Date, Match_Time, Match_Round)
        VALUES ($tournament_id, $player1_id, $player2_id, '$club1', '$club2', '$match_date', '$match_time', '$match_round')");
    
    $message = "New match created successfully.";
}

$matches = mysqli_query($conn, "
    SELECT m.*, 
           t.Tournament_Name,
           p1.Gamer_Tag AS Player1_Name,
           p2.Gamer_Tag AS Player2_Name,
           w.Gamer_Tag AS Winner_Name
    FROM Matches m
    LEFT JOIN Tournaments t ON m.Tournament_ID = t.Tournament_ID
    LEFT JOIN Players p1 ON m.Player1_ID = p1.Player_ID
    LEFT JOIN Players p2 ON m.Player2_ID = p2.Player_ID
    LEFT JOIN Players w ON m.Winner_ID = w.Player_ID
    ORDER BY m.Match_ID DESC
");

$tournaments = mysqli_query($conn, "SELECT Tournament_ID, Tournament_Name FROM Tournaments");
$players = mysqli_query($conn, "SELECT Player_ID, Gamer_Tag FROM Players WHERE Status = 'Active' OR Status IS NULL");
$players2 = mysqli_query($conn, "SELECT Player_ID, Gamer_Tag FROM Players WHERE Status = 'Active' OR Status IS NULL");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Matches - Admin</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .form-box {
            background: rgba(12,15,25,0.95);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .form-box h2 {
            margin-bottom: 18px;
            font-size: 18px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group label {
            font-size: 13px;
            color: #aaa;
        }
        .form-group input,
        .form-group select {
            background: #10101c;
            border: 1px solid #292943;
            color: white;
            padding: 10px 12px;
            border-radius: 6px;
            font-size: 14px;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #6c3cff;
        }
        .btn-primary {
            background: #6c3cff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-primary:hover {
            background: #844fff;
        }
        .message {
            background: rgba(97,226,148,0.15);
            color: #61e294;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .score-form {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .score-form input {
            width: 70px;
            background: #10101c;
            border: 1px solid #292943;
            color: white;
            padding: 6px;
            border-radius: 4px;
            text-align: center;
        }
        .score-form select {
            background: #10101c;
            border: 1px solid #292943;
            color: white;
            padding: 6px;
            border-radius: 4px;
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-update {
            background: #3a3a5c;
            color: white;
        }
        .btn-delete {
            background: #5c2a2a;
            color: #ff6b6b;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
        }
    </style>
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
        <a href="manage_matches.php" class="active">Manage Matches</a>
        <a href="manage_auctions.php">Manage Auctions</a>
        <a href="logout.php">Logout</a>
    </aside>

    <main class="main-content">
        <h1>Manage Matches</h1>
        <p class="subtitle">Create, update scores and manage all matches</p>

        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Add New Match Form -->
        <div class="form-box">
            <h2>+ Create New Match</h2>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Tournament</label>
                        <select name="tournament_id" required>
                            <option value="">Select Tournament</option>
                            <?php while ($t = mysqli_fetch_assoc($tournaments)): ?>
                                <option value="<?php echo $t['Tournament_ID']; ?>">
                                    <?php echo htmlspecialchars($t['Tournament_Name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Round</label>
                        <input type="text" name="match_round" placeholder="e.g. Quarter Final" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Player 1</label>
                        <select name="player1_id" required>
                            <option value="">Select Player</option>
                            <?php 
                            mysqli_data_seek($players, 0);
                            while ($p = mysqli_fetch_assoc($players)): ?>
                                <option value="<?php echo $p['Player_ID']; ?>">
                                    <?php echo htmlspecialchars($p['Gamer_Tag']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Player 2</label>
                        <select name="player2_id" required>
                            <option value="">Select Player</option>
                            <?php while ($p = mysqli_fetch_assoc($players2)): ?>
                                <option value="<?php echo $p['Player_ID']; ?>">
                                    <?php echo htmlspecialchars($p['Gamer_Tag']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Club Player 1</label>
                        <input type="text" name="club1" placeholder="e.g. Real Madrid" required>
                    </div>
                    <div class="form-group">
                        <label>Club Player 2</label>
                        <input type="text" name="club2" placeholder="e.g. Barcelona" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Match Date</label>
                        <input type="date" name="match_date" required>
                    </div>
                    <div class="form-group">
                        <label>Match Time</label>
                        <input type="time" name="match_time" required>
                    </div>
                </div>

                <button type="submit" name="add_match" class="btn-primary">Create Match</button>
            </form>
        </div>

        <!-- Matches Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tournament</th>
                        <th>Player 1</th>
                        <th>Player 2</th>
                        <th>Round</th>
                        <th>Date</th>
                        <th>Score</th>
                        <th>Winner</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($m = mysqli_fetch_assoc($matches)): ?>
                    <tr>
                        <td><?php echo $m['Match_ID']; ?></td>
                        <td><?php echo htmlspecialchars($m['Tournament_Name'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($m['Player1_Name']); ?></td>
                        <td><?php echo htmlspecialchars($m['Player2_Name']); ?></td>
                        <td><?php echo htmlspecialchars($m['Match_Round']); ?></td>
                        <td><?php echo $m['Match_Date']; ?></td>
                        <td>
                            <form method="POST" class="score-form">
                                <input type="hidden" name="match_id" value="<?php echo $m['Match_ID']; ?>">
                                <input type="text" name="final_score" value="<?php echo htmlspecialchars($m['Final_Score'] ?? ''); ?>" placeholder="0-0">
                                <select name="winner_id">
                                    <option value="">Draw</option>
                                    <option value="<?php echo $m['Player1_ID']; ?>" <?php echo $m['Winner_ID'] == $m['Player1_ID'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($m['Player1_Name']); ?>
                                    </option>
                                    <option value="<?php echo $m['Player2_ID']; ?>" <?php echo $m['Winner_ID'] == $m['Player2_ID'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($m['Player2_Name']); ?>
                                    </option>
                                </select>
                                <button type="submit" name="update_score" class="btn-sm btn-update">Update</button>
                            </form>
                        </td>
                        <td><?php echo htmlspecialchars($m['Winner_Name'] ?? 'Draw / Pending'); ?></td>
                        <td>
                            <a href="?delete=<?php echo $m['Match_ID']; ?>" class="btn-delete" onclick="return confirm('Delete this match?')">Delete</a>
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