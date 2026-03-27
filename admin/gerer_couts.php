<?php
// 1. Sécurité et Connexion
session_start();
require_once 'check_auth.php'; 
require_once '../config/db.php';

$message = '';
$msg_type = '';

// =========================================================
// 2. TRAITEMENT DU FORMULAIRE EN MASSE (BULK UPDATE)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prix_achat'])) {
    try {
        // On démarre une transaction : si une mise à jour plante, on annule tout pour protéger les données
        $pdo->beginTransaction();
        
        // Requête préparée pour la performance
        $stmt = $pdo->prepare("UPDATE produits SET prix_achat = ? WHERE id = ?");
        
        // On boucle sur toutes les cases du tableau envoyées par le formulaire
        foreach ($_POST['prix_achat'] as $id_produit => $prix) {
            // Nettoyage de sécurité : on s'assure que c'est bien un nombre entier positif
            $prix_clean = max(0, intval(str_replace(' ', '', $prix)));
            $id_clean = intval($id_produit);
            
            $stmt->execute([$prix_clean, $id_clean]);
        }
        
        $pdo->commit();
        $message = "Les coûts fournisseurs ont été mis à jour ! Votre Bilan Financier est désormais précis.";
        $msg_type = "success";
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = "Erreur technique lors de la sauvegarde : " . $e->getMessage();
        $msg_type = "error";
    }
}

// 3. Récupération de tout le catalogue
try {
    $produits = $pdo->query("SELECT id, nom, prix, prix_achat, stock FROM produits ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}

include 'includes/admin_header.php'; 
?>

<div class="container py-5" style="background: #f4f7f6; min-height: 100vh; padding: 30px; font-family: 'Poppins', sans-serif;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 style="margin: 0; font-family: 'Playfair Display', serif; color: #1a1a1a;">
                <i class="fas fa-file-invoice-dollar" style="color: #d4af37;"></i> Gestion des Coûts
            </h2>
            <p style="color: #888; margin: 5px 0 0;">Saisissez vos prix d'achat fournisseurs pour calculer vos bénéfices.</p>
        </div>
        <div>
            <a href="finances.php" class="btn" style="background: white; color: #1a1a1a; border: 1px solid #ddd; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; margin-right: 10px;">
                <i class="fas fa-chart-line"></i> Voir le Bilan
            </a>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div style="padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; font-weight: 500; 
            <?= $msg_type === 'success' ? 'background: #e6ffed; color: #27ae60; border: 1px solid #c3e6cb;' : 'background: #fdedec; color: #e74c3c; border: 1px solid #f5c6cb;' ?>">
            <i class="fas <?= $msg_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>"></i>
            <?= $message ?>
        </div>
    <?php endif; ?>

    <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
        
        <form action="" method="POST">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 800px;">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid #ddd;">
                            <th style="padding: 15px; color: #666; font-size: 0.85rem; text-transform: uppercase;">ID</th>
                            <th style="padding: 15px; color: #666; font-size: 0.85rem; text-transform: uppercase;">Nom du Bijou</th>
                            <th style="padding: 15px; color: #666; font-size: 0.85rem; text-transform: uppercase; text-align: center;">Stock</th>
                            <th style="padding: 15px; color: #666; font-size: 0.85rem; text-transform: uppercase; text-align: right;">Prix de Vente (Client)</th>
                            <th style="padding: 15px; color: #d4af37; font-size: 0.85rem; text-transform: uppercase; text-align: right;">Prix d'Achat (Fournisseur)</th>
                            <th style="padding: 15px; color: #27ae60; font-size: 0.85rem; text-transform: uppercase; text-align: right;">Marge Est.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produits as $p): ?>
                            <tr style="border-bottom: 1px solid #f1f1f1; transition: 0.2s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 15px; font-weight: bold; color: #888;">#<?= $p['id'] ?></td>
                                
                                <td style="padding: 15px; font-weight: 500; color: #1a1a1a;">
                                    <?= htmlspecialchars($p['nom']) ?>
                                </td>
                                
                                <td style="padding: 15px; text-align: center;">
                                    <span style="background: <?= $p['stock'] > 0 ? '#e6ffed' : '#fdedec' ?>; color: <?= $p['stock'] > 0 ? '#27ae60' : '#e74c3c' ?>; padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                        <?= $p['stock'] ?>
                                    </span>
                                </td>
                                
                                <td style="padding: 15px; text-align: right; font-weight: 600;">
                                    <?= number_format($p['prix'], 0, ',', ' ') ?> F
                                </td>
                                
                                <td style="padding: 15px; text-align: right;">
                                    <div style="position: relative; display: inline-block; width: 140px;">
                                        <input type="number" 
                                               name="prix_achat[<?= $p['id'] ?>]" 
                                               value="<?= htmlspecialchars($p['prix_achat']) ?>" 
                                               min="0"
                                               style="width: 100%; padding: 10px 35px 10px 10px; border: 2px solid #eee; border-radius: 8px; font-family: 'Poppins', sans-serif; font-weight: 600; color: #1a1a1a; outline: none; text-align: right; transition: 0.3s;"
                                               onfocus="this.style.borderColor='#d4af37'"
                                               onblur="this.style.borderColor='#eee'"
                                               oninput="calculerMarge(this, <?= $p['prix'] ?>)">
                                        <span style="position: absolute; right: 12px; top: 10px; color: #aaa; font-weight: bold;">F</span>
                                    </div>
                                </td>

                                <td style="padding: 15px; text-align: right;">
                                    <span class="badge-marge" style="background: #f8f9fa; padding: 6px 12px; border-radius: 8px; font-weight: bold; font-size: 0.9rem; color: #666;">
                                        <?php 
                                            if ($p['prix'] > 0) {
                                                $marge = (($p['prix'] - $p['prix_achat']) / $p['prix']) * 100;
                                                echo round($marge) . '%';
                                            } else {
                                                echo '0%';
                                            }
                                        ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 30px; text-align: right; border-top: 1px solid #eee; padding-top: 20px;">
                <button type="submit" style="background: #1a1a1a; color: white; border: none; padding: 15px 30px; border-radius: 8px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.1);" onmouseover="this.style.background='#d4af37'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='#1a1a1a'; this.style.transform='translateY(0)';">
                    <i class="fas fa-save"></i> Enregistrer tous les prix
                </button>
            </div>
        </form>
        
    </div>
</div>

<script>
// Javascript pour calculer la marge en temps réel quand le gérant tape le prix !
function calculerMarge(inputElement, prixVente) {
    let prixAchat = parseFloat(inputElement.value);
    let celluleMarge = inputElement.closest('tr').querySelector('.badge-marge');
    
    if (isNaN(prixAchat) || prixAchat < 0) prixAchat = 0;
    
    if (prixVente > 0) {
        let marge = ((prixVente - prixAchat) / prixVente) * 100;
        marge = Math.round(marge);
        
        celluleMarge.innerText = marge + '%';
        
        // Changement de couleur dynamique (Rouge si perte, Vert si bon)
        if (marge < 20) {
            celluleMarge.style.color = '#e74c3c';
            celluleMarge.style.background = '#fdedec';
        } else if (marge >= 40) {
            celluleMarge.style.color = '#27ae60';
            celluleMarge.style.background = '#e6ffed';
        } else {
            celluleMarge.style.color = '#f39c12';
            celluleMarge.style.background = '#fff9e6';
        }
    }
}
</script>

<?php include 'includes/admin_footer.php'; ?>