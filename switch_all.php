<?php
// Fichier : switch_all.php
session_start();

if (isset($_GET['country'])) {
    $country = $_GET['country'];

    if ($country == 'SN') {
        $_SESSION['lang'] = 'fr';
        $_SESSION['devise'] = 'FCFA';
    } 
    elseif ($country == 'GM') {
        $_SESSION['lang'] = 'en';
        $_SESSION['devise'] = 'GMD';
    }
}

// Retour automatique à la page d'où l'on vient
if (isset($_SERVER['HTTP_REFERER'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    header('Location: index.php');
}
exit();