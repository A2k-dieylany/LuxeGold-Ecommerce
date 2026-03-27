<?php
// 1. Sécurité et Connexion
require_once 'check_auth.php'; 
require_once '../config/db.php';

// =========================================================
// 2. ENDPOINT API (Traitement AJAX asynchrone)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action']) && $_POST['ajax_action'] === 'livrer') {
    // 🛡️ BOUCLIER ANTI-CRASH : On bloque tout affichage d'erreur HTML intempestif
    ob_start();

    // On force l'en-tête JSON pour la réponse
    header('Content-Type: application/json');
    
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    
    if ($id) {
        try {
            $stmt = $pdo->prepare("UPDATE commandes SET statut = 'Livrée' WHERE id = ?");
            $stmt->execute([$id]);

            // --- NOUVEAU : AUTOMATISATION EMAIL ---
            $stmtInfo = $pdo->prepare("SELECT c.nom_client, u.email FROM commandes c LEFT JOIN utilisateurs u ON c.id_utilisateur = u.id WHERE c.id = ?");
            $stmtInfo->execute([$id]);
            $client_info = $stmtInfo->fetch();

            if ($client_info && !empty($client_info['email'])) {
                if (file_exists('includes/mailer.php')) {
                    require_once 'includes/mailer.php';
                    if (function_exists('envoyerEmailLivraison')) {
                        // ⚠️ CORRECTION ICI : Ajout de $pdo en premier paramètre !
                        envoyerEmailLivraison($pdo, $client_info['email'], $client_info['nom_client'], $id);
                    }
                }
            }
            // --------------------------------------
            
            // On vide le buffer des potentielles erreurs cachées pour garder un JSON pur
            ob_clean(); 
            echo json_encode(['success' => true, 'message' => 'Commande mise à jour avec succès.']);
        } catch (PDOException $e) {
            error_log("Erreur AJAX Livraison: " . $e->getMessage());
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'Erreur de base de données.']);
        }
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'ID de commande invalide.']);
    }
    exit(); // IMPORTANT : On stoppe l'exécution ici pour ne pas afficher le HTML
}

