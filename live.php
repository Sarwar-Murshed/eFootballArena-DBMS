<?php

session_start();

require_once "db.php";

if (!isset($_SESSION["Gamer_Tag"])) {

    header("Location: login.php");

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
        WHERE ls.Status = 'Live'
        ORDER BY ls.Stream_ID DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Live Matches | eFootballArena</title>

    <link rel="stylesheet" href="dashboard.css">

    <link rel="stylesheet" href="live.css">

</head>

<body>

    <nav class="top-nav">

        <div class="logo-area">

            <div class="football-logo">⚽</div>

            <span>eFootballArena</span>

        </div>

        <div class="nav-links">

            <a href="dashboard.php">Home</a>

            <a href="tournament.php">Tournaments</a>

            <a href="matches.php">Matches</a>

            <a href="teams.php">My Team</a>

            <a href="rankings.php">Rankings</a>

            <a href="player_features.php">Players</a>

            <a href="auction.php">Auctions</a>

            <a href="live.php" class="active">Live</a>

        </div>

    </nav>

    <main class="live-page">

        <div class="page-heading">

            <div>

                <span class="small-heading">eFOOTBALL ARENA</span>

                <h1>Live Matches</h1>

                <p>Watch the action live from the arena.</p>

            </div>

            <a href="dashboard.php" class="back-button">
                ← Dashboard
            </a>

        </div>

        <?php

        if (mysqli_num_rows($result) > 0) {

            while ($stream = mysqli_fetch_assoc($result)) {

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

        ?>

        <section class="live-card">

            <div class="live-header">

                <div class="live-badge">

                    <span class="live-dot"></span>

                    LIVE NOW

                </div>

                <div class="stream-status">

                    LIVE

                </div>

            </div>

            <div class="match-area">

                <div class="player-side player-left">

                    <div class="player-name">

                        <?php
                        echo htmlspecialchars($player1_display);
                        ?>

                    </div>

                    <div class="club-name">

                        <?php
                        echo htmlspecialchars(
                            $stream["Club_Player1"]
                        );
                        ?>

                    </div>

                </div>

                <div class="score-area">

                    <div class="score">

                        <span>

                            <?php
                            echo htmlspecialchars($score1);
                            ?>

                        </span>

                        <strong>-</strong>

                        <span>

                            <?php
                            echo htmlspecialchars($score2);
                            ?>

                        </span>

                    </div>

                    <div class="match-timer">

                        48:23

                    </div>

                </div>

                <div class="player-side player-right">

                    <div class="player-name">

                        <?php
                        echo htmlspecialchars($player2_display);
                        ?>

                    </div>

                    <div class="club-name">

                        <?php
                        echo htmlspecialchars(
                            $stream["Club_Player2"]
                        );
                        ?>

                    </div>

                </div>

            </div>

            <div class="match-information">

                <div class="tournament-name">

                    <?php
                    echo htmlspecialchars(
                        $stream["Tournament_Name"]
                    );
                    ?>

                </div>

                <div class="round-name">

                    <?php
                    echo htmlspecialchars(
                        $stream["Match_Round"]
                    );
                    ?>

                </div>

            </div>

            <div class="viewer-count">

                <span class="eye-icon">👁</span>

                <span>

                    <span
                        class="viewer-number"
                        data-viewers="<?php echo (int)$stream["Viewer_Count"]; ?>"
                    >

                        <?php
                        echo number_format(
                            $stream["Viewer_Count"]
                        );
                        ?>

                    </span>

                </span>

                <span>Viewers</span>

            </div>

            <div class="watch-area">

                <a
                    href="live_watch.php?stream_id=<?php echo (int)$stream["Stream_ID"]; ?>"
                    class="watch-button"
                >

                    ▶ WATCH STREAM

                </a>

            </div>

            <div class="live-details">

                <div class="details-box">

                    <div class="details-title">

                        MATCH INFORMATION

                    </div>

                    <div class="details-content">

                        <div>

                            <span>Match ID</span>

                            <strong>

                                #

                                <?php
                                echo htmlspecialchars(
                                    $stream["Match_ID"]
                                );
                                ?>

                            </strong>

                        </div>

                        <div>

                            <span>Round</span>

                            <strong>

                                <?php
                                echo htmlspecialchars(
                                    $stream["Match_Round"]
                                );
                                ?>

                            </strong>

                        </div>

                        <div>

                            <span>Start Time</span>

                            <strong>

                                <?php
                                echo htmlspecialchars(
                                    $stream["Start_Time"]
                                );
                                ?>

                            </strong>

                        </div>

                    </div>

                </div>

                <div class="details-box">

                    <div class="details-title">

                        LIVE CHAT

                    </div>

                    <div class="chat-area">

                        <?php

                        $stream_id = (int)$stream["Stream_ID"];

                        $chat_sql = "SELECT
                                        lc.Message,
                                        lc.Message_Time,
                                        p.Gamer_Tag
                                     FROM Live_Chat lc
                                     JOIN Players p
                                        ON lc.Player_ID = p.Player_ID
                                     WHERE lc.Stream_ID = $stream_id
                                     ORDER BY lc.Message_Time ASC";

                        $chat_result = mysqli_query(
                            $conn,
                            $chat_sql
                        );

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
                                    preg_replace(
                                        '/\d+$/',
                                        '',
                                        $chat["Gamer_Tag"]
                                    )
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

                                <?php
                                echo htmlspecialchars(
                                    $chat["Message_Time"]
                                );
                                ?>

                            </div>

                        </div>

                        <?php

                            }

                        } else {

                        ?>

                        <div class="empty-message">

                            No chat messages yet.

                        </div>

                        <?php

                        }

                        ?>

                    </div>

                </div>

            </div>

            <div class="reviews-section">

                <div class="reviews-title">

                    AUDIENCE REVIEWS

                </div>

                <?php

                $match_id = (int)$stream["Match_ID"];

                $review_sql = "SELECT
                                    ar.Audience_Name,
                                    ar.Rating,
                                    ar.Review_Text,
                                    ar.Review_Date,
                                    p.Gamer_Tag
                               FROM Audience_Reviews ar
                               JOIN Players p
                                   ON ar.Player_ID = p.Player_ID
                               WHERE ar.Match_ID = $match_id
                               ORDER BY ar.Review_Date DESC";

                $review_result = mysqli_query(
                    $conn,
                    $review_sql
                );

                if (
                    $review_result &&
                    mysqli_num_rows($review_result) > 0
                ) {

                    while (
                        $review = mysqli_fetch_assoc(
                            $review_result
                        )
                    ) {

                ?>

                <div class="review-card">

                    <div class="review-top">

                        <div class="review-author">

                            <?php
                            echo htmlspecialchars(
                                $review["Audience_Name"]
                            );
                            ?>

                        </div>

                        <div class="review-rating">

                            <?php
                            echo htmlspecialchars(
                                $review["Rating"]
                            );
                            ?>/5 ★

                        </div>

                    </div>

                    <div class="review-player">

                        Player:

                        <?php
                        echo htmlspecialchars(
                            preg_replace(
                                '/\d+$/',
                                '',
                                $review["Gamer_Tag"]
                            )
                        );
                        ?>

                    </div>

                    <div class="review-text">

                        <?php
                        echo htmlspecialchars(
                            $review["Review_Text"]
                        );
                        ?>

                    </div>

                    <div class="review-date">

                        <?php
                        echo htmlspecialchars(
                            $review["Review_Date"]
                        );
                        ?>

                    </div>

                </div>

                <?php

                    }

                } else {

                ?>

                <div class="empty-message">

                    No audience reviews yet.

                </div>

                <?php

                }

                ?>

            </div>

        </section>

        <?php

            }

        } else {

        ?>

        <div class="no-live">

            <div class="no-live-icon">

                ⚽

            </div>

            <h2>No Live Matches</h2>

            <p>

                There are currently no matches being streamed live.

            </p>

            <a href="matches.php" class="back-button">

                View Matches

            </a>

        </div>

        <?php

        }

        ?>

    </main>

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

    <script src="music.js"></script>

    <script>

        document.querySelectorAll(".viewer-number").forEach(function(viewer) {

            let viewerCount = parseInt(
                viewer.dataset.viewers,
                10
            ) || 0;

            function updateViewers() {

                const change =
                    Math.floor(Math.random() * 31) - 15;

                viewerCount += change;

                if (viewerCount < 1150) {

                    viewerCount = 1150;

                }

                if (viewerCount > 1400) {

                    viewerCount = 1400;

                }

                viewer.textContent =
                    viewerCount.toLocaleString();

                const nextUpdate =
                    Math.floor(Math.random() * 3000) + 2000;

                setTimeout(
                    updateViewers,
                    nextUpdate
                );

            }

            const firstUpdate =
                Math.floor(Math.random() * 3000) + 2000;

            setTimeout(
                updateViewers,
                firstUpdate
            );

        });

        document.querySelectorAll(".match-timer").forEach(function(timer) {

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

            function updateTimer() {

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

            updateTimer();

            setInterval(
                updateTimer,
                50
            );

        });

        document.querySelectorAll(".chat-area").forEach(function(chatArea) {

            let userIsScrolling = false;

            function isAtBottom() {

                return (
                    chatArea.scrollHeight -
                    chatArea.scrollTop -
                    chatArea.clientHeight
                ) <= 50;

            }

            chatArea.addEventListener("scroll", function() {

                if (isAtBottom()) {

                    userIsScrolling = false;

                } else {

                    userIsScrolling = true;

                }

            });

            setTimeout(function() {

                chatArea.scrollTop =
                    chatArea.scrollHeight;

            }, 100);

            const positiveComments = [

                "What a great match!",

                "Amazing goal!",

                "What a save!",

                "This match is incredible!",

                "Great attack!",

                "Beautiful play!",

                "That was brilliant!",

                "What a chance!",

                "Excellent defending!",

                "The match is getting intense!"

            ];

            const negativeComments = [

                "That was a poor pass.",

                "What a missed chance!",

                "The defense looks weak.",

                "That should have been a goal.",

                "Poor finishing!",

                "Too many mistakes!",

                "The goalkeeper should have done better.",

                "That was disappointing.",

                "They need to improve their passing.",

                "Bad decision there."

            ];

            const gamers = [

                "Sarwar",

                "Shafin",

                "Farha",

                "Nolan"

            ];

            function addLiveComment() {

                const wasAtBottom =
                    isAtBottom();

                const isPositive =
                    Math.random() > 0.45;

                const comments =
                    isPositive
                        ? positiveComments
                        : negativeComments;

                const message =
                    comments[
                        Math.floor(
                            Math.random() * comments.length
                        )
                    ];

                const gamer =
                    gamers[
                        Math.floor(
                            Math.random() * gamers.length
                        )
                    ];

                const now =
                    new Date();

                const time =
                    now.toLocaleTimeString([], {
                        hour: "2-digit",
                        minute: "2-digit"
                    });

                const messageDiv =
                    document.createElement("div");

                messageDiv.className =
                    "chat-message";

                messageDiv.innerHTML = `

                    <div class="chat-user">

                        ${gamer}

                    </div>

                    <div class="chat-text">

                        ${message}

                    </div>

                    <div class="chat-time">

                        ${time}

                    </div>

                `;

                chatArea.appendChild(
                    messageDiv
                );

                if (
                    wasAtBottom &&
                    !userIsScrolling
                ) {

                    chatArea.scrollTo({

                        top: chatArea.scrollHeight,

                        behavior: "smooth"

                    });

                }

            }

            setInterval(function() {

                addLiveComment();

            }, 5000);

        });

    </script>

</body>

</html>