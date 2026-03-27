<?php
function ajouterAuPanier($id_produit) {
    if(!isset($_SESSION['panier'])) { $_SESSION['panier'] = []; }
    
    if(isset($_SESSION['panier'][$id_produit])) {
        $_SESSION['panier'][$id_produit]++;
    } else {
        $_SESSION['panier'][$id_produit] = 1;
    }
}

function compterPanier() {
    return isset($_SESSION['panier']) ? array_sum($_SESSION['panier']) : 0;
}


// Fichier : includes/fonctions.php

function formaterPrix($prix_fcfa) {
    // 1. On définit le taux de change
    $taux_dalasi = 0.115; 

    // 2. On vérifie si la session existe et quelle devise est choisie
    if (isset($_SESSION['devise']) && $_SESSION['devise'] == 'GMD') {
        $prix_gmd = $prix_fcfa * $taux_dalasi;
        return number_format(ceil($prix_gmd), 0, ',', ' ') . ' GMD';
    } else {
        return number_format($prix_fcfa, 0, ',', ' ') . ' FCFA';
    }
}

function enregistrerLog($pdo, $action, $details) {
    $id_admin = $_SESSION['admin_id'] ?? 0;
    $admin_nom = $_SESSION['admin_name'] ?? 'Inconnu';
    $ip = $_SERVER['REMOTE_ADDR'];

    $stmt = $pdo->prepare("INSERT INTO logs_activite (id_admin, admin_nom, action, details, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$id_admin, $admin_nom, $action, $details, $ip]);
}

?>