// =========================================================
// 3. AFFICHAGE DE LA PAGE (Mode classique)
// =========================================================
try {
    $commandes = $pdo->query("SELECT * FROM commandes ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    error_log("Erreur Lecture Commandes: " . $e->getMessage());
    $commandes = []; 
}

include 'includes/admin_header.php'; 
?>

<div class="container py-5" style="background: #f4f4f4; min-height: 100vh; padding: 30px; font-family: 'Poppins', sans-serif;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h2 style="margin: 0; font-family: 'Playfair Display', serif;">📦 Gestion des Commandes</h2>
            <p style="color: #888; margin: 5px 0 0;">Suivi des ventes en temps réel</p>
        </div>
        <a href="dashboard.php" class="btn" style="text-decoration: none; color: #1a1a1a; border: 1px solid #1a1a1a; padding: 10px 20px; border-radius: 8px; font-weight: 600; transition: all 0.3s;">
            <i class="fas fa-arrow-left"></i> Retour au Dashboard
        </a>
    </div>

    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

    <div style="overflow-x: auto; background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #1a1a1a; color: #d4af37;">
                    <th style="padding: 18px;">ID</th>
                    <th style="padding: 18px;">Client</th>
                    <th style="padding: 18px;">Montant</th>
                    <th style="padding: 18px;">Date</th>
                    <th style="padding: 18px;">Statut</th>
                    <th style="padding: 18px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($commandes)): ?>
                    <tr>
                        <td colspan="6" style="padding: 40px; text-align: center; color: #888;">Aucune commande enregistrée.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($commandes as $c): ?>
                    <tr id="row-<?= $c['id'] ?>" style="border-bottom: 1px solid #eee; transition: background 0.3s;" onmouseover="this.style.background='#fcfaf2'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 18px; font-weight: bold;">#<?= $c['id'] ?></td>
                        
                        <td style="padding: 18px;">
                            <i class="fas fa-user-circle" style="color: #ccc; margin-right: 5px;"></i> 
                            <?= htmlspecialchars($c['nom_client'] ?? 'Client Anonyme') ?>
                        </td>
                        
                        <td style="padding: 18px;">
                            <span style="color: #d4af37; font-weight: bold;">
                                <?= number_format($c['total'], 0, ',', ' ') ?> <?= htmlspecialchars($c['devise'] ?? 'FCFA') ?>
                            </span>
                        </td>
                        
                        <td style="padding: 18px; font-size: 0.85rem; color: #666;">
                            <i class="far fa-calendar-alt" style="margin-right: 5px;"></i> 
                            <?= !empty($c['created_at']) ? date('d/m/Y H:i', strtotime($c['created_at'])) : "Non renseignée" ?>
                        </td>
                        
                        <td style="padding: 18px;" class="cell-statut">
                            <?php 
                                $statut = $c['statut'] ?? 'En attente';
                                $isLivree = (strtolower($statut) == 'livrée' || strtolower($statut) == 'livree');
                                $bg = $isLivree ? '#e6ffed' : '#fff4e6';
                                $cl = $isLivree ? '#28a745' : '#d4af37';
                            ?>
                            <span class="badge-statut" style="padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; background: <?= $bg ?>; color: <?= $cl ?>; text-transform: uppercase; letter-spacing: 1px; display: inline-block; transition: all 0.4s ease;">
                                <?= htmlspecialchars($statut) ?>
                            </span>
                        </td>
                        
                        <td style="padding: 18px; display: flex; gap: 10px; align-items: center;" class="cell-actions">
                            
                            <a href="facture.php?id=<?= $c['id'] ?>" target="_blank" style="background: #1a1a1a; color: white; text-decoration: none; padding: 7px 12px; border-radius: 6px; font-size: 11px; font-weight: bold; transition: 0.2s;" title="Voir la facture" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                                <i class="fas fa-file-invoice"></i> FACTURE
                            </a>

                            <?php 
                            $tel_wa = isset($c['telephone_client']) ? preg_replace('/[^0-9]/', '', $c['telephone_client']) : '';
                            if (!empty($tel_wa)): 
                                $msg_wa = urlencode("Bonjour " . ($c['nom_client'] ?? '') . " 💎, nous confirmons votre commande #" . $c['id'] . " d'un montant de " . number_format($c['total'], 0, ',', ' ') . " " . ($c['devise'] ?? 'FCFA') . " chez LuxeGold. Elle est en cours de traitement !");
                            ?>
                            <a href="https://wa.me/<?= $tel_wa ?>?text=<?= $msg_wa ?>" target="_blank" style="background: #25D366; color: white; text-decoration: none; padding: 7px 12px; border-radius: 6px; font-size: 11px; font-weight: bold; transition: 0.2s; display: inline-flex; align-items: center; gap: 5px;" title="Contacter sur WhatsApp" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                                <i class="fab fa-whatsapp" style="font-size: 14px;"></i> CHAT
                            </a>
                            <?php endif; ?>
                            <?php if(!$isLivree): ?>
                                <button type="button" class="btn-ajax-livrer" data-id="<?= $c['id'] ?>" style="background: #28a745; color: white; border: none; padding: 7px 12px; border-radius: 6px; font-size: 11px; font-weight: bold; cursor: pointer; transition: 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                                   <i class="fas fa-truck"></i> LIVRER
                                </button>
                            <?php else: ?>
                                <span class="badge-termine" style="color: #28a745; font-size: 0.9rem; margin-left: 5px; font-weight: 500;"><i class="fas fa-check-double"></i> Terminé</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Fonction utilitaire pour afficher des notifications "Toast" (plus pro que alert())
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.style.padding = '15px 25px';
        toast.style.marginBottom = '10px';
        toast.style.borderRadius = '8px';
        toast.style.color = 'white';
        toast.style.fontWeight = 'bold';
        toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        toast.style.transition = 'opacity 0.4s ease';
        toast.style.background = type === 'success' ? '#28a745' : '#dc3545';
        toast.innerHTML = type === 'success' ? `<i class="fas fa-check-circle"></i> ${message}` : `<i class="fas fa-exclamation-circle"></i> ${message}`;
        
        const container = document.getElementById('toast-container');
        container.appendChild(toast);
        
        // Disparition automatique après 3 secondes
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    }

    // Cibler tous les boutons "LIVRER"
    const btnLivrer = document.querySelectorAll('.btn-ajax-livrer');

    btnLivrer.forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('Êtes-vous sûr de vouloir marquer cette commande comme livrée ?')) {
                return;
            }

            const idCommande = this.getAttribute('data-id');
            const row = document.getElementById(`row-${idCommande}`);
            const btnOriginalHtml = this.innerHTML;

            // UX : Empêcher le double-clic et montrer le chargement
            this.disabled = true;
            this.style.opacity = '0.5';
            this.style.cursor = 'not-allowed';
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';

            try {
                // Préparation des données POST
                const formData = new FormData();
                formData.append('ajax_action', 'livrer');
                formData.append('id', idCommande);

                // Requête HTTP Asynchrone
                const response = await fetch('commandes.php', {
                    method: 'POST',
                    body: formData
                });

                // Vérification du code HTTP (200 OK)
                if (!response.ok) throw new Error("Erreur serveur");

                const result = await response.json();

                if (result.success) {
                    // 1. Mise à jour du badge Statut
                    const badge = row.querySelector('.badge-statut');
                    badge.textContent = 'LIVRÉE';
                    badge.style.background = '#e6ffed';
                    badge.style.color = '#28a745';

                    // 2. Remplacement du bouton par le texte "Terminé"
                    const actionCell = row.querySelector('.cell-actions');
                    this.remove(); // Supprime le bouton cliqué
                    
                    const spanTermine = document.createElement('span');
                    spanTermine.className = 'badge-termine';
                    spanTermine.style.color = '#28a745';
                    spanTermine.style.fontSize = '0.9rem';
                    spanTermine.style.marginLeft = '5px';
                    spanTermine.style.fontWeight = '500';
                    spanTermine.innerHTML = '<i class="fas fa-check-double"></i> Terminé';
                    
                    actionCell.appendChild(spanTermine);

                    // 3. Notification pro
                    showToast(`La commande #${idCommande} a été livrée avec succès !`, 'success');
                } else {
                    throw new Error(result.error || "Erreur inconnue");
                }

            } catch (error) {
                console.error('AJAX Error:', error);
                showToast(`Échec de la mise à jour : ${error.message}`, 'error');
                
                // Restauration du bouton en cas d'erreur
                this.disabled = false;
                this.style.opacity = '1';
                this.style.cursor = 'pointer';
                this.innerHTML = btnOriginalHtml;
            }
        });
    });
});
</script>

<?php include 'includes/admin_footer.php'; ?>