<?php

session_start();

if (!isset($_SESSION["Gamer_Tag"]) || !isset($_SESSION["Player_ID"])) {
    header("Location: login.php");
    exit();
}

require_once "db.php";

$playerID = $_SESSION["Player_ID"];
$gamerTag = $_SESSION["Gamer_Tag"];

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["upload_picture"])) {

    if (isset($_FILES["profile_pic"]) && $_FILES["profile_pic"]["error"] === UPLOAD_ERR_OK) {

        $allowed = ["jpg", "jpeg", "png", "webp"];

        $fileName = $_FILES["profile_pic"]["name"];
        $fileTmp = $_FILES["profile_pic"]["tmp_name"];
        $fileSize = $_FILES["profile_pic"]["size"];

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {

            $error = "Only JPG, JPEG, PNG and WEBP files are allowed.";

        } elseif ($fileSize > 2 * 1024 * 1024) {

            $error = "File size must be less than 2MB.";

        } else {

            $uploadDir = __DIR__ . "/uploads/profiles/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $newFileName = "player_" . $playerID . "_" . time() . "." . $ext;

            $uploadPath = $uploadDir . $newFileName;

            $databasePath = "uploads/profiles/" . $newFileName;

            if (move_uploaded_file($fileTmp, $uploadPath)) {

                $stmt = $conn->prepare(
                    "SELECT Profile_Picture FROM Players WHERE Player_ID = ?"
                );

                $stmt->bind_param("i", $playerID);
                $stmt->execute();

                $oldPlayer = $stmt->get_result()->fetch_assoc();

                $stmt->close();

                $stmt = $conn->prepare(
                    "UPDATE Players SET Profile_Picture = ? WHERE Player_ID = ?"
                );

                $stmt->bind_param("si", $databasePath, $playerID);

                if ($stmt->execute()) {

                    $stmt->close();

                    if (!empty($oldPlayer["Profile_Picture"])) {

                        $oldPath = __DIR__ . "/" . $oldPlayer["Profile_Picture"];

                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    $message = "Profile picture updated successfully!";

                } else {

                    $stmt->close();

                    if (file_exists($uploadPath)) {
                        unlink($uploadPath);
                    }

                    $error = "Failed to update profile picture.";
                }

            } else {

                $error = "Failed to save the uploaded image.";
            }
        }

    } else {

        $error = "Please select an image.";
    }
}

$stmt = $conn->prepare(
    "SELECT * FROM Players WHERE Player_ID = ?"
);

$stmt->bind_param("i", $playerID);
$stmt->execute();

$player = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$player) {
    echo "Player not found.";
    exit();
}

$profilePic = null;

