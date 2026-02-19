<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/QRGenerator.php';

use App\QRGenerator;

header("Access-Control-Allow-Origin: *");

$qr = new QRGenerator();

$method = $_SERVER['REQUEST_METHOD'];

// Detectar ruta correctamente en XAMPP
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/QRExam/public', '', $uri);
$uri = str_replace('/index.php', '', $uri);

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header("Content-Type: application/json");
    echo json_encode($data);
    exit;
}

/*
|--------------------------------------------------------------------------
| ENDPOINT: GET tipos
|--------------------------------------------------------------------------
*/

if ($uri === '/api/qr/types' && $method === 'GET') {
    jsonResponse([
        "types" => ["text", "url", "wifi", "geo"]
    ]);
}

/*
|--------------------------------------------------------------------------
| ENDPOINT: GET generar QR (para navegador)
|--------------------------------------------------------------------------
*/

if ($uri === '/api/qr/generate' && $method === 'GET') {

    try {

        $type = $_GET['type'] ?? null;
        $size = $_GET['size'] ?? 300;
        $error = $_GET['error_correction'] ?? 'M';

        $qr->validateGeneralParams($size, $error);

        switch ($type) {

            case "text":
                $data = $_GET['content'] ?? null;
                break;

            case "url":
                $data = $_GET['content'] ?? null;
                $qr->validateUrl($data);
                break;

            case "wifi":
                $data = $qr->generateWifi(
                    $_GET['ssid'],
                    $_GET['password'] ?? '',
                    $_GET['security'] ?? 'WPA'
                );
                break;

            case "geo":
                $data = $qr->generateGeo(
                    $_GET['lat'],
                    $_GET['lng']
                );
                break;

            default:
                jsonResponse(["error"=>"Tipo no soportado"],400);
        }

        $image = $qr->generate($data, $size, $error);

        header("Content-Type: image/png");
        echo $image;
        exit;

    } catch (Exception $e) {
        jsonResponse(["error"=>$e->getMessage()],400);
    }
}

/*
|--------------------------------------------------------------------------
| ENDPOINT no encontrado
|--------------------------------------------------------------------------
*/

jsonResponse(["error"=>"Endpoint no encontrado"],404);
