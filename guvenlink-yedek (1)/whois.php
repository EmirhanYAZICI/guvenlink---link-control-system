<?php
// Merkezi menü sistemini dahil et
require_once 'menu.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GüvenLink - Whois Sorgulama</title>
    <!-- SweetAlert CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS dosyasını dahil et -->
    <link rel="stylesheet" href="style.css">
    <!-- Whois CSS -->
    <link rel="stylesheet" href="whois.css">
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
            <div class="whois-checker-section">
                <div class="modern-card">
                    <div class="card-header">
                        <h1>Whois Sorgulama</h1>
                        <p class="checker-text">Alan adıyla ilgili kayıt bilgilerini sorgulayın. Domain sahibi, kayıt tarihi, bitiş tarihi ve sunucu bilgilerini öğrenin.</p>
                    </div>
                    
                    <div class="whois-input-area">
                        <div class="domain-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <div class="domain-input-container">
                            <input type="text" id="domainInput" class="domain-input" placeholder="örnek.com" autocomplete="off">
                            <p class="domain-hint">Bir domain adı girin (örn: google.com, example.org)</p>
                        </div>
                        <button id="checkDomainBtn" class="action-btn">Whois Sorgula</button>
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
    
    <script>
        <?php 
        // Menü JavaScript kodunu ekle
        echo getMenuJavaScriptCode(); 
        ?>
        
        // Whois sorgulama işlevselliği
        const domainInput = document.getElementById('domainInput');
        const checkDomainBtn = document.getElementById('checkDomainBtn');
        const resultContainer = document.getElementById('resultContainer');
        
        // Enter tuşuna basıldığında sorgulamayı başlat
        domainInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                checkDomainBtn.click();
            }
        });
        
        // Whois Sorgulama butonu işlevi
        checkDomainBtn.addEventListener('click', () => {
            const domain = domainInput.value.trim();
            
            if (domain === '') {
                Swal.fire({
                    title: 'Hata',
                    text: 'Lütfen bir domain adı girin',
                    icon: 'error',
                    confirmButtonText: 'Tamam',
                    confirmButtonColor: '#4361ee'
                });
                return;
            }
            
            // Domain adı formatını kontrol et
            if (!isValidDomain(domain)) {
                Swal.fire({
                    title: 'Hata',
                    text: 'Geçerli bir domain adı girin (örn. example.com)',
                    icon: 'error',
                    confirmButtonText: 'Tamam',
                    confirmButtonColor: '#4361ee'
                });
                return;
            }
            
            // Whois sorgulamayı başlat
            checkDomainBtn.textContent = "Sorgulanıyor...";
            checkDomainBtn.disabled = true;
            
            // Sonuç konteynerini görünür yap ve içeriğini temizle
            resultContainer.style.display = "block";
            resultContainer.innerHTML = '<div style="text-align: center;"><div class="spinner"></div><p>Domain bilgileri sorgulanıyor...</p></div>';
            
            // PHP WHOIS API'sine istek yap
            getWhoisData(domain)
                .then(whoisData => {
                    // Sonuçları göster
                    displayWhoisResults(domain, whoisData);
                    
                    // Butonu sıfırla
                    checkDomainBtn.textContent = "Whois Sorgula";
                    checkDomainBtn.disabled = false;
                })
                .catch(error => {
                    resultContainer.innerHTML = `<div class="error-message">Hata oluştu: ${error.message}</div>`;
                    checkDomainBtn.textContent = "Whois Sorgula";
                    checkDomainBtn.disabled = false;
                });
        });
        
        // Domain adının geçerli olup olmadığını kontrol et
        function isValidDomain(domain) {
            // Basit domain kontrolü
            const domainRegex = /^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/i;
            return domainRegex.test(domain);
        }
        
        // WHOIS API'sine istek yapan fonksiyon
        async function getWhoisData(domain) {
            try {
                // Kendi PHP API'mize istek yap
                const apiUrl = `whois_api.php?domain=${encodeURIComponent(domain)}`;
                
                const response = await fetch(apiUrl);
                
                if (!response.ok) {
                    throw new Error(`API isteği başarısız oldu: ${response.status}`);
                }
                
                const data = await response.json();
                
                // API hata döndürüyorsa
                if (data.error) {
                    throw new Error(data.message || 'WHOIS verileri alınamadı');
                }
                
                return data;
            } catch (error) {
                console.error("WHOIS API hatası:", error);
                throw new Error("Domain bilgileri alınamadı. Lütfen daha sonra tekrar deneyin.");
            }
        }
        
        // Tarih formatını düzenle
        function formatDate(dateString) {
            if (!dateString || dateString === 'Bilinmiyor') {
                return 'Bilinmiyor';
            }
            
            try {
                const date = new Date(dateString);
                
                // Geçersiz tarih kontrolü
                if (isNaN(date.getTime())) {
                    return dateString; // Orijinal string'i geri döndür
                }
                
                const day = date.getDate();
                const month = date.getMonth() + 1;
                const year = date.getFullYear();
                
                return `${day < 10 ? '0' + day : day}.${month < 10 ? '0' + month : month}.${year}`;
            } catch (e) {
                return dateString; // Hata durumunda orijinal string'i geri döndür
            }
        }
        
        // Whois sonuçlarını göster
        function displayWhoisResults(domain, whoisData) {
            // Sonuç container'ını temizle
            resultContainer.innerHTML = '';
            
            // Domain bilgisi
            const domainInfoEl = document.createElement('div');
            domainInfoEl.className = 'domain-info';
            domainInfoEl.innerHTML = `
                <div class="domain-info-title">Sorgulanan Domain:</div>
                <div>${domain}</div>
            `;
            resultContainer.appendChild(domainInfoEl);
            
            // Domain özellikleri
            const domainFeaturesEl = document.createElement('div');
            domainFeaturesEl.className = 'domain-features';
            
            // Domain kullanılabilirlik durumu
            const availabilityBadge = document.createElement('div');
            availabilityBadge.className = whoisData.available ? 'feature-badge available' : 'feature-badge taken';
            availabilityBadge.innerHTML = whoisData.available ? 
                '<i class="fas fa-check-circle"></i> Kayıt Edilebilir' : 
                '<i class="fas fa-times-circle"></i> Kayıtlı';
            domainFeaturesEl.appendChild(availabilityBadge);
            
            // Domain uzantısı
            const tldBadge = document.createElement('div');
            tldBadge.className = 'feature-badge';
            tldBadge.innerHTML = `<i class="fas fa-tag"></i> .${whoisData.tld}`;
            domainFeaturesEl.appendChild(tldBadge);
            
            // Domain yaşı - eğer domain kayıtlı ise
            if (!whoisData.available && whoisData.creationDate && whoisData.creationDate !== 'Bilinmiyor') {
                try {
                    const creationDate = new Date(whoisData.creationDate);
                    const now = new Date();
                    // Geçerli bir tarih kontrolü
                    if (!isNaN(creationDate.getTime())) {
                        const ageInYears = Math.floor((now - creationDate) / (365 * 24 * 60 * 60 * 1000));
                        const ageBadge = document.createElement('div');
                        ageBadge.className = 'feature-badge';
                        ageBadge.innerHTML = `<i class="fas fa-clock"></i> ${ageInYears} Yaşında`;
                        domainFeaturesEl.appendChild(ageBadge);
                    }
                } catch (e) {
                    console.error("Tarih hesaplama hatası:", e);
                }
            }
            
            domainInfoEl.appendChild(domainFeaturesEl);
            
            // Sekmeler
            const tabsContainer = document.createElement('div');
            tabsContainer.className = 'tabs';
            tabsContainer.innerHTML = `
                <button class="tab active" data-tab="summary">Özet Bilgiler</button>
                <button class="tab" data-tab="raw">Ham Veri</button>
            `;
            resultContainer.appendChild(tabsContainer);
            
            // Sekme içeriği container'ları
            const summaryTabContent = document.createElement('div');
            summaryTabContent.className = 'tab-content active';
            summaryTabContent.id = 'summary-tab';
            
            const rawTabContent = document.createElement('div');
            rawTabContent.className = 'tab-content';
            rawTabContent.id = 'raw-tab';
            
            resultContainer.appendChild(summaryTabContent);
            resultContainer.appendChild(rawTabContent);
            
            // Domain müsait ise özet içerik
            if (whoisData.available) {
                summaryTabContent.innerHTML = `
                    <div class="whois-data">
                        <div class="whois-section">
                            <div class="section-title">Domain Durumu</div>
                            <div class="info-row">
                                <div class="info-label">Kayıt Durumu:</div>
                                <div class="info-value">Bu domain henüz kayıt edilmemiş ve kayıt için müsait durumdadır.</div>
                            </div>
                        </div>
                    </div>
                `;
                
                rawTabContent.innerHTML = `
                    <div class="whois-raw">${whoisData.rawData}</div>
                `;
            } else {
                // Özet sekme içeriği - domain kayıtlı ise
                let nameserversHTML = 'Bilinmiyor';
                if (whoisData.nameservers && whoisData.nameservers.length > 0) {
                    nameserversHTML = whoisData.nameservers.join('<br>');
                }
                
                let statusHTML = 'Bilinmiyor';
                if (whoisData.status && whoisData.status.length > 0) {
                    statusHTML = whoisData.status.join('<br>');
                }
                
                summaryTabContent.innerHTML = `
                    <div class="whois-data">
                        <!-- Kayıt Bilgileri -->
                        <div class="whois-section">
                            <div class="section-title">Kayıt Bilgileri</div>
                            <div class="info-row">
                                <div class="info-label">Kayıt Durumu:</div>
                                <div class="info-value">Kayıtlı</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Kayıtçı:</div>
                                <div class="info-value">${whoisData.registrar || 'Bilinmiyor'}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Kayıt Tarihi:</div>
                                <div class="info-value">${formatDate(whoisData.creationDate)}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Son Güncelleme:</div>
                                <div class="info-value">${formatDate(whoisData.updatedDate)}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Bitiş Tarihi:</div>
                                <div class="info-value">${formatDate(whoisData.expiryDate)}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Durum:</div>
                                <div class="info-value">${statusHTML}</div>
                            </div>
                        </div>
                        
                        <!-- Kayıt Eden Bilgileri -->
                        <div class="whois-section">
                            <div class="section-title">Kayıt Eden Bilgileri</div>
                            <div class="info-row">
                                <div class="info-label">Kayıt Eden:</div>
                                <div class="info-value">${whoisData.registrant?.name || 'Gizlenmiş'}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Organizasyon:</div>
                                <div class="info-value">${whoisData.registrant?.organization || 'Gizlenmiş'}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Ülke:</div>
                                <div class="info-value">${whoisData.registrant?.country || 'Bilinmiyor'}</div>
                            </div>
                        </div>
                        
                        <!-- Sunucu Bilgileri -->
                        <div class="whois-section">
                            <div class="section-title">Sunucu Bilgileri</div>
                            <div class="info-row">
                                <div class="info-label">İsim Sunucuları:</div>
                                <div class="info-value">${nameserversHTML}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">DNSSEC:</div>
                                <div class="info-value">${whoisData.dnssec || 'Bilinmiyor'}</div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Ham veri sekme içeriği
                rawTabContent.innerHTML = `
                    <div class="whois-raw">${whoisData.rawData || 'Ham veri bulunamadı.'}</div>
                `;
            }
            
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
                    if (tabId === 'summary') {
                        summaryTabContent.classList.add('active');
                    } else if (tabId === 'raw') {
                        rawTabContent.classList.add('active');
                    }
                });
            });
        }
    </script>
</body>
</html>