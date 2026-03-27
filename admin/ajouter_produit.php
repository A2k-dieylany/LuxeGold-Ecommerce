<?php
// 1. Sécurité et Connexion
require_once 'check_auth.php'; 
require_once '../config/db.php';

$message = "";

// --- NOUVEAU : FONCTION D'OPTIMISATION WEBP ---
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
// ----------------------------------------------

// 2. LOGIQUE DE TRAITEMENT
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $prix = $_POST['prix'];
    $prix_achat = isset($_POST['prix_achat']) ? intval($_POST['prix_achat']) : 0; 
    $description = htmlspecialchars($_POST['description']);
    $stock = $_POST['stock'];
    $id_categorie = $_POST['id_categorie'];
    
    // NOUVEAU : Récupération du métal (vide si non sélectionné)
    $metal = isset($_POST['metal']) ? htmlspecialchars($_POST['metal']) : '';

    // Variables pour les médias
    $img_nom = null;
    $video_nom = null;
    $modele_3d_nom = null;

    // --- A. GESTION DE L'IMAGE (Avec compression WebP) ---
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            // On force l'extension .webp pour le fichier final
            $img_nom = time() . "_" . uniqid() . ".webp"; 
            $cible = "../assets/img/produits/" . $img_nom;

            if (!optimiserImageWebP($_FILES['image']['tmp_name'], $cible)) {
                $message = "❌ Erreur d'optimisation de l'image.";
            }
        } else {
            $message = "❌ Format d'image non autorisé (JPG, PNG, WEBP uniquement).";
        }
    } else {
        $message = "❌ Veuillez sélectionner une image valide.";
    }

    // --- B. GESTION DE LA VIDÉO (Optionnel) ---
    if (empty($message) && isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        if ($_FILES['video']['size'] <= 20485760) { // Max 10 Mo
            $ext_vid = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
            if (in_array($ext_vid, ['mp4', 'webm'])) {
                $video_nom = time() . "_vid_" . uniqid() . "." . $ext_vid;
                move_uploaded_file($_FILES['video']['tmp_name'], "../assets/videos/" . $video_nom);
            } else {
                $message = "❌ Format vidéo non autorisé (MP4, WEBM).";
            }
        } else {
            $message = "❌ La vidéo est trop lourde (Max 20Mo).";
        }
    }

    // --- C. GESTION DU MODÈLE 3D (Optionnel) ---
    if (empty($message) && isset($_FILES['modele_3d']) && $_FILES['modele_3d']['error'] == 0) {
        $ext_3d = strtolower(pathinfo($_FILES['modele_3d']['name'], PATHINFO_EXTENSION));
        if (in_array($ext_3d, ['glb', 'gltf'])) {
            $modele_3d_nom = time() . "_3d_" . uniqid() . "." . $ext_3d;
            move_uploaded_file($_FILES['modele_3d']['tmp_name'], "../assets/3d/" . $modele_3d_nom);
        } else {
            $message = "❌ Format 3D non autorisé (GLB, GLTF).";
        }
    }

    // --- D. INSERTION EN BASE DE DONNÉES ---
    if (empty($message) && $img_nom) {
        // MISE À JOUR : Ajout de la colonne 'metal' dans la requête
        $stmt = $pdo->prepare("INSERT INTO produits (nom, prix, prix_achat, description, image, video, modele_3d, stock, id_categorie, metal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$nom, $prix, $prix_achat, $description, $img_nom, $video_nom, $modele_3d_nom, $stock, $id_categorie, $metal])) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Le nouveau bijou a été ajouté avec succès !'];
            header('Location: produits.php');
            exit();
        } else {
            $message = "❌ Erreur SQL lors de l'enregistrement.";
        }
    }
}

include 'includes/admin_header.php';
?>

