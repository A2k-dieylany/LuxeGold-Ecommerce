<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si l'admin n'est pas connecté, redirection immédiate
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// INCLUSION AUTOMATIQUE POUR TOUTES LES PAGES ADMIN
// On utilise dirname(__FILE__) pour garantir que le chemin est correct peu importe la page
require_once dirname(__FILE__) . '/../includes/fonctions.php';