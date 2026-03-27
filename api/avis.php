<?php
require_once '../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $p_id = (int)$_POST['produit_id'];
    $nom = htmlspecialchars($_POST['nom']);
    $note = (int)$_POST['note'];
    $com = htmlspecialchars($_POST['commentaire']);

    if (!empty($nom) && $note >= 1 && $note <= 5) {
        $stmt = $pdo->prepare("INSERT INTO avis (produit_id, client_nom, note, commentaire) VALUES (?, ?, ?, ?)");
        $stmt->execute([$p_id, $nom, $note, $com]);
        
        echo json_encode(['success' => true, 'message' => 'Merci ! Votre avis est en cours de modération.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs correctement.']);
    }
}