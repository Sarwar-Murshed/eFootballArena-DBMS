<?php

session_start();

require_once "db.php";

if (!isset($_SESSION["Gamer_Tag"])) {

    header("Location: login.php");

    exit();

}

$stream_id = isset($_GET["stream_id"])

    ? (int)$_GET["stream_id"]

    : 0;

if ($stream_id <= 0) {

    header("Location: live.php");

    exit();

}

$sql = "SELECT

            ls.Stream_ID,

            ls.Stream_URL,

            ls.Status,

            ls.Start_Time,

            ls.End_Time,

            ls.Viewer_Count,

            m.Match_ID,

            m.Player1_ID,

            m.Player2_ID,

            m.Club_Player1,

            m.Club_Player2,

            m.Match_Round,

            m.Final_Score,

            p1.Gamer_Tag AS Player1,

            p2.Gamer_Tag AS Player2,

            t.Tournament_Name

        FROM Live_Streams ls

        JOIN Matches m

            ON ls.Match_ID = m.Match_ID

        JOIN Players p1

            ON m.Player1_ID = p1.Player_ID

        JOIN Players p2

            ON m.Player2_ID = p2.Player_ID

        JOIN Tournaments t

            ON m.Tournament_ID = t.Tournament_ID

        WHERE ls.Stream_ID = $stream_id

        AND ls.Status = 'Live'

        LIMIT 1";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) === 0) {

    header("Location: live.php");

    exit();

}

$stream = mysqli_fetch_assoc($result);

$player1_display = preg_replace(

    '/\d+$/',

    '',

    $stream["Player1"]

);

$player2_display = preg_replace(

    '/\d+$/',

    '',

    $stream["Player2"]

);

$score_parts = explode(

    "-",

    $stream["Final_Score"]

);

$score1 = isset($score_parts[0])

    ? trim($score_parts[0])

    : "0";

$score2 = isset($score_parts[1])

    ? trim($score_parts[1])

    : "0";

$viewer_count = (int)$stream["Viewer_Count"];

$chat_sql = "SELECT

                lc.Chat_ID,

                lc.Player_ID,

                lc.Message,

                p.Gamer_Tag

             FROM Live_Chat lc

             JOIN Players p

                ON lc.Player_ID = p.Player_ID

             WHERE lc.Stream_ID = $stream_id

             ORDER BY lc.Chat_ID ASC";

