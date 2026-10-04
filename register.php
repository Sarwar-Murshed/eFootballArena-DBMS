<?php
require_once "db.php";

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $gamer_tag = trim($_POST["gamer_tag"]);
    $real_name = trim($_POST["real_name"]);
    $country   = trim($_POST["country"]);
    $password  = $_POST["password"];

    $check_sql = "SELECT Player_ID FROM Players WHERE Gamer_Tag = '$gamer_tag'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $message = "Gamer Tag already exists.";
    } else {

        $sql = "INSERT INTO Players (Gamer_Tag, Real_Name, Country, Password, Join_Date, Status)
                VALUES ('$gamer_tag', '$real_name', '$country', '$password', CURDATE(), 'Active')";

        if (mysqli_query($conn, $sql)) {

            $new_player_id = mysqli_insert_id($conn);

            $wallet_sql = "INSERT INTO Manager_Wallet (Manager_ID, Balance)
                           VALUES ($new_player_id, 100000.00)";
            mysqli_query($conn, $wallet_sql);

            $bot_players = [
                
                ['Alex Rivera',     'GK', 7.0, 0, 0, 0, 18, 10],
                ['Marcus Cole',     'GK', 6.8, 0, 0, 0, 15, 10],

            
                ['Ethan Brooks',    'DF', 7.1, 1, 2, 22, 0, 10],
                ['Liam Foster',     'DF', 6.9, 1, 1, 20, 0, 10],
                ['Noah Bennett',    'DF', 6.8, 0, 2, 19, 0, 10],
                ['Owen Hayes',      'DF', 6.7, 1, 1, 18, 0, 10],
                ['Caleb Morgan',    'DF', 6.6, 0, 1, 17, 0, 10],

                
                ['Lucas Reed',      'MF', 7.0, 4, 5, 12, 0, 10],
                ['Julian Shaw',     'MF', 6.9, 3, 4, 14, 0, 10],
                ['Felix Grant',     'MF', 6.8, 2, 5, 11, 0, 10],
                ['Theo Blake',      'MF', 6.7, 3, 3, 13, 0, 10],

            
                ['Ryan Cooper',     'FW', 7.2, 9, 3, 2, 0, 10],
                ['Mason Price',     'FW', 7.0, 7, 4, 2, 0, 10],
                ['Jake Turner',     'FW', 6.9, 6, 3, 3, 0, 10],
                ['Leo Sanders',     'FW', 6.8, 5, 2, 2, 0, 10],
            ];

    
            foreach ($bot_players as $bot) {

                list($name, $pos, $rating, $goals, $assists, $tackles, $saves, $matches) = $bot;

                $insert_fp = "INSERT INTO Football_Players
                              (Player_Name, Position, Player_Type, Overall_Rating, Goals, Assists, Tackles, Saves, Matches_Played)
                              VALUES
                              ('$name', '$pos', 'Bot', $rating, $goals, $assists, $tackles, $saves, $matches)";

                if (mysqli_query($conn, $insert_fp)) {
                    $fp_id = mysqli_insert_id($conn);

        
                    $assign = "INSERT INTO Team_Players
                               (Manager_ID, Football_Player_ID, Acquired_Date, Acquisition_Type)
                               VALUES
                               ($new_player_id, $fp_id, CURDATE(), 'Initial')";
                    mysqli_query($conn, $assign);
                }
            }

            $message = "Account created successfully! You received 15 Bot players + 1 lakh coins.";
            $success = true;

        } else {
            $message = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - eFootball Arena</title>
    <link rel="stylesheet" href="register.css">
</head>

<body>

<div class="register-container">
    <div class="register-box">

        <h1>eFootball Arena</h1>
        <h2>Create Account</h2>

        <?php if (!empty($message)) { ?>
            <p class="message" style="<?php echo $success ? 'color:#61e294;' : 'color:#ff6b6b;'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php } ?>

        <form method="POST">
            <input type="text" name="gamer_tag" placeholder="Gamer Tag" required>
            <input type="text" name="real_name" placeholder="Real Name" required>
            <input type="text" name="country" placeholder="Country" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit">Sign Up</button>
        </form>

        <p class="login-text">
            Already have an account?
            <a href="login.php">Sign In</a>
        </p>

    </div>
</div>

</body>
</html>