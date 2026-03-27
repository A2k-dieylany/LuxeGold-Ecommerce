<?php
// 1. Sécurité et Connexion
require_once 'check_auth.php'; 
require_once '../config/db.php';

$message = "";

// 2. LOGIQUE DE TRAITEMENT
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $video_nom = null;

    // Gestion de la vidéo (Optionnelle)
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        if ($_FILES['video']['size'] <= 10485760) { // Max 10 Mo
            $ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['mp4', 'webm'])) {
                $video_nom = time() . "_cat_" . uniqid() . "." . $ext;
                if (!move_uploaded_file($_FILES['video']['tmp_name'], "../assets/videos/" . $video_nom)) {
                    $message = "❌ Erreur lors du téléchargement de la vidéo.";
                }
            } else {
                $message = "❌ Format vidéo non autorisé (MP4, WEBM uniquement).";
            }
        } else {
            $message = "❌ La vidéo est trop lourde (Max 10Mo).";
        }
    }

    // Insertion en base de données
    if (empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO categories (nom, video) VALUES (?, ?)");
        
        if ($stmt->execute([$nom, $video_nom])) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'La nouvelle catégorie a été créée !'];
            // Redirection vers la liste des catégories (à adapter selon ton site)
            header('Location: dashboard.php'); 
            exit();
        } else {
            $message = "❌ Erreur SQL lors de l'enregistrement.";
        }
    }
}

include 'includes/admin_header.php';
?>

<main class="dashboard-container" style="max-width: 600px; margin: 40px auto;">
    
    <div style="background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #eee;">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="font-family: 'Playfair Display', serif; color: #1a1a1a; margin-bottom: 10px;">Nouvelle Catégorie</h2>
            <div style="width: 50px; height: 2px; background: #d4af37; margin: auto;"></div>
        </div>
        
        <?php if($message): ?>
            <div style="background: #fff5f5; color: #e74c3c; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #fed7d7; text-align: center;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="ajouter_categorie.php" method="POST" enctype="multipart/form-data">
            
            <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #555;">Nom de la catégorie</label>
                <input type="text" name="nom" required placeholder="ex: Bracelets Or" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 1.1rem;">
            </div>

            <div style="background: #fdfaf0; padding: 20px; border-radius: 10px; border: 1px dashed #d4af37; margin-bottom: 30px;">
                <div class="form-group">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #1a1a1a;">Vidéo d'arrière-plan (Optionnel)</label>
                    <input type="file" name="video" accept="video/mp4, video/webm" style="width: 100%;">
                    <p style="font-size: 11px; color: #888; margin-top: 5px; margin-bottom: 0;">Vidéo animée pour la page d'accueil (MP4, Max 10Mo).</p>
                </div>
            </div>

            <div style="display: flex; gap: 15px;">
                <button type="submit" style="flex: 2; padding: 15px; background: #1a1a1a; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s;" onmouseover="this.style.background='#d4af37'" onmouseout="this.style.background='#1a1a1a'">
                    <i class="fas fa-plus"></i> CRÉER LA CATÉGORIE
                </button>
                <a href="dashboard.php" style="flex: 1; display:flex; align-items:center; justify-content:center; padding: 15px; background: #eee; text-decoration: none; color: #666; border-radius: 8px; font-weight: bold;">
                    ANNULER
                </a>
            </div>
        </form>
    </div>

</main>

<?php include 'includes/admin_footer.php'; ?>