<?php
require_once '../config/db.php';


// Vérification Admin
if (!isset($_SESSION['admin'])) { header('Location: ../index.php'); exit(); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($id > 0 && in_array($action, ['valider', 'annuler'])) {
    
    // 1. On récupère l'état actuel de la commande
    $stmt = $pdo->prepare("SELECT statut FROM commandes WHERE id = ?");
    $stmt->execute([$id]);
    $commande = $stmt->fetch();

    if (!$commande) { die("Commande introuvable."); }

    // EMPÊCHER LES DOUBLONS : Si la commande est déjà traitée, on ne fait rien
    if ($commande['statut'] != 'en_attente') {
        header('Location: commandes.php?error=deja_traitee');
        exit();
    }

    if ($action == 'valider') {
        // Cas simple : On change juste le statut
        $update = $pdo->prepare("UPDATE commandes SET statut = 'validee' WHERE id = ?");
        $update->execute([$id]);

    } elseif ($action == 'annuler') {
        // CAS CRITIQUE : RESTOCKAGE
        // 1. On récupère tous les produits de cette commande
        $stmtDetails = $pdo->prepare("SELECT id_produit, quantite FROM details_commande WHERE id_commande = ?");
        $stmtDetails->execute([$id]);
        $details = $stmtDetails->fetchAll();

        // 2. Pour chaque produit, on remet le stock
        foreach ($details as $d) {
            $restock = $pdo->prepare("UPDATE produits SET stock = stock + ? WHERE id = ?");
            $restock->execute([$d['quantite'], $d['id_produit']]);
        }

        // 3. On passe la commande en annulée
        $update = $pdo->prepare("UPDATE commandes SET statut = 'annulee' WHERE id = ?");
        $update->execute([$id]);
    }

    header('Location: commandes.php?success=1');
} else {
    header('Location: commandes.php');
}
?>