<?php
session_start();

// 1. On récupère la langue demandée (fr ou en)
if (isset($_GET['lang'])) {
    $lang_code = $_GET['lang'];
    
    // Petite sécurité : on n'accepte que fr ou en
    if ($lang_code == 'fr' || $lang_code == 'en') {
        $_SESSION['lang'] = $lang_code;
    }
}

// 2. Retour à la page précédente
if (isset($_SERVER['HTTP_REFERER'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    header('Location: index.php');
}
exit();
?>