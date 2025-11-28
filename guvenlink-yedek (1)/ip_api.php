<?php
/**
 * IP API - IP Adresi Sorgulama Servisi
 * 
 * Bu script, IP adreslerinin coğrafi konum, ISP ve güvenlik bilgilerini döndürür
 */

// CORS için header'lar
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Eylem parametresi kontrolü
$action = isset($_GET['action']) ? $_GET['action'] : 'lookup';

if ($action === 'client_ip') {
    // Kullanıcının IP adresini döndür
    getClientIP();
} else {
    // IP adresi parametresini al
    $ip = isset($_GET['ip']) ? trim($_GET['ip']) : '';
    
    // IP geçerli mi veya belirtilmiş mi kontrol et
    if (empty($ip)) {
        jsonResponse([
            'error' => true,
            'message' => 'Geçersiz veya boş IP adresi'
        ]);
        exit;
    }
    
    // IP verilerini al ve döndür
    getIPDetails($ip);
}

/**
 * Kullanıcının IP adresini tespit et ve döndür
 */
function getClientIP() {
    $clientIP = '';
    
    // Proxy arkasındaki gerçek IP'yi almak için çeşitli header'ları kontrol et
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $clientIP = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Bu birden fazla IP içerebilir, ilkini al
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $clientIP = trim($ipList[0]);
    } else {
        $clientIP = $_SERVER['REMOTE_ADDR'];
    }
    
    // IPv4 veya IPv6 formatına uygunluğunu doğrula
    if (filter_var($clientIP, FILTER_VALIDATE_IP)) {
        jsonResponse([
            'error' => false,
            'ip' => $clientIP
        ]);
    } else {
        jsonResponse([
            'error' => true,
            'message' => 'IP adresi tespit edilemedi',
            'ip' => '127.0.0.1' // Fallback olarak localhost
        ]);
    }
    
    exit;
}

/**
 * IP adresi detaylarını çeşitli API'lerden alarak döndürür
 */
function getIPDetails($ip) {
    // IP'nin özel bir ağa ait olup olmadığını kontrol et
    if (isPrivateIP($ip)) {
        jsonResponse([
            'error' => false,
            'ip' => $ip,
            'version' => (strpos($ip, ':') !== false) ? 6 : 4,
            'is_private' => true,
            'country' => 'Local Network',
            'country_code' => 'LO',
            'city' => 'Local',
            'isp' => 'Private Network',
            'organization' => 'Private Network',
            'asn' => 'Private',
            'is_datacenter' => false,
            'is_tor' => false,
            'is_proxy' => false,
            'is_anonymous' => false,
            'is_abuser' => false,
            'is_spam' => false,
            'usage_type' => 'Private',
            'security_summary' => 'Bu IP özel bir ağa ait, internet üzerinden erişilemez.'
        ]);
        exit;
    }
    
    // IP Bilgilerini çeş API'lerden al
    $ipInfo = queryIPInfoAPIs($ip);
    
    // Sonuçları standart formatta döndür
    jsonResponse($ipInfo);
    exit;
}

/**
 * IP adresinin özel bir ağa ait olup olmadığını kontrol eder
 */
function isPrivateIP($ip) {
    return filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    ) === false;
}

/**
 * Çeşitli IP bilgi API'lerine sorgu yapar ve sonuçları birleştirir
 */
