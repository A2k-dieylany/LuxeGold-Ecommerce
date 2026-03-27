<?php
require_once 'config/db.php';
require_once 'includes/fonctions.php';
include 'includes/header.php';

$id_paiement = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM paiements WHERE id = ?");
$stmt->execute([$id_paiement]);
$p = $stmt->fetch();
?>

<div class="container text-center" style="padding: 100px 20px;">
    <div style="max-width: 400px; margin: auto; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-top: 5px solid #1dcaff;">
        <img src="assets/img/wave.png" width="80" class="mb-4">
        <h2>Paiement Wave</h2>
        <p>Montant à payer : <strong><?= formaterPrix($p['montant']) ?></strong></p>
        
        <div id="loading-payment">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-3">Veuillez valider la transaction sur votre téléphone...</p>
        </div>

        <script>
            setTimeout(() => {
                window.location.href = "api/confirmation_succes.php?id=<?= $id_paiement ?>&method=wave";
            }, 5000);
        </script>
    </div>
</div>

<?php include 'includes/footer.php'; ?>