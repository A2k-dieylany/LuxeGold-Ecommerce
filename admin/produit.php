<?php
require_once '../check_auth.php';
require_once '../../config/db.php';

$produits = $pdo->query("SELECT * FROM produits ORDER BY id DESC")->fetchAll();
include '../includes/admin_header.php';
?>

<div class="dashboard-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>💎 Inventaire des Bijoux</h2>
        <a href="ajouter.php" class="btn btn-gold"><i class="fas fa-plus"></i> Ajouter un bijou</a>
    </div>

    <table style="width: 100%; background: white; border-radius: 10px; border-collapse: collapse; overflow: hidden;">
        <thead style="background: #1a1a1a; color: white;">
            <tr>
                <th style="padding: 15px;">Image</th>
                <th style="padding: 15px;">Désignation</th>
                <th style="padding: 15px;">Prix</th>
                <th style="padding: 15px;">Stock</th>
                <th style="padding: 15px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($produits as $p): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px; text-align: center;">
                    <img src="../../assets/img/produits/<?= $p['image'] ?>" width="50" style="border-radius: 5px;">
                </td>
                <td style="padding: 10px;"><strong><?= htmlspecialchars($p['nom']) ?></strong></td>
                <td style="padding: 10px; color: #d4af37; font-weight: bold;"><?= $p['prix'] ?></td>
                <td style="padding: 10px;">
                    <span style="padding: 4px 8px; border-radius: 5px; background: <?= $p['stock'] < 5 ? '#ffe6e6' : '#e6ffed' ?>;">
                        <?= $p['stock'] ?> pces
                    </span>
                </td>
                <td style="padding: 10px;">
                    <a href="modifier.php?id=<?= $p['id'] ?>" style="color: blue; margin-right: 10px;"><i class="fas fa-edit"></i></a>
                    <a href="supprimer.php?id=<?= $p['id'] ?>" style="color: red;" onclick="return confirm('Supprimer ce bijou ?')"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/admin_footer.php'; ?>