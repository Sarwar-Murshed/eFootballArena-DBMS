<?php
session_start();

if (!isset($_SESSION["Gamer_Tag"])) {
    header("Location: login.php");
    exit();
}

include("db.php");

$Gamer_Tag = $_SESSION["Gamer_Tag"];

$player_sql = "SELECT Player_ID, Real_Name FROM Players WHERE Gamer_Tag = ?";
$stmt = $conn->prepare($player_sql);
$stmt->bind_param("s", $Gamer_Tag);
$stmt->execute();
$player_result = $stmt->get_result();
$player = $player_result->fetch_assoc();

if (!$player) {
    header("Location: login.php");
    exit();
}

$Player_ID = $player["Player_ID"];
$Real_Name = $player["Real_Name"];

$player_images = [
    "Cristiano Ronaldo" => "Ronaldo.jpg",
    "Kylian Mbappe" => "Kylian Mbappe.jpg",
    "Kevin De Bruyne" => "Kevin De Bruyne.jpg",
    "Virgil van Dijk" => "Virgil van Dijk.jpg",
    "Thibaut Courtois" => "Thibaut Courtois.jpg",
    "khvicha kvaratskhelia" => "khvicha kvaratskhelia.jpg",
    "Jude Bellingham" => "Jude Bellingham.jpg",
    "Marcelo" => "Marcelo.jpg",
    "Alisson Becker" => "Alisson Becker.jpg",
    "Vinicius Jr" => "Vini Jr.jpg",
    "Rodri" => "Rodri.png",
    "Sergio Ramos" => "Sergio Ramos.jpg",
    "Harry Kane" => "Harry Kane.jpg",
    "Luka Modric" => "Luka Modric.jpg",
    "Achraf Hakimi" => "Achraf Hakimi.jpg",
    "Lionel Messi" => "Lionel Messi.jpg",
    "Erling Haaland" => "Erling Haland.jpg",
    "Pedri" => "Pedri.jpg",
    "William Saliba" => "William Saliba.jpg",
    "Ederson" => "Ederson.jpg",
    "Bukayo Saka" => "Bukayo Saka.jpg",
    "Bernardo Silva" => "Bernardo Silva.jpg",
    "Marquinhos" => "Marquinhos.jpg",
    "Mike Maignan" => "Mike Maigan.jpg",
    "Son Heung-min" => "Son.jpg",
    "Toni Kroos" => "Toni Kroos.jpg",
    "John Stones" => "John Stones.jpg",
    "Robert Lewandowski" => "Robert Lewandoski.jpg",
    "Declan Rice" => "Declan Rice.jpg",
    "Trent Alexander-Arnold" => "Trent Alexander Arnold.jpg",
    "Neymar Jr" => "Neymar jr.jpg",
    "Lautaro Martinez" => "Lautaro Martinez.jpg",
    "Martin Odegaard" => "Martin Odegaard.jpg",
    "Antonio Rudiger" => "Antonio Rudiger.jpg",
    "Emiliano Martinez" => "Emiliano Martinez.jpg",
    "Raphinha" => "Raphina.jpg",
    "Federico Valverde" => "Federico Valverde.jpg",
    "Ronald Araujo" => "Ronald Araujo.jpg",
    "Gianluigi Donnarumma" => "Gianluigi Donnarumma.jpg",
    "Ousmane Dembele" => "Dembele.jpg",
    "Frenkie de Jong" => "Frenkie de Jong.jpg",
    "Matthijs de Ligt" => "Matthijs de Ligt.jpg",
    "Victor Osimhen" => "Victor Osimhen.jpg",
    "Joshua Kimmich" => "Joshua Kimmich.jpg",
    "Theo Hernandez" => "Theo Hernandez.jpg",
    "Karim Benzema" => "Karim Benzema.jpg",
    "Phil Foden" => "Phil Foden.jpg",
    "Gavi" => "Gavi.jpg",
    "Eder Militao" => "Eder Militao.png",
    "Jan Oblak" => "Jan Oblak.png",
    "Marcus Rashford" => "Marcus Rashford.jpg",
    "Bruno Fernandes" => "Bruno Fernandes.jpg",
    "Raphael Varane" => "Raphael Varane.jpg",
    "Marc-Andre ter Stegen" => "Ter Stegen.jpg",
    "Riyad Mahrez" => "Riyad Mahrez.jpg",
    "Casemiro" => "Casemiro.jpg",
    "Kyle Walker" => "Kyle Walker.jpg",
    "Romelu Lukaku" => "Romelu Lukaku.jpg",
    "Thomas Muller" => "Thomas Muller.jpg",
    "Alphonso Davies" => "Alphonso Davies.jpg"
];

