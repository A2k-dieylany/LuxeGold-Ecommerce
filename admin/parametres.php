<?php
require_once 'check_auth.php';
require_once '../config/db.php';

$message_sec = ""; // Message pour la section sécurité
$message_gen = ""; // Message pour la section générale

// ==========================================
// 1. TRAITEMENT DES FORMULAIRES
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // --- A. SI ON A SOUMIS LE FORMULAIRE DE MOT DE PASSE ---
    if (isset($_POST['update_password'])) {
        $old_pass = $_POST['old_pass'];
        $new_pass = $_POST['new_pass'];
        $confirm_pass = $_POST['confirm_pass'];

        $stmt = $pdo->prepare("SELECT mot_de_passe FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();

        if (password_verify($old_pass, $admin['mot_de_passe'])) {
            if ($new_pass === $confirm_pass) {
                $hash = password_hash($new_pass, PASSWORD_BCRYPT);
                $update = $pdo->prepare("UPDATE admins SET mot_de_passe = ? WHERE id = ?");
                $update->execute([$hash, $_SESSION['admin_id']]);
                $message_sec = "<div style='padding: 15px; background: #e6ffed; color: #2e7d32; border-radius: 8px; margin-bottom: 20px; border: 1px solid #2e7d32;'>✅ Mot de passe mis à jour avec succès !</div>";
            } else {
                $message_sec = "<div style='padding: 15px; background: #fdf2f2; color: #c53030; border-radius: 8px; margin-bottom: 20px; border: 1px solid #feb2b2;'>❌ Les nouveaux mots de passe ne correspondent pas.</div>";
            }
        } else {
            $message_sec = "<div style='padding: 15px; background: #fdf2f2; color: #c53030; border-radius: 8px; margin-bottom: 20px; border: 1px solid #feb2b2;'>❌ L'ancien mot de passe est incorrect.</div>";
        }
    }

    // --- B. SI ON A SOUMIS LE FORMULAIRE DES PARAMÈTRES BOUTIQUE ---
    if (isset($_POST['update_settings'])) {
        $nom = trim($_POST['nom_boutique']);
        $whatsapp = trim($_POST['telephone_whatsapp']);
        $email = trim($_POST['email_contact']);
        $adresse = trim($_POST['adresse_boutique']);
        
        // Récupération de la config actuelle pour connaître l'ancien logo
        $stmtCurrent = $pdo->query("SELECT logo_filename FROM parametres WHERE id = 1");
        $currentConfig = $stmtCurrent->fetch();
        $currentLogo = $currentConfig['logo_filename'] ?? 'logo.png';
        $newLogoName = $currentLogo; // Par défaut, on garde l'ancien

        // --- GESTION DE L'UPLOAD DU LOGO ---
        if (isset($_FILES['nouveau_logo']) && $_FILES['nouveau_logo']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'svg'];
            $filename = $_FILES['nouveau_logo']['name'];
            $filetype = $_FILES['nouveau_logo']['type'];
            $filesize = $_FILES['nouveau_logo']['size'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            // Vérifications (Taille max 2Mo et extension)
            if ($filesize > 2 * 1024 * 1024) {
                 $message_gen = "<div style='padding: 15px; background: #fdf2f2; color: #c53030; border-radius: 8px; margin-bottom: 20px; border: 1px solid #feb2b2;'>❌ Le logo est trop lourd (Max 2Mo).</div>";
            } elseif (!in_array($ext, $allowed)) {
                 $message_gen = "<div style='padding: 15px; background: #fdf2f2; color: #c53030; border-radius: 8px; margin-bottom: 20px; border: 1px solid #feb2b2;'>❌ Format invalide (PNG, JPG, SVG uniquement).</div>";
            } else {
                // Tout est bon, on prépare le nouveau nom unique
                $newLogoName = "logo_" . time() . "." . $ext;
                $upload_dir = "../assets/img/"; // Chemin vers le dossier images

                if (move_uploaded_file($_FILES['nouveau_logo']['tmp_name'], $upload_dir . $newLogoName)) {
                    // Succès de l'upload ! 
                    // Optionnel : On pourrait supprimer l'ancien logo ici pour gagner de la place
                } else {
                    $message_gen = "<div style='padding: 15px; background: #fdf2f2; color: #c53030; border-radius: 8px; margin-bottom: 20px; border: 1px solid #feb2b2;'>❌ Erreur technique lors de l'enregistrement du logo.</div>";
                    $newLogoName = $currentLogo; // On remet l'ancien nom en cas d'échec
                }
            }
        }

        // Mise à jour de la base de données si pas d'erreur bloquante
        if (strpos($message_gen, '❌') === false) {
            try {
                // Note l'ajout de `logo_filename = ?` dans la requête
                $stmt = $pdo->prepare("UPDATE parametres SET nom_boutique = ?, telephone_whatsapp = ?, email_contact = ?, adresse_boutique = ?, logo_filename = ? WHERE id = 1");
                $stmt->execute([$nom, $whatsapp, $email, $adresse, $newLogoName]);
                $message_gen = "<div style='padding: 15px; background: #e6ffed; color: #2e7d32; border-radius: 8px; margin-bottom: 20px; border: 1px solid #2e7d32;'>✅ Paramètres et logo mis à jour !</div>";
            } catch (PDOException $e) {
                $message_gen = "<div style='padding: 15px; background: #fdf2f2; color: #c53030; border-radius: 8px; margin-bottom: 20px; border: 1px solid #feb2b2;'>❌ Erreur de sauvegarde base de données.</div>";
            }
        }
    }
}