$chat_result = mysqli_query(

    $conn,

    $chat_sql

);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Watch Live | eFootball Arena</title>

    <link rel="stylesheet" href="dashboard.css">

    <style>

        * {

            box-sizing: border-box;

        }

        body {

            margin: 0;

            min-height: 100vh;

            font-family: Arial, Helvetica, sans-serif;

            background:

                linear-gradient(

                    rgba(5, 8, 18, 0.82),

                    rgba(5, 8, 18, 0.94)

                ),

                url("Images/dashboard-bg.png") center/cover fixed;

            color: #ffffff;

        }

        .watch-page {

            width: 100%;

            max-width: 1450px;

            margin: 0 auto;

            padding: 30px 35px 50px;

        }

        .watch-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 25px;

            gap: 20px;

        }

        .watch-title h1 {

            margin: 0;

            font-size: 28px;

            letter-spacing: 1px;

        }

        .watch-title p {

            margin: 7px 0 0;

            color: #aeb7c7;

            font-size: 14px;

        }

        .back-button {

            text-decoration: none;

            color: #ffffff;

            background: rgba(255,255,255,0.08);

            border: 1px solid rgba(255,255,255,0.15);

            padding: 11px 18px;

            border-radius: 10px;

            font-size: 13px;

            font-weight: 700;

            transition: 0.25s ease;

        }

        .back-button:hover {

            background: rgba(255,255,255,0.16);

            transform: translateY(-2px);

        }

        .live-badge {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            background: #e50914;

            color: #ffffff;

            padding: 7px 13px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 800;

            margin-bottom: 15px;

        }

        .live-dot {

            width: 8px;

            height: 8px;

            background: #ffffff;

            border-radius: 50%;

            animation: livePulse 1.2s infinite;

        }

        @keyframes livePulse {

            0% {

                opacity: 1;

                transform: scale(1);

            }

            50% {

                opacity: 0.35;

                transform: scale(0.7);

            }

            100% {

                opacity: 1;

                transform: scale(1);

            }

        }

        .match-bar {

            background: rgba(12, 17, 31, 0.94);

            border: 1px solid rgba(255,255,255,0.08);

            border-radius: 18px;

            padding: 22px 25px;

            display: grid;

            grid-template-columns: 1fr auto 1fr;

            align-items: center;

            gap: 25px;

            margin-bottom: 22px;

            box-shadow: 0 18px 45px rgba(0,0,0,0.35);

        }

        .team {

            display: flex;

            flex-direction: column;

            gap: 7px;

        }

        .team.left {

            text-align: right;

        }

        .team.right {

            text-align: left;

        }

        .team-name {

            font-size: 20px;

            font-weight: 800;

        }

        .club-name {

            color: #9da7ba;

            font-size: 12px;

        }

        .score-box {

            text-align: center;

            min-width: 180px;

        }

        .score {

            font-size: 36px;

            font-weight: 900;

            letter-spacing: 4px;

        }

        .timer {

            margin-top: 6px;

            color: #ff4545;

            font-size: 16px;

            font-weight: 800;

            font-variant-numeric: tabular-nums;

        }

        .round {

            margin-top: 5px;

            color: #8e98aa;

            font-size: 11px;

        }

        .viewer-count {

            margin-top: 8px;

            color: #c2cad7;

            font-size: 12px;

        }

        .main-watch-area {

            display: grid;

            grid-template-columns: minmax(0, 1fr) 360px;

            gap: 20px;

            align-items: stretch;

            transition: 0.3s ease;

        }

        .main-watch-area.chat-hidden {

            grid-template-columns: 1fr;

        }

        .video-card {

            background: #080c16;

            border: 1px solid rgba(255,255,255,0.08);

            border-radius: 18px;

            overflow: hidden;

            box-shadow: 0 18px 50px rgba(0,0,0,0.45);

            transition: 0.3s ease;

        }

        .main-watch-area.chat-hidden .video-card {

            width: 100%;

        }

        .video-header {

            height: 52px;

            padding: 0 18px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            background: rgba(18,23,38,0.95);

            border-bottom: 1px solid rgba(255,255,255,0.07);

        }

        .video-header-title {

            font-size: 13px;

            font-weight: 800;

            letter-spacing: 0.6px;

        }

        .video-status {

            display: flex;

            align-items: center;

            gap: 7px;

            color: #ff4141;

            font-size: 11px;

            font-weight: 800;

        }

        .video-status span {

            width: 7px;

            height: 7px;

            background: #ff3030;

            border-radius: 50%;

        }

        .video-container {

            position: relative;

            width: 100%;

            aspect-ratio: 16 / 9;

            background: #000000;

            cursor: pointer;

        }

        .video-container iframe {

            position: absolute;

            inset: 0;

            width: 100%;

            height: 100%;

            border: 0;

            pointer-events: none;

        }

        .fullscreen-hint {

            position: absolute;

            bottom: 14px;

            right: 15px;

            padding: 8px 12px;

            background: rgba(0,0,0,0.72);

            border: 1px solid rgba(255,255,255,0.15);

            border-radius: 8px;

            color: #ffffff;

            font-size: 10px;

            font-weight: 700;

            opacity: 0;

            transition: 0.25s ease;

            pointer-events: none;

        }

        .video-container:hover .fullscreen-hint {

            opacity: 1;

        }

        .chat-card {

            background: rgba(10, 14, 25, 0.97);

            border: 1px solid rgba(255,255,255,0.08);

            border-radius: 18px;

            overflow: hidden;

            min-height: 100%;

            display: flex;

            flex-direction: column;

            box-shadow: 0 18px 50px rgba(0,0,0,0.35);

            transition: 0.3s ease;

        }

        .chat-card.hidden {

            display: none;

        }

        .chat-header {

            height: 52px;

            padding: 0 12px 0 18px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            border-bottom: 1px solid rgba(255,255,255,0.08);

            background: rgba(18,23,38,0.95);

        }

        .chat-title {

            font-size: 13px;

            font-weight: 800;

        }

        .chat-header-right {

            display: flex;

            align-items: center;

            gap: 10px;

        }

        .chat-online {

            font-size: 10px;

            color: #6df59b;

            font-weight: 700;

        }

        .close-chat {

            width: 28px;

            height: 28px;

            border: none;

            border-radius: 7px;

            background: rgba(255,255,255,0.08);

            color: #ffffff;

            font-size: 17px;

            line-height: 28px;

            text-align: center;

            cursor: pointer;

            transition: 0.2s ease;

        }

        .close-chat:hover {

            background: #e50914;

            transform: scale(1.05);

        }

        .open-chat-button {

            position: fixed;

            right: 25px;

            bottom: 25px;

            width: 52px;

            height: 52px;

            border: none;

            border-radius: 50%;

            background: #e50914;

            color: #ffffff;

            font-size: 21px;

            cursor: pointer;

            display: none;

            align-items: center;

            justify-content: center;

            box-shadow: 0 10px 30px rgba(229,9,20,0.4);

            z-index: 1000;

            transition: 0.25s ease;

        }

        .open-chat-button:hover {

            transform: scale(1.08);

        }

        .open-chat-button.show {

            display: flex;

        }

        .chat-messages {

            flex: 1;

            min-height: 420px;

            max-height: 600px;

            overflow-y: auto;

            padding: 16px;

        }

        .chat-message {

            padding: 10px 11px;

            margin-bottom: 10px;

            background: rgba(255,255,255,0.045);

            border-radius: 10px;

            border: 1px solid rgba(255,255,255,0.045);

        }

        .chat-user {

            color: #ffffff;

            font-size: 12px;

            font-weight: 800;

            margin-bottom: 5px;

        }

        .chat-text {

            color: #c0c7d4;

            font-size: 12px;

            line-height: 1.45;

        }

        .chat-time {

            color: #697386;

            font-size: 9px;

            margin-top: 5px;

        }

        .chat-footer {

            padding: 12px 15px;

            color: #697386;

            font-size: 10px;

            text-align: center;

            border-top: 1px solid rgba(255,255,255,0.07);

        }

        .extra-info {

            margin-top: 20px;

            display: grid;

            grid-template-columns: repeat(3, 1fr);

            gap: 15px;

        }

        .info-box {

            background: rgba(12,17,30,0.88);

            border: 1px solid rgba(255,255,255,0.07);

            border-radius: 14px;

            padding: 18px;

        }

        .info-label {

            color: #7f899c;

            font-size: 10px;

            text-transform: uppercase;

            letter-spacing: 1px;

            margin-bottom: 8px;

        }

        .info-value {

            font-size: 14px;

            font-weight: 700;

        }

        @media (max-width: 1000px) {

            .main-watch-area {

                grid-template-columns: 1fr;

            }

            .chat-card {

                min-height: 400px;

            }

            .chat-messages {

                min-height: 300px;

                max-height: 400px;

            }

        }

        @media (max-width: 700px) {

            .watch-page {

                padding: 20px 15px 40px;

            }

            .watch-header {

                align-items: flex-start;

                flex-direction: column;

            }

            .match-bar {

                grid-template-columns: 1fr;

                text-align: center;

            }

            .team.left,

            .team.right {

                text-align: center;

            }

            .extra-info {

                grid-template-columns: 1fr;

            }

            .team-name {

                font-size: 17px;

            }

            .fullscreen-hint {

                display: none;

            }

        }

    </style>

