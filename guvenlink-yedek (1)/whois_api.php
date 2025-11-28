<?php
/**
 * Whois API - Basit PHP Whois Sorgu Scripti
 * 
 * Bu script, doğrudan PHP üzerinden WHOIS sorgusu yapar
 * API başarısız olduğunda alternatif olarak kullanılır
 */

// CORS için header'lar (gerekirse ayarlayın)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Domain parametresini al
$domain = isset($_GET['domain']) ? trim($_GET['domain']) : '';

// Domain geçerli mi kontrol et
if (empty($domain) || !checkValidDomain($domain)) {
    echo json_encode([
        'error' => true,
        'message' => 'Geçersiz domain adı'
    ]);
    exit;
}

// WHOIS sunucusunu belirle
$whoisServer = getWhoisServer($domain);

if (empty($whoisServer)) {
    echo json_encode([
        'error' => true,
        'message' => 'Bu domain uzantısı için WHOIS sunucusu bulunamadı'
    ]);
    exit;
}

// WHOIS sorgusu yap
$whoisData = queryWhoisServer($whoisServer, $domain);

if ($whoisData === false) {
    echo json_encode([
        'error' => true,
        'message' => 'WHOIS sunucusuna erişilemedi'
    ]);
    exit;
}

// WHOIS verilerini ayrıştır
$parsedData = parseWhoisData($domain, $whoisData);

// JSON formatında döndür
echo json_encode($parsedData);
exit;

/**
 * Domain adının geçerli olup olmadığını kontrol eder
 */
function checkValidDomain($domain) {
    $domainRegex = '/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/i';
    return preg_match($domainRegex, $domain);
}

/**
 * TLD'ye göre WHOIS sunucusunu belirler
 */
function getWhoisServer($domain) {
    // TLD'yi al
    $tld = getTopLevelDomain($domain);
    
    // TLD'ye göre WHOIS sunucusunu belirle
    $whoisServers = [
        'com' => 'whois.verisign-grs.com',
        'net' => 'whois.verisign-grs.com',
        'org' => 'whois.pir.org',
        'info' => 'whois.afilias.net',
        'biz' => 'whois.neulevel.biz',
        'io' => 'whois.nic.io',
        'co' => 'whois.nic.co',
        'me' => 'whois.nic.me',
        'us' => 'whois.nic.us',
        'uk' => 'whois.nic.uk',
        'ca' => 'whois.cira.ca',
        'de' => 'whois.denic.de',
        'fr' => 'whois.nic.fr',
        'nl' => 'whois.domain-registry.nl',
        'eu' => 'whois.eu',
        'ru' => 'whois.tcinet.ru',
        'cc' => 'whois.nic.cc',
        'tv' => 'whois.nic.tv',
        'name' => 'whois.nic.name',
        'ws' => 'whois.website.ws',
        'bz' => 'whois.belizenic.bz',
        'mobi' => 'whois.dotmobiregistry.net',
        'pro' => 'whois.registrypro.pro',
        'edu' => 'whois.educause.edu',
        'gov' => 'whois.dotgov.gov',
        'int' => 'whois.iana.org',
        'mil' => 'whois.nic.mil',
        'ac' => 'whois.nic.ac',
        'ae' => 'whois.aeda.net.ae',
        'at' => 'whois.nic.at',
        'be' => 'whois.dns.be',
        'ch' => 'whois.nic.ch',
        'cl' => 'whois.nic.cl',
        'cz' => 'whois.nic.cz',
        'dk' => 'whois.dk-hostmaster.dk',
        'es' => 'whois.nic.es',
        'fi' => 'whois.fi',
        'gr' => 'whois.ics.forth.gr',
        'hu' => 'whois.nic.hu',
        'il' => 'whois.isoc.org.il',
        'jp' => 'whois.jprs.jp',
        'kr' => 'whois.kr',
        'li' => 'whois.nic.li',
        'lt' => 'whois.domreg.lt',
        'lu' => 'whois.dns.lu',
        'mx' => 'whois.mx',
        'no' => 'whois.norid.no',
        'nz' => 'whois.srs.net.nz',
        'pl' => 'whois.dns.pl',
        'pt' => 'whois.dns.pt',
        'se' => 'whois.iis.se',
        'sg' => 'whois.sgnic.sg',
        'tr' => 'whois.nic.tr',
        'va' => 'whois.ripe.net',
        'xxx' => 'whois.nic.xxx',
        'academy' => 'whois.donuts.co',
        'app' => 'whois.nic.google',
        'dev' => 'whois.nic.google',
        'ai' => 'whois.nic.ai',
        'design' => 'whois.nic.design',
        'top' => 'whois.nic.top',
        'xyz' => 'whois.nic.xyz',
        'site' => 'whois.nic.site',
        'shop' => 'whois.nic.shop',
        'club' => 'whois.nic.club'
    ];
    
    return isset($whoisServers[$tld]) ? $whoisServers[$tld] : '';
}

/**
 * Domain'in TLD (üst seviye etki alanı) bölümünü döndürür
 */
function getTopLevelDomain($domain) {
    $parts = explode('.', $domain);
    return end($parts);
}

/**
 * WHOIS sunucusuna sorgu gönderir
 */
