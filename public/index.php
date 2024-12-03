<?php
require 'vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;

// Cek apakah parameter GET lengkap
if (!isset($_GET['qris']) || !isset($_GET['nominal']) || !isset($_GET['biaya_layanan'])) {
    die("Error: Parameter 'qris', 'nominal', dan 'biaya_layanan' wajib diisi.");
}

// Ambil data dari parameter GET
$qris = $_GET['qris'];
$qty = $_GET['nominal'];
$yn = strtolower($_GET['biaya_layanan']); // y/n untuk biaya layanan

$tax = null; // Default biaya layanan kosong
if ($yn == 'y') {
    if (!isset($_GET['jenis_biaya']) || !isset($_GET['nilai_biaya'])) {
        die("Error: Parameter 'jenis_biaya' dan 'nilai_biaya' wajib diisi jika 'biaya_layanan' = y.");
    }
    
    $fee = strtolower($_GET['jenis_biaya']); // r = rupiah, p = persen
    $fee_value = $_GET['nilai_biaya'];

    if ($fee == 'r') {
        $tax = "55020256" . sprintf("%02d", strlen($fee_value)) . $fee_value;
    } elseif ($fee == 'p') {
        $tax = "55020357" . sprintf("%02d", strlen($fee_value)) . $fee_value;
    } else {
        die("Error: Jenis biaya layanan harus 'r' untuk rupiah atau 'p' untuk persen.");
    }
}

$qris = substr($qris, 0, -4);
$step1 = str_replace("010211", "010212", $qris);
$step2 = explode("5802ID", $step1);
$uang = "54" . sprintf("%02d", strlen($qty)) . $qty;

if (empty($tax)) {
    $uang .= "5802ID";
} else {
    $uang .= $tax . "5802ID";
}

$fix = trim($step2[0]) . $uang . trim($step2[1]);
$fix .= ConvertCRC16($fix);

$qrBase64 = generateQRCodeFile($fix);

// Output hasil dalam format JSON
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'data' => $qrBase64,
]);

function generateQRCodeFile($data) {
    try {
        // Buat QR Code menggunakan endroid/qr-code
        $result = Builder::create()
            ->data($data)    // Data untuk QR Code
            ->size(300)      // Ukuran QR Code
            ->margin(10)     // Margin QR Code
            ->build();

        // Tentukan nama file QR Code
        $fileName = 'qrcode.png';

        // Simpan gambar QR Code ke file
        file_put_contents($fileName, $result->getString());

        // Kembalikan URL file gambar QR Code
        return "https://amfcode.my.id/qrapi/images/" . $fileName;

    } catch (Exception $e) {
        return "Error generating QR Code: " . $e->getMessage();
    }
}


function ConvertCRC16($str) {
    function charCodeAt($str, $i) {
        return ord(substr($str, $i, 1));
    }
    $crc = 0xFFFF;
    $strlen = strlen($str);
    for ($c = 0; $c < $strlen; $c++) {
        $crc ^= charCodeAt($str, $c) << 8;
        for ($i = 0; $i < 8; $i++) {
            if ($crc & 0x8000) {
                $crc = ($crc << 1) ^ 0x1021;
            } else {
                $crc = $crc << 1;
            }
        }
    }
    $hex = $crc & 0xFFFF;
    $hex = strtoupper(dechex($hex));
    if (strlen($hex) == 3) $hex = "0" . $hex;
    return $hex;
}
