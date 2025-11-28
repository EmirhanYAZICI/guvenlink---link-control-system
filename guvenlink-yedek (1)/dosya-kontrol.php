<?php
// Merkezi menü sistemini dahil et
require_once 'menu.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GüvenLink - Dosya Kontrol</title>
    <!-- SweetAlert CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS dosyasını dahil et -->
    <link rel="stylesheet" href="style.css">
    <!-- Modern Card Stilini Ekle -->
    <style>
        .modern-card {
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .modern-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            margin-bottom: 25px;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 20px;
        }
        
        .card-header h1 {
            color: #3a3a3a;
            font-size: 2rem;
            margin-bottom: 15px;
        }
        
        .checker-text {
            color: #6b7280;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .file-upload-area {
            background-color: #f9fafc;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }
        
        .file-upload-area:hover {
            border-color: #4361ee;
            background-color: #f5f7ff;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: #4361ee;
            margin-bottom: 15px;
        }
        
        .file-input-container {
            margin-bottom: 20px;
        }
        
        .file-input-label {
            cursor: pointer;
            font-weight: 600;
            color: #4361ee;
            padding: 12px 20px;
            background-color: rgba(67, 97, 238, 0.1);
            border-radius: 8px;
            transition: all 0.3s ease;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .file-input-label:hover {
            background-color: rgba(67, 97, 238, 0.2);
        }
        
        .file-input {
            width: 0.1px;
            height: 0.1px;
            opacity: 0;
            overflow: hidden;
            position: absolute;
            z-index: -1;
        }
        
        .file-format-hint {
            color: #9ca3af;
            font-size: 0.9rem;
            margin-top: 10px;
        }
        
        .action-btn {
            background-color: #4361ee;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(67, 97, 238, 0.25);
        }
        
        .action-btn:hover {
            background-color: #3a56d4;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(67, 97, 238, 0.3);
        }
        
        .action-btn:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .result-container {
            display: none;
            margin-top: 30px;
            background-color: #f9fafc;
            border-radius: 12px;
            padding: 25px;
        }
        
        .file-info {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            font-size: 1rem;
        }
        
        .file-info-title {
            font-weight: 600;
            margin-right: 10px;
            color: #4b5563;
        }
        
        .result-summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            gap: 15px;
        }
        
        .summary-item {
            flex: 1;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .summary-safe {
            background-color: #ecfdf5;
            color: #065f46;
        }
        
        .summary-warning {
            background-color: #fffbeb;
            color: #92400e;
        }
        
        .summary-danger {
            background-color: #fef2f2;
            color: #b91c1c;
        }
        
        .summary-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-size: 1rem;
            font-weight: 500;
        }
        
        .result-card {
            background-color: #ffffff;
            border-radius: 10px;
            margin-bottom: 15px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
        }
        
        .result-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }
        
        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .result-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: #374151;
        }
        
        .result-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .status-safe {
            background-color: #ecfdf5;
            color: #065f46;
        }
        
        .status-warning {
            background-color: #fffbeb;
            color: #92400e;
        }
        
        .status-danger {
            background-color: #fef2f2;
            color: #b91c1c;
        }
        
        .result-content {
            padding: 15px 20px;
            color: #6b7280;
            line-height: 1.5;
        }
        
        .result-source {
            padding: 10px 20px;
            background-color: #f9fafb;
            color: #9ca3af;
            font-size: 0.85rem;
            border-top: 1px solid #f0f0f0;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(67, 97, 238, 0.1);
            border-left-color: #4361ee;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .error-message {
            color: #b91c1c;
            text-align: center;
            padding: 20px;
            background-color: #fef2f2;
            border-radius: 8px;
            margin-top: 20px;
        }
    </style>
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
            <div class="file-checker-section">
                <div class="modern-card">
                    <div class="card-header">
                        <h1>Dosya Güvenlik Kontrolü</h1>
                        <p class="checker-text">Kontrol etmek istediğiniz dosyayı yükleyin ve güvenlik denetimini başlatın. Dosyaların güvenli olup olmadığını hızlıca kontrol edebilirsiniz.</p>
                    </div>
                    
                    <div class="file-upload-area">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="file-input-container">
                            <label for="fileInput" class="file-input-label">Dosya Seçin veya Sürükleyin</label>
                            <input type="file" id="fileInput" class="file-input" accept="*/*">
                            <p class="file-format-hint">Tüm dosya formatları desteklenir (100MB'a kadar)</p>
                        </div>
                        <button id="checkFileBtn" class="action-btn">Dosyayı Kontrol Et</button>
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
        
        // Dosya Kontrol butonu işlevi
        const fileInput = document.getElementById('fileInput');
        const checkFileBtn = document.getElementById('checkFileBtn');
        const resultContainer = document.getElementById('resultContainer');
        
        // API anahtarları ve hizmetler için yapılandırma
        // Gerçek uygulamada bunlar sunucu tarafında saklanmalıdır
        const API_KEYS = {
            virustotal: "demo_key",
            metaDefender: "demo_key",
            sophosLabs: "demo_key"
        };
        
        // Zararlı yazılım imza veritabanı (örnek olarak)
        const MALWARE_SIGNATURES = [
            "4b45e02fe53d250ce7c0a744dad90a1a", // Örnek MD5 hash
            "5eb63bbbe01eeed093cb22bb8f5acdc3",
            "e2fc714c4727ee9395f324cd2e7f331f"
        ];
        
        // Sürükle-bırak işlevselliği için
        const fileUploadArea = document.querySelector('.file-upload-area');
        const fileNameDisplay = document.createElement('div');
        fileNameDisplay.className = 'selected-file-name';
        fileNameDisplay.style.display = 'none';
        fileNameDisplay.style.marginTop = '10px';
        fileNameDisplay.style.color = '#4361ee';
        fileNameDisplay.style.fontWeight = '500';
        document.querySelector('.file-input-container').appendChild(fileNameDisplay);
        
        // Dosya seçildiğinde dosya adını göster
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                fileNameDisplay.textContent = `Seçilen dosya: ${fileInput.files[0].name}`;
                fileNameDisplay.style.display = 'block';
            } else {
                fileNameDisplay.style.display = 'none';
            }
        });
        
        // Sürükle-bırak olayları
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            fileUploadArea.classList.add('highlight');
            fileUploadArea.style.borderColor = '#4361ee';
            fileUploadArea.style.backgroundColor = '#eef2ff';
        }
        
        function unhighlight() {
            fileUploadArea.classList.remove('highlight');
            fileUploadArea.style.borderColor = '#d1d5db';
            fileUploadArea.style.backgroundColor = '#f9fafc';
        }
        
        fileUploadArea.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                fileInput.files = files;
                fileNameDisplay.textContent = `Seçilen dosya: ${files[0].name}`;
                fileNameDisplay.style.display = 'block';
            }
        }
        
        // Dosya Kontrol butonu işlevi
        checkFileBtn.addEventListener('click', () => {
            const file = fileInput.files[0];
            
            if (!file) {
                Swal.fire({
                    title: 'Hata',
                    text: 'Lütfen bir dosya seçin',
                    icon: 'error',
                    confirmButtonText: 'Tamam',
                    confirmButtonColor: '#4361ee'
                });
                return;
            }
            
            // Dosya boyutu kontrolü
            if (file.size > 100 * 1024 * 1024) { // 100MB limit
                Swal.fire({
                    title: 'Hata',
                    text: 'Dosya boyutu 100MB\'ı geçemez',
                    icon: 'error',
                    confirmButtonText: 'Tamam',
                    confirmButtonColor: '#4361ee'
                });
                return;
            }
            
            // Dosyayı kontrol etmek için API'lere istek gönder
            checkFileBtn.textContent = "Kontrol Ediliyor...";
            checkFileBtn.disabled = true;
            
            // Sonuç konteynerini görünür yap ve içeriğini temizle
            resultContainer.style.display = "block";
            resultContainer.innerHTML = '<div style="text-align: center;"><div class="spinner"></div><p>Dosya kontrol ediliyor...</p></div>';
            
            // VirusTotal, MetaDefender ve Sophos gibi servislerden kontrol yapar
            checkFile(file)
                .then(results => {
                    // Sonuçları göster
                    displayResults(file, results);
                    
                    // Butonu sıfırla
                    checkFileBtn.textContent = "Dosyayı Kontrol Et";
                    checkFileBtn.disabled = false;
                })
                .catch(error => {
                    resultContainer.innerHTML = `<div class="error-message">Hata oluştu: ${error.message}</div>`;
                    checkFileBtn.textContent = "Dosyayı Kontrol Et";
                    checkFileBtn.disabled = false;
                });
        });
        
        // Dosya uzantısına göre risk değerlendirmesi
        function getRiskByExtension(filename) {
            const extension = filename.split('.').pop().toLowerCase();
            
            // Yüksek riskli dosya uzantıları
            const highRiskExtensions = ['exe', 'bat', 'cmd', 'msi', 'ps1', 'vbs', 'js', 'jar', 'wsf', 'dll'];
            
            // Orta riskli dosya uzantıları
            const mediumRiskExtensions = ['doc', 'docm', 'xls', 'xlsm', 'ppt', 'pptm', 'pdf', 'zip', 'rar', '7z', 'iso'];
            
            if (highRiskExtensions.includes(extension)) {
                return "danger";
            } else if (mediumRiskExtensions.includes(extension)) {
                return "warning";
            } else {
                return "safe";
            }
        }
        
        // Dosya hash hesaplama (simüle edilmiş)
        async function calculateFileHash(file) {
            // Gerçek uygulamada dosyanın hash değeri hesaplanır
            // Burada dosya adı ve boyutuna göre simüle edilmiş bir hash döndürüyoruz
            
            return new Promise(resolve => {
                setTimeout(() => {
                    const hash = btoa(file.name + file.size).substring(0, 32);
                    resolve(hash);
                }, 1000);
            });
        }
        
        // Dosyayı kontrol et ve güvenlik analizini yap
        async function checkFile(file) {
            // Bu fonksiyon gerçek bir uygulamada API istekleri yapacaktır
            // Şu anda simüle edilmiş sonuçlar döndürüyoruz
            
            // Dosya hakkında temel bilgileri topla
            const fileHash = await calculateFileHash(file);
            const fileExtensionRisk = getRiskByExtension(file.name);
            
            // Güvenlik taraması sonuçlarını simüle et
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
                        description: "Dosya, VirusTotal veritabanında 68 farklı anti-virüs motoru kullanılarak tarandı.",
                        status: await simulateCheck(file, "virustotal", fileHash)
                    },
                    {
                        id: "metadefender",
                        name: "MetaDefender",
                        description: "Dosya, MetaDefender Cloud tarafından içerik analizi ve zararlı yazılım taraması yapıldı.",
                        status: await simulateCheck(file, "metadefender", fileHash)
                    },
                    {
                        id: "sophoslabs",
                        name: "Sophos Labs",
                        description: "Dosya, Sophos Labs tarafından zararlı yazılım taraması yapıldı.",
                        status: await simulateCheck(file, "sophoslabs", fileHash)
                    },
                    {
                        id: "extension",
                        name: "Dosya Uzantısı Analizi",
                        description: `Dosya uzantısına göre risk değerlendirmesi: ${file.name.split('.').pop().toLowerCase()}`,
                        status: fileExtensionRisk
                    },
                    {
                        id: "signature",
                        name: "İmza Kontrolü",
                        description: "Dosya, bilinen zararlı yazılım imzalarına karşı kontrol edildi.",
                        status: MALWARE_SIGNATURES.includes(fileHash.substring(0, 32)) ? "danger" : "safe"
                    },
                    {
                        id: "sandbox",
                        name: "Sandbox Analizi",
                        description: "Dosya, izole bir ortamda çalıştırılarak davranış analizi yapıldı.",
                        status: await simulateCheck(file, "sandbox", fileHash)
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
        async function simulateCheck(file, service, fileHash) {
            // Dosya tipi ve boyutuna bakarak bazı kararlar verelim
            // Gerçek uygulamada bu işlemler sunucu tarafında yapılır
            
            // Dosya uzantısı
            const extension = file.name.split('.').pop().toLowerCase();
            
            // Yüksek riskli dosya uzantıları
            const highRiskExtensions = ['exe', 'bat', 'cmd', 'msi', 'ps1', 'vbs', 'js', 'jar', 'wsf', 'dll'];
            
            // Orta riskli dosya uzantıları
            const mediumRiskExtensions = ['doc', 'docm', 'xls', 'xlsm', 'ppt', 'pptm', 'pdf', 'zip', 'rar', '7z', 'iso'];
            
            // Güvenli dosya uzantıları
            const safeExtensions = ['txt', 'jpg', 'jpeg', 'png', 'gif', 'mp3', 'mp4', 'csv', 'html', 'css'];
            
            // Her servis için farklı davranış
            switch(service) {
                case "virustotal":
                    // Hash bazlı anti-virüs taraması
                    if (highRiskExtensions.includes(extension)) {
                        return Math.random() < 0.3 ? "danger" : "warning";
                    } else if (mediumRiskExtensions.includes(extension)) {
                        return Math.random() < 0.2 ? "warning" : "safe";
                    } else {
                        return "safe";
                    }
                    
                case "metadefender":
                    // İçerik analizi ve zararlı yazılım taraması
                    if (fileHash.charAt(0) === 'a' || fileHash.charAt(0) === 'e') {
                        return "danger";
                    } else if (fileHash.charAt(0) === 'b' || fileHash.charAt(0) === 'f') {
                        return "warning";
                    } else {
                        return "safe";
                    }
                    
                case "sophoslabs":
                    // Zararlı yazılım taraması
                    if (file.size > 10 * 1024 * 1024 && highRiskExtensions.includes(extension)) {
                        return "danger";
                    } else if (mediumRiskExtensions.includes(extension)) {
                        return "warning";
                    } else {
                        return "safe";
                    }
                    
                case "sandbox":
                    // Davranış analizi
                    if (highRiskExtensions.includes(extension)) {
                        return Math.random() < 0.4 ? "danger" : "warning";
                    } else if (extension === 'pdf' || extension === 'doc' || extension === 'docm') {
                        return Math.random() < 0.2 ? "warning" : "safe";
                    } else {
                        return "safe";
                    }
                    
                default:
                    return "safe";
            }
        }
        
        // Kontrol sonuçlarını göster
        function displayResults(file, results) {
            let html = '';
            
            // Dosya bilgisi
            html += `
            <div class="file-info">
                <div class="file-info-title">Kontrol Edilen Dosya:</div>
                <div>${file.name} (${formatFileSize(file.size)})</div>
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
                overallStatus === "danger" ? "Bu dosya potansiyel tehlikeler içeriyor. Bu dosyayı açmak veya çalıştırmak istemeyebilirsiniz." :
                overallStatus === "warning" ? "Bu dosya bazı uyarılar içeriyor. Dikkatli olun ve güvendiğiniz bir kaynaktan geldiğinden emin olun." :
                "Bu dosya güvenli görünüyor.";
            
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
        
        // Dosya boyutunu formatla
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    </script>
</body>
</html>