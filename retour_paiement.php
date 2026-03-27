<?php
session_start();
require_once 'config/db.php';
require_once 'includes/fonctions.php';

$cmd_id = $_GET['cmd'] ?? null;

if ($cmd_id) {
    // 1. Mettre à jour le statut de la commande en "Payée"
    $stmt = $pdo->prepare("UPDATE commandes SET statut = 'payée' WHERE id = ?");
    $stmt->execute([$cmd_id]);

    // 2. Vider le panier
    unset($_SESSION['panier']);
    unset($_SESSION['promo']);
}

include 'includes/header.php';
?>

<main style="padding: 100px 20px; text-align: center; background: #fafafa; min-height: 60vh;">
    <div style="background: white; max-width: 600px; margin: 0 auto; padding: 50px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
        
        <div style="width: 80px; height: 80px; background: #e6ffed; color: #28a745; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 20px;">
            <i class="fas fa-check"></i>
        </div>
        
        <h1 style="font-family: 'Playfair Display', serif; color: #1a1a1a;">Paiement Réussi !</h1>
        <p style="color: #666; font-size: 1.1rem; line-height: 1.6; margin-bottom: 30px;">
            Merci pour votre commande chez Jewelry Beug Fallou.<br>
            Votre transaction a bien été enregistrée et votre commande <strong>#CMD-<?= str_pad($cmd_id, 4, '0', STR_PAD_LEFT) ?></strong> est en cours de préparation.
        </p>

        <div style="display: flex; gap: 15px; justify-content: center;">
            <a href="mon_compte.php" style="padding: 15px 30px; background: #1a1a1a; color: white; text-decoration: none; border-radius: 10px; font-weight: bold; transition: 0.3s;">
                <i class="fas fa-user"></i> Mon Espace
            </a>
            <a href="catalogue.php" style="padding: 15px 30px; border: 2px solid #d4af37; color: #d4af37; text-decoration: none; border-radius: 10px; font-weight: bold; transition: 0.3s;">
                Continuer mes achats
            </a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>