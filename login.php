<?php

session_start();
require_once "db.php";

if (isset($_POST["Login"])) {

    $username = $_POST["Username"];
    $password = $_POST["Password"];

    $player_sql = "SELECT * FROM Players
                   WHERE Gamer_Tag = '$username'
                   AND Password = '$password'";

    $player_result = mysqli_query($conn, $player_sql);

    if (mysqli_num_rows($player_result) > 0) {

        $player = mysqli_fetch_assoc($player_result);

        $_SESSION["Player_ID"] = $player["Player_ID"];
        $_SESSION["Gamer_Tag"] = $player["Gamer_Tag"];
        $_SESSION["User_Type"] = "Player";

        header("Location: dashboard.php");
        exit();
    }

    $admin_sql = "SELECT * FROM Admins
                  WHERE Email = '$username'
                  AND Password = '$password'";

    $admin_result = mysqli_query($conn, $admin_sql);

    if (mysqli_num_rows($admin_result) > 0) {

        $admin = mysqli_fetch_assoc($admin_result);

        $_SESSION["Admin_ID"] = $admin["Admin_ID"];
        $_SESSION["Admin_Name"] = $admin["Admin_Name"];
        $_SESSION["Role"] = $admin["Role"];
        $_SESSION["User_Type"] = "Admin";

        header("Location: admin_dashboard.php");
        exit();
    }

    $error = "Invalid username or password";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>eFootball Arena - Login</title>

    <link rel="stylesheet" href="login.css">

</head>

<body>

<div class="login-box">

    <div class="logo">

        <div class="ea-logo">

            <span>EA</span>

            <div class="ball">⚽</div>

        </div>

        <h1>
            <span class="e">e</span>Football
        </h1>

        <h2>ARENA</h2>

        <div class="line"></div>

        <p>THE ULTIMATE FOOTBALL BATTLE</p>

    </div>


    <form action="login.php" method="post" id="loginForm">

        <label>Username / Email</label>

        <input
            type="text"
            name="Username"
            id="username"
            placeholder="Enter your gamer tag or email"
            required
        >


        <label>Password</label>

        <div class="password-box">

            <input
                type="password"
                name="Password"
                id="password"
                placeholder="Enter your password"
                required
            >

            <button type="button" id="showPassword">
                Show
            </button>

        </div>


        <input
            type="submit"
            name="Login"
            value="LOGIN"
            class="login-button"
        >


        <p class="signup-text">
            Don't have an account?
            <a href="register.php">Sign Up</a>
        </p>


        <?php

        if (isset($error)) {

            echo '<p class="error">' . $error . '</p>';

        }

        ?>

    </form>


    <div class="login-info">

        <p>Player: Gamer Tag + Password</p>

        <p>Admin: Email + Password</p>

    </div>

</div>


<script src="login.js"></script>

</body>

</html>