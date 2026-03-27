<?php
session_start();
header('Content-Type: application/json');

if (isset($_POST['produit_id'])) {
    $id = (int)$_POST['produit_id'];
    $quantite = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 1;

    // Créer le panier s'il n'existe pas
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }

    // Ajouter ou incrémenter
    if (isset($_SESSION['panier'][$id])) {
        $_SESSION['panier'][$id] += $quantite;
    } else {
        $_SESSION['panier'][$id] = $quantite;
    }

    // Calculer le nouveau total d'articles
    $total_articles = array_sum($_SESSION['panier']);

    echo json_encode(['status' => 'success', 'total' => $total_articles]);
    exit();
}

echo json_encode(['status' => 'error']);
?>