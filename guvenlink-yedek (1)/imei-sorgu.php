<?php
// Merkezi menü sistemini dahil et
require_once 'menu.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GüvenLink - İMEI Sorgulama</title>
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
                <h1>İMEI Sorgulama</h1>
                <p class="checker-text">Cihazınızın İMEI numarasını girerek marka ve model bilgisini sorgulayabilirsiniz. İMEI numaranızı telefonunuzda *#06# tuşlayarak öğrenebilirsiniz.</p>
                
                <div class="url-input-container">
                    <input type="text" id="imeiInput" class="url-input" placeholder="15 haneli İMEI numarasını girin" autocomplete="off" maxlength="15" inputmode="numeric" pattern="[0-9]*">
                    <button id="checkImeiBtn" class="action-btn">İMEI Sorgula</button>
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
        
        // İMEI Kontrol butonu işlevi
        const imeiInput = document.getElementById('imeiInput');
        const checkImeiBtn = document.getElementById('checkImeiBtn');
        const resultContainer = document.getElementById('resultContainer');
        
        // İMEI Kontrol butonu işlevi
        checkImeiBtn.addEventListener('click', () => {
            checkImei();
        });
        
        // Enter tuşuna basıldığında İMEI kontrolünü başlat
        imeiInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                checkImei();
            }
        });
        
        // Sadece sayısal giriş için
        imeiInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        function checkImei() {
            // İMEI giriş değerini alıp "Bakımda" mesajı göster
            Swal.fire({
                title: 'Bakımda',
                text: 'İMEI sorgulama sistemi şu anda bakım çalışmaları nedeniyle geçici olarak hizmet dışıdır. Lütfen daha sonra tekrar deneyiniz.',
                icon: 'info',
                confirmButtonText: 'Tamam',
                confirmButtonColor: '#4361ee'
            });
            
            // Sorgu işlemi yapılmayacak, sadece bakımda mesajı gösterilecek
        }
        
        // İMEI geçerliliğini kontrol et (Luhn algoritması)
        function isValidImei(imei) {
            // 15 haneli olmalı
            if (!/^\d{15}$/.test(imei)) {
                return false;
            }
            
            // Luhn algoritması kontrolü
            let sum = 0;
            for (let i = 0; i < 14; i++) {
                let d = parseInt(imei.charAt(i));
                if (i % 2 == 1) {
                    d *= 2;
                    if (d > 9) d -= 9;
                }
                sum += d;
            }
            
            let checkDigit = (10 - (sum % 10)) % 10;
            return checkDigit == parseInt(imei.charAt(14));
        }
        
        // Kontrol sonuçlarını göster
        function displayResults(imei, results) {
            // Sadece IMEI ve cihaz bilgisini göster
            let html = '';
            
            if (results.error) {
                html = `<div class="error-message">${results.error}</div>`;
            } else {
                html = `
                <div class="result-card">
                    <div class="result-header">
                        <div class="result-title">İMEI Sorgu Sonucu</div>
                    </div>
                    <div class="result-content">
                        <table class="result-table">
                            <tr>
                                <th>İMEI Numarası</th>
                                <th>Cihaz Marka/Modeli</th>
                            </tr>
                            <tr>
                                <td>${imei}</td>
                                <td>${results.deviceInfo}</td>
                            </tr>
                        </table>
                    </div>
                </div>`;
            }
            
            resultContainer.innerHTML = html;
        }
    </script>
    
    <style>
        .result-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .result-table th,
        .result-table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #e0e0e0;
        }
        
        .result-table th {
            background-color: #f5f5f5;
            font-weight: 600;
        }
        
        .error-message {
            background-color: #fff2f0;
            border: 1px solid #ffccc7;
            padding: 15px;
            border-radius: 4px;
            margin-top: 15px;
            color: #cf1322;
        }
        
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border-left-color: #4361ee;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</body>
</html>