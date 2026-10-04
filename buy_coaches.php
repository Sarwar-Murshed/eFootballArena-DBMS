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
    && isset($_POST["buy_coach_id"])
) {

    $coachId = (int)$_POST["buy_coach_id"];

    $coachSQL = "
        SELECT Coach_ID, Coach_Name, Price
        FROM Coaches
        WHERE Coach_ID = ?
    ";

    $stmt = $conn->prepare($coachSQL);
    $stmt->bind_param("i", $coachId);
    $stmt->execute();

    $coach = $stmt->get_result()->fetch_assoc();


    if (!$coach) {

        $message = "Coach not found.";

    } else {

        $price = (float)$coach["Price"];


        /* Check if already owned */

        $ownedSQL = "
            SELECT Coach_ID
            FROM Manager_Coach
            WHERE Manager_ID = ?
            AND Coach_ID = ?
        ";

        $stmt = $conn->prepare($ownedSQL);
        $stmt->bind_param("ii", $managerID, $coachId);
        $stmt->execute();

        $alreadyOwned =
            $stmt->get_result()->num_rows > 0;


        if ($alreadyOwned) {

            $message =
                "You already own " .
                $coach["Coach_Name"] . ".";

        } else {

            $walletSQL = "
                SELECT Balance
                FROM Manager_Wallet
                WHERE Manager_ID = ?
            ";

            $stmt = $conn->prepare($walletSQL);
            $stmt->bind_param("i", $managerID);
            $stmt->execute();

            $wallet = $stmt->get_result()->fetch_assoc();


            if (!$wallet) {

                $message = "Wallet not found.";

            } elseif ($wallet["Balance"] < $price) {

                $message = "Not enough coins.";

            } else {

                $conn->begin_transaction();

                try {

                    /* Deduct coins */

                    $updateWallet = $conn->prepare("
                        UPDATE Manager_Wallet
                        SET Balance = Balance - ?
                        WHERE Manager_ID = ?
                    ");

                    $updateWallet->bind_param(
                        "di",
                        $price,
                        $managerID
                    );

                    $updateWallet->execute();


                    /* Add coach */

                    $insertCoach = $conn->prepare("
                        INSERT INTO Manager_Coach
                        (
                            Manager_ID,
                            Coach_ID,
                            Acquired_Date
                        )
                        VALUES
                        (
                            ?,
                            ?,
                            CURRENT_DATE
                        )
                    ");

                    $insertCoach->bind_param(
                        "ii",
                        $managerID,
                        $coachId
                    );

                    $insertCoach->execute();


                    $conn->commit();


                    $message =
                        $coach["Coach_Name"] .
                        " hired successfully!";


                } catch (Exception $e) {

                    $conn->rollback();

                    $message =
                        "Unable to hire coach.";
                }
            }
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

$currentSQL = "
    SELECT
        c.Coach_Name
    FROM Manager_Coach mc
    JOIN Coaches c
        ON mc.Coach_ID = c.Coach_ID
    WHERE mc.Manager_ID = ?
    ORDER BY mc.Acquired_Date ASC, mc.Coach_ID ASC
    LIMIT 1
";

$stmt = $conn->prepare($currentSQL);
$stmt->bind_param("i", $managerID);
$stmt->execute();

$current = $stmt->get_result()->fetch_assoc();

$currentCoach = $current
    ? $current["Coach_Name"]
    : "None";


$coachesSQL = "
    SELECT
        c.Coach_ID,
        c.Coach_Name,
        c.Image,
        c.Price,
        c.Description
    FROM Coaches c
    WHERE NOT EXISTS (
        SELECT 1
        FROM Manager_Coach mc
        WHERE mc.Manager_ID = ?
        AND mc.Coach_ID = c.Coach_ID
    )
    ORDER BY c.Price DESC
";

$stmt = $conn->prepare($coachesSQL);
$stmt->bind_param("i", $managerID);
$stmt->execute();

$coaches = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Buy Coaches - eFootball Arena</title>

<link
    rel="stylesheet"
    href="markets.css"
>

<style>

.wallet-bar {
    text-align: center;
    margin-bottom: 20px;
    font-size: 17px;
}

.wallet-bar span {
    color: #b39aff;
    font-weight: bold;
    font-size: 20px;
}

.current-coach {
    text-align: center;
    margin-bottom: 25px;
    color: #aaa;
}

.coaches-grid {
    display: grid;
    grid-template-columns:
        repeat(auto-fill, minmax(180px, 1fr));
    gap: 18px;
}

.coach-card-buy {
    background: rgba(12,15,25,0.95);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    overflow: hidden;
    text-align: center;
    transition: 0.25s;
}

.coach-card-buy:hover {
    border-color: #9d7cff;
    transform: translateY(-4px);
}

.coach-card-buy img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    object-position: top center;
}

.coach-info-buy {
    padding: 14px;
}

.coach-info-buy h3 {
    font-size: 16px;
    margin-bottom: 6px;
}

.price {
    color: #61e294;
    font-weight: bold;
    font-size: 15px;
    margin: 8px 0;
}

.buy-btn {
    background: #6c3cff;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    width: 100%;
}

.buy-btn:hover {
    background: #844fff;
}

.message {
    text-align: center;
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 8px;
    background: rgba(97, 226, 148, 0.15);
    color: #61e294;
}

.no-coaches {
    text-align: center;
    color: #777;
    padding: 50px 0;
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

        <a
            href="buy_coaches.php"
            class="active"
        >
            BUY COACHES
        </a>

        <a href="sell_coaches.php">
            SELL COACHES
        </a>

    </nav>


    <div class="profile">
        <?php echo htmlspecialchars($gamerTag); ?>
    </div>

</header>


<main class="markets-page">


    <div class="page-header">

        <h1>BUY COACHES</h1>

        <p>
            Hire coaches for your team
        </p>

    </div>


    <div class="wallet-bar">

        Your Wallet:

        <span>
            <?php echo number_format($balance); ?>
        </span>

        coins

    </div>


    <div class="current-coach">

        Current Coach:

        <strong>
            <?php echo htmlspecialchars($currentCoach); ?>
        </strong>

    </div>


    <?php if ($message): ?>

        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <?php if ($coaches->num_rows > 0): ?>

        <div class="coaches-grid">

            <?php while ($c = $coaches->fetch_assoc()): ?>

                <div class="coach-card-buy">

                    <img
                        src="<?php
                            echo htmlspecialchars($c["Image"]);
                        ?>"
                        alt=""
                    >

                    <div class="coach-info-buy">

                        <h3>
                            <?php
                            echo htmlspecialchars(
                                $c["Coach_Name"]
                            );
                            ?>
                        </h3>

                        <div class="price">

                            <?php
                            echo number_format(
                                $c["Price"]
                            );
                            ?>

                            coins

                        </div>

                        <form method="POST">

                            <input
                                type="hidden"
                                name="buy_coach_id"
                                value="<?php
                                    echo $c["Coach_ID"];
                                ?>"
                            >

                            <button
                                type="submit"
                                name="buy_coach"
                                class="buy-btn"
                            >
                                HIRE NOW
                            </button>

                        </form>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="no-coaches">
            You already own all available coaches.
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