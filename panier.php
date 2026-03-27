<?php 
require_once 'config/db.php';
require_once 'includes/fonctions.php'; 

// --- NOUVEAU : PRÉ-REMPLISSAGE DU FORMULAIRE (UX CLIENT CONNECTÉ) ---
$client_connecte = false;
$nom_client = '';
$tel_client = '';
$email_client = '';
$adresse_client = '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['client_id'])) {
    $stmtClient = $pdo->prepare("SELECT nom, telephone, email, adresse FROM utilisateurs WHERE id = ?");
    $stmtClient->execute([$_SESSION['client_id']]);
    $dataClient = $stmtClient->fetch(PDO::FETCH_ASSOC);

    if ($dataClient) {
        $client_connecte = true;
        $nom_client = $dataClient['nom'] ?? '';
        $tel_client = $dataClient['telephone'] ?? '';
        $email_client = $dataClient['email'] ?? '';
        $adresse_client = $dataClient['adresse'] ?? '';
    }
}
// -------------------------------------------------------------------

include 'includes/header.php'; 

// --- LOGIQUE CODE PROMO ---
$msg_promo = "";
$style_class = "";

if (isset($_POST['btn_promo'])) {
    $code_saisi = strtoupper(htmlspecialchars($_POST['code_input'])); 
    
    $stmt = $pdo->prepare("SELECT * FROM codes_promo WHERE code = ?");
    $stmt->execute([$code_saisi]);
    $promo = $stmt->fetch();

    if ($promo) {
        $_SESSION['promo'] = [
            'code' => $promo['code'],
            'remise' => $promo['reduction'] 
        ];
        $text_apply = ($_SESSION['lang'] == 'en') ? "applied" : "appliqué";
        $msg_promo = "Code " . $promo['code'] . " $text_apply ! (-" . $promo['reduction'] . "%)";
        $style_class = "alert-success";
    } else {
        $msg_promo = ($_SESSION['lang'] == 'en') ? "Invalid promo code." : "Code promo invalide.";
        $style_class = "alert-error";
    }
}

if (isset($_GET['del_promo'])) {
    unset($_SESSION['promo']);
}

// --- INITIALISATION PANIER ---
$panier_vide = empty($_SESSION['panier']);
$total_general = 0;

$wa_greet = ($_SESSION['lang'] == 'en') ? "New Order" : "Nouvelle Commande";
$message_whatsapp = "✨ * $wa_greet - Jewelry Beug Fallou* ✨\n\n";
?>

