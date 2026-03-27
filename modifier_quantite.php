<?php
require_once 'config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($id > 0 && isset($_SESSION['panier'][$id])) {
    if ($action === 'plus') {
        // --- NOUVEAU : VÉRIFICATION DU STOCK ---
        $stmt = $pdo->prepare("SELECT stock FROM produits WHERE id = ?");
        $stmt->execute([$id]);
        $produit = $stmt->fetch();

        if ($produit && $_SESSION['panier'][$id] < $produit['stock']) {
            $_SESSION['panier'][$id]++;
        } else {
            // Optionnel : tu peux passer un message d'erreur via la session
            $_SESSION['erreur_stock'] = "Stock maximum atteint pour ce bijou.";
        }
        
    } elseif ($action === 'moins') {
        $_SESSION['panier'][$id]--;
        
        // Si la quantité tombe à 0, on supprime l'article
        if ($_SESSION['panier'][$id] <= 0) {
            unset($_SESSION['panier'][$id]);
        }
    }
}

header('Location: panier.php');
exit();