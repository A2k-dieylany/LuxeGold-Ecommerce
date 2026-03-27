<?php
require_once 'check_auth.php';
require_once '../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        // 1. Récupérer le nom du client AVANT suppression pour le Journal d'Activité
        $stmtInfo = $pdo->prepare("SELECT nom FROM utilisateurs WHERE id = ?");
        $stmtInfo->execute([$id]);
        $client = $stmtInfo->fetch(PDO::FETCH_ASSOC);

        if ($client) {
            $nom_client = $client['nom'];

            // 2. Suppression physique en base de données
            $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
            $stmt->execute([$id]);
            
            // 3. ENREGISTREMENT DANS LE JOURNAL
            if (function_exists('enregistrerLog')) {
                enregistrerLog($pdo, "SUPPRESSION_CLIENT", "A supprimé le compte client : " . $nom_client . " (ID: " . $id . ")");
            }

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Le client a été supprimé de la base de données.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Ce client est introuvable.'];
        }
        
    } catch (PDOException $e) {
        // Astuce Pro : Si l'erreur est déclenchée, c'est généralement parce que le client a des commandes.
        error_log("Erreur Suppression Client ID $id : " . $e->getMessage());
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Impossible de supprimer ce client. Il possède probablement un historique de commandes.'];
    }
}

// Redirection immédiate vers la liste des clients
header('Location: clients.php');
exit();
?>