<main class="cart-page">
    <div class="container">
        
        <div class="cart-header">
            <h1 class="cart-title">
                <i class="fas fa-shopping-bag"></i>
                <?= $lang['cart_title'] ?? 'Votre Panier' ?>
            </h1>
            <?php if(!$panier_vide): ?>
                <a href="catalogue.php" class="continue-shopping">
                    <i class="fas fa-arrow-left"></i>
                    <?= $lang['cart_continue'] ?? 'Continuer mes achats' ?>
                </a>
            <?php endif; ?>
        </div>

        <?php if ($panier_vide): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <h2 class="empty-cart-title"><?= $lang['cart_empty'] ?? 'Votre panier est vide' ?></h2>
                <p class="empty-cart-text">
                    <?= ($_SESSION['lang'] == 'en') 
                        ? 'Discover our exclusive jewelry collection' 
                        : 'Découvrez notre collection exclusive de bijoux' ?>
                </p>
                <a href="catalogue.php" class="btn btn-gold btn-large">
                    <i class="fas fa-gem"></i>
                    <?= $lang['cart_back_shop'] ?? 'Découvrir nos bijoux' ?>
                </a>
            </div>
        <?php else: ?>
            
            <div class="cart-table-container">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th><?= $lang['cart_col_product'] ?? 'Produit' ?></th>
                            <th><?= $lang['cart_col_price'] ?? 'Prix' ?></th>
                            <th><?= $lang['cart_col_qty'] ?? 'Quantité' ?></th>
                            <th><?= $lang['cart_col_total'] ?? 'Total' ?></th>
                            <th class="text-center"><?= ($_SESSION['lang'] == 'en') ? 'Action' : 'Action' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rowIndex = 0;
                        foreach ($_SESSION['panier'] as $id => $qte): 
                            $stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
                            $stmt->execute([$id]);
                            $p = $stmt->fetch();
                            $rowIndex++;

                            // --- CALCUL DU PRIX UNITAIRE AVEC RÉDUCTION PRODUIT ---
                            $reduction_produit = (int)($p['reduction'] ?? 0);
                            $prix_unitaire_final = $p['prix'];
                            if ($reduction_produit > 0) {
                                $prix_unitaire_final = $p['prix'] * (1 - ($reduction_produit / 100));
                            }
                            
                            $sous_total = $prix_unitaire_final * $qte;
                            $total_general += $sous_total;
                            
                            $prix_affiche_wa = formaterPrix($sous_total);
                            $qty_label = ($_SESSION['lang'] == 'en') ? "Qty" : "Qté";
                            $message_whatsapp .= "▫️ *{$p['nom']}*\n   $qty_label : {$qte} | $prix_affiche_wa\n";
                        ?>
                        <tr class="cart-item" style="animation-delay: <?= $rowIndex * 0.1 ?>s">
                            <td data-label="<?= $lang['cart_col_product'] ?? 'Produit' ?>">
                                <div class="cart-product-info">
                                    <div class="cart-product-image">
                                        <img src="assets/img/produits/<?= htmlspecialchars($p['image']) ?>" 
                                             alt="<?= htmlspecialchars($p['nom']) ?>">
                                    </div>
                                    <div class="cart-product-details">
                                        <h3 class="cart-product-name"><?= htmlspecialchars($p['nom']) ?></h3>
                                        <?php if(!empty($p['metal'])): ?>
                                            <span class="cart-product-meta"><?= ($_SESSION['lang'] == 'en') ? 'Metal' : 'Métal' ?>: <?= htmlspecialchars($p['metal']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td data-label="<?= $lang['cart_col_price'] ?? 'Prix' ?>" class="cart-price">
                                <?php if ($reduction_produit > 0): ?>
                                    <div class="price-with-discount">
                                        <span class="old-price"><?= formaterPrix($p['prix']) ?></span>
                                        <span class="new-price"><?= formaterPrix($prix_unitaire_final) ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="regular-price"><?= formaterPrix($p['prix']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td data-label="<?= $lang['cart_col_qty'] ?? 'Quantité' ?>">
                                <div class="quantity-controls">
                                    <a href="modifier_quantite.php?id=<?= $p['id'] ?>&action=moins" 
                                       class="quantity-btn" 
                                       aria-label="Diminuer la quantité">
                                        <i class="fas fa-minus"></i>
                                    </a>
                                    <span class="quantity-value"><?= $qte ?></span>
                                    <a href="modifier_quantite.php?id=<?= $p['id'] ?>&action=plus" 
                                       class="quantity-btn" 
                                       aria-label="Augmenter la quantité">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                </div>
                            </td>
                            <td data-label="<?= $lang['cart_col_total'] ?? 'Total' ?>" class="cart-subtotal">
                                <span class="subtotal-value"><?= formaterPrix($sous_total) ?></span>
                            </td>
                            <td data-label="Action" class="cart-actions">
                                <a href="supprimer_panier.php?id=<?= $p['id'] ?>" 
                                   class="cart-remove-btn"
                                   onclick="return confirm('<?= ($_SESSION['lang'] == 'en') ? 'Remove this item?' : 'Retirer cet article ?' ?>')"
                                   aria-label="Supprimer l'article">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php
                // --- CALCUL DE LA REMISE GLOBALE (CODE PROMO) ---
                $montant_remise_code = 0;
                $pourcentage_code = 0;
                if (isset($_SESSION['promo'])) {
                    $pourcentage_code = $_SESSION['promo']['remise'];
                    $montant_remise_code = $total_general * ($pourcentage_code / 100);
                }
                $total_final = $total_general - $montant_remise_code;

                // WhatsApp final
                $message_whatsapp .= "\n━━━━━━━━━━━━━━━";
                $message_whatsapp .= "\nTotal : " . formaterPrix($total_general);
                if($montant_remise_code > 0) { 
                    $message_whatsapp .= "\n🏷️ Code Promo (-$pourcentage_code%) : -" . formaterPrix($montant_remise_code); 
                }
                $message_whatsapp .= "\n💰 *NET À PAYER : " . formaterPrix($total_final) . "*";
                $message_whatsapp_encoded = urlencode($message_whatsapp);
            ?>

            <div class="cart-summary-section">
                <div class="summary-grid">
                    
                    <div class="promo-block">
                        <h3 class="block-title">
                            <i class="fas fa-tag"></i>
                            <?= $lang['cart_promo_code'] ?? 'Code Promo' ?>
                        </h3>
                        
                        <?php if(isset($_SESSION['promo'])): ?>
                            <div class="promo-applied">
                                <div class="promo-badge">
                                    <span class="promo-code"><?= $_SESSION['promo']['code'] ?></span>
                                    <span class="promo-discount">-<?= $pourcentage_code ?>%</span>
                                </div>
                                <a href="panier.php?del_promo=1" class="promo-remove" aria-label="Retirer le code promo">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        <?php else: ?>
                            <form action="panier.php" method="POST" class="promo-form">
                                <div class="promo-input-group">
                                    <input type="text" 
                                           name="code_input" 
                                           placeholder="Ex: LUXE20" 
                                           class="promo-input"
                                           maxlength="20">
                                    <button type="submit" name="btn_promo" class="promo-submit">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </form>
                            
                            <?php if($msg_promo): ?>
                                <div class="promo-message <?= $style_class ?>">
                                    <i class="fas fa-<?= $style_class == 'alert-success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                                    <?= $msg_promo ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="total-block">
                        <div class="total-calculations">
                            <div class="total-row">
                                <span><?= $lang['cart_subtotal'] ?? 'Sous-total' ?></span>
                                <span class="total-value"><?= formaterPrix($total_general) ?></span>
                            </div>
                            
                            <?php if($montant_remise_code > 0): ?>
                                <div class="total-row discount-row">
                                    <span>
                                        <i class="fas fa-tag"></i>
                                        <?= $lang['cart_promo_code'] ?? 'Code Promo' ?> (-<?= $pourcentage_code ?>%)
                                    </span>
                                    <span class="discount-value">- <?= formaterPrix($montant_remise_code) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="total-divider"></div>
                            
                            <div class="total-row final-row">
                                <span><?= $lang['cart_net_pay'] ?? 'Net à payer' ?></span>
                                <span class="final-total"><?= formaterPrix($total_final) ?></span>
                            </div>
                        </div>

                        <form method="POST" style="margin-top: 25px; border-top: 1px solid rgba(212,175,55,0.3); padding-top: 20px;">                            
                            <h4 style="margin-top: 0; margin-bottom: 15px; color: #d4af37; font-size: 1.1rem; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-truck"></i> <?= ($_SESSION['lang'] == 'en') ? 'Your Information' : 'Vos informations' ?>
                            </h4>

                            <?php if($client_connecte): ?>
                                <div style="background: rgba(40, 167, 69, 0.1); border: 1px solid rgba(40, 167, 69, 0.3); padding: 10px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; color: #a3d7a5; display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-user-check" style="font-size: 1.2rem;"></i> 
                                    <span><?= ($_SESSION['lang'] == 'en') ? 'Welcome back' : 'Ravi de vous revoir' ?> <strong><?= htmlspecialchars($nom_client) ?></strong>. <?= ($_SESSION['lang'] == 'en') ? 'Your details are pre-filled.' : 'Vos informations sont pré-remplies.' ?></span>
                                </div>
                            <?php endif; ?>

                            <div style="margin-bottom: 15px;">
                                <input type="text" name="nom" required placeholder="<?= ($_SESSION['lang'] == 'en') ? 'Full Name *' : 'Votre nom complet *' ?>" 
                                       value="<?= htmlspecialchars($nom_client) ?>"
                                       style="width: 100%; padding: 12px; border: 1px solid #444; background: rgba(255,255,255,0.05); color: white; border-radius: 8px; box-sizing: border-box; outline: none; transition: 0.3s;"
                                       onfocus="this.style.borderColor='#d4af37'" onblur="this.style.borderColor='#444'">
                            </div>
                            
                            <div style="margin-bottom: 15px;">
                                <input type="tel" name="telephone" required 
                                       placeholder="<?= ($_SESSION['lang'] == 'en') ? 'WhatsApp Number (e.g., +221 77 123 45 67) *' : 'Votre numéro WhatsApp (ex: +221 77 123 45 67) *' ?>" 
                                       value="<?= htmlspecialchars($tel_client) ?>"
                                       pattern="^\+?[0-9\s\-]{8,20}$"
                                       title="<?= ($_SESSION['lang'] == 'en') ? 'Please enter a valid phone number (8 to 20 characters)' : 'Veuillez entrer un numéro valide composé de chiffres (8 à 20 caractères)' ?>"
                                       style="width: 100%; padding: 12px; border: 1px solid #444; background: rgba(255,255,255,0.05); color: white; border-radius: 8px; box-sizing: border-box; outline: none; transition: 0.3s;"
                                       onfocus="this.style.borderColor='#d4af37'" onblur="this.style.borderColor='#444'">
                            </div>
                            
                            <div style="margin-bottom: 15px;">
                                <input type="email" name="email" placeholder="<?= ($_SESSION['lang'] == 'en') ? 'Email Address (Optional)' : 'Votre adresse Email (Optionnel)' ?>" 
                                       value="<?= htmlspecialchars($email_client) ?>"
                                       style="width: 100%; padding: 12px; border: 1px solid #444; background: rgba(255,255,255,0.05); color: white; border-radius: 8px; box-sizing: border-box; outline: none; transition: 0.3s;"
                                       onfocus="this.style.borderColor='#d4af37'" onblur="this.style.borderColor='#444'">
                            </div>
                            
                            <div style="margin-bottom: 20px;">
                                <textarea name="adresse" rows="2" placeholder="<?= ($_SESSION['lang'] == 'en') ? 'Full Delivery Address (Optional)' : 'Adresse complète de livraison (Optionnel)' ?>" 
                                          style="width: 100%; padding: 12px; border: 1px solid #444; background: rgba(255,255,255,0.05); color: white; border-radius: 8px; box-sizing: border-box; font-family: inherit; outline: none; transition: 0.3s;"
                                          onfocus="this.style.borderColor='#d4af37'" onblur="this.style.borderColor='#444'"><?= htmlspecialchars($adresse_client) ?></textarea>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 15px; margin-top: 10px;">
                                
                                <button type="submit" formaction="payer_commande.php" style="background: #1a1a1a; color: white; border: 1px solid #333; padding: 16px; width: 100%; border-radius: 8px; font-weight: bold; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; transition: 0.3s;" onmouseover="this.style.background='#333'" onmouseout="this.style.background='#1a1a1a'">
                                    <i class="fas fa-lock" style="color: #d4af37;"></i>
                                    <span><?= ($_SESSION['lang'] == 'en') ? 'Secure Online Payment' : 'Paiement Sécurisé en Ligne' ?></span>
                                </button>

                                <div style="display: flex; align-items: center; text-align: center; color: #888; font-size: 0.9rem;">
                                    <div style="flex: 1; border-bottom: 1px solid #444;"></div>
                                    <span style="padding: 0 10px; color: #ccc;">OU</span>
                                    <div style="flex: 1; border-bottom: 1px solid #444;"></div>
                                </div>

                                <button type="submit" formaction="valider_commande.php" style="background: #25D366; color: white; border: none; padding: 16px; width: 100%; border-radius: 8px; font-weight: bold; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; transition: 0.3s;" onmouseover="this.style.background='#20b858'" onmouseout="this.style.background='#25D366'">
                                    <i class="fab fa-whatsapp" style="font-size: 1.3rem;"></i>
                                    <span><?= ($_SESSION['lang'] == 'en') ? 'Order via WhatsApp' : 'Commander via WhatsApp' ?></span>
                                </button>
                                
                            </div>
                            
                        </form>
                        <div class="payment-guarantee" style="margin-top: 20px;">
                            <i class="fas fa-shield-alt"></i>
                            <span><?= ($_SESSION['lang'] == 'en') ? 'Your connection and data are protected.' : 'Vos données et transactions sont protégées.' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cart-suggestions">
                <h3 class="suggestions-title">
                    <?= ($_SESSION['lang'] == 'en') ? 'You might also like' : 'Vous aimerez aussi' ?>
                </h3>
                <a href="catalogue.php" class="suggestions-link">
                    <?= $lang['cart_continue'] ?? 'Continuer mes achats' ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
/* ============================================
   PAGE PANIER - STYLES AMÉLIORÉS
   ============================================ */

.cart-page {
    padding: 40px 0;
    background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
    min-height: 80vh;
}

/* En-tête du panier */
.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.cart-title {
    font-size: 2rem;
    color: #000;
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 0;
    font-family: 'Playfair Display', serif;
}

.cart-title i {
    color: #d4af37;
}

.continue-shopping {
    color: #888;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
    transition: color 0.3s ease;
}

.continue-shopping:hover {
    color: #d4af37;
}

/* Panier vide */
.empty-cart {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.03);
    border: 1px solid #f0f0f0;
    animation: fadeInUp 0.6s ease;
}

.empty-cart-icon {
    font-size: 5rem;
    color: #d4af37;
    margin-bottom: 30px;
    opacity: 0.5;
}

.empty-cart-title {
    font-size: 1.8rem;
    color: #333;
    margin-bottom: 15px;
    font-family: 'Playfair Display', serif;
}

.empty-cart-text {
    color: #999;
    margin-bottom: 30px;
    font-size: 1.1rem;
}

/* Tableau du panier */
.cart-table-container {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.03);
    border: 1px solid #f0f0f0;
    margin-bottom: 30px;
}

