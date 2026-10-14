<?php
/* kkw_check.php - open this if something does not work. It shows what is OK and what is missing. */
echo '<div style="font-family:Arial;max-width:650px;margin:30px auto">';
echo '<h2>KKW Result Portal - System Check</h2><ul style="line-height:2">';
echo '<li>PHP version: <b>' . PHP_VERSION . '</b> ' . (version_compare(PHP_VERSION, '7.4', '>=') ? '&#9989;' : '&#10060; (need 7.4 or newer)') . '</li>';
echo '<li>mysqli extension: ' . (extension_loaded('mysqli') ? '&#9989; loaded' : '&#10060; missing (enable extension=mysqli in php.ini)') . '</li>';
$files = ['kkw_db_config.php', 'kkw_parts/kkw_top.php', 'kkw_images/kkw_banner.png', 'kkw_assets/kkw_style.css',
          'kkw_assets/kkw_bootstrap/bootstrap.min.css', 'kkw_assets/kkw_js/html2pdf.bundle.min.js'];
foreach ($files as $fl) {
    echo '<li>' . $fl . ': ' . (file_exists(__DIR__ . '/' . $fl) ? '&#9989;' : '&#10060; missing - extract the full ZIP again') . '</li>';
}
echo '</ul>';
require __DIR__ . '/kkw_db_config.php';   // shows a clear message if MySQL fails
echo '<p style="color:green;font-size:1.2em">&#9989; MySQL connected. Database <b>' . $KKW_DB . '</b> and tables are ready.</p>';
echo '<p><a href="kkw_login.php">Go to Login page</a> (admin / admin123)</p></div>';
