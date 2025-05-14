<?php
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Получаем API-ключ
$apiKey = $_ENV['YANDEX_API_KEY'] ?? null;
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'API-ключ не указан в .env']);
    exit;
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Метод не поддерживается']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$address = $input['address'] ?? '';

if (empty($address)) {
    http_response_code(400);
    echo json_encode(['error' => 'Адрес не указан']);
    exit;
}

$url = "https://geocode-maps.yandex.ru/1.x/?apikey=" . urlencode($apiKey) . "&geocode=" . urlencode($address) . "&format=json";
error_log("Request URL: " . $url);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка cURL: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}
error_log("Yandex Response: " . $response);
curl_close($ch);

$data = json_decode($response, true);
$geoObject = $data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject'] ?? null;

if (!$geoObject) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Адрес не найден или ошибка API',
        'yandex_response' => $data,
        'http_status' => http_response_code()
    ]);
    exit;
}

$structuredAddress = $geoObject['metaDataProperty']['GeocoderMetaData']['Address']['Components'];
$coordinates = array_map('floatval', explode(' ', $geoObject['Point']['pos']));
$coordinates = array_reverse($coordinates);

$metroUrl = "https://geocode-maps.yandex.ru/1.x/?apikey=" . urlencode($apiKey) . "&geocode=" . urlencode($coordinates[1] . ',' . $coordinates[0]) . "&kind=metro&format=json";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $metroUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$metroResponse = curl_exec($ch);
if ($metroResponse === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка cURL для метро: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}
curl_close($ch);

$metroData = json_decode($metroResponse, true);
$metro = $metroData['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['name'] ?? 'Метро не найдено';

$result = [
    'structuredAddress' => $structuredAddress,
    'coordinates' => $coordinates,
    'metro' => $metro
];

echo json_encode($result);
?>