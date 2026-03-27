<?php
session_start();
// Vérification si l'admin est connecté (adapte selon ton système de sécurité habituel)
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/db.php';

$message = "";

// --- LOGIQUE DE MODÉRATION (Valider ou Supprimer) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id_avis = (int)$_GET['id'];
    
    if ($_GET['action'] === 'valider') {
        $stmt = $pdo->prepare("UPDATE avis SET statut = 'valide' WHERE id = ?");
        if ($stmt->execute([$id_avis])) {
            $message = "<div class='alert alert-success'>L'avis a été approuvé et est maintenant visible sur le site.</div>";
        }
    } elseif ($_GET['action'] === 'supprimer') {
        $stmt = $pdo->prepare("DELETE FROM avis WHERE id = ?");
        if ($stmt->execute([$id_avis])) {
            $message = "<div class='alert alert-danger'>L'avis a été supprimé définitivement.</div>";
        }
    }
}

// --- RÉCUPÉRATION DES AVIS (Avec le nom du produit) ---
// On utilise un JOIN pour savoir sur quel bijou l'avis a été laissé
$stmtAvis = $pdo->query("
    SELECT a.*, p.nom AS nom_produit, p.image AS image_produit 
    FROM avis a 
    LEFT JOIN produits p ON a.produit_id = p.id 
    ORDER BY a.statut = 'en_attente' DESC, a.created_at DESC
");
$lesAvis = $stmtAvis->fetchAll();

// Inclusion de ton header admin (ajuste le nom si besoin)
if (file_exists('includes/admin_header.php')) {
    include 'includes/admin_header.php';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modération des Avis | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { background: #f4f4f4; font-family: 'Poppins', sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .header-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; }
        .header-title h2 { margin: 0; color: #1a1a1a; font-family: 'Playfair Display', serif; font-size: 1.8rem; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .alert-success { background: #e8f5e9; color: #27ae60; border: 1px solid #27ae60; }
        .alert-danger { background: #fdeaea; color: #e74c3c; border: 1px solid #e74c3c; }

        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #1a1a1a; color: #d4af37; padding: 15px; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; }
        td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        tr:hover { background: #fcfcfc; }

        .produit-info { display: flex; align-items: center; gap: 10px; }
        .produit-img { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; }
        
        .stars { color: #d4af37; font-size: 0.9rem; }
        .commentaire-text { font-size: 0.9rem; color: #555; max-width: 300px; line-height: 1.4; }
        
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; }
        .badge-attente { background: #fff3cd; color: #f39c12; border: 1px solid #f39c12; }
        .badge-valide { background: #e8f5e9; color: #27ae60; border: 1px solid #27ae60; }

        .btn { padding: 8px 12px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 0.85rem; font-weight: bold; color: white; display: inline-flex; align-items: center; gap: 5px; transition: 0.2s; }
        .btn-valider { background: #27ae60; }
        .btn-valider:hover { background: #219653; }
        .btn-supprimer { background: #e74c3c; }
        .btn-supprimer:hover { background: #c0392b; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-title">
        <h2><i class="fas fa-comments"></i> Modération des Avis</h2>
        <a href="dashboard.php" class="btn" style="background: #1a1a1a;"><i class="fas fa-arrow-left"></i> Retour au Dashboard</a>
    </div>

    <?= $message ?>

    <?php if (empty($lesAvis)): ?>
        <div style="text-align: center; padding: 50px; color: #888;">
            <i class="far fa-folder-open" style="font-size: 3rem; margin-bottom: 15px;"></i>
            <p>Aucun avis client pour le moment.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Client</th>
                    <th>Note</th>
                    <th>Commentaire</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lesAvis as $avis): ?>
                    <tr>
                        <td>
                            <div class="produit-info">
                                <img src="../assets/img/produits/<?= htmlspecialchars($avis['image_produit'] ?? 'default.png') ?>" class="produit-img" alt="Bijou">
                                <strong><?= htmlspecialchars($avis['nom_produit'] ?? 'Produit inconnu') ?></strong>
                            </div>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($avis['client_nom']) ?></strong><br>
                            <span style="font-size: 0.8rem; color: #888;"><?= date('d/m/Y H:i', strtotime($avis['created_at'])) ?></span>
                        </td>
                        <td class="stars">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="fa<?= $i <= $avis['note'] ? 's' : 'r' ?> fa-star"></i>
                            <?php endfor; ?>
                        </td>
                        <td class="commentaire-text">
                            "<?= htmlspecialchars($avis['commentaire']) ?>"
                        </td>
                        <td>
                            <?php if (($avis['statut'] ?? 'en_attente') === 'valide'): ?>
                                <span class="badge badge-valide"><i class="fas fa-check"></i> Publié</span>
                            <?php else: ?>
                                <span class="badge badge-attente"><i class="fas fa-hourglass-half"></i> En attente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (($avis['statut'] ?? 'en_attente') !== 'valide'): ?>
                                <a href="gestion_avis.php?action=valider&id=<?= $avis['id'] ?>" class="btn btn-valider" onclick="return confirm('Approuver et publier cet avis sur le site ?');">
                                    <i class="fas fa-check"></i> Valider
                                </a>
                            <?php endif; ?>
                            
                            <a href="gestion_avis.php?action=supprimer&id=<?= $avis['id'] ?>" class="btn btn-supprimer" onclick="return confirm('Supprimer cet avis définitivement ?');">
                                <i class="fas fa-trash-alt"></i> Supprimer
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
<?php 
if (file_exists('includes/admin_footer.php')) {
    include 'includes/admin_footer.php';
}
?>