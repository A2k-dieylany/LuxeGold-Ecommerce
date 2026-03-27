<?php
session_start();
require_once 'check_auth.php'; 
require_once '../config/db.php';

// Récupération des 50 derniers logs
$logs = $pdo->query("SELECT * FROM logs_activite ORDER BY created_at DESC LIMIT 50")->fetchAll();

include 'includes/admin_header.php'; 
?>

<div class="container py-5" style="background: #f4f7f6; min-height: 100vh; padding: 30px; font-family: 'Poppins', sans-serif;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 style="font-family: 'Playfair Display', serif;"><i class="fas fa-history" style="color: #d4af37;"></i> Journal d'Activité</h2>
        <a href="dashboard.php" style="text-decoration: none; color: #666; font-weight: 600;"><i class="fas fa-times"></i> Fermer</a>
    </div>

    <div style="background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #1a1a1a; color: #d4af37;">
                    <th style="padding: 15px;">Date & Heure</th>
                    <th style="padding: 15px;">Administrateur</th>
                    <th style="padding: 15px;">Action</th>
                    <th style="padding: 15px;">Détails</th>
                    <th style="padding: 15px;">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($logs as $l): ?>
                <tr style="border-bottom: 1px solid #eee; font-size: 0.9rem;">
                    <td style="padding: 15px; color: #888;"><?= date('d/m/Y H:i', strtotime($l['created_at'])) ?></td>
                    <td style="padding: 15px;"><strong><?= htmlspecialchars($l['admin_nom']) ?></strong></td>
                    <td style="padding: 15px;">
                        <span style="background: #fdfaf0; color: #d4af37; padding: 3px 8px; border-radius: 5px; font-size: 0.75rem; font-weight: bold;">
                            <?= $l['action'] ?>
                        </span>
                    </td>
                    <td style="padding: 15px; color: #555;"><?= htmlspecialchars($l['details']) ?></td>
                    <td style="padding: 15px; color: #ccc; font-size: 0.8rem;"><?= $l['ip_address'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>