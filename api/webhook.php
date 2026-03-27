<?php
require_once '../config/db.php';

// Récupération des données envoyées par Orange/QMoney
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['status']) && $data['status'] == 'SUCCESS') {
    $commande_id = $data['order_id'];
    $trans_id = $data['tx_id'];

    // Mettre à jour le statut du paiement
    $stmt = $pdo->prepare("UPDATE paiements SET statut = 'complete', transaction_id = ? WHERE commande_id = ?");
    $stmt->execute([$trans_id, $commande_id]);

    // Ici, tu pourrais aussi déclencher l'envoi d'un email de confirmation
}