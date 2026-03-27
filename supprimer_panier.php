<?php
require_once 'config/db.php'; // Pour accéder à la session et à la BDD

// On récupère l'ID du produit à supprimer
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // On vérifie si le produit existe dans le panier
    if (isset($_SESSION['panier'][$id])) {
        // On retire l'entrée spécifique du tableau panier
        unset($_SESSION['panier'][$id]);
    }
}

// Redirection immédiate vers le panier pour voir le changement
header('Location: panier.php');
exit();
?>