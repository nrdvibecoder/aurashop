<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_email'])) {
    $email = filter_var($_POST['newsletter_email'], FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            
            if (!$pdo) {
                $dbUrlTemp = getenv('DATABASE_URL') ?: ($_ENV['DATABASE_URL'] ?? null);
                if ($dbUrlTemp) {
                    $parsedUrlTemp = parse_url($dbUrlTemp);
                    $hostTemp = $parsedUrlTemp['host'] ?? 'localhost';
                    $portTemp = $parsedUrlTemp['port'] ?? '5432';
                    $userTemp = rawurldecode($parsedUrlTemp['user'] ?? '');
                    $passTemp = rawurldecode($parsedUrlTemp['pass'] ?? '');
                    $dbnameTemp = ltrim($parsedUrlTemp['path'] ?? '', '/');
                    $dsnTemp = "pgsql:host=$hostTemp;port=$portTemp;dbname=$dbnameTemp;sslmode=require";
                } else {
                    $dsnTemp = "pgsql:host=localhost;port=5432;dbname=aurashop";
                    $userTemp = "postgres";
                    $passTemp = "";
                }
                $pdo = new PDO($dsnTemp, $userTemp, $passTemp, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            }
            if ($pdo) {
                $stmt = $pdo->prepare("INSERT INTO newsletter (email) VALUES (?) ON CONFLICT (email) DO NOTHING");
                $stmt->execute([$email]);
            }
            $_SESSION['newsletter_msg'] = "Merci pour votre inscription ! / Thank you for subscribing!";
        } catch (PDOException $e) {
            $_SESSION['newsletter_msg'] = "Erreur: " . $e->getMessage();
        }
    } else {
        $_SESSION['newsletter_msg'] = "Adresse email invalide. / Invalid email address.";
    }
    
    $redirect_url = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: " . $redirect_url);
    exit;
}

$dbUrl = getenv('DATABASE_URL') ?: ($_ENV['DATABASE_URL'] ?? null);
$pdo = null;
$pdoError = null;

try {
    if ($dbUrl) {
        $parsedUrl = parse_url($dbUrl);
        $host = $parsedUrl['host'] ?? 'localhost';
        $port = $parsedUrl['port'] ?? '5432';
        $user = rawurldecode($parsedUrl['user'] ?? '');
        $pass = rawurldecode($parsedUrl['pass'] ?? '');
        $dbname = ltrim($parsedUrl['path'] ?? '', '/');
        
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    } else {
        
        $dsn = "pgsql:host=localhost;port=5432;dbname=aurashop";
        $user = "postgres";
        $pass = "";
    }

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    $pdo = null;
    $pdoError = $e->getMessage();
}

function get_cart() {
    if (isset($_COOKIE['aura_cart'])) {
        $cart = json_decode($_COOKIE['aura_cart'], true);
        return is_array($cart) ? $cart : [];
    }
    return [];
}

function save_cart($cart) {
    setcookie('aura_cart', json_encode($cart), time() + (86400 * 30), '/');
}

function get_auth() {
    if (isset($_COOKIE['aura_auth'])) {
        $auth = json_decode($_COOKIE['aura_auth'], true);
        return is_array($auth) ? $auth : null;
    }
    return null;
}

function set_auth($id, $role) {
    $authData = ['id' => (int)$id, 'role' => $role];
    setcookie('aura_auth', json_encode($authData), time() + (86400 * 30), '/');
}

function clear_auth() {
    setcookie('aura_auth', '', time() - 3600, '/');
}

function requireAuth() {
    $auth = get_auth();
    if (!$auth) {
        $redirect = urlencode($_SERVER['REQUEST_URI']);
        header("Location: login.php?redirect=" . $redirect);
        exit;
    }
    return $auth;
}

function requireAdmin() {
    $auth = get_auth();
    if (!$auth || $auth['role'] !== 'admin') {
        header("Location: index.php");
        exit;
    }
    return $auth;
}

function format_price($amount) {
    return number_format((int)$amount, 0, '.', ' ') . ' DA';
}

function sanitize($v) {
    return htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
}

function generate_order_number() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

require_once 'lang.php';
$language = $_COOKIE['lang'] ?? 'fr';
if (!in_array($language, ['en', 'fr'])) {
    $language = 'fr';
}

function __($key) {
    global $translations, $language;
    return $translations[$language][$key] ?? $key;
}
