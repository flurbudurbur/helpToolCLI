<?php

if ($argv != null && count($argv) > 1) {
    $input = $argv;
} else {
    echo "no input\n";
    exit;
}

$helpCMD = [
    "global" => [
        "help: Show this menu",
        "month: Show month",
        "year: Show year"
    ],
    "month" => [
        "Stuff",
        "more Exmplanations1"
    ]
];

$isInvalid = false;
$validCMDs = [
    "help",
    "month" => [
        "1",
        "2",
        "3",
        "4",
        "5",
        "6",
        "7",
        "8",
        "9",
        "10",
        "11",
        "12"
    ],
    "year" => [
        "2020",
        "2021",
        "2022",
        "2023",
        "2024",
        "2025",
        "2026",
        "2027",
        "2028",
        "2029",
        "2030"
    ]
];


// get html from url
function getHTML(string $url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

echo getHTML("https://raw.githubusercontent.com/flurbudurbur/helpToolCLI/main/help.md");

die();

if (array_key_exists(strtolower($input[1]), $validCMDs)) {
    if (is_array($validCMDs[strtolower($input[1])])) {
        if (!in_array(strtolower($input[2]), $validCMDs[strtolower($input[1])])) {
            $isInvalid = true;
        }
    }
} else {
    $isInvalid = true;
}

// if last variable is help
if (strtolower($input[count($input) - 1]) == "help" || $isInvalid) {
    if (count($input) == 2) {
        $helpChoice = "global";
    } else {
        $helpChoice = strtolower($input[count($input) - 2]);
    }

    foreach ($helpCMD[$helpChoice] as $helpLine) {
        echo $helpLine . "\n";
    }
}