function getPlayerImage($name, $player_images) {
    $file = $player_images[$name] ?? "";
    return $file ? "Players/" . $file : "Players/bot.jpg";
}

$achievement_sql = "
    SELECT Achievements.Achievement_Name, Achievements.Description, Player_Achievements.Date_Earned
    FROM Player_Achievements
    JOIN Achievements ON Player_Achievements.Achievement_ID = Achievements.Achievement_ID
    WHERE Player_Achievements.Player_ID = ?
    ORDER BY Player_Achievements.Date_Earned DESC
";
$stmt = $conn->prepare($achievement_sql);
$stmt->bind_param("i", $Player_ID);
$stmt->execute();
$achievement_result = $stmt->get_result();

$analytics_sql = "SELECT * FROM Favorite_Club_Analytics WHERE Player_ID = ?";
$stmt = $conn->prepare($analytics_sql);
$stmt->bind_param("i", $Player_ID);
$stmt->execute();
$analytics_result = $stmt->get_result();

$recommendation_sql = "
    SELECT * FROM Recommendations
    WHERE Player_ID = ?
    ORDER BY Generated_Date DESC
";
$stmt = $conn->prepare($recommendation_sql);
$stmt->bind_param("i", $Player_ID);
$stmt->execute();
$recommendation_result = $stmt->get_result();

$h2h_sql = "
    SELECT Head_To_Head.*, P1.Gamer_Tag AS Player1_Name, P2.Gamer_Tag AS Player2_Name
    FROM Head_To_Head
    JOIN Players P1 ON Head_To_Head.Player1_ID = P1.Player_ID
    JOIN Players P2 ON Head_To_Head.Player2_ID = P2.Player_ID
    WHERE Head_To_Head.Player1_ID = ? OR Head_To_Head.Player2_ID = ?
";
$stmt = $conn->prepare($h2h_sql);
$stmt->bind_param("ii", $Player_ID, $Player_ID);
$stmt->execute();
$h2h_result = $stmt->get_result();

$transfer_sql = "
    SELECT Player_Transfers.*, Football_Players.Player_Name,
           P1.Gamer_Tag AS From_Manager, P2.Gamer_Tag AS To_Manager
    FROM Player_Transfers
    JOIN Football_Players ON Player_Transfers.Football_Player_ID = Football_Players.Football_Player_ID
    JOIN Players P1 ON Player_Transfers.From_Manager_ID = P1.Player_ID
    JOIN Players P2 ON Player_Transfers.To_Manager_ID = P2.Player_ID
    WHERE Player_Transfers.From_Manager_ID = ? OR Player_Transfers.To_Manager_ID = ?
    ORDER BY Player_Transfers.Transfer_Date DESC
";
$stmt = $conn->prepare($transfer_sql);
$stmt->bind_param("ii", $Player_ID, $Player_ID);
$stmt->execute();
$transfer_result = $stmt->get_result();

$award_sql = "
    SELECT Player_Awards.*, Football_Players.Player_Name
    FROM Player_Awards
    JOIN Football_Players ON Player_Awards.Football_Player_ID = Football_Players.Football_Player_ID
    JOIN Team_Players ON Player_Awards.Football_Player_ID = Team_Players.Football_Player_ID
    WHERE Team_Players.Manager_ID = ?
    ORDER BY Player_Awards.Award_Period DESC
";
$stmt = $conn->prepare($award_sql);
$stmt->bind_param("i", $Player_ID);
$stmt->execute();
$award_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Features - eFootballArena</title>
    <link rel="stylesheet" href="player_features.css">
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
        <a href="matches.php">MATCHES</a>
        <a href="teams.php">MY TEAM</a>
        <a href="lineup.php">LINEUPS</a>
        <a href="transfers.php">TRANSFERS</a>
        <a href="auction.php">AUCTIONS</a>
        <a href="player_features.php" class="active">FEATURES</a>
    </nav>
    <div class="profile"><?php echo htmlspecialchars($Gamer_Tag); ?></div>
</header>

