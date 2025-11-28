<?php
/**
 * GüvenLink Merkezi Menü Sistemi
 * Bu dosya, tüm sayfalarda kullanılacak menü yapısını merkezi olarak yönetir.
 */

// Menü öğelerini tanımlama
$menuItems = [
    [
        'url' => 'https://guvenlink.net/',
        'icon' => '🏠',
        'text' => 'Ana Sayfa'
    ],
        [
        'url' => 'whois.php',
        'icon' => '🔍',
        'text' => 'Whois Sorgula'
    ],
        [
        'url' => 'ip-sorgu.php',
        'icon' => '🌍',
        'text' => 'IP Sorgula'
    ],
            [
        'url' => 'imei-sorgu.php',
        'icon' => '📱',
        'text' => 'Imei Sorgula'
    ],
    [
        'url' => 'url-kontrol.php',
        'icon' => '🔗',
        'text' => 'Link Kontrolü'
    ],
    [
        'url' => 'dosya-kontrol.php',
        'icon' => '📄',
        'text' => 'Dosya Kontrolü'
    ],
];

// Duyurular için merkezi verileri tanımlama
$announcements = [
    [
        'title' => 'Bakım',
        'date' => '12 Mart 2025',
        'text' => 'İmei sorgulama servisi bakıma alındı.'
    ],
    [
        'title' => 'Yeni Güncelleme',
        'date' => '12 Mart 2025',
        'text' => 'GüvenLink URL kara listemiz güncellendi.'
    ],
    [
        'title' => 'Sitemiz Aktif!',
        'date' => '11 Mart 2025',
        'text' => 'Sitemiz hizmete açıldı!'
    ],
];

/**
 * Sidebar (yan menü) HTML'ini oluşturan fonksiyon
 */
function getSidebarHTML() {
    global $menuItems;
    
    $html = '<div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">GüvenLink</div>
            <button class="close-sidebar" id="closeSidebar">&times;</button>
        </div>
        <ul class="nav-links">';
    
    // Menü öğelerini ekle
    foreach ($menuItems as $item) {
        $html .= '<li><a href="' . $item['url'] . '"><span>' . $item['icon'] . '</span> ' . $item['text'] . '</a></li>';
    }
    
    $html .= '</ul>
    </div>';
    
    return $html;
}

/**
 * Header (üst menü) HTML'ini oluşturan fonksiyon
 */
function getHeaderHTML() {
    $html = '<header>
        <button class="menu-btn" id="menuBtn">☰</button>
        <div class="logo">GüvenLink</div>
        <div class="right-icons">
            <button class="theme-btn" id="themeBtn">◐</button>
            <button class="notification-btn" id="notificationBtn">🔔<span class="notification-dot"></span></button>
        </div>
    </header>';
    
    return $html;
}

/**
 * Overlay (arka plan kapatma) HTML'ini oluşturan fonksiyon
 */
function getOverlayHTML() {
    return '<div class="overlay" id="overlay"></div>';
}

/**
 * Bildirimler paneli HTML'ini oluşturan fonksiyon
 */
function getNotificationsHTML() {
    global $announcements;
    
    $html = '<div class="notifications-container" id="notifications">
        <div class="notifications-header">
            <h3>Bildirimler</h3>
            <button class="close-notifications" id="closeNotifications">&times;</button>
        </div>';
    
    // Duyuruları ekle
    foreach ($announcements as $announcement) {
        $html .= '<div class="announcement">
            <div class="announcement-title">' . $announcement['title'] . '</div>
            <div class="announcement-date"><span>📅</span> ' . $announcement['date'] . '</div>
            <div class="announcement-text">' . $announcement['text'] . '</div>
        </div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Sidebar duyurular bölümü HTML'ini oluşturan fonksiyon
 */
function getSidebarAnnouncementsHTML() {
    global $announcements;
    
    $html = '<div class="card announcements">
        <h2 style="color: var(--primary-color);"><span>🔔</span> Duyurular</h2>';
    
    // Duyuruları ekle
    foreach ($announcements as $announcement) {
        $html .= '<div class="announcement">
            <div class="announcement-title">' . $announcement['title'] . '</div>
            <div class="announcement-date"><span>📅</span> ' . $announcement['date'] . '</div>
            <div class="announcement-text">' . $announcement['text'] . '</div>
        </div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Menü JavaScript kodunu oluşturan fonksiyon
 */
function getMenuJavaScriptCode() {
    return "
    // DOM elemanlarını seçme
    const menuBtn = document.getElementById('menuBtn');
    const sidebar = document.getElementById('sidebar');
    const closeSidebar = document.getElementById('closeSidebar');
    const overlay = document.getElementById('overlay');
    const themeBtn = document.getElementById('themeBtn');
    const notificationBtn = document.getElementById('notificationBtn');
    const notifications = document.getElementById('notifications');
    const closeNotifications = document.getElementById('closeNotifications');
    
    // Menü açma/kapama
    menuBtn.addEventListener('click', () => {
        sidebar.classList.add('active');
        overlay.classList.add('active');
    });
    
    closeSidebar.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });
    
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        notifications.classList.remove('active');
    });
    
    // Tema değiştirme
    themeBtn.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        const isDarkMode = document.body.classList.contains('dark-mode');
        themeBtn.textContent = isDarkMode ? '☀️' : '◐';
        
        // Tema tercihini localStorage'a kaydet
        localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
    });
    
    // Bildirimler açma/kapama
    notificationBtn.addEventListener('click', (event) => {
        event.stopPropagation(); // Tıklama olayının dökümana yayılmasını engelle
        notifications.classList.toggle('active');
    });
    
    closeNotifications.addEventListener('click', () => {
        notifications.classList.remove('active');
    });
    
    // Sayfa içindeki herhangi bir yere tıklandığında bildirimleri kapat
    document.addEventListener('click', (event) => {
        // Eğer tıklanan eleman bildirimler paneli veya bildirim butonu değilse
        if (!notifications.contains(event.target) && event.target !== notificationBtn) {
            notifications.classList.remove('active');
        }
    });
    
    // Sayfa yüklendiğinde tema tercihini kontrol et
    document.addEventListener('DOMContentLoaded', () => {
        // Kaydedilmiş tema tercihini kontrol et ve uygula
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            themeBtn.textContent = '☀️';
        }
    });";
}
?>