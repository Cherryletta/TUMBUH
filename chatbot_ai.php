<?php
header("Content-Type: application/json");

$API_KEY = "AIzaSyAmJIWPvVlrgZrt7U8jG23hDDxUAqwIA9Y";

$data = json_decode(file_get_contents("php://input"), true);
$userMessage = trim($data['message'] ?? '');

if ($userMessage === '') {
    echo json_encode(["reply" => "Pesan kosong"]);
    exit;
}

// ✅ MODEL AMAN
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $API_KEY;


$payload = [
    "contents" => [
        [
            "parts" => [
                [
                    "text" =>
                    "Kamu adalah Bot TUMBUH, asisten ramah untuk website lingkungan.\n\n" .
                    "User: " . $userMessage
                ]
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode($payload)
]);

$response = curl_exec($ch);

if ($response === false) {
    echo json_encode([
        "reply" => "Server tidak bisa menghubungi Gemini (cURL error)"
    ]);
    exit;
}

curl_close($ch);

$result = json_decode($response, true);

// 🔴 ERROR ASLI
if (isset($result['error'])) {
    echo json_encode([
        "reply" => "ERROR GEMINI",
        "detail" => $result['error']
    ]);
    exit;
}

// ✅ AMBIL JAWABAN
if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    echo json_encode([
        "reply" => "Format respons tidak dikenali",
        "raw" => $result
    ]);
    exit;
}

echo json_encode([
    "reply" => $result['candidates'][0]['content']['parts'][0]['text']
]);
