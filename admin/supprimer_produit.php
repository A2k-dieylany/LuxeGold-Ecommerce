<?php
// 1. Sécurité et Connexion
require_once 'check_auth.php'; 
require_once '../config/db.php';

// 2. Récupération de l'ID du produit
$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        // A. Récupérer le nom et l'image pour le log et le nettoyage du disque
        $stmtInfo = $pdo->prepare("SELECT nom, image FROM produits WHERE id = ?");
        $stmtInfo->execute([$id]);
        $p = $stmtInfo->fetch();

        if ($p) {
            $nom_produit = $p['nom'];
            $chemin_image = "../assets/img/produits/" . $p['image'];
            
            // On ne supprime le fichier que s'il existe vraiment
            if (!empty($p['image']) && file_exists($chemin_image)) {
                unlink($chemin_image);
            }

            // B. Supprimer le produit de la base de données
            $stmt = $pdo->prepare("DELETE FROM produits WHERE id = ?");
            $stmt->execute([$id]);

            // C. ENREGISTREMENT DANS LE JOURNAL D'ACTIVITÉ
            if (function_exists('enregistrerLog')) {
                enregistrerLog($pdo, "SUPPRESSION", "Produit supprimé : " . $nom_produit . " (ID: " . $id . ")");
            }
        }

        // Redirection avec un paramètre de succès
        header('Location: produits.php?success=delete');
        exit();

    } catch (PDOException $e) {
        // En cas d'erreur (ex: produit lié à une commande), on redirige avec une erreur
        error_log("Erreur suppression: " . $e->getMessage());
        header('Location: produits.php?error=delete_failed');
        exit();
    }
} else {
    // Si l'ID n'est pas valide
    header('Location: produits.php');
    exit();
}