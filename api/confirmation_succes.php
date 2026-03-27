<?php
require_once '../config/db.php';
session_start();

$id = $_GET['id'];
$method = $_GET['method'];

// 1. Update de la base de données
$stmt = $pdo->prepare("UPDATE paiements SET statut = 'complete', transaction_id = ? WHERE id = ?");
$stmt->execute(['WAVE-' . strtoupper(uniqid()), $id]);

// 2. Vider le panier
unset($_SESSION['panier']);

// 3. Message WhatsApp de confirmation
$msg = "✅ *Paiement Confirmé!*\n\nMerci pour votre achat sur Jewelry Beug Fallou.\nMode : " . strtoupper($method) . "\nNous préparons votre commande.";
$url_wa = "https://wa.me/2202657822?text=" . urlencode($msg);

// ... après la mise à jour du statut de paiement ...

// Récupérer les produits de la commande (via la session avant de la vider)
foreach ($_SESSION['panier'] as $id => $qte) {
    // On décrémente le stock
    $updateStock = $pdo->prepare("UPDATE produits SET stock = stock - ? WHERE id = ? AND stock >= ?");
    $updateStock->execute([$qte, $id, $qte]);
    
    // Alerte Admin si stock faible (Optionnel)
    $check = $pdo->prepare("SELECT stock, nom FROM produits WHERE id = ?");
    $check->execute([$id]);
    $prod = $check->fetch();
    
    if ($prod['stock'] < 5) {
        // Ici on pourrait envoyer un mail : "Alerte stock faible sur : " . $prod['nom']
    }
}

// ... ensuite vider la session et rediriger vers WhatsApp ...

echo "<h1>Paiement réussi !</h1><p>Redirection vers WhatsApp...</p>";
echo "<script>setTimeout(() => { window.location.href = '$url_wa'; }, 2000);</script>";