<main class="features-page">

    <div class="page-header">
        <div class="header-icon">⚽</div>
        <h1>PLAYER FEATURES</h1>
        <p>Welcome, <strong><?php echo htmlspecialchars($Gamer_Tag); ?></strong></p>
    </div>

    <section class="features-grid">

        <!-- Achievements -->
        <div class="feature-card achievements-card">
            <div class="card-header">
                <div>
                    <span class="card-label">PLAYER RECORD</span>
                    <h2>🏆 Achievements</h2>
                </div>
                <div class="card-icon">🏆</div>
            </div>
            <div class="card-content">
                <?php if ($achievement_result->num_rows > 0): ?>
                    <?php while ($achievement = $achievement_result->fetch_assoc()): ?>
                        <div class="achievement-item">
                            <div class="achievement-icon">★</div>
                            <div class="achievement-info">
                                <h3><?php echo htmlspecialchars($achievement["Achievement_Name"]); ?></h3>
                                <p><?php echo htmlspecialchars($achievement["Description"]); ?></p>
                                <span>Earned: <?php echo htmlspecialchars($achievement["Date_Earned"]); ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-message">No achievements available.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Club Analytics -->
        <div class="feature-card analytics-card">
            <div class="card-header">
                <div>
                    <span class="card-label">PERFORMANCE</span>
                    <h2>📊 Club Analytics</h2>
                </div>
                <div class="card-icon">📊</div>
            </div>
            <div class="card-content">
                <?php if ($analytics_result->num_rows > 0): ?>
                    <?php while ($analytics = $analytics_result->fetch_assoc()): ?>
                        <div class="club-name">
                            <span>FAVORITE CLUB</span>
                            <strong><?php echo htmlspecialchars($analytics["Favorite_Club"]); ?></strong>
                        </div>
                        <div class="analytics-stats">
                            <div>
                                <strong><?php echo htmlspecialchars($analytics["Matches"]); ?></strong>
                                <span>Matches</span>
                            </div>
                            <div>
                                <strong><?php echo htmlspecialchars($analytics["Wins"]); ?></strong>
                                <span>Wins</span>
                            </div>
                            <div>
                                <strong><?php echo htmlspecialchars($analytics["Win_Rate"]); ?>%</strong>
                                <span>Win Rate</span>
                            </div>
                        </div>
                        <div class="best-club">
                            <span>BEST CLUB</span>
                            <strong><?php echo htmlspecialchars($analytics["Best_Club"]); ?></strong>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-message">No club analytics available.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recommendations -->
        <div class="feature-card recommendation-card">
            <div class="card-header">
                <div>
                    <span class="card-label">PLAYER DEVELOPMENT</span>
                    <h2>💡 Recommendations</h2>
                </div>
                <div class="card-icon">💡</div>
            </div>
            <div class="card-content">
                <?php if ($recommendation_result->num_rows > 0): ?>
                    <?php while ($recommendation = $recommendation_result->fetch_assoc()): ?>
                        <div class="recommendation-item">
                            <div class="recommendation-title">
                                <span>AREA OF IMPROVEMENT</span>
                                <strong><?php echo htmlspecialchars($recommendation["Area_of_Improvement"]); ?></strong>
                            </div>
                            <p><?php echo htmlspecialchars($recommendation["Recommendation"]); ?></p>
                            <small>Generated: <?php echo htmlspecialchars($recommendation["Generated_Date"]); ?></small>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-message">No recommendations available.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Head To Head -->
        <div class="feature-card h2h-card">
            <div class="card-header">
                <div>
                    <span class="card-label">COMPETITIVE RECORD</span>
                    <h2>⚔ Head To Head</h2>
                </div>
                <div class="card-icon">⚔</div>
            </div>
            <div class="card-content">
                <?php if ($h2h_result->num_rows > 0): ?>
                    <?php while ($h2h = $h2h_result->fetch_assoc()): ?>
                        <div class="h2h-players">
                            <div class="h2h-player">
                                <span>PLAYER 1</span>
                                <strong><?php echo htmlspecialchars($h2h["Player1_Name"]); ?></strong>
                            </div>
                            <div class="vs">VS</div>
                            <div class="h2h-player">
                                <span>PLAYER 2</span>
                                <strong><?php echo htmlspecialchars($h2h["Player2_Name"]); ?></strong>
                            </div>
                        </div>
                        <div class="h2h-stats">
                            <div>
                                <strong><?php echo htmlspecialchars($h2h["Total_Matches"]); ?></strong>
                                <span>Matches</span>
                            </div>
                            <div>
                                <strong><?php echo htmlspecialchars($h2h["Player1_Wins"]); ?></strong>
                                <span>P1 Wins</span>
                            </div>
                            <div>
                                <strong><?php echo htmlspecialchars($h2h["Player2_Wins"]); ?></strong>
                                <span>P2 Wins</span>
                            </div>
                            <div>
                                <strong><?php echo htmlspecialchars($h2h["Draws"]); ?></strong>
                                <span>Draws</span>
                            </div>
                        </div>
                        <div class="h2h-result">
                            <span>LAST RESULT</span>
                            <strong><?php echo htmlspecialchars($h2h["Last_Match_Result"]); ?></strong>
                            <small>Winning Percentage: <?php echo htmlspecialchars($h2h["Winning_Percentage"]); ?>%</small>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-message">No head-to-head records available.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Transfers -->
        <div class="feature-card transfer-card">
            <div class="card-header">
                <div>
                    <span class="card-label">MARKET ACTIVITY</span>
                    <h2>🔄 Player Transfers</h2>
                </div>
                <div class="card-icon">🔄</div>
            </div>
            <div class="card-content">
                <?php if ($transfer_result->num_rows > 0): ?>
                    <?php while ($transfer = $transfer_result->fetch_assoc()): ?>
                        <div class="transfer-item">
                            <div class="transfer-player">
                                <img src="<?php echo getPlayerImage($transfer["Player_Name"], $player_images); ?>" alt="">
                                <div>
                                    <span>PLAYER</span>
                                    <strong><?php echo htmlspecialchars($transfer["Player_Name"]); ?></strong>
                                </div>
                            </div>
                            <div class="transfer-route">
                                <div>
                                    <span>FROM</span>
                                    <strong><?php echo htmlspecialchars($transfer["From_Manager"]); ?></strong>
                                </div>
                                <div class="arrow">→</div>
                                <div>
                                    <span>TO</span>
                                    <strong><?php echo htmlspecialchars($transfer["To_Manager"]); ?></strong>
                                </div>
                            </div>
                            <div class="transfer-details">
                                <span><?php echo htmlspecialchars($transfer["Transfer_Type"]); ?></span>
                                <span>Fee: <?php echo number_format($transfer["Transfer_Fee"], 2); ?></span>
                                <span><?php echo htmlspecialchars($transfer["Transfer_Date"]); ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-message">No transfers available.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Awards -->
        <div class="feature-card awards-card">
            <div class="card-header">
                <div>
                    <span class="card-label">PLAYER PERFORMANCE</span>
                    <h2>🥇 Player Awards</h2>
                </div>
                <div class="card-icon">🥇</div>
            </div>
            <div class="card-content">
                <?php if ($award_result->num_rows > 0): ?>
                    <?php while ($award = $award_result->fetch_assoc()): ?>
                        <div class="award-item">
                            <div class="award-top">
                                <div class="award-player">
                                    <img src="<?php echo getPlayerImage($award["Player_Name"], $player_images); ?>" alt="">
                                    <div>
                                        <span class="award-type"><?php echo htmlspecialchars($award["Award_Type"]); ?></span>
                                        <h3><?php echo htmlspecialchars($award["Player_Name"]); ?></h3>
                                    </div>
                                </div>
                                <span class="award-date"><?php echo htmlspecialchars($award["Award_Period"]); ?></span>
                            </div>
                            <div class="award-stats">
                                <div>
                                    <strong><?php echo htmlspecialchars($award["Goals"]); ?></strong>
                                    <span>Goals</span>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($award["Assists"]); ?></strong>
                                    <span>Assists</span>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($award["Tackles"]); ?></strong>
                                    <span>Tackles</span>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($award["Saves"]); ?></strong>
                                    <span>Saves</span>
                                </div>
                            </div>
                            <p class="award-reason"><?php echo htmlspecialchars($award["Reason"]); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-message">No awards available.</div>
                <?php endif; ?>
            </div>
        </div>

    </section>

    <div class="back-section">
        <a href="dashboard.php">← BACK TO DASHBOARD</a>
    </div>

</main>

<script src="music.js"></script>
</body>
</html>
