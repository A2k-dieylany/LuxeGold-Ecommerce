<?php
// 1. Sécurité et Connexion
require_once 'check_auth.php'; 
require_once '../config/db.php';

$message = "";
$id = $_GET['id'] ?? 0;

// 2. Récupération des infos de la catégorie
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$categorie = $stmt->fetch();

if (!$categorie) { 
    header('Location: dashboard.php'); 
    exit(); 
}

// 3. Traitement de la mise à jour
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $video_finale = $categorie['video']; // On garde l'ancienne par défaut

    // Gestion du remplacement de la vidéo
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        if ($_FILES['video']['size'] <= 20485760) {
            $ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['mp4', 'webm'])) {
                $nouvelle_video = time() . "_cat_" . uniqid() . "." . $ext;
                $cible = "../assets/videos/" . $nouvelle_video;

                if (move_uploaded_file($_FILES['video']['tmp_name'], $cible)) {
                    // Si succès, on efface l'ancienne vidéo du serveur !
                    if (!empty($categorie['video']) && file_exists("../assets/videos/" . $categorie['video'])) {
                        unlink("../assets/videos/" . $categorie['video']);
                    }
                    $video_finale = $nouvelle_video;
                } else {
                    $message = "❌ Erreur lors du téléchargement de la vidéo.";
                }
            } else {
                $message = "❌ Format vidéo non autorisé.";
            }
        } else {
            $message = "❌ La vidéo est trop lourde (Max 10Mo).";
        }
    }

    // Mise à jour SQL
    if (empty($message)) {
        $update = $pdo->prepare("UPDATE categories SET nom = ?, video = ? WHERE id = ?");
        
        if ($update->execute([$nom, $video_finale, $id])) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Catégorie modifiée avec succès !'];
            header('Location: dashboard.php');
            exit();
        } else {
            $message = "❌ Erreur lors de la mise à jour en base de données.";
        }
    }
}

include 'includes/admin_header.php';
?>

<main class="dashboard-container" style="max-width: 600px; margin: 40px auto;">

    <div style="background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #eee;">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="font-family: 'Playfair Display', serif; color: #1a1a1a;">Modifier Catégorie</h2>
            <p style="color: #d4af37; font-weight: bold;"><?= htmlspecialchars($categorie['nom']) ?></p>
            <div style="width: 50px; height: 2px; background: #d4af37; margin: 15px auto;"></div>
        </div>
        
        <?php if($message): ?>
            <div style="background: #fff5f5; color: #e74c3c; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #fed7d7; text-align: center;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            
            <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Nom de la catégorie</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($categorie['nom']) ?>" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 1.1rem;">
            </div>

            <div style="background: #fdfaf0; padding: 20px; border-radius: 10px; border: 1px dashed #d4af37; margin-bottom: 30px;">
                
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 10px; color: #1a1a1a;">Vidéo Actuelle</label>
                        <?php if(!empty($categorie['video'])): ?>
                            <div style="width: 80px; height: 80px; background: #000; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;" title="Une vidéo est déjà enregistrée"><i class="fas fa-play-circle fa-2x"></i></div>
                        <?php else: ?>
                            <div style="width: 80px; height: 80px; background: #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999;"><i class="fas fa-video-slash"></i></div>
                        <?php endif; ?>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #666;">Ajouter/Remplacer la vidéo</label>
                        <input type="file" name="video" accept="video/mp4, video/webm" style="width: 100%;">
                    </div>
                </div>

            </div>

            <div style="display: flex; gap: 15px;">
                <button type="submit" style="flex: 2; padding: 15px; background: #1a1a1a; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s;" onmouseover="this.style.background='#d4af37'" onmouseout="this.style.background='#1a1a1a'">
                    <i class="fas fa-check"></i> ENREGISTRER 
                </button>
                <a href="dashboard.php" style="flex: 1; text-align: center; padding: 15px; background: #eee; text-decoration: none; color: #666; border-radius: 8px; font-weight: bold;">
                    RETOUR
                </a>
            </div>
        </form>
    </div>

</main>

<?php include 'includes/admin_footer.php'; ?>