if (!empty($player["Profile_Picture"])) {

    $picturePath = __DIR__ . "/" . $player["Profile_Picture"];

    if (file_exists($picturePath)) {
        $profilePic = $player["Profile_Picture"];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile - eFootball Arena</title>

    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="profile.css">

</head>

<body>

<header class="topbar">

    <div class="brand">

        <div class="brand-logo">⚽</div>

        <div>
            <div class="brand-title">eFootball</div>
            <div class="brand-subtitle">ARENA</div>
        </div>

    </div>

    <nav class="navigation">

        <a href="dashboard.php">HOME</a>

        <a href="tournament.php">TOURNAMENTS</a>

        <a href="transfers.php">TRANSFERS</a>

        <a href="auction.php">AUCTIONS</a>

        <a href="profile.php" class="active">PROFILE</a>

    </nav>

    <div class="top-right">

        <div class="profile">

            <div class="profile-picture">

                <?php if ($profilePic): ?>

                    <img
                        src="<?php echo htmlspecialchars($profilePic); ?>"
                        alt="Profile"
                    >

                <?php else: ?>

                    <?php echo strtoupper(substr($gamerTag, 0, 1)); ?>

                <?php endif; ?>

            </div>

            <span class="profile-name">
                <?php echo htmlspecialchars($gamerTag); ?>
            </span>

        </div>

        <a href="logout.php" class="logout-button">
            LOGOUT
        </a>

    </div>

</header>

<main class="profile-page">

    <div class="profile-header">

        <div class="header-content">

            <span class="small-title">
                eFOOTBALL ARENA 2026
            </span>

            <h1>
                MY <span>PROFILE</span>
            </h1>

            <p>
                Manage your account and personalize your Arena identity.
            </p>

        </div>

    </div>

    <?php if ($message): ?>

        <div class="alert success">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>

    <?php if ($error): ?>

        <div class="alert error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>

    <div class="profile-container">

        <div class="profile-pic-section">

            <div class="pic-wrapper">

                <?php if ($profilePic): ?>

                    <img
                        id="previewImage"
                        src="<?php echo htmlspecialchars($profilePic); ?>"
                        alt="Profile Picture"
                    >

                <?php else: ?>

                    <div id="previewPlaceholder" class="placeholder">
                        <?php echo strtoupper(substr($gamerTag, 0, 1)); ?>
                    </div>

                    <img
                        id="previewImage"
                        src=""
                        alt="Preview"
                        style="display:none;"
                    >

                <?php endif; ?>

            </div>

            <form
                method="POST"
                enctype="multipart/form-data"
                class="upload-form"
            >

                <label class="upload-btn">

                    <input
                        type="file"
                        name="profile_pic"
                        id="profileInput"
                        accept="image/jpeg,image/png,image/webp"
                        hidden
                    >

                    CHANGE PHOTO

                </label>

                <button
                    type="submit"
                    name="upload_picture"
                    class="save-btn"
                >
                    SAVE PICTURE
                </button>

            </form>

            <p class="upload-note">
                JPG, PNG or WEBP • Max 2MB
            </p>

        </div>

        <div class="profile-info-section">

            <h2>Player Information</h2>

            <div class="info-grid">

                <div class="info-item">

                    <span class="label">
                        Player ID
                    </span>

                    <span class="value">
                        #<?php echo htmlspecialchars($player["Player_ID"]); ?>
                    </span>

                </div>

                <div class="info-item">

                    <span class="label">
                        Gamer Tag
                    </span>

                    <span class="value">
                        <?php echo htmlspecialchars($player["Gamer_Tag"]); ?>
                    </span>

                </div>

                <div class="info-item">

                    <span class="label">
                        Real Name
                    </span>

                    <span class="value">
                        <?php echo htmlspecialchars($player["Real_Name"]); ?>
                    </span>

                </div>

                <div class="info-item">

                    <span class="label">
                        Country
                    </span>

                    <span class="value">
                        <?php echo htmlspecialchars($player["Country"] ?? "—"); ?>
                    </span>

                </div>

                <div class="info-item">

                    <span class="label">
                        Email
                    </span>

                    <span class="value">
                        <?php echo htmlspecialchars($player["Email"] ?? "—"); ?>
                    </span>

                </div>

                <div class="info-item">

                    <span class="label">
                        Platform
                    </span>

                    <span class="value">
                        <?php echo htmlspecialchars($player["Platform"] ?? "—"); ?>
                    </span>

                </div>

                <div class="info-item">

                    <span class="label">
                        Favorite Club
                    </span>

                    <span class="value">
                        <?php echo htmlspecialchars($player["Favorite_Club"] ?? "—"); ?>
                    </span>

                </div>

                <div class="info-item">

                    <span class="label">
                        Preferred Formation
                    </span>

                    <span class="value">
                        <?php echo htmlspecialchars($player["Preferred_Formation"] ?? "—"); ?>
                    </span>

                </div>

                <div class="info-item">

                    <span class="label">
                        Join Date
                    </span>

                    <span class="value">
                        <?php echo htmlspecialchars($player["Join_Date"] ?? "—"); ?>
                    </span>

                </div>

                <div class="info-item">

                    <span class="label">
                        Status
                    </span>

                    <span class="value status-<?php echo strtolower($player["Status"] ?? "active"); ?>">

                        <?php echo htmlspecialchars($player["Status"] ?? "Active"); ?>

                    </span>

                </div>

            </div>

        </div>

    </div>

    <div class="back-section">

        <a href="dashboard.php" class="back-button">
            ← BACK TO DASHBOARD
        </a>

    </div>

</main>

<audio id="backgroundMusic" loop>

    <source src="Audio/background.mp3" type="audio/mpeg">

</audio>

<audio id="clickSound">

    <source src="Audio/click.mp3" type="audio/mpeg">

</audio>

<script src="music.js"></script>
<script src="profile.js"></script>

</body>

</html>