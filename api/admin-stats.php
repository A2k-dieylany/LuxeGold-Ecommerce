<?php
require_once '../config/db.php';
header('Content-Type: application/json');

try {
    // 1. Répartition du Chiffre d'Affaires par pays (SN vs GM)
    $ca_query = $pdo->query("SELECT devise, SUM(total) as montant FROM commandes WHERE statut = 'payé' GROUP BY devise");
    $ca_data = $ca_query->fetchAll(PDO::FETCH_ASSOC);

    // 2. Top 5 des produits les plus populaires (mis en favoris)
    // Note: On ajoute p.image et p.id pour le tableau d'alertes du dashboard
    $fav_query = $pdo->query("
        SELECT p.nom, COUNT(w.id) as total_fav 
        FROM produits p 
        LEFT JOIN wishlist w ON p.id = w.produit_id 
        GROUP BY p.id 
        ORDER BY total_fav DESC LIMIT 5
    ");
    $fav_data = $fav_query->fetchAll(PDO::FETCH_ASSOC);

    // 3. Récupération des stocks bas
    $stock_bas = $pdo->query("
        SELECT id, nom, stock, prix, image 
        FROM produits 
        WHERE stock < 5 
        ORDER BY stock ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // 4. Dernières commandes (Optionnel mais recommandé pour ton dashboard)
    $dernieres_commandes = $pdo->query("
        SELECT id, nom_client, total, devise, statut, created_at 
        FROM commandes 
        ORDER BY created_at DESC LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    // UN SEUL ET UNIQUE ECHO A LA FIN
    echo json_encode([
        'ca' => $ca_data,
        'favoris' => $fav_data,
        'alertes' => $stock_bas,
        'dernieres_commandes' => $dernieres_commandes
    ]);

} catch (PDOException $e) {
    // En cas d'erreur SQL, on renvoie un message propre
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}