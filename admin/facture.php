<?php
// admin/facture.php
session_start();
require_once 'check_auth.php'; 
require_once '../config/db.php';

// On inclut notre nouveau moteur de facture
require_once 'includes/generateur_facture.php'; 

$id_commande = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_commande > 0) {
    // On appelle la fonction en mode 'I' pour afficher à l'écran
    genererLaFacturePDF($pdo, $id_commande, 'I');
} else {
    die("ID Commande invalide.");
}
?>