.cart-table {
    width: 100%;
    border-collapse: collapse;
}

.cart-table th {
    background: #f8f8f8;
    padding: 20px 15px;
    text-align: left;
    color: #666;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 1px;
}

.cart-table td {
    padding: 20px 15px;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}

.cart-item {
    animation: fadeInUp 0.5s ease forwards;
    opacity: 0;
}

/* Image produit */
.cart-product-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.cart-product-image {
    width: 80px;
    height: 80px;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #eee;
}

.cart-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.cart-product-image:hover img {
    transform: scale(1.1);
}

.cart-product-name {
    font-size: 1.1rem;
    margin-bottom: 5px;
    color: #333;
    font-weight: 600;
}

.cart-product-meta {
    font-size: 0.8rem;
    color: #999;
}

/* Prix */
.cart-price {
    font-weight: 600;
}

.price-with-discount {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.old-price {
    color: #999;
    font-size: 0.85rem;
    text-decoration: line-through;
}

.new-price {
    color: #e74c3c;
    font-size: 1rem;
    font-weight: 700;
}

.regular-price {
    color: #d4af37;
    font-size: 1rem;
    font-weight: 700;
}

/* Contrôles de quantité */
.quantity-controls {
    display: flex;
    align-items: center;
    gap: 8px;
}

.quantity-btn {
    width: 32px;
    height: 32px;
    background: #f0f0f0;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: #333;
    font-weight: bold;
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.quantity-btn:hover {
    background: #d4af37;
    color: white;
    transform: scale(1.05);
}

.quantity-value {
    min-width: 30px;
    text-align: center;
    font-weight: 600;
    font-size: 1.1rem;
}

/* Sous-total */
.cart-subtotal {
    font-weight: 700;
    color: #d4af37;
    font-size: 1.1rem;
}

/* Bouton suppression */
.cart-remove-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background: #fee;
    border-radius: 8px;
    color: #e74c3c;
    text-decoration: none;
    transition: all 0.3s ease;
}

.cart-remove-btn:hover {
    background: #e74c3c;
    color: white;
    transform: rotate(360deg);
}

/* Section récapitulatif */
.cart-summary-section {
    margin-top: 30px;
}

.summary-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

/* Bloc Promo */
.promo-block {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.03);
    border: 1px solid #f0f0f0;
}

