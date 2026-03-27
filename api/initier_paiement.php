<?php
// On active le rapport d'erreurs pour le debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/db.php';
require_once '../includes/fonctions.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_SESSION['panier'])) {
    
    // 1. Nettoyage des données
    $nom = htmlspecialchars($_POST['nom']);
    $telephone = htmlspecialchars($_POST['telephone']);
    $methode = $_POST['methode'];
    $devise = $_SESSION['devise'];
    $commande_id = "JB-" . strtoupper(uniqid());

    // 2. Calcul du montant final côté serveur (Sécurité !)
    $total_final = 0;
    foreach ($_SESSION['panier'] as $id => $qte) {
        $stmt = $pdo->prepare("SELECT prix FROM produits WHERE id = ?");
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        if ($p) {
            $total_final += ($p['prix'] * $qte);
        }
    }

    // 3. Enregistrement en base de données
    try {
        $sql = "INSERT INTO paiements (commande_id, methode, telephone, montant, devise, statut) 
                VALUES (:cmd, :meth, :tel, :mnt, :dev, 'en_attente')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':cmd'  => $commande_id,
            ':meth' => $methode,
            ':tel'  => $telephone,
            ':mnt'  => $total_final,
            ':dev'  => $devise
        ]);
        
        $paiement_db_id = $pdo->lastInsertId();

        // 4. Redirection vers le simulateur de l'opérateur choisi
        // Note: On sort du dossier /api/ pour aller à la racine
        if ($methode == 'wave') {
            header("Location: ../wave_process.php?id=$paiement_db_id");
        } elseif ($methode == 'orange_money') {
            header("Location: ../orange_money_process.php?id=$paiement_db_id");
        } else {
            header("Location: ../qmoney_process.php?id=$paiement_db_id");
        }
        exit();

    } catch (PDOException $e) {
        die("Erreur base de données : " . $e->getMessage());
    }

} else {
    header('Location: ../catalogue.php');
    exit();
}