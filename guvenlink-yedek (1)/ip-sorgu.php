<?php
// Merkezi menü sistemini dahil et
require_once 'menu.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GüvenLink - IP Sorgulama</title>
    <!-- SweetAlert CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS dosyasını dahil et -->
    <link rel="stylesheet" href="style.css">
    <!-- IP Sorgulama CSS -->
    <link rel="stylesheet" href="ip.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
            <div class="ip-checker-section">
                <div class="modern-card">
                    <div class="card-header">
                        <h1>IP Adresi Sorgulama</h1>
                        <p class="checker-text">IP adresi hakkında detaylı bilgi alın. Konum, ISP, ülke ve ASN bilgilerini tek bir sorguyla öğrenin.</p>
                    </div>
                    
                    <div class="ip-input-area">
                        <div class="ip-icon">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <div class="ip-input-container">
                            <input type="text" id="ipInput" class="ip-input" placeholder="8.8.8.8" autocomplete="off">
                            <p class="ip-hint">Bir IPv4 veya IPv6 adresi girin</p>
                        </div>
                        <button id="checkIpBtn" class="action-btn">IP Sorgula</button>
                    </div>
                    
                    <div id="resultContainer" class="result-container">
                        <!-- Sonuçlar buraya yüklenecek -->
                    </div>
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
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        <?php 
        // Menü JavaScript kodunu ekle
        echo getMenuJavaScriptCode(); 
        ?>
        
        // IP sorgulama işlevselliği
        const ipInput = document.getElementById('ipInput');
        const checkIpBtn = document.getElementById('checkIpBtn');
        const resultContainer = document.getElementById('resultContainer');
        
        // Enter tuşuna basıldığında sorgulamayı başlat
        ipInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                checkIpBtn.click();
            }
        });
        
        // Sayfa yüklendiğinde otomatik olarak kendi IP adresini göster
        document.addEventListener('DOMContentLoaded', () => {
            fetchClientIP();
        });
        
        // Kullanıcının kendi IP adresini getir
        async function fetchClientIP() {
            try {
                const response = await fetch('ip_api.php?action=client_ip');
                
                if (!response.ok) {
                    throw new Error(`API isteği başarısız oldu: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.error) {
                    console.error('IP adresiniz alınamadı:', data.message);
                    return;
                }
                
                // IP adresini input alanına yerleştir
                ipInput.value = data.ip;
                
                // Otomatik sorgu yap
                checkIpBtn.click();
            } catch (error) {
                console.error('IP adresiniz alınırken hata oluştu:', error);
            }
        }
        
        // IP Sorgulama butonu işlevi
        checkIpBtn.addEventListener('click', () => {
            const ip = ipInput.value.trim();
            
            if (ip === '') {
                Swal.fire({
                    title: 'Hata',
                    text: 'Lütfen bir IP adresi girin',
                    icon: 'error',
                    confirmButtonText: 'Tamam',
                    confirmButtonColor: '#4361ee'
                });
                return;
            }
            
            // IP adres formatını kontrol et
            if (!isValidIP(ip)) {
                Swal.fire({
                    title: 'Hata',
                    text: 'Geçerli bir IP adresi girin (IPv4 veya IPv6)',
                    icon: 'error',
                    confirmButtonText: 'Tamam',
                    confirmButtonColor: '#4361ee'
                });
                return;
            }
            
            // IP sorgulamayı başlat
            checkIpBtn.textContent = "Sorgulanıyor...";
            checkIpBtn.disabled = true;
            
            // Sonuç konteynerini görünür yap ve içeriğini temizle
            resultContainer.style.display = "block";
            resultContainer.innerHTML = '<div style="text-align: center;"><div class="spinner"></div><p>IP bilgileri sorgulanıyor...</p></div>';
            
            // IP API'sine istek yap
            getIPData(ip)
                .then(ipData => {
                    // Sonuçları göster
                    displayIPResults(ip, ipData);
                    
                    // Butonu sıfırla
                    checkIpBtn.textContent = "IP Sorgula";
                    checkIpBtn.disabled = false;
                })
                .catch(error => {
                    resultContainer.innerHTML = `<div class="error-message">Hata oluştu: ${error.message}</div>`;
                    checkIpBtn.textContent = "IP Sorgula";
                    checkIpBtn.disabled = false;
                });
        });
        
        // IP adresinin geçerli olup olmadığını kontrol et
        function isValidIP(ip) {
            // IPv4 kontrolü
            const ipv4Regex = /^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
            
            // IPv6 kontrolü (basitleştirilmiş)
            const ipv6Regex = /^([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$|^([0-9a-fA-F]{1,4}:){1,7}:|^([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}$|^([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}$|^([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}$|^([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}$|^([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}$|^[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})$|^:((:[0-9a-fA-F]{1,4}){1,7}|:)$/;
            
            return ipv4Regex.test(ip) || ipv6Regex.test(ip);
        }
        
        // IP API'sine istek yapan fonksiyon
        async function getIPData(ip) {
            try {
                // Kendi PHP API'mize istek yap
                const apiUrl = `ip_api.php?ip=${encodeURIComponent(ip)}`;
                
                const response = await fetch(apiUrl);
                
                if (!response.ok) {
                    throw new Error(`API isteği başarısız oldu: ${response.status}`);
                }
                
                const data = await response.json();
                
                // API hata döndürüyorsa
                if (data.error) {
                    throw new Error(data.message || 'IP bilgileri alınamadı');
                }
                
                return data;
            } catch (error) {
                console.error("IP API hatası:", error);
                throw new Error("IP bilgileri alınamadı. Lütfen daha sonra tekrar deneyin.");
            }
        }
        
        // IP sonuçlarını göster
        function displayIPResults(ip, ipData) {
            // Sonuç container'ını temizle
            resultContainer.innerHTML = '';
            
            // Sekmeler
            const tabsContainer = document.createElement('div');
            tabsContainer.className = 'tabs';
            tabsContainer.innerHTML = `
                <button class="tab active" data-tab="general">Genel Bilgiler</button>
                <button class="tab" data-tab="map">Harita</button>
                <button class="tab" data-tab="tech">Teknik Detaylar</button>
                <button class="tab" data-tab="security">Güvenlik</button>
            `;
            resultContainer.appendChild(tabsContainer);
            
            // Sekme içeriği container'ları
            const generalTabContent = document.createElement('div');
            generalTabContent.className = 'tab-content active';
            generalTabContent.id = 'general-tab';
            
            const mapTabContent = document.createElement('div');
            mapTabContent.className = 'tab-content';
            mapTabContent.id = 'map-tab';
            
            const techTabContent = document.createElement('div');
            techTabContent.className = 'tab-content';
            techTabContent.id = 'tech-tab';
            
            const securityTabContent = document.createElement('div');
            securityTabContent.className = 'tab-content';
            securityTabContent.id = 'security-tab';
            
            resultContainer.appendChild(generalTabContent);
            resultContainer.appendChild(mapTabContent);
            resultContainer.appendChild(techTabContent);
            resultContainer.appendChild(securityTabContent);
            
            // Genel sekme içeriği
            generalTabContent.innerHTML = `
                <div class="ip-data">
                    <div class="ip-header">
                        <div class="ip-address">${ip}</div>
                        <div class="ip-badges">
                            <div class="ip-badge" title="IP Versiyonu">
                                <i class="fas fa-code-branch"></i> IPv${ipData.version || "4"}
                            </div>
                            <div class="ip-badge ${ipData.is_datacenter ? 'badge-warning' : ''}" title="Datacenter IP">
                                <i class="fas fa-server"></i> ${ipData.is_datacenter ? 'Datacenter IP' : 'Bireysel IP'}
                            </div>
                            <div class="ip-badge ${ipData.is_tor ? 'badge-danger' : ''}" title="Tor Çıkış Noktası">
                                <i class="fas fa-mask"></i> ${ipData.is_tor ? 'Tor Exit Node' : 'Tor Değil'}
                            </div>
                            <div class="ip-badge ${ipData.is_proxy ? 'badge-warning' : ''}" title="Proxy/VPN">
                                <i class="fas fa-shield-alt"></i> ${ipData.is_proxy ? 'Proxy/VPN' : 'Proxy Değil'}
                            </div>
                        </div>
                    </div>
                    
                    <div class="ip-section">
                        <div class="section-title">Konum Bilgileri</div>
                        <div class="info-row">
                            <div class="info-label">Ülke:</div>
                            <div class="info-value">
                                <img src="https://flagcdn.com/16x12/${ipData.country_code?.toLowerCase() || 'xx'}.png" 
                                    alt="${ipData.country || 'Bilinmiyor'}" class="flag-icon"> 
                                ${ipData.country || 'Bilinmiyor'}
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Bölge:</div>
                            <div class="info-value">${ipData.region || 'Bilinmiyor'}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Şehir:</div>
                            <div class="info-value">${ipData.city || 'Bilinmiyor'}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Posta Kodu:</div>
                            <div class="info-value">${ipData.postal_code || 'Bilinmiyor'}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Koordinatlar:</div>
                            <div class="info-value">${ipData.latitude ? `${ipData.latitude}, ${ipData.longitude}` : 'Bilinmiyor'}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Saat Dilimi:</div>
                            <div class="info-value">${ipData.timezone || 'Bilinmiyor'}</div>
                        </div>
                    </div>
                    
                    <div class="ip-section">
                        <div class="section-title">Servis Sağlayıcı Bilgileri</div>
                        <div class="info-row">
                            <div class="info-label">ISP:</div>
                            <div class="info-value">${ipData.isp || 'Bilinmiyor'}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Organizasyon:</div>
                            <div class="info-value">${ipData.organization || 'Bilinmiyor'}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">ASN:</div>
                            <div class="info-value">${ipData.asn || 'Bilinmiyor'}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Kullanım Tipi:</div>
                            <div class="info-value">${ipData.usage_type || 'Bilinmiyor'}</div>
                        </div>
                    </div>
                </div>
            `;
            
            // Harita sekme içeriği
            if (ipData.latitude && ipData.longitude) {
                mapTabContent.innerHTML = `
                    <div class="ip-data">
                        <div class="map-container" id="ipMap"></div>
                        <div class="ip-section">
                            <div class="section-title">Konum Detayları</div>
                            <div class="info-row">
                                <div class="info-label">Adres:</div>
                                <div class="info-value">${ipData.city ? `${ipData.city}, ${ipData.region}, ${ipData.country}` : 'Bilinmiyor'}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Koordinatlar:</div>
                                <div class="info-value">${ipData.latitude}, ${ipData.longitude}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Hassasiyet:</div>
                                <div class="info-value">${ipData.accuracy_radius ? `~${ipData.accuracy_radius} km` : 'Bilinmiyor'}</div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Haritayı yükle
                setTimeout(() => {
                    const map = L.map('ipMap').setView([ipData.latitude, ipData.longitude], 13);
                    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);
                    
                    const accuracyRadius = ipData.accuracy_radius || 1000;
                    
                    // Konum işaretleyici
                    L.marker([ipData.latitude, ipData.longitude]).addTo(map)
                        .bindPopup(`<b>${ip}</b><br>${ipData.city || ''}, ${ipData.country || 'Bilinmiyor'}`);
                    
                    // Kesinlik çemberi
                    L.circle([ipData.latitude, ipData.longitude], {
                        color: 'blue',
                        fillColor: '#3388ff',
                        fillOpacity: 0.1,
                        radius: accuracyRadius * 1000 // km to m
                    }).addTo(map);
                    
                }, 100);
            } else {
                mapTabContent.innerHTML = `
                    <div class="ip-data">
                        <div class="no-map-message">
                            <i class="fas fa-map-marked-alt"></i>
                            <p>Bu IP adresi için konum bilgisi bulunamadı.</p>
                        </div>
                    </div>
                `;
            }
            
            // Teknik sekme içeriği
            techTabContent.innerHTML = `
                <div class="ip-data">
                    <div class="ip-section">
                        <div class="section-title">IP Ağ Bilgileri</div>
                        <div class="info-row">
                            <div class="info-label">IP Adresi:</div>
                            <div class="info-value">${ip}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">IP Versiyonu:</div>
                            <div class="info-value">IPv${ipData.version || "4"}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Alt Ağ:</div>
                            <div class="info-value">${ipData.subnet || 'Bilinmiyor'}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">CIDR:</div>
                            <div class="info-value">${ipData.cidr || 'Bilinmiyor'}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Prefix:</div>
                            <div class="info-value">${ipData.prefix_len || 'Bilinmiyor'}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Aralık:</div>
                            <div class="info-value">${ipData.ip_range ? `${ipData.ip_range.start} - ${ipData.ip_range.end}` : 'Bilinmiyor'}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Aralık Boyutu:</div>
                            <div class="info-value">${ipData.ip_range_size || 'Bilinmiyor'}</div>
                        </div>
                    </div>
                    
                    <div class="ip-section">
                        <div class="section-title">Ağ Servis Sağlayıcı</div>
                        <div class="info-row">
                            <div class="info-label">ISP:</div>
                            <div class="info-value">${ipData.isp || 'Bilinmiyor'}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Organizasyon:</div>
                            <div class="info-value">${ipData.organization || 'Bilinmiyor'}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">ASN:</div>
                            <div class="info-value">${ipData.asn || 'Bilinmiyor'}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Kayıt Kurumu:</div>
                            <div class="info-value">${ipData.registry || 'Bilinmiyor'}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">ASN Tipi:</div>
                            <div class="info-value">${ipData.asn_type || 'Bilinmiyor'}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Ülke Kodu:</div>
                            <div class="info-value">${ipData.country_code || 'Bilinmiyor'}</div>
                        </div>
                    </div>
                </div>
            `;
            
            // Güvenlik sekme içeriği
            securityTabContent.innerHTML = `
                <div class="ip-data">
                    <div class="ip-section security-overview">
                        <div class="section-title">Güvenlik Değerlendirmesi</div>
                        <div class="security-score">
                            <div class="security-meter ${getSecurityMeterClass(ipData)}">
                                <div class="security-meter-value">${calculateSecurityScore(ipData)}/100</div>
                                <div class="security-meter-label">${getSecurityRating(ipData)}</div>
                            </div>
                            <div class="security-summary">
                                <p>${getSecuritySummary(ipData)}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="ip-section">
                        <div class="section-title">Risk Faktörleri</div>
                        <div class="risk-factors">
                            <div class="risk-factor ${ipData.is_datacenter ? 'risk-warning' : 'risk-safe'}">
                                <div class="risk-icon">
                                    <i class="fas fa-server"></i>
                                </div>
                                <div class="risk-details">
                                    <div class="risk-name">Datacenter IP</div>
                                    <div class="risk-status">${ipData.is_datacenter ? 'Evet' : 'Hayır'}</div>
                                    <div class="risk-description">${ipData.is_datacenter ? 'Bu IP bir datacenter/hosting şirketine ait, genellikle bireysel kullanıcılar tarafından kullanılmaz.' : 'Bu IP bir datacenter veya hosting şirketine ait değil.'}</div>
                                </div>
                            </div>
                            
                            <div class="risk-factor ${ipData.is_tor ? 'risk-danger' : 'risk-safe'}">
                                <div class="risk-icon">
                                    <i class="fas fa-mask"></i>
                                </div>
                                <div class="risk-details">
                                    <div class="risk-name">Tor Exit Node</div>
                                    <div class="risk-status">${ipData.is_tor ? 'Evet' : 'Hayır'}</div>
                                    <div class="risk-description">${ipData.is_tor ? 'Bu IP bir Tor exit node olarak kullanılıyor, bu internette anonim gezinme için kullanılır.' : 'Bu IP bir Tor çıkış noktası değil.'}</div>
                                </div>
                            </div>
                            
                            <div class="risk-factor ${ipData.is_proxy ? 'risk-warning' : 'risk-safe'}">
                                <div class="risk-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="risk-details">
                                    <div class="risk-name">Proxy/VPN</div>
                                    <div class="risk-status">${ipData.is_proxy ? 'Evet' : 'Hayır'}</div>
                                    <div class="risk-description">${ipData.is_proxy ? 'Bu IP proxy veya VPN hizmeti olarak tanımlanmış, bu kullanıcının gerçek konumunu gizlemesine olanak sağlar.' : 'Bu IP bir proxy veya VPN değil.'}</div>
                                </div>
                            </div>
                            
                            <div class="risk-factor ${ipData.is_anonymous ? 'risk-danger' : 'risk-safe'}">
                                <div class="risk-icon">
                                    <i class="fas fa-user-secret"></i>
                                </div>
                                <div class="risk-details">
                                    <div class="risk-name">Anonim Servis</div>
                                    <div class="risk-status">${ipData.is_anonymous ? 'Evet' : 'Hayır'}</div>
                                    <div class="risk-description">${ipData.is_anonymous ? 'Bu IP bir anonimleştirme servisi kullanıyor.' : 'Bu IP herhangi bir anonimleştirme servisi kullanmıyor.'}</div>
                                </div>
                            </div>
                            
                            <div class="risk-factor ${ipData.is_abuser ? 'risk-danger' : 'risk-safe'}">
                                <div class="risk-icon">
                                    <i class="fas fa-ban"></i>
                                </div>
                                <div class="risk-details">
                                    <div class="risk-name">Kötüye Kullanım Geçmişi</div>
                                    <div class="risk-status">${ipData.is_abuser ? 'Evet' : 'Hayır'}</div>
                                    <div class="risk-description">${ipData.is_abuser ? 'Bu IP, geçmişte kötü amaçlı etkinliklerle ilişkilendirilmiş.' : 'Bu IP için bilinen bir kötüye kullanım geçmişi yok.'}</div>
                                </div>
                            </div>
                            
                            <div class="risk-factor ${ipData.is_spam ? 'risk-danger' : 'risk-safe'}">
                                <div class="risk-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="risk-details">
                                    <div class="risk-name">Spam Geçmişi</div>
                                    <div class="risk-status">${ipData.is_spam ? 'Evet' : 'Hayır'}</div>
                                    <div class="risk-description">${ipData.is_spam ? 'Bu IP, spam gönderimi ile ilişkilendirilmiş.' : 'Bu IP için bilinen bir spam geçmişi yok.'}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Sekme işlevselliği
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Tüm sekmeleri pasif yap
                    tabs.forEach(t => t.classList.remove('active'));
                    
                    // Tüm sekme içeriklerini gizle
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.remove('active');
                    });
                    
                    // Tıklanan sekmeyi aktif yap
                    tab.classList.add('active');
                    
                    // İlgili içeriği göster
                    const tabId = tab.getAttribute('data-tab');
                    document.getElementById(`${tabId}-tab`).classList.add('active');
                });
            });
        }
        
        // Güvenlik puanı hesapla
        function calculateSecurityScore(ipData) {
            let score = 100;
            
            // Risk faktörlerine göre puan düşür
            if (ipData.is_datacenter) score -= 10;
            if (ipData.is_tor) score -= 30;
            if (ipData.is_proxy) score -= 20;
            if (ipData.is_anonymous) score -= 25;
            if (ipData.is_abuser) score -= 35;
            if (ipData.is_spam) score -= 25;
            
            // Minimum 0 olabilir
            return Math.max(0, score);
        }
        
        // Güvenlik değerlendirmesi sınıf adı
        function getSecurityMeterClass(ipData) {
            const score = calculateSecurityScore(ipData);
            
            if (score >= 80) return 'security-high';
            if (score >= 50) return 'security-medium';
            return 'security-low';
        }
        
        // Güvenlik değerlendirmesi
        function getSecurityRating(ipData) {
            const score = calculateSecurityScore(ipData);
            
            if (score >= 80) return 'Güvenli';
            if (score >= 50) return 'Orta Risk';
            return 'Yüksek Risk';
        }
        
        // Güvenlik özeti
        function getSecuritySummary(ipData) {
            // API'den gelen hazır özet varsa onu kullan
            if (ipData.security_summary) {
                return ipData.security_summary;
            }
            
            // Yoksa oluştur
            const score = calculateSecurityScore(ipData);
            
            if (score >= 80) {
                return "Bu IP adresi için herhangi bir ciddi risk faktörü tespit edilmedi. Genel olarak güvenli görünüyor.";
            } else if (score >= 50) {
                let factors = [];
                if (ipData.is_datacenter) factors.push("bir datacenter'a ait olması");
                if (ipData.is_proxy) factors.push("proxy/VPN kullanımı");
                
                return `Bu IP adresi ${factors.join(" ve ")} nedeniyle orta derecede risk faktörleri taşıyor. Dikkatli olunmalıdır.`;
            } else {
                let factors = [];
                if (ipData.is_tor) factors.push("Tor çıkış noktası olması");
                if (ipData.is_anonymous) factors.push("anonim servis kullanımı");
                if (ipData.is_abuser) factors.push("kötüye kullanım geçmişi");
                if (ipData.is_spam) factors.push("spam gönderim geçmişi");
                
                return `Bu IP adresi ${factors.join(", ")} gibi yüksek risk faktörleri taşıyor. Bu IP'den gelen isteklere karşı dikkatli olunmalıdır.`;
            }
        }
    </script>
</body>
</html>