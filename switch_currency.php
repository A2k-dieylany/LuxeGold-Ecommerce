<?php
// Fichier : switch_currency.php
session_start();

// 1. On récupère la devise demandée (FCFA ou GMD)
if (isset($_GET['to'])) {
    $_SESSION['devise'] = $_GET['to'];
}

// 2. On retourne à la page précédente
if (isset($_SERVER['HTTP_REFERER'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    // Si on ne sait pas d'où tu viens, retour accueil
    header('Location: index.php');
}
exit();
?>