<?php
require_once 'config/db.php';
require_once 'includes/fonctions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirection si non connecté
if (!isset($_SESSION['client_id'])) {
    header('Location: connexion.php');
    exit();
}

$client_id = $_SESSION['client_id'];

// 1. Récupérer les infos du client
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$client_id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Récupération de l'historique des commandes (par ID ou Téléphone)
$telephone_client = $client['telephone'] ?? '';
$id_client = $client['id'];

try {
    // On cherche dans les bonnes colonnes : id_utilisateur ou telephone_client
    $stmtCmd = $pdo->prepare("SELECT * FROM commandes WHERE id_utilisateur = ? OR telephone_client = ? ORDER BY id DESC");
    $stmtCmd->execute([$id_client, $telephone_client]);
    $commandes = $stmtCmd->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $commandes = []; 
}

// 3. Récupérer ses produits favoris (Wishlist)
try {
    $session_id = session_id();
    $stmtWish = $pdo->prepare("
        SELECT p.* FROM produits p 
        JOIN wishlist w ON p.id = w.produit_id 
        WHERE w.session_id = ? AND p.deleted = 0 
        ORDER BY w.created_at DESC
    ");
    $stmtWish->execute([$session_id]);
    $favoris = $stmtWish->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $favoris = [];
}

include 'includes/header.php';
?>

<main style="padding: 60px 20px; background: #f9f9f9; min-height: 70vh; font-family: 'Poppins', sans-serif;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; flex-wrap: wrap; gap: 20px;">
            <div>
                <h1 style="font-family: 'Playfair Display', serif; margin: 0; color: #1a1a1a;">
                    <?= $lang['acc_greeting'] ?? 'Bonjour' ?>, <?= htmlspecialchars($client['nom']) ?> ✨
                </h1>
                <p style="color: #666; margin-top: 5px;"><?= $lang['acc_welcome'] ?? 'Bienvenue dans votre espace personnel' ?></p>
            </div>
            <a href="deconnexion_client.php" style="background: white; color: #e74c3c; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; border: 1px solid #e74c3c; transition: 0.3s; box-shadow: 0 4px 10px rgba(231,76,60,0.1);" onmouseover="this.style.background='#fee'" onmouseout="this.style.background='white'">
                <i class="fas fa-sign-out-alt"></i> <?= $lang['acc_logout'] ?? 'Déconnexion' ?>
            </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px; margin-bottom: 30px;">
            
            <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); border-top: 4px solid #d4af37;">
                <h3 style="margin-top: 0; color: #d4af37; margin-bottom: 25px; font-family: 'Playfair Display', serif; font-size: 1.4rem;">
                    <i class="fas fa-user-circle"></i> <?= $lang['acc_info_title'] ?? 'Mes Informations' ?>
                </h3>
                
                <div style="display: flex; flex-direction: column; gap: 15px; color: #444;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                        <span style="color: #888;"><?= $lang['acc_name'] ?? 'Nom :' ?></span>
                        <strong><?= htmlspecialchars($client['nom']) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                        <span style="color: #888;"><?= $lang['acc_email'] ?? 'Email :' ?></span>
                        <strong><?= htmlspecialchars($client['email']) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                        <span style="color: #888;"><?= $lang['acc_phone'] ?? 'WhatsApp :' ?></span>
                        <strong><?= htmlspecialchars($client['telephone'] ?? ($lang['acc_not_provided'] ?? 'Non renseigné')) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #888;"><?= $lang['acc_member_since'] ?? 'Membre depuis :' ?></span>
                        <strong><?= date('d/m/Y', strtotime($client['created_at'])) ?></strong>
                    </div>
                </div>
            </div>

            <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); border-top: 4px solid #1a1a1a; grid-column: auto / span 2;">
                <h3 style="margin-top: 0; color: #1a1a1a; margin-bottom: 25px; font-family: 'Playfair Display', serif; font-size: 1.4rem;">
                    <i class="fas fa-shopping-bag" style="color: #d4af37;"></i> <?= $lang['acc_orders_title'] ?? 'Historique de mes Commandes' ?>
                </h3>
                
                <?php if (count($commandes) > 0): ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="background: #f9f9f9;">
                                    <th style="padding: 15px; border-bottom: 2px solid #ddd; color: #666;"><?= $lang['acc_order_num'] ?? 'N° Commande' ?></th>
                                    <th style="padding: 15px; border-bottom: 2px solid #ddd; color: #666;"><?= $lang['acc_date'] ?? 'Date' ?></th>
                                    <th style="padding: 15px; border-bottom: 2px solid #ddd; color: #666;"><?= $lang['acc_amount'] ?? 'Montant Total' ?></th>
                                    <th style="padding: 15px; border-bottom: 2px solid #ddd; color: #666;"><?= $lang['acc_status'] ?? 'Statut' ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($commandes as $cmd): ?>
                                    <tr style="transition: background 0.3s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                                        <td style="padding: 15px; border-bottom: 1px solid #eee; font-weight: bold;">#CMD-<?= str_pad($cmd['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td style="padding: 15px; border-bottom: 1px solid #eee;"><?= date('d/m/Y à H:i', strtotime($cmd['created_at'])) ?></td>
                                        <td style="padding: 15px; border-bottom: 1px solid #eee; font-weight: bold; color: #d4af37;"><?= number_format($cmd['total'] ?? 0, 0, ',', ' ') ?> FCFA</td>
                                        <td style="padding: 15px; border-bottom: 1px solid #eee;">
                                            <span style="background: #e6ffed; color: #28a745; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; border: 1px solid #c3e6cb;">
                                                <i class="fas fa-check-circle"></i> <?= $lang['acc_status_transmitted'] ?? 'Transmise' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 30px; color: #888; background: #fafafa; border-radius: 10px;">
                        <i class="fas fa-box-open" style="font-size: 3rem; color: #ddd; margin-bottom: 15px;"></i>
                        <p style="margin-bottom: 15px;"><?= $lang['acc_no_orders'] ?? 'Vous n\'avez pas encore passé de commande.' ?></p>
                        <a href="catalogue.php" style="background: #1a1a1a; color: #d4af37; padding: 12px 25px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; transition: 0.3s;" onmouseover="this.style.background='#d4af37'; this.style.color='white';" onmouseout="this.style.background='#1a1a1a'; this.style.color='#d4af37';"><?= $lang['acc_discover_col'] ?? 'Découvrir nos collections' ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); border-top: 4px solid #ff4d4d;">
            <h3 style="margin-top: 0; color: #1a1a1a; margin-bottom: 25px; font-family: 'Playfair Display', serif; font-size: 1.4rem;">
                <i class="fas fa-heart" style="color: #ff4d4d;"></i> <?= $lang['acc_fav_title'] ?? 'Mes Bijoux Favoris' ?>
            </h3>

            <?php if (count($favoris) > 0): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
                    <?php foreach($favoris as $fav): ?>
                        <div style="border: 1px solid #eee; border-radius: 10px; overflow: hidden; transition: transform 0.3s, box-shadow 0.3s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 20px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                            <a href="produit.php?id=<?= $fav['id'] ?>" style="display: block; text-decoration: none; color: inherit;">
                                <div style="height: 200px; overflow: hidden; background: #f9f9f9; position: relative;">
                                    <img src="assets/img/produits/<?= htmlspecialchars($fav['image']) ?>" alt="<?= htmlspecialchars($fav['nom']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <div style="position: absolute; top: 10px; right: 10px; background: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                        <i class="fas fa-heart" style="color: #ff4d4d;"></i>
                                    </div>
                                </div>
                                <div style="padding: 15px; text-align: center;">
                                    <h4 style="margin: 0 0 10px 0; font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($fav['nom']) ?></h4>
                                    <p style="margin: 0; color: #d4af37; font-weight: bold;"><?= number_format($fav['prix'], 0, ',', ' ') ?> FCFA</p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 30px; color: #888; background: #fafafa; border-radius: 10px;">
                    <i class="far fa-heart" style="font-size: 3rem; color: #ddd; margin-bottom: 15px;"></i>
                    <p><?= $lang['acc_no_fav'] ?? 'Vous n\'avez pas encore ajouté de bijoux à vos favoris.' ?></p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php include 'includes/footer.php'; ?>