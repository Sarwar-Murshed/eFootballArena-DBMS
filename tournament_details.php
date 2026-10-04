<?php 
 
session_start(); 
 
if(!isset($_SESSION["Gamer_Tag"])) { 
    header("Location: login.php"); 
    exit(); 
} 
 
include("db.php"); 
 
if(!isset($_GET["id"])) { 
    header("Location: tournament.php"); 
    exit(); 
} 
 
$tournament_id = $_GET["id"]; 
 
$sql = "SELECT * FROM Tournaments WHERE Tournament_ID = '$tournament_id'"; 
$result = mysqli_query($conn, $sql); 
 
if(mysqli_num_rows($result) == 0) { 
    echo "Tournament not found."; 
    exit(); 
} 
 
$tournament = mysqli_fetch_assoc($result); 
 
$name = $tournament["Tournament_Name"]; 
$type = $tournament["Tournament_Type"]; 
$prize = $tournament["Prize_Pool"]; 
$deadline = $tournament["Registration_Deadline"]; 
$maximum = $tournament["Maximum_Players"]; 
$status = $tournament["Tournament_Status"]; 
$start = $tournament["Start_Date"]; 
$end = $tournament["End_Date"]; 
 
?> 
 
<!DOCTYPE html> 
<html lang="en"> 
 
<head> 
 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
 
    <title><?php echo $name; ?> - eFootball ARENA</title> 
 
    <link rel="stylesheet" href="tournament_details.css"> 
 
</head> 
 
<body> 
 
<div class="page"> 
 
    <div class="top-bar"> 
 
        <div class="logo"> 
            eFOOTBALL <span>ARENA</span> 
        </div> 
 
        <a href="tournament.php" class="back-btn"> 
            BACK TO TOURNAMENTS 
        </a> 
 
    </div> 
 
 
    <div class="tournament-header"> 
 
        <p>COMPETITION DETAILS</p> 
 
        <h1><?php echo $name; ?></h1> 
 
        <div class="line"></div> 
 
        <span class="status"> 
            <?php echo strtoupper($status); ?> 
        </span> 
 
    </div> 
 
 
    <div class="details-box"> 
 
        <div class="detail"> 
            <span>TOURNAMENT TYPE</span> 
            <h2><?php echo $type; ?></h2> 
        </div> 
 
        <div class="detail"> 
            <span>PRIZE POOL</span> 
            <h2><?php echo $prize; ?></h2> 
        </div> 
 
        <div class="detail"> 
            <span>MAXIMUM PLAYERS</span> 
            <h2><?php echo $maximum; ?></h2> 
        </div> 
 
        <div class="detail"> 
            <span>REGISTRATION DEADLINE</span> 
            <h2><?php echo $deadline; ?></h2> 
        </div> 
 
        <div class="detail"> 
            <span>START DATE</span> 
            <h2><?php echo $start; ?></h2> 
        </div> 
 
        <div class="detail"> 
            <span>END DATE</span> 
            <h2><?php echo $end; ?></h2> 
        </div> 
 
    </div> 
 
 
    <div class="action-area"> 
 
        <button class="join-btn"> 
            JOIN TOURNAMENT 
        </button> 
 
        <a href="tournament.php" class="return-btn"> 
            RETURN 
        </a> 
 
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