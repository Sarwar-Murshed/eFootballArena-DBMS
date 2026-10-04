<?php

session_start();
require_once "db.php";

header("Content-Type: application/json");

if (!isset($_SESSION["Player_ID"])) {

    echo json_encode([
        "success" => false,
        "message" => "Not logged in."
    ]);

    exit();
}

$auctionID =
    (int)($_GET["auction_id"] ?? 0);


if ($auctionID <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid auction."
    ]);

    exit();
}


$sql = $conn->prepare("
    SELECT
        Auction_Status,
        Current_Bid,
        Auction_Ends_At
    FROM Auctions
    WHERE Auction_ID = ?
");

$sql->bind_param(
    "i",
    $auctionID
);

$sql->execute();

$auction =
    $sql
    ->get_result()
    ->fetch_assoc();


if (!$auction) {

    echo json_encode([
        "success" => false,
        "message" => "Auction not found."
    ]);

    exit();
}


if (
    $auction["Auction_Status"] === "Live" &&
    !empty($auction["Auction_Ends_At"]) &&
    strtotime($auction["Auction_Ends_At"]) <= time()
) {

    $finish = $conn->prepare("
        UPDATE Auctions
        SET Auction_Status = 'Sold'
        WHERE Auction_ID = ?
        AND Auction_Status = 'Live'
    ");

    $finish->bind_param(
        "i",
        $auctionID
    );

    $finish->execute();

    $auction["Auction_Status"] = "Sold";
}


echo json_encode([

    "success" => true,

    "current_bid" =>
        $auction["Current_Bid"],

    "auction_ends_at" =>
        $auction["Auction_Ends_At"],

    "status" =>
        $auction["Auction_Status"]

]);

?>