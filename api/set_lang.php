<?php
$lang = $_GET['lang'] ?? 'fr';
if (in_array($lang, ['en', 'fr'])) {
    setcookie('lang', $lang, time() + (86400 * 30), '/');
}
$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header("Location: " . $redirect);
exit;
