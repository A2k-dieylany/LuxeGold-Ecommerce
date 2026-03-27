<?php
// 1. Sécurité et Connexion
require_once 'check_auth.php'; 
require_once '../config/db.php';

$message = "";

// ==========================================
// 2. TRAITEMENT DES ACTIONS (PROMOS UNIQUEMENT)
// ==========================================

// --- A. APPLICATION DE RÉDUCTION EN MASSE (SOLDES) ---
if (isset($_POST['appliquer_solde'])) {
    $id_categorie = $_POST['id_categorie_solde'];
    $pourcentage = (int)$_POST['pourcentage'];

    if (!empty($id_categorie)) {
        if ($pourcentage >= 0 && $pourcentage <= 100) {
            $stmt = $pdo->prepare("UPDATE produits SET reduction = ? WHERE id_categorie = ?");
            $stmt->execute([$pourcentage, $id_categorie]);
            $message = "✅ Prix réduits avec succès de $pourcentage% !";
        } else {
            $message = "❌ Erreur : La réduction doit être comprise entre 0 et 100.";
        }
    }
}

// --- B. CRÉATION DE CODE PROMO ---
if (isset($_POST['ajouter_code_promo'])) {
    $code = htmlspecialchars($_POST['code']);
    $reduction_promo = (int)$_POST['reduction_promo'];
    $date_expiration = $_POST['date_expiration'];

    if (!empty($code) && $reduction_promo > 0 && $reduction_promo <= 100) {
        try {
            // CORRECTION ICI : codes_promo au lieu de code_promos
            $stmt = $pdo->prepare("INSERT INTO codes_promo (code, reduction, date_expiration) VALUES (?, ?, ?)");
            $stmt->execute([$code, $reduction_promo, $date_expiration]);
            $message = "✅ Code promo '$code' créé avec succès !";
        } catch (PDOException $e) {
            // Petite astuce temporaire pour voir la vraie erreur si tes colonnes n'ont pas les mêmes noms
            $message = "❌ Erreur SQL : " . $e->getMessage();
        }
    }
}

include 'includes/admin_header.php';
?>

<main style="padding: 30px; max-width: 1300px; margin: 0 auto; font-family: 'Poppins', sans-serif;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h2 style="margin: 0; font-family: 'Playfair Display', serif;">🏷️ Gestion des Promotions</h2>
            <p style="color: #888; margin: 5px 0 0;">Gérez vos soldes et vos codes de réduction</p>
        </div>
    </div>

    <?php if($message): ?>
        <div style="background: #e6ffed; color: #28a745; padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #c3e6cb; font-weight: bold; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px; margin-bottom: 40px;">
        
        <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); border-top: 4px solid #e74c3c;">
            <h3 style="margin-top: 0; margin-bottom: 20px; color: #e74c3c;">
                <i class="fas fa-percentage"></i> Soldes par Catégorie
            </h3>
            <p style="color: #666; font-size: 0.9rem; margin-bottom: 20px;">Appliquez une réduction immédiate sur tous les bijoux d'une catégorie spécifique.</p>
            
            <form method="POST">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Choisir la catégorie :</label>
                    <select name="id_categorie_solde" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9;">
                        <option value="">-- Sélectionner --</option>
                        <?php
                        $stmtCat = $pdo->query("SELECT id, nom FROM categories ORDER BY nom ASC");
                        while($cat = $stmtCat->fetch()) {
                            echo "<option value='{$cat['id']}'>{$cat['nom']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Réduction (0 à 100%) :</label>
                    <input type="number" name="pourcentage" min="0" max="100" placeholder="Ex: 20" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box;">
                    <small style="color: #888;">Mettez 0 pour annuler les soldes sur cette catégorie.</small>
                </div>
                
                <button type="submit" name="appliquer_solde" style="width: 100%; padding: 15px; background: #e74c3c; color: white; font-weight: bold; border: none; border-radius: 8px; cursor: pointer; transition: 0.3s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                    APPLIQUER LA RÉDUCTION
                </button>
            </form>
        </div>

        <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); border-top: 4px solid #d4af37;">
            <h3 style="margin-top: 0; margin-bottom: 20px; color: #d4af37;">
                <i class="fas fa-ticket-alt"></i> Créer un Code Promo
            </h3>
            <p style="color: #666; font-size: 0.9rem; margin-bottom: 20px;">Générez un code secret que vos clients pourront utiliser dans leur panier.</p>
            
            <form method="POST">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Code (texte) :</label>
                    <input type="text" name="code" placeholder="Ex: TABASKI20" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; text-transform: uppercase; font-weight: bold;">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px;">Réduction (%) :</label>
                        <input type="number" name="reduction_promo" min="1" max="100" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px;">Expiration :</label>
                        <input type="date" name="date_expiration" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box;">
                    </div>
                </div>

                <button type="submit" name="ajouter_code_promo" style="width: 100%; padding: 15px; background: #1a1a1a; color: #d4af37; font-weight: bold; border: none; border-radius: 8px; cursor: pointer; transition: 0.3s;" onmouseover="this.style.background='#d4af37'; this.style.color='white';" onmouseout="this.style.background='#1a1a1a'; this.style.color='#d4af37';">
                    GÉNÉRER LE CODE
                </button>
            </form>
        </div>

    </div>

    <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; margin-bottom: 20px;">📋 Codes Promo Actifs</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #1a1a1a; color: #d4af37;">
                    <th style="padding: 15px;">Code</th>
                    <th style="padding: 15px;">Réduction</th>
                    <th style="padding: 15px;">Date d'expiration</th>
                    <th style="padding: 15px;">Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    // CORRECTION ICI AUSSI
                    $stmtListe = $pdo->query("SELECT * FROM codes_promo ORDER BY id DESC");
                    
                    if ($stmtListe->rowCount() == 0) {
                        echo "<tr><td colspan='4' style='padding: 30px; text-align: center; color: #888;'>Aucun code promo créé.</td></tr>";
                    } else {
                        $dateDuJour = date('Y-m-d');
                        while ($row = $stmtListe->fetch()) {
                            $estExpire = ($row['date_expiration'] < $dateDuJour);
                            $badgeStatut = $estExpire 
                                ? "<span style='background: #ffe6e6; color: #dc3545; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: bold;'>Expiré</span>" 
                                : "<span style='background: #e6ffed; color: #28a745; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: bold;'>Valide</span>";

                            echo "<tr style='border-bottom: 1px solid #eee;'>";
                            echo "<td style='padding: 15px; font-weight: bold; font-family: monospace; font-size: 1.1rem;'>" . htmlspecialchars($row['code']) . "</td>";
                            echo "<td style='padding: 15px; color: #e74c3c; font-weight: bold;'>" . htmlspecialchars($row['reduction']) . "%</td>";
                            echo "<td style='padding: 15px; color: #666;'>" . date('d/m/Y', strtotime($row['date_expiration'])) . "</td>";
                            echo "<td style='padding: 15px;'>" . $badgeStatut . "</td>";
                            echo "</tr>";
                        }
                    }
                } catch (PDOException $e) {
                    echo "<tr><td colspan='4' style='padding: 30px; text-align: center; color: #888;'>Erreur SQL : " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</main>

<?php include 'includes/admin_footer.php'; ?>