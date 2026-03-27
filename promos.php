<?php
require_once 'check_auth.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_cat = $_POST['id_categorie'];
    $remise = (int)$_POST['pourcentage']; // ex: 20

    // On change juste le chiffre de la colonne reduction
    $stmt = $pdo->prepare("UPDATE produits SET reduction = ? WHERE id_categorie = ?");
    $stmt->execute([$remise, $id_cat]);
    
    $message = "La promo est active !";
}
?>