.block-title {
    font-size: 1.2rem;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #333;
}

.block-title i {
    color: #d4af37;
}

.promo-applied {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #e6ffed;
    border: 1px solid #28a745;
    border-radius: 10px;
    padding: 15px;
}

.promo-badge {
    display: flex;
    align-items: center;
    gap: 10px;
}

.promo-code {
    font-weight: 700;
    color: #28a745;
    background: white;
    padding: 5px 12px;
    border-radius: 20px;
    border: 1px solid #28a745;
}

.promo-discount {
    font-weight: 600;
    color: #28a745;
}

.promo-remove {
    color: #e74c3c;
    font-size: 1.2rem;
    transition: transform 0.3s ease;
}

.promo-remove:hover {
    transform: scale(1.2);
}

.promo-form {
    width: 100%;
}

.promo-input-group {
    display: flex;
    gap: 10px;
}

.promo-input {
    flex: 1;
    padding: 12px 15px;
    border: 2px solid #eee;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.promo-input:focus {
    outline: none;
    border-color: #d4af37;
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
}

.promo-submit {
    padding: 0 20px;
    background: #d4af37;
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.promo-submit:hover {
    background: #b8960c;
    transform: translateY(-2px);
}

.promo-message {
    margin-top: 15px;
    padding: 10px 15px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
}

.promo-message.alert-success {
    background: #e6ffed;
    color: #28a745;
    border: 1px solid #28a745;
}

.promo-message.alert-error {
    background: #fee;
    color: #e74c3c;
    border: 1px solid #e74c3c;
}

/* Bloc Total */
.total-block {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    border: 2px solid #d4af37;
    box-shadow: 0 15px 30px rgba(212, 175, 55, 0.15);
}

.total-calculations {
    margin-bottom: 25px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    color: #ccc;
}

.discount-row {
    color: #d4af37;
}

.total-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, #d4af37, transparent);
    margin: 15px 0;
}

