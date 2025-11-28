<?php
// Merkezi menü sistemini dahil et
require_once 'menu.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GüvenLink - URL Kontrol</title>
    <!-- SweetAlert CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CSS dosyasını dahil et -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php 
    // Header'ı ekle
    echo getHeaderHTML();
    
    // Sidebar'ı ekle
    echo getSidebarHTML();
    
    // Overlay'i ekle
    echo getOverlayHTML();
    
    // Bildirimler panelini ekle
    echo getNotificationsHTML();
    ?>
    
    <main>
        <div class="main-content">
            <div class="url-checker-section">
                <h1>URL Güvenlik Kontrolü</h1>
                <p class="checker-text">Kontrol etmek istediğiniz URL adresini girin ve güvenlik denetimini başlatın. Web adreslerinin güvenli olup olmadığını hızlıca kontrol edebilirsiniz.</p>
                
                <div class="url-input-container">
                    <input type="text" id="urlInput" class="url-input" placeholder="https://örnek.com" autocomplete="off">
                    <button id="checkUrlBtn" class="action-btn">URL'yi Kontrol Et</button>
                </div>
                
                <div id="resultContainer" class="result-container">
                    <!-- Sonuçlar buraya yüklenecek -->
                </div>
            </div>
        </div>
        
        <div class="sidebar-content">
            <?php 
            // Duyurular bölümünü ekle
            echo getSidebarAnnouncementsHTML(); 
            ?>
        </div>
    </main>
    
    <script>
        <?php 
        // Menü JavaScript kodunu ekle
        echo getMenuJavaScriptCode(); 
        ?>
        
        // URL Kontrol butonu işlevi
        const urlInput = document.getElementById('urlInput');
        const checkUrlBtn = document.getElementById('checkUrlBtn');
        const resultContainer = document.getElementById('resultContainer');
        
        // API anahtarları ve hizmetler için yapılandırma
        // Gerçek uygulamada bunlar sunucu tarafında saklanmalıdır
        const API_KEYS = {
            virustotal: "demo_key",
            safeBrowsing: "demo_key",
            phishtank: "demo_key"
        };
        
        // USOM kara liste URL'si
        const USOM_BLACKLIST_URL = "https://www.usom.gov.tr/url-list.txt";
        let usomBlacklist = []; // USOM kara listesi burada saklanacak
        
        // URL Kontrol butonu işlevi
        checkUrlBtn.addEventListener('click', () => {
            const url = urlInput.value.trim();
            
            if (url === '') {
                Swal.fire({
                    title: 'Hata',
                    text: 'Lütfen bir URL girin',
                    icon: 'error',
                    confirmButtonText: 'Tamam',
                    confirmButtonColor: '#4361ee'
                });
                return;
            }
            
            // URL formatını kontrol et
            if (!isValidUrl(url)) {
                Swal.fire({
                    title: 'Hata',
                    text: 'Geçerli bir URL girin (örn. https://example.com)',
                    icon: 'error',
                    confirmButtonText: 'Tamam',
                    confirmButtonColor: '#4361ee'
                });
                return;
            }
            
            // URL'yi kontrol etmek için API'lere istek gönder
            checkUrlBtn.textContent = "Kontrol Ediliyor...";
            checkUrlBtn.disabled = true;
            
            // Sonuç konteynerini görünür yap ve içeriğini temizle
            resultContainer.style.display = "block";
            resultContainer.innerHTML = '<div style="text-align: center;"><div class="spinner"></div><p>URL kontrol ediliyor...</p></div>';
            
            // Virustotal, Google Safe Browsing ve Phishtank gibi servislerden kontrol yapar
            checkUrl(url)
                .then(results => {
                    // Sonuçları göster
                    displayResults(url, results);
                    
                    // Butonu sıfırla
                    checkUrlBtn.textContent = "URL'yi Kontrol Et";
                    checkUrlBtn.disabled = false;
                })
                .catch(error => {
                    resultContainer.innerHTML = `<div class="error-message">Hata oluştu: ${error.message}</div>`;
                    checkUrlBtn.textContent = "URL'yi Kontrol Et";
                    checkUrlBtn.disabled = false;
                });
        });
        
        // URL geçerliliğini kontrol et
        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }
        
        // Enter tuşuna basıldığında URL kontrolünü başlat
        urlInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                checkUrlBtn.click();
            }
        });
        
        // Sayfa yüklendiğinde USOM kara listesini al
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                // USOM listesini al
                await fetchUSOMListing();
            } catch (error) {
                console.error("USOM listesi yüklenemedi:", error);
            }
        });
        
        // USOM kara listesini al ve hafızada sakla
        async function fetchUSOMListing() {
            try {
                const response = await fetch(USOM_BLACKLIST_URL);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const text = await response.text();
                // Satır satır ayır ve boş satırları temizle
                usomBlacklist = text.split('\n')
                    .map(line => line.trim())
                    .filter(line => line.length > 0);
                
                console.log(`USOM kara listesi yüklendi: ${usomBlacklist.length} URL`);
            } catch (error) {
                console.error("USOM kara listesi alınamadı:", error);
                // Eğer bir hata oluşursa, demo olarak bazı örnek zararlı URL'ler ekle
                usomBlacklist = [
                    "malware-site.com",
                    "phishing-example.org",
                    "evil-domain.net",
                    "scam.suspicious-site.com",
                    "virus.malicious-domain.xyz"
                ];
            }
        }
        
        // URL'yi USOM kara listesinde kontrol et
        async function checkUSOMListing(url) {
            try {
                // URL'yi domain adına çevir
                const domain = new URL(url).hostname;
                
                // Eğer liste henüz yüklenmediyse, yükle
                if (usomBlacklist.length === 0) {
                    await fetchUSOMListing();
                }
                
                // Domain veya domain'in herhangi bir alt domain'i listede var mı kontrol et
                for (const blacklistedUrl of usomBlacklist) {
                    if (domain === blacklistedUrl || domain.endsWith('.' + blacklistedUrl)) {
                        return "danger";
                    }
                }
                
                return "safe";
            } catch (error) {
                console.error("USOM kontrolü yapılırken hata oluştu:", error);
                return "warning"; // Hata durumunda uyarı döndür
            }
        }
        
        // URL'yi kontrol et ve güvenlik analizini yap
        async function checkUrl(url) {
            // Bu fonksiyon gerçek bir uygulamada API istekleri yapacaktır
            // Şu anda simüle edilmiş sonuçlar döndürüyoruz
            
            // LinkGuard yapısına benzer simüle edilmiş kontroller
            const results = {
                summary: {
                    safe: 0,
                    warning: 0,
                    danger: 0
                },
                checks: [
                    {
                        id: "virustotal",
                        name: "VirusTotal",
                        description: "URL, VirusTotal veritabanında 68 farklı güvenlik hizmeti kullanılarak tarandı.",
                        status: await simulateCheck(url, "virustotal")
                    },
                    {
                        id: "safebrowsing",
                        name: "Google Safe Browsing",
                        description: "URL, Google Safe Browsing API kullanılarak zararlı yazılım ve oltalama saldırıları için kontrol edildi.",
                        status: await simulateCheck(url, "safebrowsing")
                    },
                    {
                        id: "phishtank",
                        name: "PhishTank",
                        description: "URL, PhishTank veritabanında bilinen oltalama sitelerine karşı kontrol edildi.",
                        status: await simulateCheck(url, "phishtank")
                    },
                    {
                        id: "urlscan",
                        name: "URLScan.io",
                        description: "URL, URLScan.io tarafından tarandı ve sayfa içeriği analiz edildi.",
                        status: await simulateCheck(url, "urlscan")
                    },
                    {
                        id: "ssl",
                        name: "SSL Sertifika Kontrolü",
                        description: "URL'nin SSL sertifikası ve güvenlik yapılandırması kontrol edildi.",
                        status: await simulateCheck(url, "ssl")
                    },
                    {
                        id: "guvenlink",
                        name: "Güvenlink Kara Liste",
                        description: "URL, USOM (Ulusal Siber Olaylara Müdahale Merkezi) kara listesinde kontrol edildi.",
                        status: await checkUSOMListing(url)
                    }
                ]
            };
            
            // Özet için sayıları hesapla
            results.checks.forEach(check => {
                if (check.status === "safe") {
                    results.summary.safe++;
                } else if (check.status === "warning") {
                    results.summary.warning++;
                } else if (check.status === "danger") {
                    results.summary.danger++;
                }
            });
            
            // 2 saniyelik gecikme ekle (gerçek API çağrısını simüle etmek için)
            return new Promise(resolve => {
                setTimeout(() => {
                    resolve(results);
                }, 2000);
            });
        }
        
        // Simüle edilmiş kontrol sonucu (gerçek uygulamada API'lere istek atılacak)
        async function simulateCheck(url, service) {
            // URL'nin içeriğine bakarak bazı kararlar verelim
            // Gerçek uygulamada bu işlemler sunucu tarafında yapılır
            
            // Bazı zararlı anahtar kelimeleri kontrol et
            const maliciousKeywords = ['phishing', 'malware', 'virus', 'hack', 'crack', 'free-download', 'warez'];
            const urlLower = url.toLowerCase();
            
            // Bazı güvenilir domain'ler
            const trustedDomains = ['google.com', 'microsoft.com', 'apple.com', 'amazon.com', 'github.com'];
            
            try {
                const domain = new URL(url).hostname;
                
                // Güvenilir domain kontrolü
                for (const trustedDomain of trustedDomains) {
                    if (domain.endsWith(trustedDomain)) {
                        return "safe";
                    }
                }
                
                // Zararlı kelime kontrolü
                for (const keyword of maliciousKeywords) {
                    if (urlLower.includes(keyword)) {
                        return "danger";
                    }
                }
                
                // Her servis için farklı davranış
                switch(service) {
                    case "virustotal":
                        // Hash bazlı bir kontrol gibi düşünelim
                        return domain.length % 3 === 0 ? "warning" : "safe";
                        
                    case "safebrowsing":
                        // Google'ın zararlı site listesi kontrolü
                        return domain.length % 5 === 0 ? "danger" : "safe";
                        
                    case "phishtank":
                        // Oltalama sitesi kontrolü
                        return domain.includes("-") ? "warning" : "safe";
                        
                    case "urlscan":
                        // URL tarama hizmeti
                        return domain.length % 7 === 0 ? "warning" : "safe";
                        
                    case "ssl":
                        // SSL sertifika kontrolü
                        return url.startsWith("https") ? "safe" : "warning";
                        
                    default:
                        return "safe";
                }
            } catch (e) {
                return "danger"; // URL ayrıştırılamazsa tehlikeli olarak değerlendir
            }
        }
        
        // Kontrol sonuçlarını göster
        function displayResults(url, results) {
            let html = '';
            
            // URL bilgisi
            html += `
            <div class="url-info">
                <div class="url-info-title">Kontrol Edilen URL:</div>
                <div>${url}</div>
            </div>
            `;
            
            // Özet bölümü
            html += `
            <div class="result-summary">
                <div class="summary-item summary-safe">
                    <div class="summary-value">${results.summary.safe}</div>
                    <div class="summary-label">Güvenli</div>
                </div>
                <div class="summary-item summary-warning">
                    <div class="summary-value">${results.summary.warning}</div>
                    <div class="summary-label">Uyarı</div>
                </div>
                <div class="summary-item summary-danger">
                    <div class="summary-value">${results.summary.danger}</div>
                    <div class="summary-label">Tehlikeli</div>
                </div>
            </div>
            `;
            
            // Her bir kontrol için sonuç kartı
            results.checks.forEach(check => {
                const statusClass = 
                    check.status === "safe" ? "status-safe" : 
                    check.status === "warning" ? "status-warning" : "status-danger";
                
                const statusText = 
                    check.status === "safe" ? "Güvenli" : 
                    check.status === "warning" ? "Uyarı" : "Tehlikeli";
                
                html += `
                <div class="result-card">
                    <div class="result-header">
                        <div class="result-title">${check.name}</div>
                        <div class="result-status ${statusClass}">${statusText}</div>
                    </div>
                    <div class="result-content">
                        ${check.description}
                    </div>
                    <div class="result-source">Kaynak: ${check.id}</div>
                </div>
                `;
            });
            
            // Genel sonuç
            const overallStatus = 
                results.summary.danger > 0 ? "danger" :
                results.summary.warning > 0 ? "warning" : "safe";
            
            const overallMessage = 
                overallStatus === "danger" ? "Bu URL potansiyel tehlikeler içeriyor. Devam etmek istemeyebilirsiniz." :
                overallStatus === "warning" ? "Bu URL bazı uyarılar içeriyor. Dikkatli olun." :
                "Bu URL güvenli görünüyor.";
            
            const statusClass = 
                overallStatus === "safe" ? "status-safe" : 
                overallStatus === "warning" ? "status-warning" : "status-danger";
            
            html += `
            <div class="result-card">
                <div class="result-header">
                    <div class="result-title">Genel Değerlendirme</div>
                    <div class="result-status ${statusClass}">
                        ${overallStatus === "safe" ? "Güvenli" : 
                        overallStatus === "warning" ? "Dikkatli Olun" : "Tehlikeli"}
                    </div>
                </div>
                <div class="result-content">
                    ${overallMessage}
                </div>
            </div>
            `;
            
            resultContainer.innerHTML = html;
        }
    </script>
</body>
</html>