// ==========================================
// 2. RÉCUPÉRATION DES PARAMÈTRES ACTUELS
// ==========================================
$config = ['nom_boutique' => '', 'telephone_whatsapp' => '', 'email_contact' => '', 'adresse_boutique' => '', 'logo_filename' => 'logo.png'];
try {
    $stmt = $pdo->query("SELECT * FROM parametres WHERE id = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $config = $result;
    }
} catch (PDOException $e) {
    // La table n'existe pas encore
}

include 'includes/admin_header.php';
?>

<main class="dashboard-container" style="padding: 20px; max-width: 1000px; margin: 0 auto;">
    
    <div class="dashboard-header" style="margin-bottom: 30px;">
        <h1 style="font-family: 'Playfair Display', serif; margin: 0; font-size: 1.8rem;">
            <i class="fas fa-sliders-h" style="color: #d4af37;"></i> Paramètres Généraux
        </h1>
        <p style="color: #888; margin: 5px 0 0;">Gérez l'identité visuelle, les contacts et la sécurité.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px;">
        
        <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.4rem; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-top: 0;">
                <i class="fas fa-store" style="color: #d4af37;"></i> Informations & Logo
            </h2>
            
            <?= $message_gen ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_settings" value="1">

                <div style="margin-bottom: 25px; background: #f9f9f9; padding: 15px; border-radius: 10px; border: 1px dashed #ddd;">
                    <label style="display: block; font-weight: 600; margin-bottom: 10px;">Logo actuel</label>
                    <div style="display: flex; align-items: center; gap: 20px;">
                        <div style="background: white; padding: 10px; border-radius: 8px; border: 1px solid #eee;">
                            <img src="../assets/img/<?= htmlspecialchars($config['logo_filename'] ?? 'logo.png') ?>" alt="Logo actuel" style="height: 50px; width: auto; display: block;">
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 0.9rem; margin-bottom: 5px; color: #666;">Changer le logo :</label>
                            <input type="file" name="nouveau_logo" accept="image/png, image/jpeg, image/svg+xml" style="width: 100%; font-size: 0.9rem;">
                            <p style="font-size: 11px; color: #999; margin: 5px 0 0;">Formats acceptés : PNG, JPG, SVG (Max 2Mo). Fond transparent recommandé.</p>
                        </div>
                    </div>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px;">Nom de la boutique *</label>
                    <input type="text" name="nom_boutique" value="<?= htmlspecialchars($config['nom_boutique']) ?>" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px;">
                        <i class="fab fa-whatsapp" style="color: #25D366;"></i> Numéro WhatsApp *
                    </label>
                    <input type="text" name="telephone_whatsapp" value="<?= htmlspecialchars($config['telephone_whatsapp']) ?>" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px;">Email de contact</label>
                    <input type="email" name="email_contact" value="<?= htmlspecialchars($config['email_contact']) ?>" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px;">Adresse physique</label>
                    <textarea name="adresse_boutique" rows="2" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; box-sizing: border-box; font-family: inherit;"><?= htmlspecialchars($config['adresse_boutique']) ?></textarea>
                </div>

                <button type="submit" style="width: 100%; padding: 15px; background: #d4af37; color: black; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s;">
                    <i class="fas fa-save"></i> SAUVEGARDER TOUT
                </button>
            </form>
        </div>

        <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); align-self: start;">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.4rem; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-top: 0;">
                <i class="fas fa-lock" style="color: #1a1a1a;"></i> Sécurité & Mot de passe
            </h2>
            
            <?= $message_sec ?>

            <form method="POST">
                <input type="hidden" name="update_password" value="1">

                <div style="margin-bottom: 15px; margin-top: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px;">Ancien mot de passe</label>
                    <input type="password" name="old_pass" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; box-sizing: border-box;">
                </div>
                
                <hr style="border: 0; border-top: 1px dashed #eee; margin: 20px 0;">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px;">Nouveau mot de passe</label>
                    <input type="password" name="new_pass" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; box-sizing: border-box;">
                </div>
                
                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px;">Confirmer le nouveau mot de passe</label>
                    <input type="password" name="confirm_pass" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; box-sizing: border-box;">
                </div>

                <button type="submit" style="width: 100%; padding: 15px; background: #1a1a1a; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s;">
                    <i class="fas fa-key"></i> METTRE À JOUR LE MOT DE PASSE
                </button>
            </form>
        </div>

    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>