<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["Player_ID"])) {
    header("Location: login.php");
    exit();
}

$managerID = (int)$_SESSION["Player_ID"];
$gamerTag  = $_SESSION["Gamer_Tag"];
$message   = "";
date_default_timezone_set('Asia/Dhaka');

function endAuctionIfExpired($conn, $auction_id) {
    $stmt = $conn->prepare("SELECT * FROM Auctions WHERE Auction_ID = ? AND Auction_Status = 'Live'");
    $stmt->bind_param("i", $auction_id);
    $stmt->execute();
    $auction = $stmt->get_result()->fetch_assoc();
    if (!$auction) return false;
    if (empty($auction["Auction_Ends_At"])) return false;
    if (strtotime($auction["Auction_Ends_At"]) > time()) return false;

    $stmt = $conn->prepare("SELECT Manager_ID, Bid_Amount FROM Bids WHERE Auction_ID = ? ORDER BY Bid_Amount DESC, Bid_Time DESC LIMIT 1");
    $stmt->bind_param("i", $auction_id);
    $stmt->execute();
    $top = $stmt->get_result()->fetch_assoc();

    if (!$top) {
        $conn->query("UPDATE Auctions SET Auction_Status = 'Unsold' WHERE Auction_ID = $auction_id");
        return true;
    }

    $winner_id  = (int)$top["Manager_ID"];
    $bid_amount = (float)$top["Bid_Amount"];
    $player_id  = (int)$auction["Football_Player_ID"];

    $conn->query("UPDATE Auctions SET Auction_Status = 'Sold', Winner_Manager_ID = $winner_id, Current_Bid = $bid_amount WHERE Auction_ID = $auction_id");

    $conn->query("UPDATE Auction_Participants SET Participant_Status = 'Winner' WHERE Auction_ID = $auction_id AND Manager_ID = $winner_id");
    $conn->query("UPDATE Auction_Participants SET Participant_Status = 'Expired' WHERE Auction_ID = $auction_id AND Manager_ID != $winner_id AND Participant_Status = 'Active'");

    $conn->query("UPDATE Manager_Wallet SET Balance = Balance - $bid_amount WHERE Manager_ID = $winner_id");

    $exists = $conn->query("SELECT 1 FROM Team_Players WHERE Manager_ID = $winner_id AND Football_Player_ID = $player_id")->num_rows;
    if (!$exists) {
        $conn->query("INSERT INTO Team_Players (Manager_ID, Football_Player_ID, Acquired_Date, Acquisition_Type)
                      VALUES ($winner_id, $player_id, CURDATE(), 'Auction')");
    }

    $pname = $conn->query("SELECT Player_Name FROM Football_Players WHERE Football_Player_ID = $player_id")->fetch_assoc();
    $pname = $pname ? $pname["Player_Name"] : "Player";
    $wname = $conn->query("SELECT Gamer_Tag FROM Players WHERE Player_ID = $winner_id")->fetch_assoc();
    $wname = $wname ? $wname["Gamer_Tag"] : "Someone";

    $title = "Auction Ended";
    $msg   = "$wname won $pname for " . number_format($bid_amount) . " coins!";
    $conn->query("INSERT INTO Notifications (Player_ID, Title, Message, Type, Is_Read) VALUES (NULL, '$title', '$msg', 'Market', 0)");

    return true;
}

$live = $conn->query("SELECT Auction_ID FROM Auctions WHERE Auction_Status = 'Live'");
while ($row = $live->fetch_assoc()) {
    endAuctionIfExpired($conn, (int)$row["Auction_ID"]);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["place_bid"])) {
    $auction_id = (int)$_POST["auction_id"];
    $bid_amount = (float)$_POST["bid_amount"];

    endAuctionIfExpired($conn, $auction_id);

    $stmt = $conn->prepare("SELECT * FROM Auctions WHERE Auction_ID = ? AND Auction_Status = 'Live'");
    $stmt->bind_param("i", $auction_id);
    $stmt->execute();
    $auction = $stmt->get_result()->fetch_assoc();

    if ($auction) {
        $stmt = $conn->prepare("SELECT Manager_ID FROM Bids WHERE Auction_ID = ? ORDER BY Bid_Amount DESC, Bid_Time DESC LIMIT 1");
        $stmt->bind_param("i", $auction_id);
        $stmt->execute();
        $last = $stmt->get_result()->fetch_assoc();

        if ($last && (int)$last["Manager_ID"] === $managerID) {
            $message = "You are already the highest bidder. Wait for someone else to bid.";
        } else {
            $min_bid = (float)$auction["Current_Bid"] + 1000;

            if ($bid_amount >= $min_bid) {
                $stmt = $conn->prepare("SELECT Balance FROM Manager_Wallet WHERE Manager_ID = ?");
                $stmt->bind_param("i", $managerID);
                $stmt->execute();
                $wallet = $stmt->get_result()->fetch_assoc();

                if ($wallet && $wallet["Balance"] >= $bid_amount) {
                    $conn->query("UPDATE Auctions SET 
                        Current_Bid = $bid_amount,
                        Last_Bid_Time = NOW(),
                        Auction_Ends_At = DATE_ADD(NOW(), INTERVAL 3 MINUTE)
                        WHERE Auction_ID = $auction_id");

                    $conn->query("INSERT INTO Bids (Auction_ID, Manager_ID, Bid_Amount, Bid_Time)
                                  VALUES ($auction_id, $managerID, $bid_amount, NOW())");

                    $check = $conn->query("SELECT 1 FROM Auction_Participants WHERE Auction_ID = $auction_id AND Manager_ID = $managerID");
                    if ($check->num_rows == 0) {
                        $conn->query("INSERT INTO Auction_Participants (Auction_ID, Manager_ID, Joined_At, Turn_Ends_At, Participant_Status)
                                      VALUES ($auction_id, $managerID, NOW(), DATE_ADD(NOW(), INTERVAL 3 MINUTE), 'Active')");
                    } else {
                        $conn->query("UPDATE Auction_Participants SET 
                            Turn_Ends_At = DATE_ADD(NOW(), INTERVAL 3 MINUTE),
                            Participant_Status = 'Active'
                            WHERE Auction_ID = $auction_id AND Manager_ID = $managerID");
                    }

                    $pname = $conn->query("SELECT Player_Name FROM Football_Players WHERE Football_Player_ID = " . (int)$auction["Football_Player_ID"])->fetch_assoc();
                    $pname = $pname ? $pname["Player_Name"] : "a player";

                    $others = $conn->query("SELECT Manager_ID FROM Auction_Participants 
                                            WHERE Auction_ID = $auction_id AND Manager_ID != $managerID AND Participant_Status = 'Active'");
                    while ($o = $others->fetch_assoc()) {
                        $oid = (int)$o["Manager_ID"];
                        $title = "New Bid on $pname!";
                        $msg = "$gamerTag bid " . number_format($bid_amount) . " coins. You have 3 minutes!";
                        $conn->query("INSERT INTO Notifications (Player_ID, Title, Message, Type, Is_Read)
                                      VALUES ($oid, '$title', '$msg', 'Market', 0)");
                    }

                    $message = "Bid placed! Timer reset to 3:00";
                } else {
                    $message = "Not enough coins in your wallet!";
                }
            } else {
                $message = "Your bid must be at least " . number_format($min_bid) . " coins!";
            }
        }
    } else {
        $message = "This auction is not live.";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["give_up"])) {
    $auction_id = (int)$_POST["auction_id"];
    $part = $conn->query("SELECT * FROM Auction_Participants WHERE Auction_ID = $auction_id AND Manager_ID = $managerID AND Participant_Status = 'Active'");
    if ($part && $part->num_rows > 0) {
        $conn->query("UPDATE Auction_Participants SET Participant_Status = 'Given Up' WHERE Auction_ID = $auction_id AND Manager_ID = $managerID");
        $message = "You have given up on this auction.";
    } else {
        $message = "Only participants can give up.";
    }
}

$wallet_sql = "SELECT Balance FROM Manager_Wallet WHERE Manager_ID = ?";
$stmt = $conn->prepare($wallet_sql);
$stmt->bind_param("i", $managerID);
$stmt->execute();
$wallet = $stmt->get_result()->fetch_assoc();
$balance = $wallet ? $wallet["Balance"] : 0;

$sql = "
    SELECT a.*,
           fp.Player_Name, fp.Position, fp.Overall_Rating, fp.Player_Type,
           p.Gamer_Tag AS Winner_Name
    FROM Auctions a
    JOIN Football_Players fp ON a.Football_Player_ID = fp.Football_Player_ID
    LEFT JOIN Players p ON a.Winner_Manager_ID = p.Player_ID
    ORDER BY FIELD(a.Auction_Status, 'Live', 'Upcoming', 'Sold', 'Unsold'), a.Auction_Date DESC
";
$auctions = mysqli_query($conn, $sql);

$player_images = [
    "Cristiano Ronaldo" => "Ronaldo.jpg",
    "Kylian Mbappe" => "Kylian Mbappe.jpg",
    "Sergio Ramos" => "Sergio Ramos.jpg",
    "Karim Benzema" => "Karim Benzema.jpg",
    "Neymar Jr" => "Neymar jr.jpg",
    "Rodri" => "Rodri.png",
    "Lionel Messi" => "Lionel Messi.jpg",
    "Erling Haaland" => "Erling Haland.jpg",
    "Vinicius Jr" => "Vini Jr.jpg",
    "Jude Bellingham" => "Jude Bellingham.jpg",
    "Luka Modric" => "Luka Modric.jpg",
    "Toni Kroos" => "Toni Kroos.jpg",
    "Robert Lewandowski" => "Robert Lewandoski.jpg",
    "Harry Kane" => "Harry Kane.jpg"
];

function getPlayerImage($name, $type, $player_images) {
    if ($type === "Bot") return "Players/bot.jpg";
    $file = $player_images[$name] ?? "";
    return $file ? "Players/" . $file : "Players/bot.jpg";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auctions - eFootball Arena</title>
    <link rel="stylesheet" href="auction.css">
    <style>
        .timer-box {
            text-align: center;
            padding: 8px 14px 4px;
            font-size: 22px;
            font-weight: bold;
            color: #c9a227;
        }
        .timer-box.ended { color: #ff6b6b; }
        .highest-msg {
            text-align: center;
            padding: 10px 14px 14px;
            color: #61e294;
            font-size: 13px;
            font-weight: bold;
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
        <a href="tournament.php">TOURNAMENTS</a>
        <a href="transfers.php">TRANSFERS</a>
        <a href="auction.php" class="active">AUCTIONS</a>
    </nav>
    <div class="top-right">
        <div class="wallet-box">Wallet: <span><?php echo number_format($balance); ?></span></div>
        <div class="profile"><?php echo htmlspecialchars($gamerTag); ?></div>
        <a href="logout.php" class="logout-btn">LOGOUT</a>
    </div>
</header>

<main class="auction-page">
    <div class="page-header">
        <h1>PLAYER AUCTIONS</h1>
        <p style="color:#aaa;font-size:13px;margin-top:6px;">3-minute timer · resets on every bid · last bidder wins automatically</p>
    </div>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="auctions-grid">
        <?php while ($a = mysqli_fetch_assoc($auctions)):
            $img = getPlayerImage($a["Player_Name"], $a["Player_Type"], $player_images);
            $statusClass = strtolower($a["Auction_Status"]);
            $minNextBid = (float)$a["Current_Bid"] + 1000;
            $aid = (int)$a["Auction_ID"];

            $is_participant = false;
            $part = $conn->query("SELECT Participant_Status FROM Auction_Participants WHERE Auction_ID = $aid AND Manager_ID = $managerID");
            if ($part && $part->num_rows > 0) {
                $ps = $part->fetch_assoc();
                $is_participant = ($ps["Participant_Status"] === "Active");
            }

            $is_highest = false;
            $lb = $conn->query("SELECT Manager_ID FROM Bids WHERE Auction_ID = $aid ORDER BY Bid_Amount DESC, Bid_Time DESC LIMIT 1");
            if ($lb && $lb->num_rows > 0) {
                $top = $lb->fetch_assoc();
                if ((int)$top["Manager_ID"] === $managerID) $is_highest = true;
            }

            $ends_at = !empty($a["Auction_Ends_At"]) ? strtotime($a["Auction_Ends_At"]) : 0;
            $remaining = max(0, $ends_at - time());
        ?>
            <div class="auction-card status-<?php echo $statusClass; ?>" data-ends-at="<?php echo $ends_at; ?>" data-id="<?php echo $aid; ?>">
                <div class="status-badge <?php echo $statusClass; ?>">
                    <?php echo strtoupper($a["Auction_Status"]); ?>
                </div>

                <div class="player-img">
                    <img src="<?php echo $img; ?>" alt="">
                </div>

                <div class="player-info">
                    <h3><?php echo htmlspecialchars($a["Player_Name"]); ?></h3>
                    <p><?php echo $a["Position"]; ?> • Rating: <?php echo $a["Overall_Rating"]; ?></p>
                </div>

                <div class="bid-info">
                    <div><span>Starting Price</span><strong><?php echo number_format($a["Starting_Price"]); ?></strong></div>
                    <div><span>Current Bid</span><strong class="current"><?php echo number_format($a["Current_Bid"]); ?></strong></div>
                    <div><span>Auction Date</span><strong><?php echo date("d M Y H:i", strtotime($a["Auction_Date"])); ?></strong></div>
                </div>

                <?php if ($a["Auction_Status"] === "Live"): ?>
                    <div class="timer-box" id="timer-<?php echo $aid; ?>">
                        <?php
                        if ($ends_at > 0 && $remaining > 0) {
                            echo sprintf("%02d:%02d", floor($remaining / 60), $remaining % 60);
                        } elseif ($ends_at > 0) {
                            echo "00:00";
                        } else {
                            echo "Waiting for first bid";
                        }
                        ?>
                    </div>

                    <?php if ($is_highest): ?>
                        <div class="highest-msg">You are the highest bidder. Waiting for opponent...</div>
                        <?php if ($is_participant): ?>
                            <form method="POST" style="padding: 0 14px 14px;">
                                <input type="hidden" name="auction_id" value="<?php echo $aid; ?>">
                                <button type="submit" name="give_up" class="giveup-btn">GIVE UP</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <form method="POST" class="bid-form">
                            <input type="hidden" name="auction_id" value="<?php echo $aid; ?>">
                            <input type="number" name="bid_amount" min="<?php echo $minNextBid; ?>" step="1000" value="<?php echo $minNextBid; ?>" required>
                            <button type="submit" name="place_bid">PLACE BID</button>
                        </form>
                        <small class="min-bid">Minimum bid: <?php echo number_format($minNextBid); ?></small>

                        <?php if ($is_participant): ?>
                            <form method="POST" style="padding: 0 14px 14px;">
                                <input type="hidden" name="auction_id" value="<?php echo $aid; ?>">
                                <button type="submit" name="give_up" class="giveup-btn">GIVE UP</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>

                <?php elseif ($a["Auction_Status"] === "Sold"): ?>
                    <div class="winner">
                        Winner: <strong><?php echo htmlspecialchars($a["Winner_Name"] ?? "Unknown"); ?></strong>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="back-section">
        <a href="dashboard.php">← BACK TO DASHBOARD</a>
    </div>
</main>

<script src="music.js"></script>
<script>
document.querySelectorAll(".auction-card[data-ends-at]").forEach(function (card) {
    const endsAt = parseInt(card.getAttribute("data-ends-at"), 10);
    const id = card.getAttribute("data-id");
    const el = document.getElementById("timer-" + id);
    if (!el || !endsAt) return;

    function tick() {
        const left = endsAt - Math.floor(Date.now() / 1000);
        if (left <= 0) {
            el.textContent = "00:00 — Ending...";
            el.classList.add("ended");
            setTimeout(function () { location.reload(); }, 1200);
            return;
        }
        const m = Math.floor(left / 60);
        const s = left % 60;
        el.textContent = String(m).padStart(2, "0") + ":" + String(s).padStart(2, "0");
    }
    tick();
    setInterval(tick, 1000);
});
</script>
</body>
</html>