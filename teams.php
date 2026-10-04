<?php
session_start();
require_once "db.php";
require_once "functions.php";

if (!isset($_SESSION["Player_ID"])) {
    header("Location: login.php");
    exit();
}

$managerID = $_SESSION["Player_ID"];
$gamerTag  = $_SESSION["Gamer_Tag"];

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

$full_face_players = [
    "Erling Haaland", "Pedri", "Marquinhos", "Mike Maignan", "Robert Lewandowski",
    "Cristiano Ronaldo", "Thibaut Courtois", "Jude Bellingham", "Marcelo", "Vinicius Jr",
    "Sergio Ramos", "Luka Modric", "Neymar Jr", "Lautaro Martinez", "Martin Odegaard",
    "Antonio Rudiger", "Ousmane Dembele", "Theo Hernandez", "Karim Benzema", "Phil Foden",
    "Gavi", "Eder Militao", "Bruno Fernandes", "Raphael Varane", "Thomas Muller", "Gianluigi Donnarumma"
];

$sql = "
    SELECT fp.Football_Player_ID, fp.Player_Name, fp.Position, fp.Player_Type,
           fp.Overall_Rating, fp.Goals, fp.Assists, fp.Tackles, fp.Saves, fp.Matches_Played
    FROM Team_Players tp
    JOIN Football_Players fp ON tp.Football_Player_ID = fp.Football_Player_ID
    WHERE tp.Manager_ID = ?
    ORDER BY fp.Football_Player_ID
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $managerID);
$stmt->execute();
$result = $stmt->get_result();
$total_players = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Team - eFootballArena</title>
    <link rel="stylesheet" href="teams.css">
</head>
<body>
<audio id="backgroundMusic" loop>
    <source src="Audio/background.mp3" type="audio/mpeg">
</audio>
<audio id="clickSound">
    <source src="Audio/click.mp3" type="audio/mpeg">
</audio>

<header class="topbar">
    <div class="logo">eFootballArena</div>
    <nav class="navigation">
        <a href="dashboard.php">HOME</a>
        <a href="tournament.php">TOURNAMENTS</a>
        <a href="transfers.php">TRANSFERS</a>
        <a href="auction.php">AUCTIONS</a>
    </nav>
    <div class="profile"><?php echo htmlspecialchars($gamerTag); ?></div>
</header>

<main class="team-page">
    <div class="page-header">
        <h1>MY TEAM</h1>
        <p><?php echo htmlspecialchars($gamerTag); ?>'s Squad (<?php echo $total_players; ?> Players)</p>
        <a href="lineup.php" class="lineup-btn">LINEUPS →</a>
    </div>

    <div class="players-grid">
        <?php while ($player = $result->fetch_assoc()): ?>
            <?php
            $playerName = $player["Player_Name"];
            $imagePath = getPlayerImage($playerName, $player["Player_Type"], $player_images, $gamerTag);
            $imageClass = in_array($playerName, $full_face_players) ? "full-face" : "";
            ?>
            <div class="player-card">
                <div class="player-image">
                    <img class="<?php echo $imageClass; ?>" src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($playerName); ?>">
                </div>
                <div class="player-info">
                    <h2><?php echo htmlspecialchars($playerName); ?></h2>
                    <div class="player-details">
                        <span><?php echo htmlspecialchars($player["Position"]); ?></span>
                        <span><?php echo htmlspecialchars($player["Player_Type"]); ?></span>
                    </div>
                    <div class="rating"><?php echo htmlspecialchars($player["Overall_Rating"]); ?></div>
                    <div class="stats">
                        <div><strong><?php echo $player["Goals"]; ?></strong><small>Goals</small></div>
                        <div><strong><?php echo $player["Assists"]; ?></strong><small>Assists</small></div>
                        <div><strong><?php echo $player["Tackles"]; ?></strong><small>Tackles</small></div>
                        <div><strong><?php echo $player["Saves"]; ?></strong><small>Saves</small></div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="back-section">
        <a href="dashboard.php">← BACK TO DASHBOARD</a>
    </div>
</main>

<script src="music.js"></script>
<script src="teams.js"></script>
</body>
</html>