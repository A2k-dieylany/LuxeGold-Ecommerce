<?php
// 1. Désactiver l'affichage des erreurs texte pour ne pas corrompre le JSON
ini_set('display_errors', 0);
error_reporting(0);

require_once '../config/db.php';

// 2. Démarrage sécurisé de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Forcer l'en-tête JSON
header('Content-Type: application/json; charset=utf-8');

// 4. Récupération des données
$p_id = isset($_POST['produit_id']) ? (int)$_POST['produit_id'] : 0;
$session_id = session_id();

if ($p_id > 0) {
    try {
        // Vérifier si déjà en favoris
        $check = $pdo->prepare("SELECT id FROM wishlist WHERE session_id = ? AND produit_id = ?");
        $check->execute([$session_id, $p_id]);
        
        if ($check->fetch()) {
            // Déjà présent -> On le retire
            $del = $pdo->prepare("DELETE FROM wishlist WHERE session_id = ? AND produit_id = ?");
            $del->execute([$session_id, $p_id]);
            echo json_encode(['status' => 'removed', 'message' => 'Retiré des favoris']);
        } else {
            // Absent -> On l'ajoute
            $add = $pdo->prepare("INSERT INTO wishlist (session_id, produit_id) VALUES (?, ?)");
            $add->execute([$session_id, $p_id]);
            echo json_encode(['status' => 'added', 'message' => 'Ajouté aux favoris']);
        }
    } catch (PDOException $e) {
        // En cas d'erreur SQL (ex: table manquante)
        echo json_encode(['status' => 'error', 'message' => 'Erreur base de données']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID produit invalide']);
}

// 5. Arrêter le script pour éviter tout texte supplémentaire
exit;