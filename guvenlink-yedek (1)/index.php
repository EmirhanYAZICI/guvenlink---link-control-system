<?php
// Merkezi menü sistemini dahil et
require_once 'menu.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GüvenLink</title>
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
            <div class="welcome-section">
                <h1>Hoş Geldiniz</h1>
                <p class="welcome-text">Güvenlink'e hoş geldiniz. Sitemizde tamamen ücretsiz şekilde hizmet vermeyi amaçlıyoruz. Sorun yaşamanız halinde bizimle iletişime geçmekten çekinmeyin.</p>
                <button class="action-btn">
                    <span>Hemen Başlayın</span>
                </button>
            </div>
            
            <div class="services-container">
                <div class="service-column">
                    <h2><span>🛡️</span> Hizmetlerimiz</h2>
                    <ul class="service-list">
                        <li><a href="url-kontrol.php">URL Kontrolü</a></li>
                        <li><a href="dosya-kontrol.php">Dosya Kontrolü</a></li>
                        <li><a href="ip-sorgu.php">IP Sorgulama</a></li>
                        <li><a href="imei-sorgu.php">Imei Sorgulama</a></li>
                        <li><a href="whois.php">Whois Sorgulama</a></li>
                    </ul>
                </div>
                
                <div class="service-column">
                    <h2><span>✨</span> Neden Biz?</h2>
                    <ul class="service-list">
                        <li>Tamamen Ücretsiz</li>
                        <li>Yenilikçi Ekip</li>
                        <li>Modern Tasarım</li>
                        <li>Sürekli Güncel</li>
                    </ul>
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
        
        // Aksiyon butonu tıklama olayı (SweetAlert ile)
        const actionBtn = document.querySelector('.action-btn');
        actionBtn.addEventListener('click', () => {
            actionBtn.style.transform = 'scale(0.95)';
            setTimeout(() => {
                actionBtn.style.transform = '';
                Swal.fire({
                    title: 'Bilgi',
                    text: 'Sol üstteki menüye tıklayarak sitemizde gezinmeye başlayabilirsin!',
                    icon: 'info',
                    confirmButtonText: 'Tamam',
                    confirmButtonColor: '#4361ee'
                });
            }, 100);
        });
        
        // Sayaç fonksiyonu
        function showVisitorCounter() {
            // Sayaç elemanları varsa göster
            if (digit1 && digit2 && digit3 && digit4) {
                // Sabit bir değer
                const visitorCount = 1738;
                
                // Sayıyı basamaklarına ayır ve göster
                const countStr = visitorCount.toString().padStart(4, '0');
                digit1.textContent = countStr[0];
                digit2.textContent = countStr[1];
                digit3.textContent = countStr[2];
                digit4.textContent = countStr[3];
            }
        }
        
        // DOM yüklendiğinde sayacı göster
        document.addEventListener('DOMContentLoaded', () => {
            // Sayaç DOM elemanlarını seç
            const digit1 = document.getElementById('digit-1');
            const digit2 = document.getElementById('digit-2');
            const digit3 = document.getElementById('digit-3');
            const digit4 = document.getElementById('digit-4');
            
            // Sayacı göster (eğer gerekli elemanlar varsa)
            showVisitorCounter();
        });
    </script>
</body>
</html>