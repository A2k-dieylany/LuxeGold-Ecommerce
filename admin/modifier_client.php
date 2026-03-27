<?php
require_once 'check_auth.php';
require_once '../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';

// 1. Récupérer les informations actuelles du client
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

// Si le client n'existe pas, on le renvoie à la liste
if (!$client) {
    header('Location: clients.php');
    exit();
}

// 2. Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    // On permet à l'email d'être vide, donc si c'est vide, on met NULL pour la BDD
    $email_final = !empty($email) ? $email : null; 
    $telephone = trim($_POST['telephone']);
    $adresse = trim($_POST['adresse']);

    try {
        $update = $pdo->prepare("UPDATE utilisateurs SET nom = ?, email = ?, telephone = ?, adresse = ? WHERE id = ?");
        $update->execute([$nom, $email_final, $telephone, $adresse, $id]);
        
        // --- NOUVEAU : ENREGISTREMENT DANS LE JOURNAL D'ACTIVITÉ ---
        if (function_exists('enregistrerLog')) {
            enregistrerLog($pdo, "MODIF_CLIENT", "Mise à jour du client : " . $nom . " (ID: " . $id . ")");
        }
        // ----------------------------------------------------------

        // On crée un message de succès et on redirige
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Les informations du client ont été mises à jour.'];
        
        header('Location: clients.php');
        exit();
    } catch (PDOException $e) {
        // Gestion des erreurs (ex: email déjà utilisé par un autre client)
        $message = "Erreur lors de la modification. Cet email est peut-être déjà utilisé.";
    }
}

include 'includes/admin_header.php';
?>

<main class="dashboard-container" style="padding: 20px;">
    <div class="dashboard-header" style="margin-bottom: 30px;">
        <h1 style="font-family: 'Playfair Display', serif; margin: 0; font-size: 1.8rem;">
            <i class="fas fa-user-edit" style="color: #d4af37;"></i> Modifier le Client
        </h1>
        <p style="color: #888; margin: 5px 0 0;">Mettez à jour les coordonnées de <strong><?= htmlspecialchars($client['nom']) ?></strong>.</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="admin-alert error">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="form-container" style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); max-width: 600px;">
        <form method="POST" action="">
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Nom & Prénom *</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($client['nom']) ?>" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Téléphone *</label>
                <input type="text" name="telephone" value="<?= htmlspecialchars($client['telephone']) ?>" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Adresse Email <span style="color: #888; font-weight: normal; font-size: 12px;">(Optionnel)</span></label>
                <input type="email" name="email" value="<?= htmlspecialchars($client['email'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Adresse de livraison <span style="color: #888; font-weight: normal; font-size: 12px;">(Optionnel)</span></label>
                <textarea name="adresse" rows="3" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-family: inherit;"><?= htmlspecialchars($client['adresse'] ?? '') ?></textarea>
            </div>

            <div style="display: flex; gap: 15px;">
                <button type="submit" style="flex: 1; background: #d4af37; color: black; border: none; padding: 15px; border-radius: 8px; font-weight: bold; cursor: pointer;">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
                <a href="clients.php" style="flex: 1; text-align: center; background: #f4f6f9; color: #333; text-decoration: none; padding: 15px; border-radius: 8px; border: 1px solid #ddd; font-weight: bold;">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>