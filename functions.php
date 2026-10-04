<?php

function getClubJersey($playerName, $gamerTag) {

    $clubMap = [
        "Sarwar7"  => "Madrid",
        "Shafin10" => "Barca",
        "Nolan2"   => "ManU",
        "Farha8"   => "PSG"
    ];

    $club = $clubMap[$gamerTag] ?? null;
    if (!$club) return null;

    $playerName = trim($playerName);

    $nameMap = [
        "Jude Bellingham"       => "Bellingham",
        "Karim Benzema"         => "Benzema",
        "Bruno Fernandes"       => "Bruno",
        "Thibaut Courtois"      => "Courtois",
        "Virgil van Dijk"       => "Dijk",
        "Kevin De Bruyne"       => "KDB",
        "Toni Kroos"            => "Kroos",
        "khvicha kvaratskhelia" => "Kvara",
        "Khvicha Kvaratskhelia" => "Kvara",
        "Robert Lewandowski"    => "Lewandowski",
        "Romelu Lukaku"         => "Lukaku",
        "Kylian Mbappe"         => "Mbappe",
        "Lionel Messi"          => "Messi",
        "Neymar Jr"             => "Neymar",
        "Jan Oblak"             => "Oblak",
        "Pedri"                 => "Pedri",
        "Raphinha"              => "Raphinha",
        "Declan Rice"           => "Rice",
        "Cristiano Ronaldo"     => "Ronaldo",
        "Bukayo Saka"           => "Saka",
        "Federico Valverde"     => "Valvarde",
        "Valverde"              => "Valvarde",
        "Valvarde"              => "Valvarde",
        "Rodri"                 => "Rodri"
    ];

    $prefix = $nameMap[$playerName] ?? null;

    if (!$prefix) {
        $parts = explode(" ", $playerName);
        $prefix = end($parts);
    }

    $extensions = ["png", "jpg", "jpeg", "PNG", "JPG"];

    foreach ($extensions as $ext) {
        $file = "Player_Jersey/" . $prefix . "_" . $club . "." . $ext;
        if (file_exists($file)) {
            return $file;
        }
    }

    if (strtolower($prefix) === "dijk" && $club === "ManU") {
        foreach ($extensions as $ext) {
            $file = "Player_Jersey/Dijk_Man_U." . $ext;
            if (file_exists($file)) {
                return $file;
            }
        }
    }

    return null;
}

function getPlayerImage($name, $type, $player_images, $gamerTag = null) {

    if ($gamerTag) {
        $clubJersey = getClubJersey($name, $gamerTag);
        if ($clubJersey) {
            return $clubJersey;
        }
    }

    if ($type === "Bot") {
        return "Players/bot.jpg";
    }

    $file = $player_images[$name] ?? "";
    return $file ? "Players/" . $file : "Players/bot.jpg";
}

?>