.final-row {
    font-size: 1.2rem;
    font-weight: 700;
}

.final-total {
    font-size: 2rem;
    color: #d4af37;
}

.whatsapp-checkout-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 15px;
    background: #25D366;
    color: white;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    border: 1px solid #25D366;
    margin-bottom: 15px;
}

.whatsapp-checkout-btn:hover {
    background: transparent;
    color: #25D366;
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(37, 211, 102, 0.3);
}

.payment-guarantee {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #888;
    font-size: 0.85rem;
}

.payment-guarantee i {
    color: #d4af37;
}

/* Suggestions */
.cart-suggestions {
    margin-top: 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.suggestions-title {
    font-size: 1.1rem;
    color: #666;
    font-weight: 400;
}

.suggestions-link {
    color: #d4af37;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: gap 0.3s ease;
}

.suggestions-link:hover {
    gap: 10px;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 992px) {
    .summary-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}

@media (max-width: 768px) {
    .cart-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .cart-table {
        display: block;
        overflow-x: auto;
    }
    
    .cart-table th {
        white-space: nowrap;
    }
    
    .cart-product-image {
        width: 60px;
        height: 60px;
    }
    
    .cart-product-name {
        font-size: 1rem;
    }
    
    .final-total {
        font-size: 1.5rem;
    }
    
    .cart-remove-btn {
        width: 32px;
        height: 32px;
    }
}

@media (max-width: 480px) {
    .cart-title {
        font-size: 1.5rem;
    }
    
    .cart-product-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .cart-product-image {
        width: 100%;
        height: auto;
        max-width: 150px;
    }
    
    .quantity-controls {
        flex-wrap: wrap;
    }
    
    .promo-input-group {
        flex-direction: column;
    }
    
    .promo-submit {
        padding: 12px;
    }
    
    .total-block {
        padding: 20px;
    }
    
    .final-total {
        font-size: 1.3rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>