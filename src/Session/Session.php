<?php
namespace App\Session;

class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Régénère l'identifiant de session
    public function regenerate()
    {
        session_regenerate_id(true);
    }

    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function get($key)
    {
        return $_SESSION[$key] ?? null;
    }

    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    public function delete($key)
    {
        if ($this->has($key)) {
            unset($_SESSION[$key]);
        }
    }

    public function destroy()
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
?>
