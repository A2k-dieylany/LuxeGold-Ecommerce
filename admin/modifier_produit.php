<?php
// 1. Sécurité et Connexion
require_once 'check_auth.php'; 
require_once '../config/db.php';
require_once '../includes/fonctions.php';

$message = "";
$id = $_GET['id'] ?? 0;

// --- FONCTION D'OPTIMISATION WEBP ---
function optimiserImageWebP($chemin_source, $chemin_destination, $qualite = 80) {
    $info = @getimagesize($chemin_source);
    if (!$info) return false;

    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($chemin_source);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($chemin_source);
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
    } elseif ($info['mime'] == 'image/webp') {
        return move_uploaded_file($chemin_source, $chemin_destination); 
    } else {
        return false;
    }

    $succes = imagewebp($image, $chemin_destination, $qualite);
    imagedestroy($image);
    return $succes;
}
// ------------------------------------

// 2. Récupération des infos du produit
$stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
$stmt->execute([$id]);
$produit = $stmt->fetch();

// Si le produit n'existe pas, retour au dashboard
if (!$produit) { 
    header('Location: dashboard.php'); 
    exit(); 
}

// 3. Traitement de la mise à jour
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $prix = $_POST['prix'];
    $prix_achat = isset($_POST['prix_achat']) ? intval($_POST['prix_achat']) : 0;
    $description = htmlspecialchars($_POST['description']);
    $stock = $_POST['stock'];
    $id_categorie = $_POST['id_categorie'];
    $reduction = (int)$_POST['reduction']; 
    
    // NOUVEAU : Récupération du métal (vide si non sélectionné)
    $metal = isset($_POST['metal']) ? htmlspecialchars($_POST['metal']) : '';
    
    // On garde les anciens noms de fichiers par défaut
    $image_finale = $produit['image']; 
    $video_finale = $produit['video'];
    $modele_3d_final = $produit['modele_3d'];

    // --- A. Gestion de l'image (Avec compression WebP) ---
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $nouvelle_image = time() . "_" . uniqid() . ".webp";
            $cible = "../assets/img/produits/" . $nouvelle_image;

            if (optimiserImageWebP($_FILES['image']['tmp_name'], $cible)) {
                // On efface l'ancienne image si elle existe
                if (!empty($produit['image']) && file_exists("../assets/img/produits/" . $produit['image'])) {
                    unlink("../assets/img/produits/" . $produit['image']);
                }
                $image_finale = $nouvelle_image;
            } else {
                $message = "❌ Erreur lors de l'optimisation de l'image.";
            }
        } else {
            $message = "❌ Format d'image non valide.";
        }
    }

    // --- B. Gestion de la vidéo ---
    if (empty($message) && isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        if ($_FILES['video']['size'] <= 10485760) {
            $ext_vid = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
            if (in_array($ext_vid, ['mp4', 'webm'])) {
                $nouvelle_video = time() . "_vid_" . uniqid() . "." . $ext_vid;
                if (move_uploaded_file($_FILES['video']['tmp_name'], "../assets/videos/" . $nouvelle_video)) {
                    // Nettoyage ancienne vidéo
                    if (!empty($produit['video']) && file_exists("../assets/videos/" . $produit['video'])) {
                        unlink("../assets/videos/" . $produit['video']);
                    }
                    $video_finale = $nouvelle_video;
                }
            } else {
                $message = "❌ Format vidéo non autorisé (MP4, WEBM).";
            }
        } else {
            $message = "❌ La vidéo est trop lourde (Max 10Mo).";
        }
    }

    // --- C. Gestion du modèle 3D ---
    if (empty($message) && isset($_FILES['modele_3d']) && $_FILES['modele_3d']['error'] == 0) {
        $ext_3d = strtolower(pathinfo($_FILES['modele_3d']['name'], PATHINFO_EXTENSION));
        if (in_array($ext_3d, ['glb', 'gltf'])) {
            $nouveau_3d = time() . "_3d_" . uniqid() . "." . $ext_3d;
            if (move_uploaded_file($_FILES['modele_3d']['tmp_name'], "../assets/3d/" . $nouveau_3d)) {
                // Nettoyage ancien modèle 3D
                if (!empty($produit['modele_3d']) && file_exists("../assets/3d/" . $produit['modele_3d'])) {
                    unlink("../assets/3d/" . $produit['modele_3d']);
                }
                $modele_3d_final = $nouveau_3d;
            }
        } else {
            $message = "❌ Format 3D non autorisé (GLB, GLTF).";
        }
    }

    // --- D. Mise à jour SQL ---
    if (empty($message)) {
        // MISE À JOUR : Ajout de 'metal = ?'
        $update = $pdo->prepare("UPDATE produits SET nom = ?, prix = ?, prix_achat = ?, description = ?, image = ?, video = ?, modele_3d = ?, stock = ?, id_categorie = ?, reduction = ?, metal = ? WHERE id = ?");
        
        if ($update->execute([$nom, $prix, $prix_achat, $description, $image_finale, $video_finale, $modele_3d_final, $stock, $id_categorie, $reduction, $metal, $id])) {
            enregistrerLog($pdo, "MODIFICATION_PRODUIT", "A modifié le produit : " . $nom . " (ID: " . $id . ")");
            header('Location: produits.php?success=1');
            exit();
        } else {
            $message = "❌ Erreur lors de la mise à jour en base de données.";
        }
    }
}

include 'includes/admin_header.php';
?>

