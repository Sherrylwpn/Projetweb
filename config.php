<?php
/* ══════════════════════════════════════════
   CONFIG COMMUNE — config.php
   Base de données : hotel
════════════════════════════════════════════ */

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'hotel');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');

/* ── CONNEXION PDO partagée (singleton) ── */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER, DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
    return $pdo;
}

/* ── GARDE DE SESSION (protège les pages) ── */
function requireLogin(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (empty($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    // Expiration après 30 min d'inactivité
    if (time() - ($_SESSION['logged_at'] ?? 0) > 1800) {
        session_unset();
        session_destroy();
        header("Location: login.php?expired=1");
        exit;
    }
    $_SESSION['logged_at'] = time();
}

/* ── GARDE ADMIN ── */
function requireAdmin(): void {
    requireLogin();
    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        http_response_code(403);
        die('Accès interdit.');
    }
}

function isAdmin(): bool {
    return ($_SESSION['user_role'] ?? '') === 'admin';
}

/* ── JETON CSRF ── */
function csrfToken(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Jeton CSRF invalide. Requête refusée.');
    }
}

/* ── SANITISATION DES ENTRÉES ── */
function sanitizeString(string $value, int $maxLen = 255): string {
    $value = trim($value);
    $value = stripslashes($value);
    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    return substr($value, 0, $maxLen);
}

function sanitizeEmail(string $value): string {
    return filter_var(trim($value), FILTER_SANITIZE_EMAIL);
}

function sanitizeInt(mixed $value): int {
    return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
}