<main class="dashboard-container" style="max-width: 800px; margin: 40px auto;">
    
    <div style="background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #eee;">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="font-family: 'Playfair Display', serif; color: #1a1a1a; margin-bottom: 10px;">Ajouter une pièce d'exception</h2>
            <div style="width: 50px; height: 2px; background: #d4af37; margin: auto;"></div>
        </div>
        
        <?php if($message): ?>
            <div style="background: #fff5f5; color: #e74c3c; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #fed7d7; text-align: center;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="ajouter_produit.php" method="POST" enctype="multipart/form-data">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #555;">Catégorie</label>
                    <select name="id_categorie" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9;">
                        <option value="">Choisir...</option>
                        <?php
                        $stmtCat = $pdo->query("SELECT * FROM categories WHERE deleted = 0 ORDER BY nom ASC");
                        while($c = $stmtCat->fetch()) {
                            echo "<option value='{$c['id']}'>{$c['nom']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #555;">Nom du bijou</label>
                    <input type="text" name="nom" required placeholder="ex: Collier Or 18k" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #555;">Métal (Optionnel)</label>
                    <select name="metal" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9;">
                        <option value="">-- Aucun --</option>
                        <option value="Or">Or</option>
                        <option value="Argent">Argent</option>
                        <option value="Platine">Platine</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #555;">Prix Vente (FCFA)</label>
                    <input type="number" name="prix" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #d4af37;">Prix Achat (FCFA)</label>
                    <input type="number" name="prix_achat" value="0" min="0" required style="width: 100%; padding: 12px; border: 1px solid #d4af37; background: #fffcf5; border-radius: 8px; box-sizing: border-box;" title="Combien avez-vous payé ce bijou chez le fournisseur ?">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #555;">Stock initial</label>
                    <input type="number" name="stock" value="1" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #555;">Description détaillée</label>
                <textarea name="description" rows="4" placeholder="Décrivez les matériaux, le poids..." style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-family: inherit;"></textarea>
            </div>

            <div style="background: #fdfaf0; padding: 20px; border-radius: 10px; border: 1px dashed #d4af37; margin-bottom: 30px;">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #1a1a1a;">1. Photographie Principale <span style="color:red">*</span></label>
                    <input type="file" name="image" accept="image/jpeg, image/png, image/webp" required style="width: 100%;">
                    <p style="font-size: 11px; color: #888; margin-top: 5px; margin-bottom: 0;">Sera automatiquement optimisée (WebP) pour charger plus vite.</p>
                </div>

                <div style="height: 1px; background: #eaeaea; margin: 15px 0;"></div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #1a1a1a;">2. Vidéo de présentation (Optionnel)</label>
                    <input type="file" name="video" accept="video/mp4, video/webm" style="width: 100%;">
                    <p style="font-size: 11px; color: #888; margin-top: 5px; margin-bottom: 0;">Montrez les reflets du bijou (MP4, Max 10Mo).</p>
                </div>

                <div style="height: 1px; background: #eaeaea; margin: 15px 0;"></div>

                <div class="form-group">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #1a1a1a;">3. Modèle 3D interactif (Optionnel)</label>
                    <input type="file" name="modele_3d" accept=".glb, .gltf" style="width: 100%;">
                    <p style="font-size: 11px; color: #888; margin-top: 5px; margin-bottom: 0;">Pour faire tourner le bijou à 360° (Format .glb ou .gltf).</p>
                </div>

            </div>

            <div style="display: flex; gap: 15px;">
                <button type="submit" style="flex: 2; padding: 15px; background: #1a1a1a; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s;" onmouseover="this.style.background='#d4af37'" onmouseout="this.style.background='#1a1a1a'">
                    <i class="fas fa-save"></i> ENREGISTRER DANS L'INVENTAIRE
                </button>
                <a href="produits.php" style="flex: 1; display:flex; align-items:center; justify-content:center; padding: 15px; background: #eee; text-decoration: none; color: #666; border-radius: 8px; font-weight: bold;">
                    ANNULER
                </a>
            </div>
        </form>
    </div>

</main>

<?php include 'includes/admin_footer.php'; ?>