<main class="dashboard-container" style="max-width: 800px; margin: 40px auto;">

    <div style="background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #eee;">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="font-family: 'Playfair Display', serif; color: #1a1a1a;">Modifier la pièce</h2>
            <p style="color: #d4af37; font-weight: bold;"><?= htmlspecialchars($produit['nom']) ?></p>
            <div style="width: 50px; height: 2px; background: #d4af37; margin: 15px auto;"></div>
        </div>
        
        <?php if($message): ?>
            <div style="background: #fff5f5; color: #e74c3c; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #fed7d7; text-align: center;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Catégorie</label>
                    <select name="id_categorie" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9;">
                        <?php
                        $stmtCat = $pdo->query("SELECT * FROM categories WHERE deleted = 0 ORDER BY nom ASC");
                        while($c = $stmtCat->fetch()) {
                            $selected = ($c['id'] == $produit['id_categorie']) ? 'selected' : '';
                            echo "<option value='{$c['id']}' $selected>{$c['nom']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Nom du bijou</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($produit['nom']) ?>" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box;">
                </div>

                <div class="form-group">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Métal (Optionnel)</label>
                    <select name="metal" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9;">
                        <option value="" <?= empty($produit['metal']) ? 'selected' : '' ?>>-- Aucun --</option>
                        <option value="Or" <?= ($produit['metal'] == 'Or') ? 'selected' : '' ?>>Or</option>
                        <option value="Argent" <?= ($produit['metal'] == 'Argent') ? 'selected' : '' ?>>Argent</option>
                        <option value="Platine" <?= ($produit['metal'] == 'Platine') ? 'selected' : '' ?>>Platine</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Prix Vente (FCFA)</label>
                    <input type="number" name="prix" value="<?= $produit['prix'] ?>" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box;">
                </div>

                <div class="form-group">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #d4af37;">Prix Achat Fournisseur (FCFA)</label>
                    <input type="number" name="prix_achat" value="<?= $produit['prix_achat'] ?? 0 ?>" required style="width: 100%; padding: 12px; border: 2px solid #d4af37; border-radius: 8px; box-sizing: border-box; background: #fffcf5;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Réduction (%)</label>
                    <input type="number" name="reduction" value="<?= $produit['reduction'] ?? 0 ?>" min="0" max="99" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; background: #f9f9f9;">
                </div>

                <div class="form-group">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Stock en réserve</label>
                    <input type="number" name="stock" value="<?= $produit['stock'] ?>" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 30px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">Description</label>
                <textarea name="description" rows="4" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-family: inherit;"><?= htmlspecialchars($produit['description']) ?></textarea>
            </div>

            <div style="background: #fdfaf0; padding: 20px; border-radius: 10px; border: 1px dashed #d4af37; margin-bottom: 30px;">
                
                <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 10px; color: #1a1a1a;">1. Image Principale</label>
                        <?php if(!empty($produit['image']) && file_exists("../assets/img/produits/" . $produit['image'])): ?>
                            <img src="../assets/img/produits/<?= $produit['image'] ?>" width="80" height="80" style="object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                        <?php else: ?>
                            <div style="width: 80px; height: 80px; background: #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999;"><i class="fas fa-image"></i></div>
                        <?php endif; ?>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #666;">Remplacer l'image (Optionnel)</label>
                        <input type="file" name="image" accept="image/jpeg, image/png, image/webp" style="width: 100%;">
                    </div>
                </div>

                <div style="height: 1px; background: #eaeaea; margin: 20px 0;"></div>

                <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 10px; color: #1a1a1a;">2. Vidéo MP4</label>
                        <?php if(!empty($produit['video'])): ?>
                            <div style="width: 80px; height: 80px; background: #000; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;" title="Une vidéo est déjà enregistrée"><i class="fas fa-play-circle fa-2x"></i></div>
                        <?php else: ?>
                            <div style="width: 80px; height: 80px; background: #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999;"><i class="fas fa-video-slash"></i></div>
                        <?php endif; ?>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #666;">Ajouter/Remplacer la vidéo (Optionnel)</label>
                        <input type="file" name="video" accept="video/mp4, video/webm" style="width: 100%;">
                    </div>
                </div>

                <div style="height: 1px; background: #eaeaea; margin: 20px 0;"></div>

                <div style="display: flex; align-items: center; gap: 20px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 10px; color: #1a1a1a;">3. Modèle 3D</label>
                        <?php if(!empty($produit['modele_3d'])): ?>
                            <div style="width: 80px; height: 80px; background: #d4af37; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;" title="Un modèle 3D est déjà enregistré"><i class="fas fa-cube fa-2x"></i></div>
                        <?php else: ?>
                            <div style="width: 80px; height: 80px; background: #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999;"><i class="fas fa-cube"></i></div>
                        <?php endif; ?>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #666;">Ajouter/Remplacer le modèle 3D (.glb)</label>
                        <input type="file" name="modele_3d" accept=".glb, .gltf" style="width: 100%;">
                    </div>
                </div>

            </div>
            <div style="display: flex; gap: 15px;">
                <button type="submit" style="flex: 2; padding: 15px; background: #1a1a1a; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s;" onmouseover="this.style.background='#d4af37'" onmouseout="this.style.background='#1a1a1a'">
                    <i class="fas fa-check"></i> ENREGISTRER LES MODIFICATIONS
                </button>
                <a href="produits.php" style="flex: 1; display:flex; align-items:center; justify-content:center; padding: 15px; background: #eee; text-decoration: none; color: #666; border-radius: 8px; font-weight: bold;">
                    RETOUR
                </a>
            </div>
        </form>
    </div>

</main>

<?php include 'includes/admin_footer.php'; ?>