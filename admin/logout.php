<?php
// 1. On initialise la session pour pouvoir y accéder
session_start();

// 2. On vide toutes les variables de session
$_SESSION = array();

// 3. On détruit physiquement la session sur le serveur
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// 4. On redirige vers la page de connexion
header("Location: login.php");
exit();
?>