function queryIPInfoAPIs($ip) {
    $result = [
        'error' => false,
        'ip' => $ip,
        'version' => (strpos($ip, ':') !== false) ? 6 : 4
    ];
    
    // İlk olarak ip-api.com'a sorgu yap (ücretsiz, rate limit var)
    $ipApiData = fetchFromIpApi($ip);
    
    if ($ipApiData) {
        // Temel konum ve ISP bilgilerini bu API'den al
        $result = array_merge($result, $ipApiData);
    }
    
    // IPinfo.io API'den ek bilgiler al (opsiyonel, API key gerektirir)
    $ipInfoData = fetchFromIpInfo($ip);
    
    if ($ipInfoData) {
        // Eksik bilgileri tamamla
        foreach ($ipInfoData as $key => $value) {
            if (!isset($result[$key]) || empty($result[$key])) {
                $result[$key] = $value;
            }
        }
    }
    
    // IPQualityScore veya benzer bir API'den güvenlik bilgileri al
    $securityData = fetchSecurityData($ip);
    
    if ($securityData) {
        // Güvenlik verilerini ekle
        $result = array_merge($result, $securityData);
    }
    
    // Risk faktörleri yoksa varsayılan değerleri ekle
    if (!isset($result['is_datacenter'])) $result['is_datacenter'] = false;
    if (!isset($result['is_tor'])) $result['is_tor'] = false;
    if (!isset($result['is_proxy'])) $result['is_proxy'] = false;
    if (!isset($result['is_anonymous'])) $result['is_anonymous'] = false;
    if (!isset($result['is_abuser'])) $result['is_abuser'] = false;
    if (!isset($result['is_spam'])) $result['is_spam'] = false;
    
    // Güvenlik özeti
    $result['security_summary'] = generateSecuritySummary($result);
    
    return $result;
}

/**
 * ip-api.com API'sinden veri çek
 */
function fetchFromIpApi($ip) {
    try {
        // Bu API tamamen ücretsiz, API key gerektirmez
        $url = "http://ip-api.com/json/{$ip}?fields=status,message,continent,country,countryCode,region,regionName,city,district,zip,lat,lon,timezone,offset,currency,isp,org,as,asname,reverse,mobile,proxy,hosting";
        
        $response = makeHttpRequest($url);
        
        if (!$response || isset($response['status']) && $response['status'] !== 'success') {
            return null;
        }
        
        // Standart formata dönüştür
        return [
            'country' => $response['country'] ?? null,
            'country_code' => $response['countryCode'] ?? null,
            'region' => $response['regionName'] ?? null,
            'city' => $response['city'] ?? null,
            'postal_code' => $response['zip'] ?? null,
            'latitude' => $response['lat'] ?? null,
            'longitude' => $response['lon'] ?? null,
            'timezone' => $response['timezone'] ?? null,
            'isp' => $response['isp'] ?? null,
            'organization' => $response['org'] ?? null,
            'asn' => $response['as'] ?? null,
            'is_datacenter' => ($response['hosting'] ?? false) ? true : false,
            'is_proxy' => ($response['proxy'] ?? false) ? true : false
        ];
    } catch (Exception $e) {
        error_log("IP API Hatası (ip-api.com): " . $e->getMessage());
        return null;
    }
}

/**
 * IPinfo.io API'sinden veri çek (opsiyonel)
 */
function fetchFromIpInfo($ip) {
    try {
        // API anahtarı (ücretsiz planda günde 1000 istek limiti var)
        $apiKey = ''; // Kendi API anahtarınızı buraya ekleyin
        
        // API anahtarı yoksa bu API'yi atla
        if (empty($apiKey)) {
            return null;
        }
        
        $url = "https://ipinfo.io/{$ip}?token={$apiKey}";
        
        $response = makeHttpRequest($url);
        
        if (!$response || isset($response['error'])) {
            return null;
        }
        
        // AS numarasını parçala
        $asn = null;
        if (isset($response['org'])) {
            $asParts = explode(' ', $response['org'], 2);
            $asn = $asParts[0] ?? null;
            $organization = $asParts[1] ?? null;
        }
        
        // Standart formata dönüştür
        return [
            'country' => $response['country'] ?? null,
            'region' => $response['region'] ?? null,
            'city' => $response['city'] ?? null,
            'postal_code' => $response['postal'] ?? null,
            'timezone' => $response['timezone'] ?? null,
            'organization' => $organization ?? null,
            'asn' => $asn ?? null,
            'hostname' => $response['hostname'] ?? null
        ];
    } catch (Exception $e) {
        error_log("IP API Hatası (ipinfo.io): " . $e->getMessage());
        return null;
    }
}

/**
 * IP güvenlik verilerini al
 */
