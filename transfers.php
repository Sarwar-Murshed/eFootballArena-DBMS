<?php

session_start();

require_once "db.php";

if (!isset($_SESSION["Gamer_Tag"])) {
    header("Location: login.php");
    exit();
}

$sql = "SELECT
            pt.Transfer_ID,
            pt.Transfer_Type,
            pt.Transfer_Fee,
            pt.Transfer_Date,
            fp.Player_Name,
            fp.Position,
            fm.Gamer_Tag AS From_Manager,
            tm.Gamer_Tag AS To_Manager

        FROM Player_Transfers pt

        JOIN Football_Players fp
            ON pt.Football_Player_ID = fp.Football_Player_ID

        JOIN Players fm
            ON pt.From_Manager_ID = fm.Player_ID

        JOIN Players tm
            ON pt.To_Manager_ID = tm.Player_ID

        ORDER BY pt.Transfer_Date DESC,
                 pt.Transfer_ID DESC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Transfers | eFootballArena</title>

    <style>

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            min-height: 100%;
            height: auto;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            overflow-y: auto;
        }

        body {

            background:
                linear-gradient(
                    rgba(3, 7, 18, 0.55),
                    rgba(3, 7, 18, 0.90)
                ),
                url("Images/dashboard-bg.png");

            background-size: cover;
            background-position: center;
            background-attachment: fixed;

            color: white;

            font-family: Arial, Helvetica, sans-serif;
        }

        .transfer-page {

            width: 100%;

            min-height: 100vh;

            height: auto;

            padding: 35px 45px 70px;
        }

        .transfer-container {

            width: 100%;

            max-width: 1450px;

            margin: 0 auto;

            height: auto;
        }

        .page-header {

            text-align: center;

            margin-bottom: 30px;
        }

        .page-header h1 {

            margin: 0;

            font-size: 38px;

            font-weight: 800;

            letter-spacing: 1px;

            color: #ffffff;

            text-shadow:
                0 0 10px rgba(0, 229, 255, 0.45);
        }

        .page-header p {

            margin-top: 10px;

            color: #aeb8c8;

            font-size: 15px;
        }

        .back-button {

            display: inline-block;

            margin-bottom: 25px;

            padding: 11px 20px;

            background: rgba(0, 229, 255, 0.12);

            border: 1px solid rgba(0, 229, 255, 0.45);

            border-radius: 8px;

            color: #00e5ff;

            text-decoration: none;

            font-weight: 700;

            transition: 0.25s;
        }

        .back-button:hover {

            background: #00e5ff;

            color: #06111c;

            transform: translateY(-2px);

            box-shadow:
                0 0 18px rgba(0, 229, 255, 0.35);
        }

        .transfer-card {

            width: 100%;

            height: auto;

            overflow: visible;

            background: rgba(5, 10, 25, 0.78);

            border: 1px solid rgba(255, 255, 255, 0.12);

            border-radius: 15px;

            padding: 20px;

            box-shadow:
                0 15px 45px rgba(0, 0, 0, 0.35);

            backdrop-filter: blur(8px);
        }

        .table-wrapper {

            width: 100%;

            height: auto;

            overflow-x: auto;

            overflow-y: visible;
        }

        .transfer-table {

            width: 100%;

            min-width: 900px;

            border-collapse: collapse;

            table-layout: auto;
        }

        .transfer-table th {

            padding: 17px 14px;

            background: rgba(0, 229, 255, 0.12);

            color: #00e5ff;

            font-size: 14px;

            text-transform: uppercase;

            letter-spacing: 0.7px;

            border-bottom: 2px solid rgba(0, 229, 255, 0.35);

            white-space: nowrap;
        }

        .transfer-table td {

            padding: 17px 14px;

            text-align: center;

            color: #e6ebf2;

            background: rgba(5, 10, 25, 0.65);

            border-bottom: 1px solid rgba(255, 255, 255, 0.09);

            font-size: 14px;

            white-space: nowrap;
        }

        .transfer-table tbody tr {

            transition: 0.2s;
        }

        .transfer-table tbody tr:hover td {

            background: rgba(20, 40, 65, 0.9);

            color: white;
        }

        .player-name {

            font-weight: 700;

            color: #ffffff;
        }

        .position {

            color: #8be9ff;

            font-weight: 600;
        }

        .manager {

            color: #ffffff;

            font-weight: 600;
        }

        .transfer-type {

            display: inline-block;

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 800;

            text-transform: uppercase;

            background: rgba(255, 255, 255, 0.10);

            color: #ffffff;
        }

        .fee {

            color: #7cffb2;

            font-weight: 700;
        }

        .date {

            color: #b7c0cf;

            font-size: 13px;
        }

        .no-transfer {

            text-align: center;

            padding: 55px 20px;

            color: #9ba6b7;

            font-size: 18px;
        }

        .transfer-id {

            color: #78869b;

            font-size: 13px;
        }

        @media (max-width: 900px) {

            .transfer-page {

                padding: 25px 18px 50px;
            }

            .page-header h1 {

                font-size: 30px;
            }

            .transfer-card {

                padding: 12px;
            }

            .transfer-table {

                min-width: 900px;
            }
        }

    </style>

