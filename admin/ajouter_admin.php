<?php
// 1. Sécurité : Seuls les admins connectés peuvent voir cette page
require_once 'check_auth.php';
require_once '../config/db.php';

$message = '';
$type_message = ''; // 'success' ou 'error'

// 2. Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $confirmation = $_POST['confirmation'];

    // A. Vérifications de base
    if (empty($identifiant) || empty($mot_de_passe) || empty($confirmation)) {
        $message = "Veuillez remplir tous les champs.";
        $type_message = "error";
    } elseif ($mot_de_passe !== $confirmation) {
        $message = "Les mots de passe ne correspondent pas.";
        $type_message = "error";
    } elseif (strlen($mot_de_passe) < 6) {
        $message = "Le mot de passe doit contenir au moins 6 caractères.";
        $type_message = "error";
    } else {
        // B. Vérifier si l'identifiant existe déjà dans la BDD
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE identifiant = ?");
        $stmtCheck->execute([$identifiant]);
        $existe = $stmtCheck->fetchColumn();

        if ($existe > 0) {
            $message = "Cet identifiant est déjà utilisé par un autre administrateur.";
            $type_message = "error";
        } else {
            // C. Hachage du mot de passe (La Règle d'Or !)
            $mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_DEFAULT);

            // D. Insertion sécurisée
            try {
                $stmt = $pdo->prepare("INSERT INTO admins (identifiant, mot_de_passe) VALUES (?, ?)");
                $stmt->execute([$identifiant, $mot_de_passe_hache]);
                
                $message = "Succès ! Le nouvel administrateur '$identifiant' a été ajouté.";
                $type_message = "success";
                
            } catch (PDOException $e) {
                error_log("Erreur lors de l'ajout d'un admin : " . $e->getMessage());
                $message = "Une erreur technique est survenue.";
                $type_message = "error";
            }
        }
    }
}

// Inclusion de l'en-tête de ton Dashboard
include 'includes/admin_header.php';
?>

<main class="dashboard-container">
    
    <div class="dashboard-header" style="margin-bottom: 30px;">
        <h1 style="font-family: 'Playfair Display', serif; margin: 0; font-size: 1.8rem;">
            <i class="fas fa-user-plus" style="color: #d4af37;"></i> Ajouter un Administrateur
        </h1>
        <p style="color: #888; margin: 5px 0 0;">Créez un nouvel accès sécurisé au tableau de bord.</p>
    </div>

    <?php if (!empty($message)): ?>
        <div style="padding: 15px; margin-bottom: 20px; border-radius: 8px; font-weight: 500; display: flex; align-items: center; gap: 10px;
            <?= $type_message === 'success' ? 'background: #e8f5e9; color: #2e7d32; border-left: 5px solid #2e7d32;' : 'background: #ffebee; color: #c62828; border-left: 5px solid #c62828;' ?>">
            <i class="fas <?= $type_message === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="form-container" style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto;">
        
        <form method="POST" action="">
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="identifiant" style="display: block; margin-bottom: 8px; font-weight: 600;">Identifiant de connexion *</label>
                <input type="text" id="identifiant" name="identifiant" required placeholder="Ex: amadou_manager"
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; outline: none;">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="mot_de_passe" style="display: block; margin-bottom: 8px; font-weight: 600;">Mot de passe * <span style="font-size: 12px; font-weight: normal; color: #888;">(Min. 6 caractères)</span></label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required placeholder="••••••••"
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; outline: none;">
            </div>

            <div class="form-group" style="margin-bottom: 30px;">
                <label for="confirmation" style="display: block; margin-bottom: 8px; font-weight: 600;">Confirmer le mot de passe *</label>
                <input type="password" id="confirmation" name="confirmation" required placeholder="••••••••"
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; outline: none;">
            </div>

            <div style="display: flex; gap: 15px;">
                <button type="submit" class="btn-gold" style="flex: 1; background: #d4af37; color: black; border: none; padding: 15px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s;">
                    <i class="fas fa-save"></i> Créer le compte
                </button>
                <a href="dashboard.php" style="flex: 1; text-align: center; background: #f8f9fa; color: #333; text-decoration: none; padding: 15px; border-radius: 8px; border: 1px solid #ddd; font-weight: bold; transition: 0.3s;">
                    Annuler
                </a>
            </div>
        </form>

    </div>

</main>

<?php include 'includes/admin_footer.php'; ?>