<?php
// 1. Sécurité
require_once 'check_auth.php';
require_once '../config/db.php';

$clients = [];
$table_existe = true;

try {
    // Requête pour lister les clients et calculer leur total dépensé
    $sql = "SELECT u.*, 
            COUNT(c.id) as nombre_commandes, 
            SUM(c.total) as total_depense 
            FROM utilisateurs u 
            LEFT JOIN commandes c ON u.id = c.id_utilisateur 
            GROUP BY u.id 
            ORDER BY total_depense DESC, u.id DESC";
            
    $stmt = $pdo->query($sql);
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $table_existe = false;
}

include 'includes/admin_header.php';
?>

<main class="dashboard-container" style="padding: 20px;">
    
    <div class="dashboard-header" style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; background: white; padding: 20px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
        <div>
            <h1 style="font-family: 'Playfair Display', serif; margin: 0; font-size: 1.8rem;">
                <i class="fas fa-users" style="color: #d4af37;"></i> Liste des Clients
            </h1>
            <p style="color: #888; margin: 5px 0 0;">Gérez les informations de vos clients et identifiez vos meilleurs acheteurs.</p>
        </div>
        <div>
            <span style="background: #1a1a1a; color: white; padding: 10px 15px; border-radius: 8px; font-weight: bold;">
                Total : <?= count($clients) ?> client(s)
            </span>
        </div>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <div style="padding: 15px; margin-bottom: 20px; border-radius: 8px; font-weight: 500; display: flex; align-items: center; gap: 10px;
            <?= $_SESSION['flash']['type'] === 'success' ? 'background: #e8f5e9; color: #2e7d32; border-left: 5px solid #2e7d32;' : 'background: #ffebee; color: #c62828; border-left: 5px solid #c62828;' ?>">
            <i class="fas <?= $_SESSION['flash']['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            <?= htmlspecialchars($_SESSION['flash']['message']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="form-container" style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
        
        <?php if (!$table_existe): ?>
            <div style="text-align: center; padding: 40px 20px;">
                <i class="fas fa-tools" style="font-size: 4rem; color: #eee; margin-bottom: 20px;"></i>
                <h2 style="color: #333; margin-bottom: 10px;">Configuration requise</h2>
                <p style="color: #666;">Exécutez cette requête dans phpMyAdmin : <br><code>ALTER TABLE commandes ADD id_utilisateur INT NULL;</code></p>
            </div>

        <?php elseif (count($clients) > 0): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: #f9f9f9; border-bottom: 2px solid #eee;">
                            <th style="padding: 15px; color: #666;">ID</th>
                            <th style="padding: 15px; color: #666;">Nom & Prénom</th>
                            <th style="padding: 15px; color: #666;">Téléphone</th>
                            <th style="padding: 15px; color: #1a1a1a; font-weight: bold;"><i class="fas fa-star" style="color: #d4af37;"></i> Total Dépensé</th>
                            <th style="padding: 15px; color: #666;">Date d'inscription</th>
                            <th style="padding: 15px; color: #666;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($clients as $client): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px;">#<?= $client['id'] ?? '?' ?></td>
                            <td style="padding: 15px; font-weight: bold;"><?= htmlspecialchars($client['nom'] ?? 'Inconnu') ?></td>
                            <td style="padding: 15px;"><?= htmlspecialchars($client['telephone'] ?? '-') ?></td>
                            <td style="padding: 15px;">
                                <span style="font-weight: bold; color: #27ae60; font-size: 1.1rem;">
                                    <?= number_format($client['total_depense'] ?? 0, 0, ',', ' ') ?> FCFA
                                </span><br>
                                <span style="font-size: 11px; color: #888; background: #f4f4f4; padding: 2px 6px; border-radius: 10px;">
                                    <?= $client['nombre_commandes'] ?> commande(s)
                                </span>
                            </td>
                            <td style="padding: 15px; color: #888;">
                                <?= isset($client['created_at']) ? date('d/m/Y', strtotime($client['created_at'])) : '-' ?>
                            </td>
                            <td style="padding: 15px;">
                               <a href="modifier_client.php?id=<?= $client['id'] ?>" style="color: #3498db; margin-right: 15px; text-decoration: none;"><i class="fas fa-edit"></i></a>
                               <a href="supprimer_client.php?id=<?= $client['id'] ?>" style="color: #e74c3c; text-decoration: none;" onclick="return confirm('Supprimer ce client ?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px 20px;">
                <h3 style="color: #666;">Aucun client enregistré.</h3>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/admin_footer.php'; ?>