</head>

<body>

<div class="transfer-page">

    <div class="transfer-container">

        <a href="dashboard.php" class="back-button">
            ← Back to Dashboard
        </a>

        <div class="page-header">

            <h1>PLAYER TRANSFERS</h1>

            <p>
                Track the latest football player transfers between managers
            </p>

        </div>

        <div class="transfer-card">

            <div class="table-wrapper">

                <?php if ($result && $result->num_rows > 0): ?>

                    <table class="transfer-table">

                        <thead>

                            <tr>

                                <th>ID</th>

                                <th>Football Player</th>

                                <th>Position</th>

                                <th>Transfer Type</th>

                                <th>From Manager</th>

                                <th>To Manager</th>

                                <th>Transfer Fee</th>

                                <th>Date</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php while ($transfer = $result->fetch_assoc()): ?>

                            <tr>

                                <td class="transfer-id">
                                    #<?php echo htmlspecialchars($transfer["Transfer_ID"]); ?>
                                </td>

                                <td class="player-name">
                                    <?php echo htmlspecialchars($transfer["Player_Name"]); ?>
                                </td>

                                <td class="position">
                                    <?php echo htmlspecialchars($transfer["Position"]); ?>
                                </td>

                                <td>

                                    <span class="transfer-type">
                                        <?php echo htmlspecialchars($transfer["Transfer_Type"]); ?>
                                    </span>

                                </td>

                                <td class="manager">

                                    <?php

                                    echo htmlspecialchars(
                                        preg_replace(
                                            '/\d+$/',
                                            '',
                                            $transfer["From_Manager"]
                                        )
                                    );

                                    ?>

                                </td>

                                <td class="manager">

                                    <?php

                                    echo htmlspecialchars(
                                        preg_replace(
                                            '/\d+$/',
                                            '',
                                            $transfer["To_Manager"]
                                        )
                                    );

                                    ?>

                                </td>

                                <td class="fee">

                                    <?php

                                    if (
                                        $transfer["Transfer_Fee"] !== null &&
                                        $transfer["Transfer_Fee"] !== ''
                                    ) {

                                        echo number_format(
                                            (float)$transfer["Transfer_Fee"],
                                            2
                                        );

                                    } else {

                                        echo "Free";

                                    }

                                    ?>

                                </td>

                                <td class="date">

                                    <?php

                                    echo date(
                                        "d M Y, h:i A",
                                        strtotime($transfer["Transfer_Date"])
                                    );

                                    ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                        </tbody>

                    </table>

                <?php else: ?>

                    <div class="no-transfer">

                        No transfer records available.

                    </div>

                <?php endif; ?>

            </div>

        </div>

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