</head>

<body>

    <div class="watch-page">

        <div class="watch-header">

            <div class="watch-title">

                <div class="live-badge">

                    <span class="live-dot"></span>

                    LIVE NOW

                </div>

                <h1>eFootball Arena Live</h1>

                <p>

                    Watch the match live from the arena.

                </p>

            </div>

            <a href="live.php" class="back-button">

                ← BACK TO LIVE

            </a>

        </div>

        <div class="match-bar">

            <div class="team left">

                <div class="team-name">

                    <?php echo htmlspecialchars($player1_display); ?>

                </div>

                <div class="club-name">

                    <?php echo htmlspecialchars($stream["Club_Player1"]); ?>

                </div>

            </div>

            <div class="score-box">

                <div class="score">

                    <?php echo htmlspecialchars($score1); ?>

                    -

                    <?php echo htmlspecialchars($score2); ?>

                </div>

                <div

                    class="timer"

                    id="watchTimer"

                >

                    48:23

                </div>

                <div class="round">

                    <?php echo htmlspecialchars($stream["Match_Round"]); ?>

                </div>

                <div class="viewer-count">

                    👁 <?php echo number_format($viewer_count); ?> viewers

                </div>

            </div>

            <div class="team right">

                <div class="team-name">

                    <?php echo htmlspecialchars($player2_display); ?>

                </div>

                <div class="club-name">

                    <?php echo htmlspecialchars($stream["Club_Player2"]); ?>

                </div>

            </div>

        </div>

        <div

            class="main-watch-area"

            id="mainWatchArea"

        >

            <div class="video-card">

                <div class="video-header">

                    <div class="video-header-title">

                        LIVE MATCH

                    </div>

                    <div class="video-status">

                        <span></span>

                        STREAMING

                    </div>

                </div>

                <div

                    class="video-container"

                    id="videoContainer"

                    title="Double-click to enter fullscreen"

                >

                    <iframe

                        id="liveVideo"

                        src="https://www.youtube.com/embed/ylYqc496X0Y?start=815&autoplay=1&controls=0&rel=0&playsinline=1&cc_load_policy=0&cc_lang_pref=en"

                        title="Live Match"

                        allow="autoplay; encrypted-media; picture-in-picture; fullscreen"

                        allowfullscreen>

                    </iframe>

                    <div class="fullscreen-hint">

                        DOUBLE-CLICK FOR FULLSCREEN

                    </div>

                </div>

            </div>

            <div

                class="chat-card"

                id="chatCard"

            >

                <div class="chat-header">

                    <div class="chat-title">

                        LIVE CHAT

                    </div>

                    <div class="chat-header-right">

                        <div class="chat-online">

                            ● LIVE

                        </div>

                        <button

                            type="button"

                            class="close-chat"

                            id="closeChat"

                            title="Close live chat"

                        >

                            ×

                        </button>

                    </div>

                </div>

                <div

                    class="chat-messages"

                    id="chatMessages"

                >

                    <?php

                    if (

                        $chat_result &&

                        mysqli_num_rows($chat_result) > 0

                    ) {

                        while (

                            $chat = mysqli_fetch_assoc(

                                $chat_result

                            )

                        ) {

                    ?>

                    <div class="chat-message">

                        <div class="chat-user">

                            <?php

                            echo htmlspecialchars(

                                $chat["Gamer_Tag"]

                            );

                            ?>

                        </div>

                        <div class="chat-text">

                            <?php

                            echo htmlspecialchars(

                                $chat["Message"]

                            );

                            ?>

                        </div>

                        <div class="chat-time">

                            LIVE

                        </div>

                    </div>

                    <?php

                        }

                    } else {

                    ?>

                    <div class="chat-message">

                        <div class="chat-user">

                            eFootball Arena

                        </div>

                        <div class="chat-text">

                            Welcome to the live match!

                        </div>

                        <div class="chat-time">

                            LIVE

                        </div>

                    </div>

                    <?php

                    }

                    ?>

                </div>

                <div class="chat-footer">

                    Live comments from the arena

                </div>

            </div>

        </div>

        <button

            type="button"

            class="open-chat-button"

            id="openChat"

            title="Open live chat"

        >

            💬

        </button>

        <div class="extra-info">

            <div class="info-box">

                <div class="info-label">

                    Tournament

                </div>

                <div class="info-value">

                    <?php echo htmlspecialchars($stream["Tournament_Name"]); ?>

                </div>

            </div>

            <div class="info-box">

                <div class="info-label">

                    Match

                </div>

                <div class="info-value">

                    #<?php echo htmlspecialchars($stream["Match_ID"]); ?>

                </div>

            </div>

            <div class="info-box">

                <div class="info-label">

                    Status

                </div>

                <div class="info-value">

                    Live Now

                </div>

            </div>

        </div>

    </div>

    <script>

        const timer =

            document.getElementById("watchTimer");

        const sessionKey =

            "<?php echo session_id(); ?>";

        const timerKey =

            "eFootballArenaMatchStart_" + sessionKey;

        const matchStartingSeconds =

            (48 * 60) + 23;

        let matchStartMilliseconds =

            sessionStorage.getItem(timerKey);

        if (!matchStartMilliseconds) {

            matchStartMilliseconds =

                Date.now();

            sessionStorage.setItem(

                timerKey,

                matchStartMilliseconds

            );

        } else {

            matchStartMilliseconds =

                parseInt(

                    matchStartMilliseconds,

                    10

                );

        }

        function updateWatchTimer() {

            const currentTime =

                Date.now();

            const realElapsedMilliseconds =

                currentTime -

                matchStartMilliseconds;

            const footballElapsedSeconds =

                Math.floor(

                    realElapsedMilliseconds / 200

                );

            const matchTime =

                matchStartingSeconds +

                footballElapsedSeconds;

            const minutes =

                Math.floor(matchTime / 60);

            const seconds =

                matchTime % 60;

            const formattedMinutes =

                String(minutes).padStart(2, "0");

            const formattedSeconds =

                String(seconds).padStart(2, "0");

            timer.textContent =

                formattedMinutes +

                ":" +

                formattedSeconds;

        }

        updateWatchTimer();

        setInterval(

            updateWatchTimer,

            50

        );

        const chatMessages =

            document.getElementById("chatMessages");

        const fakeComments = [

            ["Sarwar7", "What a match! 🔥"],

            ["Shafin10", "That finish was crazy!"],

            ["Nolan2", "Great defending!"],

            ["Farha8", "This match is getting intense!"],

            ["Sanchez", "Amazing gameplay!"],

            ["RonaldoFan", "What a goal! 🔥🔥"]

        ];

        let commentIndex = 0;

        function addLiveComment() {

            if (!chatMessages) {

                return;

            }

            const comment =

                fakeComments[

                    commentIndex % fakeComments.length

                ];

            const messageDiv =

                document.createElement("div");

            messageDiv.className =

                "chat-message";

            messageDiv.innerHTML =

                '<div class="chat-user">' +

                comment[0] +

                '</div>' +

                '<div class="chat-text">' +

                comment[1] +

                '</div>' +

                '<div class="chat-time">' +

                'LIVE' +

                '</div>';

            chatMessages.appendChild(

                messageDiv

            );

            chatMessages.scrollTop =

                chatMessages.scrollHeight;

            commentIndex++;

        }

        setInterval(

            addLiveComment,

            5000

        );

        if (chatMessages) {

            chatMessages.scrollTop =

                chatMessages.scrollHeight;

        }

        const closeChat =

            document.getElementById("closeChat");

        const openChat =

            document.getElementById("openChat");

        const chatCard =

            document.getElementById("chatCard");

        const mainWatchArea =

            document.getElementById("mainWatchArea");

        closeChat.addEventListener(

            "click",

            function() {

                chatCard.classList.add("hidden");

                mainWatchArea.classList.add(

                    "chat-hidden"

                );

                openChat.classList.add("show");

            }

        );

        openChat.addEventListener(

            "click",

            function() {

                chatCard.classList.remove("hidden");

                mainWatchArea.classList.remove(

                    "chat-hidden"

                );

                openChat.classList.remove("show");

            }

        );

        const videoContainer =

            document.getElementById("videoContainer");

        videoContainer.addEventListener(

            "dblclick",

            function() {

                if (!document.fullscreenElement) {

                    if (

                        videoContainer.requestFullscreen

                    ) {

                        videoContainer.requestFullscreen();

                    } else if (

                        videoContainer.webkitRequestFullscreen

                    ) {

                        videoContainer.webkitRequestFullscreen();

                    } else if (

                        videoContainer.msRequestFullscreen

                    ) {

                        videoContainer.msRequestFullscreen();

                    }

                } else {

                    if (document.exitFullscreen) {

                        document.exitFullscreen();

                    } else if (

                        document.webkitExitFullscreen

                    ) {

                        document.webkitExitFullscreen();

                    } else if (

                        document.msExitFullscreen

                    ) {

                        document.msExitFullscreen();

                    }

                }

            }

        );

    </script>

</body>

</html>