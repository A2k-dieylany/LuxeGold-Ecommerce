<?php
// 1. Sécurité et Connexion
session_start();
require_once 'check_auth.php'; 
require_once '../config/db.php';

$admin_nom = $_SESSION['admin_name'] ?? 'Administrateur';

// =========================================================
// 2. MOTEUR FINANCIER (CALCUL DU BÉNÉFICE)
// =========================================================
try {
    $taux_dalasi_vers_fcfa = 1000 / 115; 

    // A. CHIFFRE D'AFFAIRES ENCAISSÉ
    $stmtCA = $pdo->query("SELECT devise, SUM(total) as total FROM commandes WHERE LOWER(statut) IN ('livrée', 'livree') GROUP BY devise")->fetchAll(PDO::FETCH_ASSOC);
    
    $ca_fcfa = 0; $ca_gmd = 0;
    foreach ($stmtCA as $row) {
        if (strtoupper($row['devise']) === 'GMD') $ca_gmd += $row['total'];
        else $ca_fcfa += $row['total'];
    }
    $ca_global_encaisse = $ca_fcfa + ($ca_gmd * $taux_dalasi_vers_fcfa);

    // B. COÛT DE REVIENT (Combien ont coûté les bijoux vendus)
    // On part du principe que le prix d'achat fournisseur est toujours en FCFA
    $stmtCout = $pdo->query("
        SELECT SUM(d.quantite * COALESCE(p.prix_achat, 0)) as cout_total
        FROM commandes c
        JOIN details_commande d ON c.id = d.id_commande
        JOIN produits p ON d.id_produit = p.id
        WHERE LOWER(c.statut) IN ('livrée', 'livree')
    ");
    $cout_global = $stmtCout->fetchColumn() ?: 0;

    // C. BÉNÉFICE NET ET MARGE
    $benefice_net = $ca_global_encaisse - $cout_global;
    $marge_pourcentage = $ca_global_encaisse > 0 ? ($benefice_net / $ca_global_encaisse) * 100 : 0;

    // D. PRÉVISIONNEL ET PANIER MOYEN
    $stmtAttente = $pdo->query("SELECT devise, SUM(total) as total FROM commandes WHERE LOWER(statut) NOT IN ('livrée', 'livree', 'annulée', 'annulee') GROUP BY devise")->fetchAll(PDO::FETCH_ASSOC);
    $ca_attente_fcfa = 0; $ca_attente_gmd = 0;
    foreach ($stmtAttente as $row) {
        if (strtoupper($row['devise']) === 'GMD') $ca_attente_gmd += $row['total'];
        else $ca_attente_fcfa += $row['total'];
    }
    $ca_global_attente = $ca_attente_fcfa + ($ca_attente_gmd * $taux_dalasi_vers_fcfa);
    
    $nb_livrees = $pdo->query("SELECT COUNT(id) FROM commandes WHERE LOWER(statut) IN ('livrée', 'livree')")->fetchColumn() ?: 0;
    $panier_moyen = $nb_livrees > 0 ? ($ca_global_encaisse / $nb_livrees) : 0;

    // E. DONNÉES DU GRAPHIQUE (7 derniers jours : CA vs Bénéfice)
    $last_7_days = [];
    $labels_graphique = [];
    $data_ca_graph = [];
    $data_benefice_graph = [];
    
    // Initialisation des 7 jours
    for ($i = 6; $i >= 0; $i--) {
        $date_complete = date('Y-m-d', strtotime("-$i days"));
        $last_7_days[$date_complete] = ['ca' => 0, 'cout' => 0];
        $labels_graphique[] = date('d/m', strtotime("-$i days"));
    }

    // Récupération CA par jour
    $stmtGraphCA = $pdo->query("
        SELECT DATE(created_at) as date_jour, devise, SUM(total) as total_jour 
        FROM commandes 
        WHERE LOWER(statut) IN ('livrée', 'livree') AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at), devise
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($stmtGraphCA as $row) {
        if (isset($last_7_days[$row['date_jour']])) {
            $montant = $row['total_jour'];
            if (strtoupper($row['devise']) === 'GMD') $montant = $montant * $taux_dalasi_vers_fcfa;
            $last_7_days[$row['date_jour']]['ca'] += $montant;
        }
    }

    // Récupération Coût par jour
    $stmtGraphCout = $pdo->query("
        SELECT DATE(c.created_at) as date_jour, SUM(d.quantite * COALESCE(p.prix_achat, 0)) as cout_jour
        FROM commandes c
        JOIN details_commande d ON c.id = d.id_commande
        JOIN produits p ON d.id_produit = p.id
        WHERE LOWER(c.statut) IN ('livrée', 'livree') AND c.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(c.created_at)
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($stmtGraphCout as $row) {
        if (isset($last_7_days[$row['date_jour']])) {
            $last_7_days[$row['date_jour']]['cout'] += $row['cout_jour'];
        }
    }

    // Préparation des tableaux JS
    foreach ($last_7_days as $data) {
        $data_ca_graph[] = round($data['ca']);
        $data_benefice_graph[] = round($data['ca'] - $data['cout']);
    }

    // =========================================================
    // --- NOUVEAU : F. VENTES DÉTAILLÉES PAR CATÉGORIE ---
    // =========================================================
    $stmtVentesCat = $pdo->query("
        SELECT c.nom AS categorie, 
               SUM(d.quantite) as qte_vendue, 
               SUM(d.quantite * d.prix_unitaire * CASE WHEN UPPER(cmd.devise) = 'GMD' THEN $taux_dalasi_vers_fcfa ELSE 1 END) as ca_genere
        FROM commandes cmd
        JOIN details_commande d ON cmd.id = d.id_commande
        JOIN produits p ON d.id_produit = p.id
        LEFT JOIN categories c ON p.id_categorie = c.id
        WHERE LOWER(cmd.statut) IN ('livrée', 'livree')
        GROUP BY c.id
        ORDER BY ca_genere DESC
    ");
    $ventes_categories = $stmtVentesCat->fetchAll(PDO::FETCH_ASSOC);

    // =========================================================
    // --- NOUVEAU : G. TOP 5 MEILLEURS CLIENTS DU MOIS ---
    // =========================================================
    $stmtTopClients = $pdo->query("
        SELECT nom_client, 
               COUNT(id) as nb_commandes, 
               SUM(total * CASE WHEN UPPER(devise) = 'GMD' THEN $taux_dalasi_vers_fcfa ELSE 1 END) as total_depense
        FROM commandes 
        WHERE LOWER(statut) IN ('livrée', 'livree') 
          AND MONTH(created_at) = MONTH(CURRENT_DATE())
          AND YEAR(created_at) = YEAR(CURRENT_DATE())
        GROUP BY nom_client
        ORDER BY total_depense DESC
        LIMIT 5
    ");
    $top_clients = $stmtTopClients->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erreur Module Financier : " . $e->getMessage());
    die("Erreur de calcul financier.");
}

include 'includes/admin_header.php'; 
?>

<div class="container py-5" style="background: #f4f7f6; min-height: 100vh; padding: 30px; font-family: 'Poppins', sans-serif;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 style="margin: 0; font-family: 'Playfair Display', serif; color: #1a1a1a;">📊 Bilan de Rentabilité</h2>
            <p style="color: #888; margin: 5px 0 0;">Analyse des marges et revenus LuxeGold</p>
        </div>
        
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button onclick="window.print()" class="btn-action" style="background: white; border: 1px solid #ddd; color: #333; padding: 10px 15px; border-radius: 8px; cursor: pointer; font-weight: 500; transition: 0.3s;" onmouseover="this.style.borderColor='#e74c3c'" onmouseout="this.style.borderColor='#ddd'">
                <i class="fas fa-file-pdf" style="color: #e74c3c;"></i> Exporter PDF
            </button>
            <button onclick="alert('Module d\'export Excel en cours de configuration. Les données seront exportées au format .xlsx');" class="btn-action" style="background: white; border: 1px solid #ddd; color: #333; padding: 10px 15px; border-radius: 8px; cursor: pointer; font-weight: 500; transition: 0.3s;" onmouseover="this.style.borderColor='#27ae60'" onmouseout="this.style.borderColor='#ddd'">
                <i class="fas fa-file-excel" style="color: #27ae60;"></i> Exporter Excel
            </button>
            <a href="dashboard.php" class="btn" style="background: #1a1a1a; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; transition: 0.3s;">
                <i class="fas fa-arrow-left"></i> Retour Opérationnel
            </a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 40px;">
        
        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border-bottom: 4px solid #1a1a1a;">
            <div style="color: #888; font-size: 0.8rem; text-transform: uppercase; font-weight: 600; margin-bottom: 10px;">Chiffre d'Affaires Brut</div>
            <div style="font-size: 1.8rem; font-weight: 700; color: #1a1a1a; margin-bottom: 5px;">
                <?= number_format($ca_global_encaisse, 0, ',', ' ') ?> <span style="font-size: 1rem; color: #888;">FCFA</span>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #27ae60, #2ecc71); padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(39, 174, 96, 0.2); color: white;">
            <div style="font-size: 0.8rem; text-transform: uppercase; font-weight: 600; margin-bottom: 10px; display: flex; justify-content: space-between;">
                Bénéfice Net <i class="fas fa-chart-line" style="font-size: 1.2rem;"></i>
            </div>
            <div style="font-size: 1.8rem; font-weight: 700; margin-bottom: 5px;">
                <?= number_format($benefice_net, 0, ',', ' ') ?> <span style="font-size: 1rem; opacity: 0.8;">FCFA</span>
            </div>
            <div style="font-size: 0.85rem; background: rgba(0,0,0,0.1); display: inline-block; padding: 3px 10px; border-radius: 20px;">
                Marge : <strong><?= number_format($marge_pourcentage, 1, ',', '') ?> %</strong>
            </div>
        </div>

        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border-bottom: 4px solid #d4af37;">
            <div style="color: #888; font-size: 0.8rem; text-transform: uppercase; font-weight: 600; margin-bottom: 10px;">Panier Moyen</div>
            <div style="font-size: 1.8rem; font-weight: 700; color: #1a1a1a; margin-bottom: 5px;">
                <?= number_format($panier_moyen, 0, ',', ' ') ?> <span style="font-size: 1rem; color: #888;">FCFA</span>
            </div>
        </div>

        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border-bottom: 4px solid #f39c12;">
            <div style="color: #888; font-size: 0.8rem; text-transform: uppercase; font-weight: 600; margin-bottom: 10px;">Prévisionnel (Attente)</div>
            <div style="font-size: 1.8rem; font-weight: 700; color: #1a1a1a; margin-bottom: 5px;">
                <?= number_format($ca_global_attente, 0, ',', ' ') ?> <span style="font-size: 1rem; color: #888;">FCFA</span>
            </div>
        </div>

    </div>

    <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 40px;">
        <h3 style="margin: 0 0 20px 0; font-size: 1.2rem; color: #1a1a1a;">
            <i class="fas fa-balance-scale" style="color: #d4af37; margin-right: 10px;"></i> Chiffre d'Affaires VS Bénéfice Net (7 derniers jours)
        </h3>
        <div style="height: 380px; width: 100%;">
            <canvas id="financeChart"></canvas>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px; margin-bottom: 40px;" class="responsive-grid">
        
        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
            <h3 style="margin: 0 0 20px 0; font-size: 1.1rem; color: #1a1a1a; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                <i class="fas fa-tags" style="color: #d4af37; margin-right: 10px;"></i> Ventes détaillées par catégorie
            </h3>
            
            <?php if (empty($ventes_categories)): ?>
                <p style="text-align: center; color: #888; padding: 20px;">Aucune vente validée pour le moment.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="padding: 12px 15px; color: #666; font-size: 0.85rem; text-transform: uppercase;">Catégorie</th>
                                <th style="padding: 12px 15px; color: #666; font-size: 0.85rem; text-transform: uppercase;">Qté Vendue</th>
                                <th style="padding: 12px 15px; color: #666; font-size: 0.85rem; text-transform: uppercase;">C.A. Généré</th>
                                <th style="padding: 12px 15px; color: #666; font-size: 0.85rem; text-transform: uppercase;">Part du CA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($ventes_categories as $vc): 
                                $part_ca = $ca_global_encaisse > 0 ? ($vc['ca_genere'] / $ca_global_encaisse) * 100 : 0;
                            ?>
                                <tr style="border-bottom: 1px solid #eee; transition: 0.3s;" onmouseover="this.style.background='#fcfcfc'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 15px; font-weight: 600; color: #333;"><?= htmlspecialchars($vc['categorie'] ?? 'Sans Catégorie') ?></td>
                                    <td style="padding: 15px; color: #666;"><?= $vc['qte_vendue'] ?> pièce(s)</td>
                                    <td style="padding: 15px; font-weight: bold; color: #1a1a1a;"><?= number_format($vc['ca_genere'], 0, ',', ' ') ?> F</td>
                                    <td style="padding: 15px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span style="font-size: 0.85rem; color: #888; width: 40px;"><?= number_format($part_ca, 1, ',', '') ?>%</span>
                                            <div style="flex: 1; height: 6px; background: #eee; border-radius: 3px; overflow: hidden;">
                                                <div style="width: <?= $part_ca ?>%; height: 100%; background: #d4af37;"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
            <h3 style="margin: 0 0 20px 0; font-size: 1.1rem; color: #1a1a1a; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                <i class="fas fa-crown" style="color: #f39c12; margin-right: 10px;"></i> Top 5 Clients du Mois
            </h3>
            
            <?php if (empty($top_clients)): ?>
                <p style="text-align: center; color: #888; padding: 20px;">Aucune donnée client pour ce mois-ci.</p>
            <?php else: ?>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 15px;">
                    <?php foreach($top_clients as $index => $client): ?>
                        <li style="display: flex; align-items: center; gap: 15px; padding: 12px; background: #f8f9fa; border-radius: 10px; border-left: 3px solid <?= $index == 0 ? '#f39c12' : ($index == 1 ? '#bdc3c7' : '#d35400') ?>;">
                            <div style="width: 40px; height: 40px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: <?= $index == 0 ? '#f39c12' : '#333' ?>; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                                #<?= $index + 1 ?>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: #333; font-size: 0.95rem;"><?= htmlspecialchars($client['nom_client']) ?></div>
                                <div style="font-size: 0.75rem; color: #888;"><i class="fas fa-shopping-bag"></i> <?= $client['nb_commandes'] ?> commande(s)</div>
                            </div>
                            <div style="font-weight: 700; color: #27ae60; font-size: 1rem;">
                                <?= number_format($client['total_depense'], 0, ',', ' ') ?> F
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

    </div>

</div>

<style>
    @media (max-width: 992px) {
        .responsive-grid {
            grid-template-columns: 1fr !important;
        }
    }
    
    /* Style pour cacher les éléments inutiles lors de l'impression PDF */
    @media print {
        .admin-navbar, .breadcrumb, .btn-action, .btn {
            display: none !important;
        }
        body, .container {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        div {
            box-shadow: none !important;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('financeChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels_graphique) ?>,
            datasets: [
                {
                    label: 'Chiffre d\'Affaires Brut',
                    data: <?= json_encode($data_ca_graph) ?>,
                    borderColor: '#1a1a1a', // Noir
                    backgroundColor: 'rgba(26, 26, 26, 0.05)',
                    borderWidth: 2,
                    pointBackgroundColor: '#1a1a1a',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Bénéfice Net',
                    data: <?= json_encode($data_benefice_graph) ?>,
                    borderColor: '#27ae60', // Vert émeraude
                    backgroundColor: 'rgba(39, 174, 96, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#27ae60',
                    pointRadius: 5,
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index', // Affiche les deux valeurs en même temps au survol
                intersect: false,
            },
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    backgroundColor: '#1a1a1a',
                    titleFont: { size: 14 },
                    bodyFont: { size: 14 },
                    padding: 15,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ' : ' + context.parsed.y.toLocaleString('fr-FR') + ' FCFA';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f0f0f0' },
                    ticks: { callback: function(value) { return value.toLocaleString('fr-FR') + ' F'; } }
                }
            }
        }
    });
</script>

<?php include 'includes/admin_footer.php'; ?>