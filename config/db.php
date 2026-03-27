<?php
// Fichier : config/db.php

// 1. Démarrage de la Session (TOUJOURS EN PREMIER)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Initialisation DEVISE (Si le client arrive pour la 1ère fois)
if (!isset($_SESSION['devise'])) {
    $_SESSION['devise'] = 'FCFA';
}

// 3. Initialisation LANGUE (NOUVEAU - AJOUTÉ ICI)
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'fr'; // On met Français par défaut
}

// 4. CONNEXION BASE DE DONNÉES
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
    $host = 'localhost';
    $db   = 'luxegold_db';
    $user = 'root';
    $pass = ''; 
} else {
    // Infos Hébergeur (à remplir plus tard pour la mise en ligne)
    $host = 'localhost'; 
    $db   = 'luxegold_db'; 
    $user = 'root';    
    $pass = ''; 
}

$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Erreur de connexion : " . $e->getMessage());
}

// --- CHARGEMENT DES PARAMÈTRES GLOBAUX DU SITE ---
// On crée des valeurs par défaut au cas où la base de données est vide
$config_site = [
    'nom_boutique' => 'Boutique', 
    'telephone_whatsapp' => '', 
    'email_contact' => '', 
    'adresse_boutique' => ''
];

try {
    $stmtConfig = $pdo->query("SELECT * FROM parametres WHERE id = 1");
    $resultConfig = $stmtConfig->fetch(PDO::FETCH_ASSOC);
    if ($resultConfig) {
        $config_site = $resultConfig; // On remplace par les vraies données
    }
} catch (PDOException $e) {
    // On ignore silencieusement si la table n'est pas encore créée
}
?>