function fetchSecurityData($ip) {
    // Bu fonksiyon gerçek bir uygulamada IPQualityScore, AbuseIPDB veya 
    // IPQS gibi güvenlik API'lerine istek yapmalıdır
    
    // Burada demo amaçlı bazı IP adresleri için sabit veriler döndürüyoruz
    // Gerçek uygulamada bu kısmı gerçek API çağrısıyla değiştirin
    
    // Demo IP tabanlı risk faktörleri
    $riskPatterns = [
        // Tor exit node'lar
        '^\d{1,3}\.\d{1,3}\.9\d{1}\.\d{1,3}$' => ['is_tor' => true, 'is_anonymous' => true],
        // Datacenter IP'leri
        '^(35\.)\d{1,3}\.\d{1,3}\.\d{1,3}$' => ['is_datacenter' => true],
        '^(34\.)\d{1,3}\.\d{1,3}\.\d{1,3}$' => ['is_datacenter' => true],
        // VPN/Proxy IP'leri
        '^(103\.)\d{1,3}\.\d{1,3}\.\d{1,3}$' => ['is_proxy' => true, 'is_anonymous' => true],
        // Spam göndericileri
        '^(5\.)\d{1,3}\.\d{1,3}\.\d{1,3}$' => ['is_spam' => true],
        // Kötüye kullanım geçmişi olanlar
        '^(185\.)\d{1,3}\.\d{1,3}\.\d{1,3}$' => ['is_abuser' => true]
    ];
    
    $result = [
        'is_datacenter' => false,
        'is_tor' => false,
        'is_proxy' => false, 
        'is_anonymous' => false,
        'is_abuser' => false,
        'is_spam' => false
    ];
    
    // IP adresini regex pattern'lere göre kontrol et
    foreach ($riskPatterns as $pattern => $risks) {
        if (preg_match('/' . $pattern . '/', $ip)) {
            foreach ($risks as $key => $value) {
                $result[$key] = $value;
            }
        }
    }
    
    return $result;
}

/**
 * Güvenlik özeti oluştur
 */
function generateSecuritySummary($data) {
    $riskCount = 0;
    
    if ($data['is_tor']) $riskCount++;
    if ($data['is_proxy']) $riskCount++;
    if ($data['is_anonymous']) $riskCount++;
    if ($data['is_abuser']) $riskCount++;
    if ($data['is_spam']) $riskCount++;
    
    if ($riskCount === 0) {
        if ($data['is_datacenter']) {
            return "Bu IP bir datacenter'a ait ancak bilinen herhangi bir risk faktörü yok.";
        }
        return "Bu IP için herhangi bir güvenlik riski tespit edilmedi.";
    } else if ($riskCount === 1) {
        if ($data['is_tor']) {
            return "Bu IP bir Tor çıkış noktası olarak kullanılıyor, bu genellikle anonimlik için tercih edilir.";
        }
        if ($data['is_proxy']) {
            return "Bu IP bir proxy veya VPN hizmeti olarak kullanılıyor. Kullanıcı anonimlik sağlamak için kullanabilir.";
        }
        if ($data['is_anonymous']) {
            return "Bu IP anonim bir hizmet olarak tanımlanmış, kullanıcının gerçek kimliğini gizleyebilir.";
        }
        if ($data['is_abuser']) {
            return "Bu IP, geçmişte kötüye kullanım olaylarıyla ilişkilendirilmiş. Dikkatli olunması önerilir.";
        }
        if ($data['is_spam']) {
            return "Bu IP, spam gönderimi geçmişine sahip. Şüpheli e-postaları dikkatle değerlendirin.";
        }
    } else {
        return "Bu IP için birden fazla risk faktörü tespit edildi. Bu IP'den gelen istekleri dikkatle değerlendirin.";
    }
}

/**
 * HTTP isteği yapar ve JSON yanıtı decode eder
 */
function makeHttpRequest($url) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, 'IP Lookup Tool/1.0');
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("HTTP İstek Hatası: " . $error);
        return null;
    }
    
    return json_decode($response, true);
}

/**
 * JSON formatında yanıt döndürür
 */
function jsonResponse($data) {
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}
?>