<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["Player_ID"])) {
    header("Location: login.php");
    exit();
}

$managerID = $_SESSION["Player_ID"];
$gamerTag  = $_SESSION["Gamer_Tag"];
$message = "";

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

function getSellPrice($name, $type, $rating) {
    if ($type === "Bot") return 40000;
    if ($name === "Cristiano Ronaldo" || $name === "Lionel Messi") return 550000;
    if ($rating >= 9.5) return 480000;
    if ($rating >= 9.2) return 380000;
    if ($rating >= 9.0) return 300000;
    if ($rating >= 8.7) return 220000;
    if ($rating >= 8.5) return 150000;
    return 90000;
}

function getPlayerImage($name, $type, $player_images) {
    if ($type === "Bot") return "Players/bot.jpg";
    $file = $player_images[$name] ?? "";
    return $file ? "Players/" . $file : "Players/bot.jpg";
}

// Handle Sell
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["sell_player_id"])) {
    $playerId = (int)$_POST["sell_player_id"];
    $price = (float)$_POST["price"];

    $check = $conn->prepare("SELECT * FROM Team_Players WHERE Manager_ID = ? AND Football_Player_ID = ?");
    $check->bind_param("ii", $managerID, $playerId);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        $conn->query("DELETE FROM Team_Players WHERE Manager_ID = $managerID AND Football_Player_ID = $playerId");
        $conn->query("UPDATE Manager_Wallet SET Balance = Balance + $price WHERE Manager_ID = $managerID");
        $conn->query("INSERT INTO Player_Transfers (Football_Player_ID, From_Manager_ID, To_Manager_ID, Transfer_Type, Transfer_Fee, Transfer_Date) VALUES ($playerId, $managerID, NULL, 'Sell', $price, NOW())");
        $message = "Player sold successfully! +$price coins";
    } else {
        $message = "You do not own this player.";
    }
}

$wallet_sql = "SELECT Balance FROM Manager_Wallet WHERE Manager_ID = ?";
$stmt = $conn->prepare($wallet_sql);
$stmt->bind_param("i", $managerID);
$stmt->execute();
$wallet = $stmt->get_result()->fetch_assoc();
$balance = $wallet ? $wallet["Balance"] : 0;

$sql = "
    SELECT fp.*
    FROM Team_Players tp
    JOIN Football_Players fp ON tp.Football_Player_ID = fp.Football_Player_ID
    WHERE tp.Manager_ID = ?
    ORDER BY fp.Overall_Rating DESC
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
    <title>Sell Players - eFootball Arena</title>
    <link rel="stylesheet" href="markets.css">
    <style>
        .wallet-bar {
            text-align: center;
            margin-bottom: 25px;
            font-size: 18px;
        }
        .wallet-bar span {
            color: #b39aff;
            font-weight: bold;
            font-size: 22px;
        }
        .players-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }
        .player-card {
            background: rgba(12,15,25,0.94);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            overflow: hidden;
            text-align: center;
        }
        .player-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            object-position: top center;
        }
        .player-info {
            padding: 14px;
        }
        .player-info h3 {
            font-size: 15px;
            margin-bottom: 6px;
        }
        .price {
            color: #61e294;
            font-weight: bold;
            font-size: 16px;
            margin: 8px 0;
        }
        .sell-btn {
            background: #2a8f5a;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
        }
        .sell-btn:hover {
            background: #35a86c;
        }
        .message {
            text-align: center;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: rgba(97, 226, 148, 0.15);
            color: #61e294;
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
    <div class="logo">eFootball <span>ARENA</span></div>
    <nav class="navigation">
        <a href="dashboard.php">HOME</a>
        <a href="markets.php">MARKETS</a>
        <a href="buy_players.php">BUY</a>
        <a href="sell_players.php" class="active">SELL</a>
    </nav>
    <div class="profile"><?php echo htmlspecialchars($gamerTag); ?></div>
</header>

<main class="markets-page">
    <div class="page-header">
        <h1>SELL PLAYERS</h1>
        <p>Sell your players and earn coins</p>
    </div>

    <div class="wallet-bar">
        Your Wallet: <span><?php echo number_format($balance); ?></span> coins
    </div>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="players-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($p = $result->fetch_assoc()): 
                $price = getSellPrice($p["Player_Name"], $p["Player_Type"], $p["Overall_Rating"]);
                $img = getPlayerImage($p["Player_Name"], $p["Player_Type"], $player_images);
            ?>
                <div class="player-card">
                    <img src="<?php echo $img; ?>" alt="">
                    <div class="player-info">
                        <h3><?php echo htmlspecialchars($p["Player_Name"]); ?></h3>
                        <div><?php echo $p["Position"]; ?> • <?php echo $p["Overall_Rating"]; ?></div>
                        <div class="price">Sell for <?php echo number_format($price); ?></div>
                        <form method="POST">
                            <input type="hidden" name="sell_player_id" value="<?php echo $p["Football_Player_ID"]; ?>">
                            <input type="hidden" name="price" value="<?php echo $price; ?>">
                            <button type="submit" class="sell-btn">SELL NOW</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="grid-column:1/-1; text-align:center; color:#aaa;">You have no players to sell.</p>
        <?php endif; ?>
    </div>

    <div class="back-section">
        <a href="markets.php">← Back to Markets</a>
    </div>
</main>

<script src="music.js"></script>
</body>
</html>