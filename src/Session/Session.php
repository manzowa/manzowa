<?php
namespace App\Session;

class Session
{
    private int $_inactivityLimit = 1800; // 30 minutes = 1800 secondes
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Vérifie si la session a expiré
        $this->checkExpiration();
    }

    // Régénère l'identifiant de session
    public function regenerate()
    {
        session_regenerate_id(true);
    }

    public function set($key, $value): void
    {
        $_SESSION[$key] = $value;
        $this->updateLastActivity();  // Met à jour la dernière activité
    }

    public function get($key)
    {
        $this->updateLastActivity();  // Met à jour la dernière activité
        return $_SESSION[$key] ?? null;
    }

    public function has($key): bool
    {
        $this->updateLastActivity();  // Met à jour la dernière activité
        return isset($_SESSION[$key]);
    }

    public function delete($key):void
    {
        if ($this->has($key)) {
            unset($_SESSION[$key]);
        }
    }

     // Vérifie si la session a expiré
    public function checkExpiration(): void
    {
        if (isset($_SESSION['last_activity']) && isset($_SESSION['user'])) {
            $session_lifetime = time() - $_SESSION['last_activity'];

            // Si la session a été inactive plus longtemps que la limite, on la détruit
            if ($session_lifetime > $this->_inactivityLimit) {
                $this->destroy();  // Détruire la session si elle est expirée
                \App\redirectToRoute('/authentification/connexion');  // Rediriger vers la page de connexion
                exit;
            }
        }
    }

     // Met à jour le timestamp de la dernière activité
    public function updateLastActivity(): void
    {
        $_SESSION['last_activity'] = time();
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
