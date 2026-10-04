<?php

session_start();
require_once "db.php";

if (!isset($_SESSION["Player_ID"])) {
    header("Location: login.php");
    exit();
}

$managerID = $_SESSION["Player_ID"];
$gamerTag = $_SESSION["Gamer_Tag"];

$message = "";

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["sell_coach_id"])
) {

    $coachId = (int)$_POST["sell_coach_id"];

    $checkSQL = "
        SELECT
            c.Coach_ID,
            c.Coach_Name,
            c.Price
        FROM Manager_Coach mc
        JOIN Coaches c
            ON mc.Coach_ID = c.Coach_ID
        WHERE mc.Manager_ID = ?
        AND mc.Coach_ID = ?
    ";

    $stmt = $conn->prepare($checkSQL);
    $stmt->bind_param("ii", $managerID, $coachId);
    $stmt->execute();

    $coach = $stmt->get_result()->fetch_assoc();


    if (!$coach) {

        $message = "You do not own this coach.";

    } else {

        $sellPrice = $coach["Price"] * 0.70;


        $conn->begin_transaction();

        try {

            $deleteSQL = "
                DELETE FROM Manager_Coach
                WHERE Manager_ID = ?
                AND Coach_ID = ?
            ";

            $stmt = $conn->prepare($deleteSQL);
            $stmt->bind_param(
                "ii",
                $managerID,
                $coachId
            );

            $stmt->execute();


            $walletSQL = "
                UPDATE Manager_Wallet
                SET Balance = Balance + ?
                WHERE Manager_ID = ?
            ";

            $stmt = $conn->prepare($walletSQL);
            $stmt->bind_param(
                "di",
                $sellPrice,
                $managerID
            );

            $stmt->execute();


            $conn->commit();


            $message =
                $coach["Coach_Name"] .
                " sold successfully! +" .
                number_format($sellPrice) .
                " coins";


        } catch (Exception $e) {

            $conn->rollback();

            $message =
                "Unable to sell coach.";
        }
    }
}

$walletSQL = "
    SELECT Balance
    FROM Manager_Wallet
    WHERE Manager_ID = ?
";

$stmt = $conn->prepare($walletSQL);
$stmt->bind_param("i", $managerID);
$stmt->execute();

$wallet = $stmt->get_result()->fetch_assoc();

$balance = $wallet
    ? $wallet["Balance"]
    : 0;


$coachesSQL = "
    SELECT
        c.Coach_ID,
        c.Coach_Name,
        c.Image,
        c.Price,
        c.Description,
        mc.Acquired_Date
    FROM Manager_Coach mc
    JOIN Coaches c
        ON mc.Coach_ID = c.Coach_ID
    WHERE mc.Manager_ID = ?
    ORDER BY mc.Acquired_Date ASC, mc.Coach_ID ASC
";

$stmt = $conn->prepare($coachesSQL);
$stmt->bind_param("i", $managerID);
$stmt->execute();

$ownedCoaches = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Sell Coaches - eFootball Arena</title>

<link
    rel="stylesheet"
    href="markets.css"
>

<style>

.wallet-bar {
    text-align: center;
    margin-bottom: 25px;
    font-size: 17px;
}

.wallet-bar span {
    color: #b39aff;
    font-weight: bold;
    font-size: 20px;
}

.message {
    text-align: center;
    padding: 12px;
    margin-bottom: 25px;
    border-radius: 8px;
    background: rgba(97, 226, 148, 0.15);
    color: #61e294;
}

.coaches-grid {
    display: grid;
    grid-template-columns:
        repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
}

.coach-card-sell {
    background: rgba(12,15,25,0.95);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 14px;
    overflow: hidden;
    text-align: center;
    transition: 0.25s;
}

.coach-card-sell:hover {
    border-color: #9d7cff;
    transform: translateY(-4px);
}

.coach-card-sell img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    object-position: top center;
}

.coach-info-sell {
    padding: 18px;
}

.coach-info-sell h2 {
    margin-bottom: 8px;
}

.original-price {
    color: #aaa;
    font-size: 14px;
    margin-bottom: 5px;
}

.sell-price {
    color: #61e294;
    font-size: 18px;
    font-weight: bold;
    margin: 12px 0;
}

.sell-btn {
    background: #2a8f5a;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    width: 100%;
}

.sell-btn:hover {
    background: #35a86c;
}

.no-coaches {
    text-align: center;
    color: #777;
    padding: 60px 0;
    font-size: 17px;
}

</style>

</head>


<body>


<audio id="backgroundMusic" loop>
    <source
        src="Audio/background.mp3"
        type="audio/mpeg"
    >
</audio>

<audio id="clickSound">
    <source
        src="Audio/click.mp3"
        type="audio/mpeg"
    >
</audio>


<header class="topbar">

    <div class="logo">
        eFootball <span>ARENA</span>
    </div>


    <nav class="navigation">

        <a href="dashboard.php">
            HOME
        </a>

        <a href="markets.php">
            MARKETS
        </a>

        <a href="buy_coaches.php">
            BUY COACHES
        </a>

        <a
            href="sell_coaches.php"
            class="active"
        >
            SELL COACHES
        </a>

    </nav>


    <div class="profile">
        <?php echo htmlspecialchars($gamerTag); ?>
    </div>

</header>


<main class="markets-page">


    <div class="page-header">

        <h1>SELL COACHES</h1>

        <p>
            Sell your owned coaches and get coins back
        </p>

    </div>


    <div class="wallet-bar">

        Your Wallet:

        <span>
            <?php echo number_format($balance); ?>
        </span>

        coins

    </div>


    <?php if ($message): ?>

        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <?php if ($ownedCoaches->num_rows > 0): ?>

        <div class="coaches-grid">

            <?php while ($coach = $ownedCoaches->fetch_assoc()): ?>

                <?php
                    $sellPrice =
                        $coach["Price"] * 0.70;
                ?>

                <div class="coach-card-sell">

                    <img
                        src="<?php
                            echo htmlspecialchars(
                                $coach["Image"]
                            );
                        ?>"
                        alt=""
                    >


                    <div class="coach-info-sell">

                        <h2>
                            <?php
                            echo htmlspecialchars(
                                $coach["Coach_Name"]
                            );
                            ?>
                        </h2>


                        <div class="original-price">

                            Original Price:
                            <?php
                            echo number_format(
                                $coach["Price"]
                            );
                            ?>

                            coins

                        </div>


                        <div class="sell-price">

                            Sell for:

                            <?php
                            echo number_format(
                                $sellPrice
                            );
                            ?>

                            coins

                        </div>


                        <form method="POST">

                            <input
                                type="hidden"
                                name="sell_coach_id"
                                value="<?php
                                    echo $coach["Coach_ID"];
                                ?>"
                            >


                            <button
                                type="submit"
                                class="sell-btn"
                            >
                                SELL COACH
                            </button>

                        </form>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="no-coaches">
            You currently don't own any coaches.
        </div>

    <?php endif; ?>


    <div class="back-section">

        <a href="markets.php">
            ← Back to Markets
        </a>

    </div>

</main>


<script src="music.js"></script>

</body>

</html>