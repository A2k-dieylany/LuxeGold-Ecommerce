<?php
// 1. Sécurité et Connexion
require_once 'check_auth.php'; 
require_once '../config/db.php';

// --- NOUVEAU : LOGIQUE DE SUPPRESSION DOUCE (SOFT DELETE) ---
if (isset($_POST['supprimer_categorie'])) {
    $id_a_supprimer = (int)$_POST['id_categorie_suppr'];
    if ($id_a_supprimer > 0) {
        try {
            // Au lieu de DELETE, on cache simplement la catégorie (deleted = 1)
            $stmtDel = $pdo->prepare("UPDATE categories SET deleted = 1 WHERE id = ?");
            $stmtDel->execute([$id_a_supprimer]);
            
            // On cache aussi tous les produits qui étaient dans cette catégorie !
            $stmtHideProducts = $pdo->prepare("UPDATE produits SET deleted = 1 WHERE id_categorie = ?");
            $stmtHideProducts->execute([$id_a_supprimer]);

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Catégorie (et ses produits) archivée avec succès !'];
            header('Location: categories.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Erreur lors de la suppression.'];
        }
    }
}
// -----------------------------------------------------------

// Récupération des catégories (Uniquement celles qui ne sont pas supprimées : deleted = 0)
try {
    $stmt = $pdo->query("SELECT * FROM categories WHERE deleted = 0 ORDER BY id DESC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erreur Lecture Catégories: " . $e->getMessage());
    $categories = []; 
}

include 'includes/admin_header.php'; 
?>

<div class="container py-5" style="background: #f4f4f4; min-height: 100vh; padding: 30px; font-family: 'Poppins', sans-serif;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h2 style="margin: 0; font-family: 'Playfair Display', serif;">📁 Gestion des Catégories</h2>
            <p style="color: #888; margin: 5px 0 0;">Organisez vos collections et leurs vidéos d'accueil</p>
        </div>
        
        <a href="ajouter_categorie.php" class="btn" style="background: #1a1a1a; color: #d4af37; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: bold; transition: all 0.3s; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
            <i class="fas fa-plus"></i> NOUVELLE CATÉGORIE
        </a>
    </div>

    <?php if(isset($_SESSION['flash'])): ?>
        <div style="background: <?= $_SESSION['flash']['type'] == 'success' ? '#e6ffed' : '#fee' ?>; 
                    color: <?= $_SESSION['flash']['type'] == 'success' ? '#28a745' : '#e74c3c' ?>; 
                    padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; 
                    border: 1px solid <?= $_SESSION['flash']['type'] == 'success' ? '#c3e6cb' : '#e74c3c' ?>; 
                    font-weight: bold; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <i class="fas <?= $_SESSION['flash']['type'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i> 
            <?= $_SESSION['flash']['message'] ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div style="overflow-x: auto; background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #1a1a1a; color: #d4af37;">
                    <th style="padding: 18px;">ID</th>
                    <th style="padding: 18px;">Nom de la catégorie</th>
                    <th style="padding: 18px;">Média d'accueil</th>
                    <th style="padding: 18px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($categories)): ?>
                    <tr>
                        <td colspan="4" style="padding: 40px; text-align: center; color: #888;">Aucune catégorie enregistrée.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($categories as $c): ?>
                    <tr style="border-bottom: 1px solid #eee; transition: background 0.3s;" onmouseover="this.style.background='#fcfaf2'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 18px; font-weight: bold;">#<?= $c['id'] ?></td>
                        
                        <td style="padding: 18px; font-size: 1.1rem; color: #1a1a1a; font-weight: 600;">
                            <?= htmlspecialchars($c['nom']) ?>
                        </td>
                        
                        <td style="padding: 18px;">
                            <?php if(!empty($c['video'])): ?>
                                <span style="background: #e6f7ff; color: #007bff; padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; border: 1px solid #b3e0ff;">
                                    <i class="fas fa-video"></i> Vidéo Animée
                                </span>
                            <?php else: ?>
                                <span style="background: #f8f9fa; color: #6c757d; padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; border: 1px solid #ddd;">
                                    <i class="fas fa-image"></i> Image statique
                                </span>
                            <?php endif; ?>
                        </td>
                        
                        <td style="padding: 18px; display: flex; gap: 10px;">
                            <a href="modifier_categorie.php?id=<?= $c['id'] ?>" style="background: #d4af37; color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; font-size: 11px; font-weight: bold; transition: 0.2s;" title="Modifier la catégorie" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                                <i class="fas fa-edit"></i> MODIFIER
                            </a>

                            <form method="POST" style="margin: 0;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ? Tous les produits à l\'intérieur seront également cachés.');">
                                <input type="hidden" name="id_categorie_suppr" value="<?= $c['id'] ?>">
                                <button type="submit" name="supprimer_categorie" style="background: #e74c3c; color: white; border: none; padding: 8px 15px; border-radius: 6px; font-size: 11px; font-weight: bold; cursor: pointer; transition: 0.2s;" title="Supprimer la catégorie" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                                    <i class="fas fa-trash-alt"></i> SUPPRIMER
                                </button>
                            </form>
                            </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>