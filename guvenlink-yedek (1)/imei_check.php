<?php
// CORS için gerekli başlıkları ayarla
header('Content-Type: application/json');

// POST isteği kontrolü
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// IMEI numarasını al
$imei = isset($_POST['imei']) ? $_POST['imei'] : '';

// IMEI formatını kontrol et
if (!preg_match('/^\d{15}$/', $imei) || !validateImei($imei)) {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz IMEI numarası']);
    exit;
}

// İMEI numarasını doğrula (Luhn algoritması)
function validateImei($imei) {
    $sum = 0;
    for ($i = 0; $i < 14; $i++) {
        $d = intval($imei[$i]);
        if ($i % 2 == 1) {
            $d *= 2;
            if ($d > 9) $d -= 9;
        }
        $sum += $d;
    }
    
    $checkDigit = (10 - ($sum % 10)) % 10;
    return $checkDigit == intval($imei[14]);
}

// TAC (İlk 8 hane) veritabanı - GSMA veritabanından alınan örnek veriler
$tacDatabase = [
    // Apple
    '01124500' => 'Apple iPhone 4',
    '01161200' => 'Apple iPhone 4S',
    '01300600' => 'Apple iPhone 5',
    '35848506' => 'Apple iPhone 5',
    '35849406' => 'Apple iPhone 5',
    '35315006' => 'Apple iPhone 5S',
    '35325006' => 'Apple iPhone 5S',
    '35489306' => 'Apple iPhone 5C',
    '35501206' => 'Apple iPhone 5S',
    '35576406' => 'Apple iPhone 5S',
    '35577206' => 'Apple iPhone 5S',
    '35854806' => 'Apple iPhone 6',
    '35855406' => 'Apple iPhone 6',
    '35856106' => 'Apple iPhone 6',
    '35856506' => 'Apple iPhone 6',
    '35856806' => 'Apple iPhone 6',
    '35857106' => 'Apple iPhone 6',
    '35857406' => 'Apple iPhone 6',
    '35857505' => 'Apple iPhone 6 Plus',
    '35858905' => 'Apple iPhone 6 Plus',
    '35859005' => 'Apple iPhone 6 Plus',
    '35859305' => 'Apple iPhone 6 Plus',
    '35859505' => 'Apple iPhone 6 Plus',
    '35861905' => 'Apple iPhone 6 Plus',
    '35862305' => 'Apple iPhone 6 Plus',
    '35863905' => 'Apple iPhone 6 Plus',
    '35960006' => 'Apple iPhone 7',
    '35960107' => 'Apple iPhone 7 Plus',
    '35332508' => 'Apple iPhone 8',
    '35332608' => 'Apple iPhone 8 Plus',
    '35332808' => 'Apple iPhone X',
    '35728707' => 'Apple iPhone XR',
    '35728907' => 'Apple iPhone XS',
    '35728807' => 'Apple iPhone XS Max',
    '35376209' => 'Apple iPhone 11',
    '35376309' => 'Apple iPhone 11 Pro',
    '35376409' => 'Apple iPhone 11 Pro Max',
    '35396910' => 'Apple iPhone 12',
    '35397010' => 'Apple iPhone 12 Mini',
    '35397110' => 'Apple iPhone 12 Pro',
    '35397210' => 'Apple iPhone 12 Pro Max',
    '35324811' => 'Apple iPhone 13',
    '35324911' => 'Apple iPhone 13 Mini',
    '35325011' => 'Apple iPhone 13 Pro',
    '35325111' => 'Apple iPhone 13 Pro Max',
    '35704313' => 'Apple iPhone 14',
    '35704413' => 'Apple iPhone 14 Plus',
    '35704513' => 'Apple iPhone 14 Pro',
    '35704613' => 'Apple iPhone 14 Pro Max',
    '35893514' => 'Apple iPhone 15',
    '35893614' => 'Apple iPhone 15 Plus',
    '35893714' => 'Apple iPhone 15 Pro',
    '35893814' => 'Apple iPhone 15 Pro Max',
    
    // Samsung
    '35251108' => 'Samsung Galaxy S10',
    '35251208' => 'Samsung Galaxy S10+',
    '35251308' => 'Samsung Galaxy S10e',
    '35269209' => 'Samsung Galaxy S20',
    '35269309' => 'Samsung Galaxy S20+',
    '35269409' => 'Samsung Galaxy S20 Ultra',
    '35295810' => 'Samsung Galaxy S21',
    '35295910' => 'Samsung Galaxy S21+',
    '35296010' => 'Samsung Galaxy S21 Ultra',
    '35370812' => 'Samsung Galaxy S22',
    '35370912' => 'Samsung Galaxy S22+',
    '35371012' => 'Samsung Galaxy S22 Ultra',
    '35432213' => 'Samsung Galaxy S23',
    '35432313' => 'Samsung Galaxy S23+',
    '35432413' => 'Samsung Galaxy S23 Ultra',
    '35683414' => 'Samsung Galaxy S24',
    '35683514' => 'Samsung Galaxy S24+',
    '35683614' => 'Samsung Galaxy S24 Ultra',
    '35251408' => 'Samsung Galaxy Note 10',
    '35251508' => 'Samsung Galaxy Note 10+',
    '35296110' => 'Samsung Galaxy Note 20',
    '35296210' => 'Samsung Galaxy Note 20 Ultra',
    '35462008' => 'Samsung Galaxy A51',
    '35462108' => 'Samsung Galaxy A71',
    '35630009' => 'Samsung Galaxy A52',
    '35630109' => 'Samsung Galaxy A72',
    '35631010' => 'Samsung Galaxy A53',
    '35631110' => 'Samsung Galaxy A73',
    '35631211' => 'Samsung Galaxy A54',
    '35631311' => 'Samsung Galaxy A34',
    '35683714' => 'Samsung Galaxy A55',
    '35683814' => 'Samsung Galaxy A35',
    
    // Xiaomi
    '86263104' => 'Xiaomi Redmi Note 10',
    '86263204' => 'Xiaomi Redmi Note 10 Pro',
    '86281205' => 'Xiaomi Redmi Note 11',
    '86281305' => 'Xiaomi Redmi Note 11 Pro',
    '86286406' => 'Xiaomi Redmi Note 12',
    '86286506' => 'Xiaomi Redmi Note 12 Pro',
    '86295307' => 'Xiaomi Redmi Note 13',
    '86295407' => 'Xiaomi Redmi Note 13 Pro',
    '86193604' => 'Xiaomi Mi 11',
    '86193704' => 'Xiaomi Mi 11 Ultra',
    '86232305' => 'Xiaomi 12',
    '86232405' => 'Xiaomi 12 Pro',
    '86232505' => 'Xiaomi 12 Ultra',
    '86275606' => 'Xiaomi 13',
    '86275706' => 'Xiaomi 13 Pro',
    '86275806' => 'Xiaomi 13 Ultra',
    '86295507' => 'Xiaomi 14',
    '86295607' => 'Xiaomi 14 Pro',
    
    // Huawei
    '86209203' => 'Huawei P30',
    '86209303' => 'Huawei P30 Pro',
    '86225904' => 'Huawei P40',
    '86226004' => 'Huawei P40 Pro',
    '86226104' => 'Huawei P40 Pro+',
    '86241105' => 'Huawei P50',
    '86241205' => 'Huawei P50 Pro',
    '86272806' => 'Huawei P60',
    '86272906' => 'Huawei P60 Pro',
    '86209403' => 'Huawei Mate 30',
    '86209503' => 'Huawei Mate 30 Pro',
    '86226204' => 'Huawei Mate 40',
    '86226304' => 'Huawei Mate 40 Pro',
    '86241305' => 'Huawei Mate 50',
    '86241405' => 'Huawei Mate 50 Pro',
    '86273006' => 'Huawei Mate 60',
    '86273106' => 'Huawei Mate 60 Pro',
    
    // OnePlus
    '86724104' => 'OnePlus 9',
    '86724204' => 'OnePlus 9 Pro',
    '86736305' => 'OnePlus 10 Pro',
    '86752506' => 'OnePlus 11',
    '86766707' => 'OnePlus 12',
    
    // Google
    '35255108' => 'Google Pixel 5',
    '35259709' => 'Google Pixel 6',
    '35259809' => 'Google Pixel 6 Pro',
    '35280810' => 'Google Pixel 7',
    '35280910' => 'Google Pixel 7 Pro',
    '35307711' => 'Google Pixel 8',
    '35307811' => 'Google Pixel 8 Pro',
    
    // Oppo
    '86733805' => 'Oppo Find X5',
    '86733905' => 'Oppo Find X5 Pro',
    '86751006' => 'Oppo Find X6',
    '86751106' => 'Oppo Find X6 Pro',
    '86765207' => 'Oppo Find X7',
    '86765307' => 'Oppo Find X7 Ultra',
    
    // Vivo
    '86738405' => 'Vivo X80',
    '86738505' => 'Vivo X80 Pro',
    '86754106' => 'Vivo X90',
    '86754206' => 'Vivo X90 Pro',
    '86768307' => 'Vivo X100',
    '86768407' => 'Vivo X100 Pro'
];

// İMEI'den cihaz bilgilerini al
function getDeviceInfo($imei) {
    global $tacDatabase;
    
    // İMEI'nin ilk 8 hanesini (TAC) al
    $tac = substr($imei, 0, 8);
    
    // TAC veritabanında eşleşme ara
    if (isset($tacDatabase[$tac])) {
        return ['deviceInfo' => $tacDatabase[$tac]];
    }
    
    // Tam eşleşme yoksa, marka tahmininde bulun
    $possibleDevices = [];
    foreach ($tacDatabase as $dbTac => $device) {
        // İlk 4 hane marka göstergesidir
        if (substr($tac, 0, 4) === substr($dbTac, 0, 4)) {
            $possibleDevices[] = $device;
        }
    }
    
    if (!empty($possibleDevices)) {
        // Benzer markadan bir cihaz
        $brand = explode(' ', $possibleDevices[0])[0];
        return ['deviceInfo' => $brand . ' (Model bilinmiyor)'];
    }
    
    // Hiçbir eşleşme bulunamadı
    return ['deviceInfo' => 'Bilinmeyen cihaz'];
}

// IMEI'yi kontrol et ve sonucu döndür
$result = getDeviceInfo($imei);
echo json_encode($result);
?>