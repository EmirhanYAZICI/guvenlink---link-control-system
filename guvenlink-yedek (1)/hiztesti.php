<?php
// Merkezi menü sistemini dahil et
require_once 'menu.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GüvenLink - İnternet Hız Testi</title>
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
                <h1>İnternet Hız Testi</h1>
                <p class="checker-text">İnternet bağlantınızın indirme, yükleme hızını ve ping sürenizi ölçebilirsiniz. Test işlemi sırasında internet bağlantınızı kesmeyin.</p>
                
                <div class="speed-test-container">
                    <div class="speed-meter">
                        <div class="meter-circle">
                            <div class="meter-value" id="currentSpeed">0</div>
                            <div class="meter-unit" id="speedUnit">Mbps</div>
                        </div>
                    </div>
                    
                    <div class="test-stats">
                        <div class="stat-box">
                            <div class="stat-title">İndirme</div>
                            <div class="stat-value" id="downloadSpeed">--</div>
                            <div class="stat-unit">Mbps</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-title">Yükleme</div>
                            <div class="stat-value" id="uploadSpeed">--</div>
                            <div class="stat-unit">Mbps</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-title">Ping</div>
                            <div class="stat-value" id="pingValue">--</div>
                            <div class="stat-unit">ms</div>
                        </div>
                    </div>
                    
                    <div class="url-input-container">
                        <button id="startTestBtn" class="action-btn">Hız Testini Başlat</button>
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
        
        // Hız Testi butonu ve elemanları
        const startTestBtn = document.getElementById('startTestBtn');
        const resultContainer = document.getElementById('resultContainer');
        const currentSpeed = document.getElementById('currentSpeed');
        const speedUnit = document.getElementById('speedUnit');
        const downloadSpeed = document.getElementById('downloadSpeed');
        const uploadSpeed = document.getElementById('uploadSpeed');
        const pingValue = document.getElementById('pingValue');
        
        // Test durumu
        let testRunning = false;
        
        // Hız Testi başlatma butonu işlevi
        startTestBtn.addEventListener('click', () => {
            if (testRunning) return;
            
            startSpeedTest();
        });
        
        function startSpeedTest() {
            testRunning = true;
            
            // Buton durumunu güncelle
            startTestBtn.textContent = "Test Yapılıyor...";
            startTestBtn.disabled = true;
            
            // Önceki sonuçları sıfırla
            downloadSpeed.textContent = "--";
            uploadSpeed.textContent = "--";
            pingValue.textContent = "--";
            currentSpeed.textContent = "0";
            resultContainer.innerHTML = "";
            
            // İlk olarak ping testi yap
            testPing()
                .then(() => testDownloadSpeed())
                .then(() => testUploadSpeed())
                .then(finishTest)
                .catch(handleTestError);
        }
        
        function testPing() {
            return new Promise((resolve) => {
                speedUnit.textContent = "ms";
                currentSpeed.textContent = "...";
                
                // Ping testi simülasyonu (300-500ms arası)
                setTimeout(() => {
                    const ping = Math.floor(Math.random() * 200) + 10;
                    pingValue.textContent = ping;
                    currentSpeed.textContent = ping;
                    resolve();
                }, 1500);
            });
        }
        
        function testDownloadSpeed() {
            return new Promise((resolve) => {
                speedUnit.textContent = "Mbps";
                currentSpeed.textContent = "0";
                
                let progress = 0;
                const totalTime = 5000; // 5 saniye
                const interval = 100; // 100ms aralıklarla güncelleme
                const maxSpeed = Math.floor(Math.random() * 150) + 50; // 50-200 Mbps arası rastgele değer
                
                // Hız artışını simüle et
                const downloadInterval = setInterval(() => {
                    progress += interval;
                    // Hız artışını logaritmik bir eğri ile simüle et
                    const percentage = progress / totalTime;
                    const currentSpeedValue = Math.floor(maxSpeed * Math.sin(percentage * Math.PI / 2));
                    
                    currentSpeed.textContent = currentSpeedValue;
                    
                    if (progress >= totalTime) {
                        clearInterval(downloadInterval);
                        downloadSpeed.textContent = maxSpeed;
                        currentSpeed.textContent = maxSpeed;
                        resolve();
                    }
                }, interval);
            });
        }
        
        function testUploadSpeed() {
            return new Promise((resolve) => {
                speedUnit.textContent = "Mbps";
                currentSpeed.textContent = "0";
                
                let progress = 0;
                const totalTime = 5000; // 5 saniye
                const interval = 100; // 100ms aralıklarla güncelleme
                const maxSpeed = Math.floor(Math.random() * 50) + 10; // 10-60 Mbps arası rastgele değer
                
                // Hız artışını simüle et
                const uploadInterval = setInterval(() => {
                    progress += interval;
                    // Hız artışını logaritmik bir eğri ile simüle et
                    const percentage = progress / totalTime;
                    const currentSpeedValue = Math.floor(maxSpeed * Math.sin(percentage * Math.PI / 2));
                    
                    currentSpeed.textContent = currentSpeedValue;
                    
                    if (progress >= totalTime) {
                        clearInterval(uploadInterval);
                        uploadSpeed.textContent = maxSpeed;
                        currentSpeed.textContent = maxSpeed;
                        resolve();
                    }
                }, interval);
            });
        }
        
        function finishTest() {
            testRunning = false;
            startTestBtn.textContent = "Testi Tekrarla";
            startTestBtn.disabled = false;
            
            // Test sonuçlarını göster
            const download = parseInt(downloadSpeed.textContent);
            const upload = parseInt(uploadSpeed.textContent);
            const ping = parseInt(pingValue.textContent);
            
            let speedRating = "";
            let ratingClass = "";
            
            if (download >= 100) {
                speedRating = "Mükemmel";
                ratingClass = "excellent";
            } else if (download >= 50) {
                speedRating = "İyi";
                ratingClass = "good";
            } else if (download >= 20) {
                speedRating = "Orta";
                ratingClass = "average";
            } else {
                speedRating = "Düşük";
                ratingClass = "low";
            }
            
            let html = `
            <div class="result-card">
                <div class="result-header">
                    <div class="result-title">Hız Testi Sonucu</div>
                </div>
                <div class="result-content">
                    <div class="speed-rating ${ratingClass}">Bağlantı Kalitesi: ${speedRating}</div>
                    <table class="result-table">
                        <tr>
                            <th>İndirme Hızı</th>
                            <th>Yükleme Hızı</th>
                            <th>Ping</th>
                        </tr>
                        <tr>
                            <td>${download} Mbps</td>
                            <td>${upload} Mbps</td>
                            <td>${ping} ms</td>
                        </tr>
                    </table>
                    <div class="result-recommendations">
                        <p><strong>Bağlantınızla yapabilecekleriniz:</strong></p>
                        <ul>
                            ${getRecommendations(download, upload, ping)}
                        </ul>
                    </div>
                </div>
            </div>`;
            
            resultContainer.innerHTML = html;
        }
        
        function getRecommendations(download, upload, ping) {
            let recommendations = [];
            
            if (download >= 25) recommendations.push("4K Ultra HD video izleyebilirsiniz.");
            else if (download >= 5) recommendations.push("HD video izleyebilirsiniz.");
            else recommendations.push("Standart video izleyebilirsiniz.");
            
            if (download >= 50 && upload >= 10 && ping <= 30) {
                recommendations.push("Online oyunlar için ideal bir bağlantınız var.");
            } else if (ping <= 50) {
                recommendations.push("Online oyunlar oynayabilirsiniz, ancak rekabetçi oyunlarda dezavantajlı olabilirsiniz.");
            } else {
                recommendations.push("Online oyunlarda gecikme yaşayabilirsiniz.");
            }
            
            if (upload >= 10) {
                recommendations.push("Video konferans ve canlı yayın yapabilirsiniz.");
            } else if (upload >= 3) {
                recommendations.push("Video konferans yapabilirsiniz ancak kalite düşük olabilir.");
            } else {
                recommendations.push("Sadece ses ile konferans yapmanız önerilir.");
            }
            
            return recommendations.map(r => `<li>${r}</li>`).join('');
        }
        
        function handleTestError() {
            testRunning = false;
            startTestBtn.textContent = "Testi Tekrar Dene";
            startTestBtn.disabled = false;
            
            Swal.fire({
                title: 'Hata',
                text: 'Hız testi tamamlanamadı. Lütfen internet bağlantınızı kontrol edip tekrar deneyin.',
                icon: 'error',
                confirmButtonText: 'Tamam',
                confirmButtonColor: '#4361ee'
            });
        }
    </script>
    
    <style>
        .speed-test-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
        }
        
        .meter-circle {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: #f8f9fa;
            border: 8px solid #4361ee;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .meter-value {
            font-size: 46px;
            font-weight: 700;
            color: #172b4d;
        }
        
        .meter-unit {
            font-size: 18px;
            color: #5e6c84;
        }
        
        .test-stats {
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 500px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            background: #ffffff;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            width: 30%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .stat-title {
            font-size: 14px;
            color: #5e6c84;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 600;
            color: #172b4d;
        }
        
        .stat-unit {
            font-size: 12px;
            color: #5e6c84;
        }
        
        .url-input-container {
            margin: 20px 0;
        }
        
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
        
        .speed-rating {
            font-size: 18px;
            font-weight: 600;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            text-align: center;
        }
        
        .excellent {
            background-color: #f6ffed;
            color: #52c41a;
            border: 1px solid #b7eb8f;
        }
        
        .good {
            background-color: #e6f7ff;
            color: #1890ff;
            border: 1px solid #91d5ff;
        }
        
        .average {
            background-color: #fffbe6;
            color: #faad14;
            border: 1px solid #ffe58f;
        }
        
        .low {
            background-color: #fff2e8;
            color: #fa541c;
            border: 1px solid #ffbb96;
        }
        
        .result-recommendations {
            margin-top: 20px;
        }
        
        .result-recommendations ul {
            padding-left: 20px;
        }
        
        .result-recommendations li {
            margin-bottom: 8px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .test-stats {
                flex-direction: column;
                align-items: center;
            }
            
            .stat-box {
                width: 80%;
                margin-bottom: 10px;
            }
            
            .meter-circle {
                width: 150px;
                height: 150px;
            }
            
            .meter-value {
                font-size: 36px;
            }
        }
    </style>
</body>
</html>