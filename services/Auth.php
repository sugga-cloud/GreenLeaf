<?php

class Auth {

    public static function start_session() {
        if (session_status() === PHP_SESSION_ACTIVE) return;

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.gc_maxlifetime', '86400');

        session_name('GLSESSID');
        session_set_cookie_params([
            'lifetime' => 86400,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        session_start();

        self::_validate_fingerprint();
    }

    private static function _fingerprint() {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return hash('sha256', $ua . '|' . substr($ip, 0, strrpos($ip, '.') + 1) . '|' . ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
    }

    private static function _validate_fingerprint() {
        if (empty($_SESSION['user_id'])) return;
        $expected = self::_fingerprint();
        if (!isset($_SESSION['_fp'])) {
            $_SESSION['_fp'] = $expected;
            return;
        }
        if (!hash_equals($_SESSION['_fp'], $expected)) {
            self::clear_session();
        }
    }

    public static function is_logged_in() {
        self::start_session();
        return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
    }

    public static function user_id() {
        self::start_session();
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    }

    public static function user_role() {
        self::start_session();
        return $_SESSION['user_role'] ?? null;
    }

    public static function is_student() {
        return self::is_logged_in() && self::user_role() === 'student';
    }

    public static function is_admin() {
        return self::is_logged_in() && self::user_role() === 'admin';
    }

    public static function login_student($user_id) {
        self::start_session();
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user_id;
        $_SESSION['user_role'] = 'student';
        $_SESSION['_fp'] = self::_fingerprint();
        $_SESSION['_login_at'] = time();
    }

    public static function login_admin() {
        self::start_session();
        session_regenerate_id(true);
        $_SESSION['user_id'] = 0;
        $_SESSION['user_role'] = 'admin';
        $_SESSION['_fp'] = self::_fingerprint();
        $_SESSION['_login_at'] = time();
    }

    public static function clear_session() {
        self::start_session();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'   => time() - 42000,
                'path'      => $params['path'],
                'domain'    => $params['domain'],
                'secure'    => $params['secure'],
                'httponly'  => $params['httponly'],
                'samesite'  => $params['samesite'] ?? 'Lax'
            ]);
        }
        session_destroy();
    }

    public static function require_student() {
        self::start_session();
        if (!self::is_student()) {
            if (self::is_admin()) {
                echo '<script>window.location.href = "?page=admin_dashboard&err=admin_cannot_access_student";</script>';
            } else {
                echo '<script>window.location.href = "?page=auth&mode=login&err=unauthorized_student";</script>';
            }
            exit;
        }
        return self::user_id();
    }

    public static function require_admin() {
        self::start_session();
        if (!self::is_admin()) {
            echo '<script>window.location.href = "?page=auth&mode=login&err=unauthorized_admin";</script>';
            exit;
        }
        return self::user_id();
    }

    public static function require_login_json() {
        self::start_session();
        if (!self::is_logged_in()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Not authenticated. Please sign in.']);
            exit;
        }
        return self::user_id();
    }

    public static function csrf_token() {
        self::start_session();
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function csrf_field() {
        return '<input type="hidden" name="csrf_token" value="' . self::csrf_token() . '">';
    }

    public static function verify_csrf() {
        self::start_session();
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return !empty($token) && hash_equals($_SESSION['_csrf_token'] ?? '', $token);
    }

    public static function hash_password($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verify_password($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function is_beta() {
        self::start_session();
        if (isset($_SESSION['beta_mode'])) {
            return !empty($_SESSION['beta_mode']);
        }
        // Check DB if not set in session yet
        global $db;
        if (!$db) {
            require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sqlite' . DIRECTORY_SEPARATOR . 'db.php';
        }
        $db_val = $db->query("SELECT value FROM settings WHERE key = 'beta_mode'")->fetchColumn();
        $_SESSION['beta_mode'] = ($db_val === '1');
        return $_SESSION['beta_mode'];
    }

    public static function set_beta($enabled) {
        self::start_session();
        $_SESSION['beta_mode'] = $enabled;
    }

    public static function beta_perm($perm_name) {
        // When beta mode is ON, check the beta_* permission override setting
        global $db;
        if (!self::is_beta()) return null;
        $val = $db->query("SELECT value FROM settings WHERE key = 'beta_$perm_name'")->fetchColumn();
        if ($val === null) return null;
        return $val === '1';
    }

    public static function beta_global_credits() {
        global $db;
        if (!self::is_beta()) return null;
        $val = $db->query("SELECT value FROM settings WHERE key = 'beta_global_credits'")->fetchColumn();
        if ($val === null) return null;
        return (int)$val;
    }

    public static function login_dev_bypass($email) {
        global $db;
        self::start_session();
        // Only works if dev_login_enabled is on in settings
        $dev_enabled = $db->query("SELECT value FROM settings WHERE key = 'dev_login_enabled'")->fetchColumn();
        if (!$dev_enabled) {
            return false;
        }
        $user_stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $user_stmt->execute([$email]);
        $student = $user_stmt->fetch();
        if ($student) {
            self::login_student((int)$student['id']);
            return true;
        }
        // Check admin
        $admin_email = $db->query("SELECT value FROM settings WHERE key = 'admin_username'")->fetchColumn() ?: 'admin@greenleaf.com';
        if ($email === $admin_email) {
            self::login_admin();
            return true;
        }
        return false;
    }
}
