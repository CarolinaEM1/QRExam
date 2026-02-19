<?php

namespace App;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class QRGenerator
{
    private $validErrorLevels = ['L', 'M', 'Q', 'H'];

    public function validateGeneralParams($size, $errorCorrection)
    {
        if ($size < 100 || $size > 1000) {
            throw new \Exception("El tamaño debe estar entre 100 y 1000 px", 400);
        }

        if (!in_array($errorCorrection, $this->validErrorLevels)) {
            throw new \Exception("Nivel de corrección inválido (L,M,Q,H)", 400);
        }
    }

    public function generate($data, $size, $errorCorrection)
    {
        try {
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($data)
                ->size($size)
                ->margin(10)
                ->build();

            return $result->getString();

        } catch (\Exception $e) {
            throw new \Exception("Error generando QR", 500);
        }
    }

    public function generateWifi($ssid, $password, $type)
    {
        if (!in_array($type, ['WPA', 'WEP', 'nopass'])) {
            throw new \Exception("Tipo de seguridad WiFi inválido", 400);
        }

        return "WIFI:T:$type;S:$ssid;P:$password;;";
    }

    public function generateGeo($lat, $lng)
    {
        if (!is_numeric($lat) || !is_numeric($lng)) {
            throw new \Exception("Coordenadas inválidas", 400);
        }

        return "geo:$lat,$lng";
    }

    public function validateUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \Exception("URL inválida", 400);
        }
    }
}