function queryWhoisServer($whoisServer, $domain) {
    $timeout = 10; // saniye
    
    try {
        // Socket bağlantısı aç
        $socket = @fsockopen($whoisServer, 43, $errno, $errstr, $timeout);
        
        if (!$socket) {
            error_log("WHOIS Sorgu Hatası: $errno - $errstr");
            return false;
        }
        
        // Bazı WHOIS sunucuları için sorgular
        $domainExt = getTopLevelDomain($domain);
        if ($domainExt == 'com' || $domainExt == 'net' || $domainExt == 'edu') {
            $domain = "domain $domain";
        }
        
        // Sorguyu gönder
        fputs($socket, $domain . "\r\n");
        
        // Yanıtı al
        $response = '';
        while (!feof($socket)) {
            $response .= fgets($socket, 4096);
        }
        
        fclose($socket);
        
        return $response;
    } catch (Exception $e) {
        error_log("WHOIS Sorgu Hatası: " . $e->getMessage());
        return false;
    }
}

/**
 * WHOIS verilerini ayrıştırır ve yapılandırılmış bir formatta döndürür
 */
function parseWhoisData($domain, $whoisData) {
    // TLD'yi al
    $tld = getTopLevelDomain($domain);
    
    // Kayıt var mı kontrol et (temel kontrol)
    $isAvailable = false;
    
    $noResultsPatterns = [
        'No match for',
        'NOT FOUND',
        'No entries found',
        'No Data Found',
        'Domain not found',
        'No information available',
        'domain name not known',
        'is free',
        'is available',
        'No match found',
        'Domain Status: Available'
    ];
    
    foreach ($noResultsPatterns as $pattern) {
        if (stripos($whoisData, $pattern) !== false) {
            $isAvailable = true;
            break;
        }
    }
    
    // Domain müsait ise
    if ($isAvailable) {
        return [
            'domain' => $domain,
            'available' => true,
            'tld' => $tld,
            'rawData' => $whoisData
        ];
    }
    
    // Domain bilgilerini ayrıştır
    $result = [
        'domain' => $domain,
        'available' => false,
        'tld' => $tld,
        'rawData' => $whoisData
    ];
    
    // WHOIS verilerinden bilgileri çıkar
    $patterns = [
        'registrar' => [
            '/Registrar:\s*(.+)$/im',
            '/Sponsoring Registrar:\s*(.+)$/im',
            '/registrar:\s*(.+)$/im'
        ],
        'creationDate' => [
            '/Creation Date:\s*(.+)$/im',
            '/Created on:\s*(.+)$/im',
            '/Created Date:\s*(.+)$/im',
            '/Registration Date:\s*(.+)$/im',
            '/Domain Registration Date:\s*(.+)$/im',
            '/created:\s*(.+)$/im'
        ],
        'updatedDate' => [
            '/Updated Date:\s*(.+)$/im',
            '/Modified:\s*(.+)$/im',
            '/Last Modified:\s*(.+)$/im',
            '/Last-update:\s*(.+)$/im',
            '/Last updated:\s*(.+)$/im',
            '/changed:\s*(.+)$/im'
        ],
        'expiryDate' => [
            '/Expiration Date:\s*(.+)$/im',
            '/Registry Expiry Date:\s*(.+)$/im',
            '/Domain Expiration Date:\s*(.+)$/im',
            '/Expiry date:\s*(.+)$/im',
            '/expires:\s*(.+)$/im'
        ],
        'status' => [
            '/Status:\s*(.+)$/im',
            '/Domain Status:\s*(.+)$/im',
            '/Registration Status:\s*(.+)$/im',
            '/status:\s*(.+)$/im'
        ],
        'nameservers' => [
            '/Name Server:\s*(.+)$/im',
            '/Nameservers:\s*(.+)$/im',
            '/nserver:\s*(.+)$/im'
        ],
        'dnssec' => [
            '/DNSSEC:\s*(.+)$/im',
            '/dnssec:\s*(.+)$/im'
        ]
    ];
    
    // Registrant bilgileri
    $registrantPatterns = [
        'name' => [
            '/Registrant Name:\s*(.+)$/im',
            '/Registrant:\s*(.+)$/im',
            '/registrant:\s*(.+)$/im'
        ],
        'organization' => [
            '/Registrant Organization:\s*(.+)$/im',
            '/Registrant Contact Organization:\s*(.+)$/im',
            '/Registrant Organisation:\s*(.+)$/im',
        ],
        'country' => [
            '/Registrant Country:\s*(.+)$/im',
            '/Registrant Contact Country:\s*(.+)$/im',
            '/country:\s*(.+)$/im',
        ]
    ];
    
    // Temel bilgileri çıkar
    foreach ($patterns as $field => $regexList) {
        foreach ($regexList as $regex) {
            if (preg_match_all($regex, $whoisData, $matches)) {
                if ($field === 'status' || $field === 'nameservers') {
                    // Bu alanlar çoklu olabilir
                    $values = [];
                    foreach ($matches[1] as $match) {
                        $values[] = trim($match);
                    }
                    $result[$field] = $values;
                    break;
                } else {
                    // Tek değer
                    $result[$field] = trim($matches[1][0]);
                    break;
                }
            }
        }
        
        // Eğer alan bulunamadıysa varsayılan değerler ata
        if (!isset($result[$field])) {
            if ($field === 'status' || $field === 'nameservers') {
                $result[$field] = [];
            } else {
                $result[$field] = 'Bilinmiyor';
            }
        }
    }
    
    // Kayıt eden bilgilerini çıkar
    $registrant = [];
    foreach ($registrantPatterns as $field => $regexList) {
        foreach ($regexList as $regex) {
            if (preg_match($regex, $whoisData, $matches)) {
                $registrant[$field] = trim($matches[1]);
                break;
            }
        }
        
        // Eğer alan bulunamadıysa varsayılan değerler ata
        if (!isset($registrant[$field])) {
            $registrant[$field] = 'Gizlenmiş';
        }
    }
    
    $result['registrant'] = $registrant;
    
    return $result;
}
?>