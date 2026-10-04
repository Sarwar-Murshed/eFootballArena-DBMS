<?php

session_start();

if(!isset($_SESSION["Gamer_Tag"])) {
    header("Location: login.php");
    exit();
}

include("db.php");

$sql = "SELECT Tournament_ID, Tournament_Name, Tournament_Status FROM Tournaments";
$result = mysqli_query($conn, $sql);

$images = [
    "images/efootball_cup.jpg",
    "images/uefa.jpg",
    "images/Fifa.jpg",
    "images/s_cup.jpg"
];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tournaments - eFootball ARENA</title>

    <link rel="stylesheet" href="tournament.css">

</head>

<body>

<div class="page">

    <div class="top-bar">

        <div class="logo">
            eFOOTBALL <span>ARENA</span>
        </div>

        <a href="dashboard.php" class="back-btn">
            BACK TO DASHBOARD
        </a>

    </div>


    <div class="title-section">

        <p>COMPETITION</p>

        <h1>TOURNAMENTS</h1>

        <div class="line"></div>

    </div>


    <div class="tournament-grid">

        <?php

        if(mysqli_num_rows($result) > 0) {

            $number = 0;

            while($tournament = mysqli_fetch_assoc($result)) {

                $name = $tournament["Tournament_Name"];
                $status = strtoupper($tournament["Tournament_Status"]);

                if($number < count($images)) {
                    $image = $images[$number];
                }
                else {
                    $image = "";
                }

        ?>

        <div class="tournament-card">

            <?php if($image != "") { ?>

                <div class="trophy-image">

                    <img src="<?php echo $image; ?>" alt="Tournament">

                </div>

            <?php } ?>


            <div class="card-overlay"></div>


            <div class="card-content">

                <div class="tournament-number">
                    0<?php echo $number + 1; ?>
                </div>

                <div class="status">
                    <?php echo $status; ?>
                </div>

                <h2>
                    <?php echo $name; ?>
                </h2>

                <a
                    href="tournament_details.php?id=<?php echo $tournament["Tournament_ID"]; ?>"
                    class="enter-btn"
                >
                    ENTER TOURNAMENT
                </a>

            </div>

        </div>

        <?php

                $number++;

            }

        }
        else {

            echo "<p>No tournaments available.</p>";

        }

        ?>

    </div>

</div>


<audio id="backgroundMusic" loop>

    <source src="Audio/background.mp3" type="audio/mpeg">

</audio>


<audio id="clickSound">

    <source src="Audio/click.mp3" type="audio/mpeg">

</audio>


<script src="music.js"></script>


</body>

</html>