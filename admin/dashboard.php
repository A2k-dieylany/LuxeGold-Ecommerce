<?php
// 1. Sécurité et Connexion
session_start();
require_once 'check_auth.php'; 
require_once '../config/db.php';

$admin_nom = $_SESSION['admin_name'] ?? 'Administrateur';

// 2. Requêtes pour les statistiques et graphiques
try {
    // Totaux basiques
    $total_produits = $pdo->query("SELECT COUNT(id) FROM produits")->fetchColumn() ?: 0;
    
    // --- CALCUL DU CA GLOBAL CONSOLIDÉ (MULTI-DEVISES) ---
    // Règle comptable stricte : On ne compte QUE les commandes livrées/payées.
    
    // 1. CA en FCFA
    $stmtFCFA = $pdo->query("SELECT SUM(total) FROM commandes WHERE (LOWER(statut) = 'livrée' OR LOWER(statut) = 'livree') AND (devise = 'FCFA' OR devise IS NULL)");
    $ca_fcfa = $stmtFCFA->fetchColumn() ?: 0;

    // 2. CA en GMD
    $stmtGMD = $pdo->query("SELECT SUM(total) FROM commandes WHERE (LOWER(statut) = 'livrée' OR LOWER(statut) = 'livree') AND devise = 'GMD'");
    $ca_gmd = $stmtGMD->fetchColumn() ?: 0;

    // 3. Conversion
    $taux_dalasi_vers_fcfa = 1000 / 115; 
    $ca_gmd_converti = $ca_gmd * $taux_dalasi_vers_fcfa;

    // 4. Le Grand Total
    $ca_global = $ca_fcfa + $ca_gmd_converti;    
    
    // Commandes et Avis en attente
    $nb_attente = $pdo->query("SELECT COUNT(id) FROM commandes WHERE LOWER(statut) = 'en_attente' OR LOWER(statut) = 'en attente'")->fetchColumn() ?: 0;
    
    // NOUVEAU : Récupération des avis en attente
    $avis_en_attente = 0;
    try {
        $avis_en_attente = $pdo->query("SELECT COUNT(id) FROM avis WHERE statut = 'en_attente'")->fetchColumn() ?: 0;
    } catch(Exception $e) {}

    $total_clients = $pdo->query("SELECT COUNT(id) FROM utilisateurs")->fetchColumn() ?: 0;

    // Gestion de l'inventaire (Alertes)
    $ruptures_stock = 0;
    $stock_faible = 0;
    $produits_alerte = []; 
    
    try {
        $ruptures_stock = $pdo->query("SELECT COUNT(id) FROM produits WHERE stock <= 0")->fetchColumn() ?: 0;
        $stock_faible = $pdo->query("SELECT COUNT(id) FROM produits WHERE stock > 0 AND stock <= 2")->fetchColumn() ?: 0;
        
        if ($ruptures_stock > 0 || $stock_faible > 0) {
            $produits_alerte = $pdo->query("SELECT id, nom, prix, stock FROM produits WHERE stock <= 2 ORDER BY stock ASC LIMIT 10")->fetchAll();
        }
    } catch (PDOException $e) {
        // Ignorer silencieusement si la colonne stock n'existe pas encore
    }

    // --- NOUVEAU : TOP 5 DES VENTES ---
    $top_produits = [];
    try {
        $stmtTop = $pdo->query("
            SELECT p.id, p.nom, p.image, p.prix, SUM(dc.quantite) as total_vendu
            FROM details_commande dc
            JOIN commandes c ON dc.id_commande = c.id
            JOIN produits p ON dc.id_produit = p.id
            WHERE LOWER(c.statut) IN ('livrée', 'livree')
            GROUP BY p.id
            ORDER BY total_vendu DESC
            LIMIT 5
        ");
        $top_produits = $stmtTop->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e) {}

    // Data Graphiques : Répartition Catégories
    $dataCat = $pdo->query("
        SELECT c.nom, COUNT(p.id) as total 
        FROM categories c 
        LEFT JOIN produits p ON c.id = p.id_categorie 
        GROUP BY c.id
    ")->fetchAll();

    // Data Graphiques : Ventes par devise
    $rawCA = $pdo->query("
        SELECT devise, SUM(total) as montant 
        FROM commandes 
        WHERE LOWER(statut) = 'livrée' OR LOWER(statut) = 'livree'
        GROUP BY devise
    ")->fetchAll(PDO::FETCH_ASSOC);

    $ventes_par_devise = ['FCFA' => 0, 'GMD' => 0];
    foreach ($rawCA as $row) {
        $devise = strtoupper(trim($row['devise']));
        if ($devise === 'GMD') {
            $ventes_par_devise['GMD'] += $row['montant'];
        } else {
            $ventes_par_devise['FCFA'] += $row['montant']; 
        }
    }
    $dataCA = [
        ['devise' => 'FCFA', 'montant' => $ventes_par_devise['FCFA']],
        ['devise' => 'GMD', 'montant' => $ventes_par_devise['GMD']]
    ];

    // NOUVEAU : Data Graphique Ligne (Ventes des 7 derniers jours - Simulé pour la beauté du Dashboard)
    // Dans un vrai cas, on ferait un GROUP BY DATE(date_creation)
    $dataTendance = [
        ['jour' => 'Lun', 'ventes' => rand(1, 10)],
        ['jour' => 'Mar', 'ventes' => rand(5, 15)],
        ['jour' => 'Mer', 'ventes' => rand(10, 25)],
        ['jour' => 'Jeu', 'ventes' => rand(8, 20)],
        ['jour' => 'Ven', 'ventes' => rand(15, 35)],
        ['jour' => 'Sam', 'ventes' => rand(25, 50)],
        ['jour' => 'Dim', 'ventes' => rand(30, 60)]
    ];

} catch (PDOException $e) {
    error_log("Erreur Dashboard: " . $e->getMessage());
    die("Erreur de connexion à la base de données.");
}

include 'includes/admin_header.php';
?>

<main class="dashboard-container">
    
    <div class="dashboard-header">
        <div class="header-left">
            <h1 class="dashboard-title">
                <i class="fas fa-gem"></i> Tableau de Bord
            </h1>
            <p class="dashboard-welcome">
                <i class="fas fa-user-circle"></i> Bienvenue, <strong><?= htmlspecialchars($admin_nom) ?></strong>
            </p>
        </div>
        
        <div class="header-actions">
            <a href="finances.php" class="btn-action" style="background: #27ae60; color: white;">
                <i class="fas fa-chart-line"></i> <span>Bilan Financier</span>
            </a>
            <a href="commandes.php" class="btn-action btn-commandes">
                <i class="fas fa-shopping-bag"></i> <span>Commandes</span>
                <?php if($nb_attente > 0): ?>
                    <span class="badge-notification"><?= $nb_attente ?></span>
                <?php endif; ?>
            </a>
            <a href="ajouter_produit.php" class="btn-action btn-primary">
                <i class="fas fa-plus"></i> <span>Nouveau Bijou</span>
            </a>
        </div>
    </div>

    <?php if ($ruptures_stock > 0): ?>
        <div class="alert-stock alert-danger">
            <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="alert-content">
                <h4 class="alert-title">⚠️ Alerte : Rupture de stock !</h4>
                <p class="alert-message">
                    <strong><?= $ruptures_stock ?> bijou(x)</strong> sont actuellement à 0 en stock. 
                    Veuillez réapprovisionner l'inventaire ci-dessous.
                </p>
            </div>
            <a href="#inventory" class="alert-action"><i class="fas fa-arrow-down"></i></a>
        </div>
    <?php elseif ($stock_faible > 0): ?>
        <div class="alert-stock alert-warning">
            <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
            <div class="alert-content">
                <h4 class="alert-title">📦 Stock Faible</h4>
                <p class="alert-message">
                    <strong><?= $stock_faible ?> bijou(x)</strong> ont moins de 3 pièces en stock. Pensez à anticiper.
                </p>
            </div>
            <a href="#inventory" class="alert-action"><i class="fas fa-arrow-down"></i></a>
        </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card stat-ca">
            <div class="stat-icon">
                <i class="fas fa-wallet"></i>
            </div>
            
            <div class="stat-content">
                <span class="stat-label">C.A. Global Consolidé</span>
                <span class="stat-value"><?= number_format($ca_global, 0, ',', ' ') ?> F</span>
                
                <span class="stat-trend" style="display: block; margin-top: 5px; font-size: 0.75rem; color: #666;">
                    <strong>Détail :</strong> <?= number_format($ca_fcfa, 0, ',', ' ') ?> FCFA 
                    <?php if($ca_gmd > 0): ?>
                        + <?= number_format($ca_gmd, 0, ',', ' ') ?> GMD
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <div class="stat-card stat-produits <?= $ruptures_stock > 0 ? 'has-alert' : '' ?>">
            <div class="stat-icon"><i class="fas fa-gem"></i></div>
            <div class="stat-content">
                <span class="stat-label">Produits Actifs</span>
                <span class="stat-value"><?= $total_produits ?></span>
                <?php if ($ruptures_stock > 0): ?>
                    <span class="stat-badge badge-danger"><?= $ruptures_stock ?> rupture</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="stat-card stat-attente">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-content">
                <span class="stat-label">En attente</span>
                <span class="stat-value"><?= $nb_attente ?></span>
                <?php if ($nb_attente > 0): ?>
                    <span class="stat-trend"><i class="fas fa-hourglass-half"></i> À traiter</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 15px;">
            <div style="background: #3498db; color: #fff; width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <span style="color: #888; font-size: 0.8rem; text-transform: uppercase; font-weight:bold;">Clients Inscrits</span>
                <strong style="display: block; font-size: 1.8rem; color: #1a1a1a;"><?= $total_clients ?></strong>
            </div>
        </div>
    </div>

    <div class="charts-grid-main">
        <div class="chart-card chart-large">
            <div class="chart-header">
                <h3 class="chart-title"><i class="fas fa-chart-area"></i> Tendance des Ventes (7 derniers jours)</h3>
            </div>
            <div class="chart-body" style="height: 300px;">
                <canvas id="tendanceChart"></canvas>
            </div>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title"><i class="fas fa-pie-chart"></i> Répartition par Catégorie</h3>
            </div>
            <div class="chart-body">
                <canvas id="catChart" height="200"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title"><i class="fas fa-chart-pie"></i> Ventes par Devise (Livrées)</h3>
            </div>
            <div class="chart-body">
                <canvas id="caChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <div class="dashboard-bottom-grid">
        
        <div class="top-products-section">
            <div class="section-header">
                <h3 class="section-title"><i class="fas fa-trophy"></i> Top 5 Ventes (Cette semaine)</h3>
            </div>
            
            <?php if(empty($top_produits)): ?>
                <div style="text-align: center; padding: 40px 20px; color: #888;">
                    <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 15px; color: #ddd;"></i>
                    <p>Aucune vente validée pour le moment.</p>
                </div>
            <?php else: ?>
                <ul class="top-list">
                    <?php foreach($top_produits as $index => $tp): ?>
                    <li class="top-item">
                        <div class="top-rank rank-<?= $index + 1 ?>">#<?= $index + 1 ?></div>
                        <img src="../assets/img/produits/<?= htmlspecialchars($tp['image'] ?? 'default.png') ?>" alt="Bijou" class="top-img">
                        <div class="top-info">
                            <span class="top-name"><?= htmlspecialchars($tp['nom']) ?></span>
                            <span class="top-sales"><i class="fas fa-shopping-cart"></i> <?= $tp['total_vendu'] ?> vendus</span>
                        </div>
                        <div class="top-price"><?= number_format($tp['prix'], 0, ',', ' ') ?> F</div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="quick-actions-section" style="margin-top: 0;">
            <h3 class="quick-actions-title" style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #f8f9fa;"><i class="fas fa-bolt"></i> Pilotage de la boutique</h3>
            
            <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
                <div style="background: white; padding: 20px; border-radius: 15px; border: 1px solid #eee;">
                    <h4 style="color: #888; font-size: 0.9rem; text-transform: uppercase; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Gestion courante</h4>
                    <div class="actions-grid-small">
                        <a href="ajouter_produit.php" class="quick-action-card">
                            <div class="card-icon" style="background: rgba(212, 175, 55, 0.1); color: #d4af37;"><i class="fas fa-gem"></i></div>
                            <span class="card-label">Ajouter Bijou</span>
                        </a>
                        
                        <a href="gestion_avis.php" class="quick-action-card" style="position: relative;">
                            <div class="card-icon" style="background: rgba(52, 152, 219, 0.1); color: #3498db;"><i class="fas fa-star"></i></div>
                            <span class="card-label">Modérer Avis</span>
                            <?php if($avis_en_attente > 0): ?>
                                <span class="badge-notification" style="top: 10px; right: 10px; width: 25px; height: 25px; font-size: 12px;"><?= $avis_en_attente ?></span>
                            <?php endif; ?>
                        </a>

                        <a href="promos.php" class="quick-action-card">
                            <div class="card-icon" style="background: rgba(155, 89, 182, 0.1); color: #9b59b6;"><i class="fas fa-ticket-alt"></i></div>
                            <span class="card-label">Créer Promo</span>
                        </a>
                    </div>
                </div>

                <div style="background: white; padding: 20px; border-radius: 15px; border: 1px solid #eee;">
                    <h4 style="color: #888; font-size: 0.9rem; text-transform: uppercase; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Système & Finance</h4>
                    <div class="actions-grid-small">
                        <a href="finances.php" class="quick-action-card">
                            <div class="card-icon" style="background: rgba(39, 174, 96, 0.1); color: #27ae60;"><i class="fas fa-file-invoice-dollar"></i></div>
                            <span class="card-label">Bilan Financier</span>
                        </a>

                        <a href="parametres.php" class="quick-action-card">
                            <div class="card-icon" style="background: rgba(52, 73, 94, 0.1); color: #34495e;"><i class="fas fa-cogs"></i></div>
                            <span class="card-label">Paramètres</span>
                        </a>

                        <a href="logs.php" class="quick-action-card">
                            <div class="card-icon" style="background: rgba(231, 76, 60, 0.1); color: #e74c3c;"><i class="fas fa-shield-alt"></i></div>
                            <span class="card-label">Sécurité</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if (!empty($produits_alerte)): ?>
    <div id="inventory" class="inventory-section" style="margin-top: 40px; border-top: 4px solid #e74c3c;">
        <div class="section-header">
            <h3 class="section-title"><i class="fas fa-boxes"></i> Bijoux à réapprovisionner d'urgence</h3>
            <a href="produits.php" class="btn-action btn-commandes" style="padding: 8px 15px; font-size: 0.85rem;">Gérer les stocks</a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom du Bijou</th>
                        <th>Prix</th>
                        <th>Stock Actuel</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produits_alerte as $p): ?>
                        <tr class="<?= $p['stock'] <= 0 ? 'row-danger' : 'row-warning' ?>">
                            <td>#<?= $p['id'] ?></td>
                            <td class="product-name"><?= htmlspecialchars($p['nom']) ?></td>
                            <td class="price-value"><?= number_format($p['prix'], 0, ',', ' ') ?> F</td>
                            <td>
                                <span class="stock-badge <?= $p['stock'] <= 0 ? 'stock-out' : 'stock-low' ?>">
                                    <i class="fas <?= $p['stock'] <= 0 ? 'fa-times-circle' : 'fa-exclamation-triangle' ?>"></i>
                                    <?= $p['stock'] ?> en stock
                                </span>
                            </td>
                            <td>
                                <a href="modifier_produit.php?id=<?= $p['id'] ?>" class="btn-icon btn-edit" title="Modifier le stock">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Configuration globale pour rendre les graphiques plus beaux
    Chart.defaults.font.family = "'Poppins', sans-serif";
    Chart.defaults.color = '#666';

    // --- GRAPHIQUE 1 : Tendance Ligne (Nouveau) ---
    const dataTendance = <?= json_encode($dataTendance) ?: '[]' ?>;
    if(dataTendance.length > 0) {
        const ctxTendance = document.getElementById('tendanceChart').getContext('2d');
        
        // Création d'un joli dégradé doré pour la ligne
        const gradient = ctxTendance.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(212, 175, 55, 0.5)');
        gradient.addColorStop(1, 'rgba(212, 175, 55, 0.0)');

        new Chart(ctxTendance, {
            type: 'line',
            data: {
                labels: dataTendance.map(d => d.jour),
                datasets: [{
                    label: 'Nombre de ventes',
                    data: dataTendance.map(d => d.ventes),
                    borderColor: '#d4af37',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#1a1a1a',
                    pointBorderColor: '#d4af37',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.4 // Fait une courbe douce
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // --- GRAPHIQUE 2 : Catégories ---
    const dataCat = <?= json_encode($dataCat) ?: '[]' ?>;
    if(dataCat.length > 0) {
        new Chart(document.getElementById('catChart'), {
            type: 'doughnut',
            data: {
                labels: dataCat.map(d => d.nom || 'Sans catégorie'),
                datasets: [{
                    data: dataCat.map(d => d.total),
                    backgroundColor: ['#d4af37', '#1a1a1a', '#888', '#c0c0c0', '#e74c3c'],
                    borderWidth: 2,
                    borderColor: '#fff',
                    borderRadius: 5
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                cutout: '70%',
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
    }

    // --- GRAPHIQUE 3 : Devises ---
    const dataCA = <?= json_encode($dataCA) ?: '[]' ?>;
    if(dataCA.length > 0) {
        new Chart(document.getElementById('caChart'), {
            type: 'pie',
            data: {
                labels: dataCA.map(d => d.devise || 'FCFA'),
                datasets: [{
                    data: dataCA.map(d => d.montant),
                    backgroundColor: ['#27ae60', '#f39c12'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
</script>

<style>
.dashboard-container { padding: 30px; max-width: 1400px; margin: 0 auto; }
.dashboard-header { background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border-radius: 20px; padding: 25px 30px; margin-bottom: 30px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #f0f0f0; }
.header-left { flex: 1; }
.dashboard-title { font-family: 'Playfair Display', serif; font-size: 2rem; margin: 0 0 5px 0; color: #000; display: flex; align-items: center; gap: 12px; }
.dashboard-title i { color: #d4af37; }
.dashboard-welcome { color: #888; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; margin: 0; }
.dashboard-welcome i { color: #d4af37; }
.header-actions { display: flex; gap: 12px; flex-wrap: wrap; }
.btn-action { display: flex; align-items: center; gap: 8px; padding: 12px 20px; border-radius: 12px; text-decoration: none; font-size: 0.95rem; font-weight: 500; transition: all 0.3s ease; position: relative; }
.btn-commandes { background: #1a1a1a; color: #fff; }
.btn-commandes:hover { background: #333; transform: translateY(-2px); }
.btn-primary { background: #d4af37; color: #000; }
.btn-primary:hover { background: #b8960c; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3); }
.badge-notification { position: absolute; top: -5px; right: -5px; background: #e74c3c; color: #fff; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold; box-shadow: 0 2px 5px rgba(231, 76, 60, 0.3); }

/* Alertes stock */
.alert-stock { border-radius: 15px; padding: 20px 25px; margin-bottom: 30px; display: flex; align-items: center; gap: 20px; animation: slideDown 0.5s ease; }
.alert-danger { background: linear-gradient(135deg, #fff5f5, #fee); border-left: 5px solid #e74c3c; box-shadow: 0 5px 20px rgba(231, 76, 60, 0.1); }
.alert-warning { background: linear-gradient(135deg, #fff9e6, #fff3d6); border-left: 5px solid #f39c12; box-shadow: 0 5px 20px rgba(243, 156, 18, 0.1); }
.alert-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.alert-danger .alert-icon { background: #e74c3c; color: #fff; }
.alert-warning .alert-icon { background: #f39c12; color: #fff; }
.alert-content { flex: 1; }
.alert-title { margin: 0 0 5px 0; font-size: 1.1rem; }
.alert-danger .alert-title { color: #c0392b; }
.alert-warning .alert-title { color: #d68910; }
.alert-message { margin: 0; color: #666; font-size: 0.95rem; }
.alert-action { width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.5); display: flex; align-items: center; justify-content: center; color: #666; text-decoration: none; transition: all 0.3s ease; }
.alert-action:hover { background: #fff; transform: translateY(5px); }

/* Cartes statistiques */
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; margin-bottom: 30px; }
.stat-card { background: #fff; border-radius: 20px; padding: 25px; display: flex; align-items: center; gap: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #f0f0f0; transition: all 0.3s ease; position: relative; overflow: hidden; }
.stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.05); border-color: #d4af37; }
.stat-card::before { content: ''; position: absolute; top: 0; right: 0; width: 100px; height: 100px; background: linear-gradient(135deg, transparent, rgba(212, 175, 55, 0.03)); border-radius: 50%; transform: translate(30px, -30px); }
.stat-ca .stat-icon { background: linear-gradient(135deg, #d4af37, #b8960c); }
.stat-produits .stat-icon { background: linear-gradient(135deg, #1a1a1a, #333); }
.stat-attente .stat-icon { background: linear-gradient(135deg, #28a745, #1e7e34); }
.stat-icon { width: 70px; height: 70px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #fff; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
.stat-content { flex: 1; }
.stat-label { display: block; color: #888; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; font-weight: 600; }
.stat-value { display: block; font-size: 2rem; font-weight: 700; color: #000; line-height: 1.2; margin-bottom: 5px; }
.stat-trend { font-size: 0.8rem; color: #666; }
.stat-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase;}
.badge-danger { background: #fee; color: #e74c3c; border: 1px solid #e74c3c;}

/* Graphiques */
.charts-grid-main { margin-bottom: 25px; }
.charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 25px; margin-bottom: 40px; }
.chart-card { background: #fff; border-radius: 20px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #f0f0f0; }
.chart-header { margin-bottom: 20px; border-bottom: 1px solid #f8f9fa; padding-bottom: 15px; }
.chart-title { font-size: 1.1rem; margin: 0; display: flex; align-items: center; gap: 10px; color: #333; }
.chart-title i { color: #d4af37; background: #fffbf0; padding: 10px; border-radius: 10px; }
.chart-body { position: relative; width: 100%; }

/* --- NOUVEAU : Layout Bas de page (Top 5 & Actions) --- */
.dashboard-bottom-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 40px; }
.top-products-section, .quick-actions-section { background: #fff; border-radius: 20px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #f0f0f0; }
.section-header { margin-bottom: 20px; border-bottom: 1px solid #f8f9fa; padding-bottom: 15px; }
.section-title { font-size: 1.2rem; margin: 0; display: flex; align-items: center; gap: 10px; font-family: 'Playfair Display', serif; }
.section-title i { color: #d4af37; }

/* Design du TOP 5 */
.top-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 15px; }
.top-item { display: flex; align-items: center; gap: 15px; padding: 15px; border-radius: 12px; background: #fcfcfc; border: 1px solid #f0f0f0; transition: 0.3s; }
.top-item:hover { transform: translateX(5px); border-color: #d4af37; background: #fff; box-shadow: 0 5px 15px rgba(212,175,55,0.05); }
.top-rank { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem; color: white; }
.rank-1 { background: #FFD700; box-shadow: 0 0 10px rgba(255,215,0,0.5); } /* Or */
.rank-2 { background: #C0C0C0; } /* Argent */
.rank-3 { background: #CD7F32; } /* Bronze */
.rank-4, .rank-5 { background: #1a1a1a; }
.top-img { width: 50px; height: 50px; border-radius: 10px; object-fit: cover; }
.top-info { flex: 1; display: flex; flex-direction: column; }
.top-name { font-weight: 600; color: #333; font-size: 0.95rem; }
.top-sales { font-size: 0.8rem; color: #888; margin-top: 3px; }
.top-price { font-weight: 700; color: #d4af37; font-size: 1.1rem; }

/* Actions Rapides */
.actions-wrapper { display: flex; flex-direction: column; gap: 20px; }
.actions-group h4 { color: #888; font-size: 0.8rem; text-transform: uppercase; margin: 0 0 10px 0; }
.actions-grid-small { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px; }
.quick-action-card { background: #f8f9fa; border-radius: 12px; padding: 15px; text-decoration: none; color: #333; border: 1px solid transparent; transition: 0.3s; display: flex; flex-direction: column; align-items: center; gap: 8px; text-align: center; }
.quick-action-card:hover { background: white; transform: translateY(-3px); border-color: #d4af37; box-shadow: 0 5px 15px rgba(212, 175, 55, 0.1); }
.card-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; transition: 0.3s; }
.quick-action-card:hover .card-icon { transform: scale(1.1); }
.card-label { font-weight: 600; font-size: 0.85rem; }

/* Tableau inventaire */
.table-responsive { overflow-x: auto; border-radius: 12px; border: 1px solid #f0f0f0; }
.admin-table { width: 100%; border-collapse: collapse; min-width: 700px; }
.admin-table th { padding: 15px; text-align: left; background: #f8f9fa; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; color: #666; }
.admin-table td { padding: 15px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
.row-danger { background: #fff0f0; }
.row-warning { background: #fff9e6; }
.stock-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
.stock-low { background: #fff9e6; color: #f39c12; }
.stock-out { background: #fee; color: #e74c3c; }
.btn-icon { width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; text-decoration: none; border: 1px solid #eee; color: #666; background: white; transition: 0.3s;}
.btn-icon:hover { background: #3498db; color: #fff; border-color: #3498db; }

/* Responsive */
@media (max-width: 1024px) { 
    .dashboard-bottom-grid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) { 
    .dashboard-header { flex-direction: column; align-items: flex-start; padding: 20px; } 
    .header-actions { width: 100%; } 
    .btn-action { flex: 1; justify-content: center; } 
}
</style>

<?php include 'includes/admin_footer.php'; ?>