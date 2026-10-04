<?php
session_start();
require_once "db.php";
require_once "functions.php";

if (!isset($_SESSION["Player_ID"])) {
    header("Location: login.php");
    exit();
}

$managerID = $_SESSION["Player_ID"];
$gamerTag = $_SESSION["Gamer_Tag"];

$formation_sql = "
    SELECT Preferred_Formation
    FROM Players
    WHERE Player_ID = ?
";

$stmt = $conn->prepare($formation_sql);
$stmt->bind_param("i", $managerID);
$stmt->execute();

$formation_result = $stmt->get_result();
$player_data = $formation_result->fetch_assoc();
$current_formation = $player_data["Preferred_Formation"] ?? "4-3-3";

$sql = "
    SELECT
        fp.Football_Player_ID,
        fp.Player_Name,
        fp.Position,
        fp.Player_Type,
        fp.Overall_Rating
    FROM Team_Players tp
    JOIN Football_Players fp
        ON tp.Football_Player_ID = fp.Football_Player_ID
    WHERE tp.Manager_ID = ?
    ORDER BY fp.Overall_Rating DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $managerID);
$stmt->execute();
$result = $stmt->get_result();

$all_players = [];
while ($row = $result->fetch_assoc()) {
    $all_players[] = $row;
}

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

$coach_sql = "
    SELECT
        c.Coach_Name,
        c.Image
    FROM Manager_Coach mc
    JOIN Coaches c
        ON mc.Coach_ID = c.Coach_ID
    WHERE mc.Manager_ID = ?
    ORDER BY mc.Acquired_Date ASC, mc.Coach_ID ASC
    LIMIT 1
";

$stmt = $conn->prepare($coach_sql);
$stmt->bind_param("i", $managerID);
$stmt->execute();
$coach_data = $stmt->get_result()->fetch_assoc();

if ($coach_data) {
    $coach = [
        "name" => $coach_data["Coach_Name"],
        "img" => $coach_data["Image"]
    ];
} else {
    $coach = [
        "name" => "Alex",
        "img" => "Coach/Alex.jpg"
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lineups - eFootball Arena</title>
    <link rel="stylesheet" href="lineup.css">
</head>
<body>

<audio id="backgroundMusic" loop>
    <source src="Audio/background.mp3" type="audio/mpeg">
</audio>

<audio id="clickSound">
    <source src="Audio/click.mp3" type="audio/mpeg">
</audio>

<header class="topbar">
    <div class="logo">
        eFootball
        <span>ARENA</span>
    </div>

    <nav class="navigation">
        <a href="dashboard.php">HOME</a>
        <a href="tournament.php">TOURNAMENTS</a>
        <a href="teams.php">MY TEAM</a>
        <a href="lineup.php" class="active">LINEUPS</a>
        <a href="auction.php">AUCTIONS</a>
    </nav>

    <div class="profile">
        <?php echo htmlspecialchars($gamerTag); ?>
    </div>
</header>

<main class="lineup-page">

    <div class="page-header">
        <h1>LINEUP</h1>
        <p>Build your starting XI</p>
    </div>

    <div class="lineup-container">

        <div class="pitch-area">
            <div class="formation-selector">
                <label>Formation:</label>
                <select id="formationSelect">
                    <option value="4-3-3" <?php echo $current_formation == "4-3-3" ? "selected" : ""; ?>>4-3-3</option>
                    <option value="4-2-3-1" <?php echo $current_formation == "4-2-3-1" ? "selected" : ""; ?>>4-2-3-1</option>
                    <option value="4-4-2" <?php echo $current_formation == "4-4-2" ? "selected" : ""; ?>>4-4-2</option>
                    <option value="4-3-1-2" <?php echo $current_formation == "4-3-1-2" ? "selected" : ""; ?>>4-3-1-2</option>
                    <option value="5-3-2" <?php echo $current_formation == "5-3-2" ? "selected" : ""; ?>>5-3-2</option>
                </select>
            </div>

            <div class="pitch" id="pitch"></div>
        </div>

        <div class="bench-area">
            <div class="coach-card">
                <img src="<?php echo htmlspecialchars($coach["img"]); ?>" alt="Coach">
                <div class="coach-info">
                    <span class="coach-label">HEAD COACH</span>
                    <h3><?php echo htmlspecialchars($coach["name"]); ?></h3>
                </div>
            </div>

            <h3 class="available-title">AVAILABLE PLAYERS</h3>

            <div class="bench-list" id="benchList">
                <?php foreach ($all_players as $p): ?>
                    <div class="bench-player"
                         data-id="<?php echo $p["Football_Player_ID"]; ?>"
                         data-name="<?php echo htmlspecialchars($p["Player_Name"]); ?>"
                         data-position="<?php echo htmlspecialchars($p["Position"]); ?>"
                         data-rating="<?php echo $p["Overall_Rating"]; ?>"
                         data-img="<?php echo htmlspecialchars(getPlayerImage($p["Player_Name"], $p["Player_Type"], $player_images, $gamerTag)); ?>">

                        <img src="<?php echo htmlspecialchars(getPlayerImage($p["Player_Name"], $p["Player_Type"], $player_images, $gamerTag)); ?>" alt="">

                        <div class="bench-info">
                            <span class="bench-name"><?php echo htmlspecialchars($p["Player_Name"]); ?></span>
                            <span class="bench-meta">
                                <?php echo htmlspecialchars($p["Position"]); ?> • <?php echo $p["Overall_Rating"]; ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <div class="back-section">
        <a href="teams.php">← Back to My Team</a>
    </div>

</main>

<script>
const managerId = "<?php echo $managerID; ?>";
</script>

<script src="music.js"></script>
<script src="lineup.js"></script>

</body>
</html>