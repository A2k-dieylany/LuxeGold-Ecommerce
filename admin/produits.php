<?php
// 1. Sécurité et Connexion
require_once 'check_auth.php';
require_once '../config/db.php';

// --- NOUVEAU : LOGIQUE DE SUPPRESSION DOUCE (SOFT DELETE) ---
if (isset($_POST['delete_id'])) {
    $id_a_supprimer = (int)$_POST['delete_id'];
    if ($id_a_supprimer > 0) {
        try {
            // Au lieu de DELETE, on "cache" simplement le produit
            $stmtDel = $pdo->prepare("UPDATE produits SET deleted = 1 WHERE id = ?");
            $stmtDel->execute([$id_a_supprimer]);
            
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Le bijou a été retiré du catalogue avec succès.'];
            header('Location: produits.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Erreur lors de la suppression.'];
        }
    }
}
// -----------------------------------------------------------

$produits = [];
$erreur = "";

try {
    // --- MODIFICATION : On ne récupère que les produits non supprimés (deleted = 0) ---
    $stmt = $pdo->query("SELECT * FROM produits WHERE deleted = 0 ORDER BY id DESC");
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erreur = "Erreur lors de la récupération : " . $e->getMessage();
}

include 'includes/admin_header.php';
?>

<main class="dashboard-container" style="padding: 20px;">
    
    <div class="dashboard-header" style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; background: white; padding: 20px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
        <div>
            <h1 style="font-family: 'Playfair Display', serif; margin: 0; font-size: 1.8rem;">
                <i class="fas fa-gem" style="color: #d4af37;"></i> Gestion de l'Inventaire
            </h1>
            <p style="color: #888; margin: 5px 0 0;">Gérez vos bijoux et surveillez l'état de vos stocks.</p>
        </div>
        
        <div>
            <a href="ajouter_produit.php" style="
                display: inline-flex; 
                align-items: center; 
                gap: 10px; 
                padding: 12px 25px; 
                background: #1a1a1a; 
                color: white; 
                text-decoration: none; 
                border-radius: 8px; 
                font-weight: bold; 
                position: relative; 
                z-index: 10; 
                transition: 0.3s;
                border: none;
            " onmouseover="this.style.background='#d4af37'; this.style.color='black';" onmouseout="this.style.background='#1a1a1a'; this.style.color='white';">
                <i class="fas fa-plus-circle" style="pointer-events: none;"></i> 
                <span style="pointer-events: none;">Nouveau Bijou</span>
            </a>
        </div>
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

    <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
        
        <?php if (count($produits) > 0): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: #f9f9f9; border-bottom: 2px solid #eee;">
                            <th style="padding: 15px; color: #666; width: 80px;">Aperçu</th>
                            <th style="padding: 15px; color: #666;">Désignation</th>
                            <th style="padding: 15px; color: #666;">Prix de vente</th>
                            <th style="padding: 15px; color: #666;">Disponibilité</th>
                            <th style="padding: 15px; color: #666;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($produits as $p): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px;">
                                <img src="../assets/img/produits/<?= htmlspecialchars($p['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($p['nom']) ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #eee;">
                            </td>
                            
                            <td style="padding: 15px;">
                                <strong style="color: #333; display: block;"><?= htmlspecialchars($p['nom']) ?></strong>
                            </td>
                            
                            <td style="padding: 15px; font-weight: bold; color: #d4af37;">
                                <?= number_format($p['prix'], 0, ',', ' ') ?> FCFA
                            </td>
                            
                            <td style="padding: 15px;">
                                <?php if ($p['stock'] <= 0): ?>
                                    <span style="background: #ffebee; color: #c62828; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;">Rupture</span>
                                <?php elseif ($p['stock'] <= 2): ?>
                                    <span style="background: #fff3cd; color: #856404; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;">Faible (<?= $p['stock'] ?>)</span>
                                <?php else: ?>
                                    <span style="background: #e8f5e9; color: #2e7d32; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;"><?= $p['stock'] ?> en stock</span>
                                <?php endif; ?>
                            </td>
                            
                            <td style="padding: 15px; display: flex; gap: 15px; align-items: center;">
                                <a href="modifier_produit.php?id=<?= $p['id'] ?>" style="color: #3498db; font-size: 1.1rem; transition: 0.2s;" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'"><i class="fas fa-edit"></i></a>
                                
                                <form method="POST" style="margin: 0;" onsubmit="return confirm('Voulez-vous vraiment retirer ce bijou du catalogue ? L\'historique des commandes sera conservé.');">
                                    <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                                    <button type="submit" style="background: none; border: none; color: #e74c3c; font-size: 1.1rem; cursor: pointer; padding: 0; transition: 0.2s;" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #999;">
                <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 15px;"></i>
                <p>Aucun produit n'est visible dans l'inventaire.</p>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>