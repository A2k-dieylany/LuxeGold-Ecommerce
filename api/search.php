<?php
require_once '../config/db.php';
require_once '../includes/fonctions.php';
header('Content-Type: application/json');

$query = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit();
}

$stmt = $pdo->prepare("SELECT id, nom, prix, image FROM produits WHERE nom LIKE ? OR description LIKE ? LIMIT 5");
$stmt->execute(["%$query%", "%$query%"]);
$resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// On formate les prix avant d'envoyer au JavaScript
foreach ($resultats as &$r) {
    $r['prix_formate'] = formaterPrix($r['prix']);
}

echo json_encode($resultats);