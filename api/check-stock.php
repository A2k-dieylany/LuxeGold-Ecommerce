<?php
require_once '../config/db.php';

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$qte_demandee = isset($_GET['qte']) ? (int)$_GET['qte'] : 1;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT stock, nom FROM produits WHERE id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();

    if ($p) {
        if ($p['stock'] >= $qte_demandee) {
            echo json_encode(['success' => true, 'stock' => $p['stock']]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => "Désolé, il ne reste que " . $p['stock'] . " exemplaire(s) de " . $p['nom'],
                'stock' => $p['stock']
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "Produit introuvable"]);
    }
} else {
    echo json_encode(['success' => false, 'message' => "ID invalide"]);
}