<?php
require_once 'config/db.php';
require_once 'includes/fonctions.php';
include 'includes/header.php';

// 1. SÉCURITÉ PANIER
if (empty($_SESSION['panier'])) {
    echo "<script>window.location.href='catalogue.php';</script>";
    exit();
}

// 2. CALCUL DU TOTAL (Version sécurisée avec gestion d'erreur)
$total_panier = 0;
try {
    foreach ($_SESSION['panier'] as $id => $qte) {
        $stmt = $pdo->prepare("SELECT prix FROM produits WHERE id = ?");
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        if ($p) {
            $total_panier += ($p['prix'] * $qte);
        }
    }
} catch (Exception $e) {
    // Si la base de données a un souci, on met un montant par défaut pour ne pas bloquer l'affichage
    $total_panier = 0;
}

$total_affiche = formaterPrix($total_panier);
?>

<main class="checkout-page">
    <div class="container">
        
        <!-- En-tête de la page -->
        <div class="checkout-header">
            <h1 class="checkout-title">
                <i class="fas fa-lock"></i>
                Finaliser ma commande
            </h1>
            <div class="checkout-steps">
                <span class="step active">1. Panier</span>
                <span class="step-separator"><i class="fas fa-chevron-right"></i></span>
                <span class="step active">2. Paiement</span>
                <span class="step-separator"><i class="fas fa-chevron-right"></i></span>
                <span class="step">3. Confirmation</span>
            </div>
        </div>

        <!-- Grille principale -->
        <div class="checkout-grid">
            
            <!-- Formulaire de paiement -->
            <div class="checkout-form-container">
                <form action="api/initier_paiement.php" method="POST" class="checkout-form">
                    
                    <!-- Section Informations personnelles -->
                    <div class="form-section">
                        <div class="section-header">
                            <span class="section-number">1</span>
                            <h3 class="section-title">Vos informations</h3>
                        </div>
                        
                        <div class="form-group">
                            <label for="nom_complet">
                                <i class="fas fa-user"></i>
                                Nom complet
                            </label>
                            <input type="text" 
                                   id="nom_complet"
                                   name="nom" 
                                   required 
                                   placeholder="Ex: Marie Diop"
                                   class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="telephone">
                                <i class="fas fa-phone-alt"></i>
                                Téléphone (Compte Mobile Money)
                            </label>
                            <input type="tel" 
                                   id="telephone"
                                   name="telephone" 
                                   required 
                                   placeholder="77 000 00 00" 
                                   class="form-input"
                                   pattern="[0-9\s]{9,15}"
                                   title="Numéro de téléphone valide">
                            <small class="input-hint">Le numéro associé à votre compte Mobile Money</small>
                        </div>
                    </div>

                    <!-- Section Méthode de paiement -->
                    <div class="form-section">
                        <div class="section-header">
                            <span class="section-number">2</span>
                            <h3 class="section-title">Méthode de paiement</h3>
                        </div>

                        <div class="payment-methods">
                            <?php 
                            // ON FORCE L'AFFICHAGE SÉNÉGAL SI LA DEVISE EST FCFA OU VIDE
                            $ma_devise = $_SESSION['devise'] ?? 'FCFA';
                            
                            if($ma_devise == 'FCFA'): 
                            ?>
                                <!-- Wave Sénégal -->
                                <label class="payment-method">
                                    <input type="radio" name="methode" value="wave" checked required class="payment-radio">
                                    <span class="payment-card wave-card">
                                        <span class="payment-radio-custom"></span>
                                        <img src="assets/img/wave.png" alt="Wave" class="payment-logo">
                                        <span class="payment-info">
                                            <span class="payment-name">Wave Sénégal</span>
                                            <span class="payment-desc">Paiement instantané sans frais</span>
                                        </span>
                                    </span>
                                </label>

                                <!-- Orange Money -->
                                <label class="payment-method">
                                    <input type="radio" name="methode" value="orange_money" required class="payment-radio">
                                    <span class="payment-card orange-card">
                                        <span class="payment-radio-custom"></span>
                                        <img src="assets/img/orange-money.png" alt="Orange Money" class="payment-logo">
                                        <span class="payment-info">
                                            <span class="payment-name">Orange Money</span>
                                            <span class="payment-desc">Accepté partout au Sénégal</span>
                                        </span>
                                    </span>
                                </label>

                            <?php else: ?>
                                <!-- QMoney Gambie -->
                                <label class="payment-method">
                                    <input type="radio" name="methode" value="qmoney" checked required class="payment-radio">
                                    <span class="payment-card qmoney-card">
                                        <span class="payment-radio-custom"></span>
                                        <img src="assets/img/qmoney.png" alt="QMoney" class="payment-logo">
                                        <span class="payment-info">
                                            <span class="payment-name">QMoney Gambia</span>
                                            <span class="payment-desc">Mobile money Gambien</span>
                                        </span>
                                    </span>
                                </label>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Bouton de paiement -->
                    <button type="submit" class="btn-payment">
                        <span class="btn-content">
                            <i class="fas fa-lock"></i>
                            Payer <span class="payment-amount"><?= $total_affiche ?></span>
                        </span>
                        <span class="btn-shine"></span>
                    </button>
                </form>
            </div>

            <!-- Récapitulatif de commande -->
            <div class="checkout-summary">
                <div class="summary-header">
                    <i class="fas fa-receipt"></i>
                    <h4>Récapitulatif</h4>
                </div>

                <div class="summary-content">
                    <!-- Articles -->
                    <div class="summary-items">
                        <?php 
                        $compteur = 0;
                        foreach ($_SESSION['panier'] as $id => $qte): 
                            $compteur++;
                            if($compteur <= 3): // Afficher seulement les 3 premiers articles
                                $stmt = $pdo->prepare("SELECT nom FROM produits WHERE id = ?");
                                $stmt->execute([$id]);
                                $nom_produit = $stmt->fetchColumn();
                        ?>
                            <div class="summary-item">
                                <span class="item-name"><?= htmlspecialchars($nom_produit) ?></span>
                                <span class="item-qty">x<?= $qte ?></span>
                            </div>
                        <?php 
                            endif;
                        endforeach; 
                        
                        if(count($_SESSION['panier']) > 3): ?>
                            <div class="summary-more">
                                + <?= count($_SESSION['panier']) - 3 ?> autre(s) article(s)
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Détails -->
                    <div class="summary-details">
                        <div class="detail-row">
                            <span>Sous-total</span>
                            <span><?= $total_affiche ?></span>
                        </div>
                        <div class="detail-row">
                            <span>Livraison</span>
                            <span class="free-shipping">Gratuite</span>
                        </div>
                        
                        <?php if(isset($_SESSION['promo'])): ?>
                            <div class="detail-row promo-row">
                                <span>
                                    <i class="fas fa-tag"></i>
                                    Code promo (-<?= $_SESSION['promo']['remise'] ?>%)
                                </span>
                                <span class="promo-value">
                                    -<?= formaterPrix($total_panier * ($_SESSION['promo']['remise'] / 100)) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Total -->
                    <div class="summary-total">
                        <span>Total à payer</span>
                        <span class="total-amount"><?= $total_affiche ?></span>
                    </div>

                    <!-- Sécurité -->
                    <div class="summary-security">
                        <div class="security-badge">
                            <i class="fas fa-shield-alt"></i>
                            <span>Transaction 100% sécurisée</span>
                        </div>
                        <p class="security-note">
                            <i class="fas fa-info-circle"></i>
                            Une demande de confirmation apparaîtra sur votre téléphone après avoir cliqué sur Payer.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lien de retour -->
        <div class="checkout-footer">
            <a href="panier.php" class="back-to-cart">
                <i class="fas fa-arrow-left"></i>
                Modifier mon panier
            </a>
        </div>
    </div>
</main>

<!-- Styles spécifiques à la page de commande -->
<style>
/* ============================================
   PAGE COMMANDE - STYLES AMÉLIORÉS
   ============================================ */

.checkout-page {
    padding: 40px 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 80vh;
}

/* En-tête */
.checkout-header {
    text-align: center;
    margin-bottom: 50px;
}

.checkout-title {
    font-size: 2.2rem;
    color: #000;
    margin-bottom: 20px;
    font-family: 'Playfair Display', serif;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.checkout-title i {
    color: #d4af37;
    font-size: 2rem;
}

/* Étapes */
.checkout-steps {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.step {
    padding: 8px 20px;
    border-radius: 30px;
    background: #fff;
    color: #999;
    font-weight: 500;
    font-size: 0.9rem;
    border: 1px solid #eee;
}

.step.active {
    background: #d4af37;
    color: #000;
    border-color: #d4af37;
    box-shadow: 0 5px 15px rgba(212, 175, 55, 0.2);
}

.step-separator {
    color: #d4af37;
    font-size: 0.9rem;
}

/* Grille principale */
.checkout-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 30px;
    align-items: start;
}

/* Formulaire */
.checkout-form-container {
    background: #fff;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    border: 1px solid #f0f0f0;
}

.form-section {
    margin-bottom: 40px;
    animation: fadeInUp 0.5s ease forwards;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
}

.section-number {
    width: 35px;
    height: 35px;
    background: #d4af37;
    color: #000;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
}

.section-title {
    font-size: 1.3rem;
    color: #000;
    margin: 0;
    font-family: 'Playfair Display', serif;
}

/* Champs de formulaire */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
}

.form-group label i {
    color: #d4af37;
    font-size: 0.9rem;
}

.form-input {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #eee;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #fafafa;
}

.form-input:hover {
    border-color: #d4af37;
}

.form-input:focus {
    outline: none;
    border-color: #d4af37;
    box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1);
    background: #fff;
}

.input-hint {
    display: block;
    margin-top: 5px;
    font-size: 0.8rem;
    color: #999;
    font-style: italic;
}

/* Méthodes de paiement */
.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.payment-method {
    cursor: pointer;
    position: relative;
}

.payment-radio {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.payment-card {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    border: 2px solid #eee;
    border-radius: 15px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.payment-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, transparent, rgba(212, 175, 55, 0.05));
    transform: translateX(-100%);
    transition: transform 0.6s ease;
}

.payment-radio:checked + .payment-card {
    border-color: #d4af37;
    background: #fff9f0;
    box-shadow: 0 10px 20px rgba(212, 175, 55, 0.1);
}

.payment-radio:checked + .payment-card::before {
    transform: translateX(0);
}

.payment-card:hover {
    transform: translateY(-3px);
    border-color: #d4af37;
    box-shadow: 0 10px 25px rgba(212, 175, 55, 0.15);
}

.payment-radio-custom {
    width: 22px;
    height: 22px;
    border: 2px solid #ddd;
    border-radius: 50%;
    position: relative;
    transition: all 0.3s ease;
}

.payment-radio:checked + .payment-card .payment-radio-custom {
    border-color: #d4af37;
    background: #d4af37;
    box-shadow: inset 0 0 0 5px #fff;
}

.payment-logo {
    width: 50px;
    height: 50px;
    object-fit: contain;
    border-radius: 10px;
    background: #fff;
    padding: 5px;
}

.payment-info {
    flex: 1;
}

.payment-name {
    display: block;
    font-weight: 700;
    color: #333;
    margin-bottom: 3px;
}

.payment-desc {
    display: block;
    font-size: 0.8rem;
    color: #999;
}

/* Bouton de paiement */
.btn-payment {
    width: 100%;
    padding: 18px;
    background: linear-gradient(135deg, #d4af37, #b8960c);
    color: #000;
    border: none;
    border-radius: 40px;
    font-size: 1.2rem;
    font-weight: 700;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 10px 20px rgba(212, 175, 55, 0.3);
}

.btn-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-payment:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(212, 175, 55, 0.4);
}

.btn-payment:active {
    transform: translateY(-1px);
}

.btn-shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.6s ease;
}

.btn-payment:hover .btn-shine {
    left: 100%;
}

.payment-amount {
    background: rgba(0,0,0,0.1);
    padding: 5px 15px;
    border-radius: 30px;
    font-size: 1.1rem;
}

/* Récapitulatif */
.checkout-summary {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    border: 1px solid #f0f0f0;
    position: sticky;
    top: 100px;
}

.summary-header {
    background: linear-gradient(135deg, #1a1a1a, #2d2d2d);
    color: #fff;
    padding: 20px 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.summary-header i {
    color: #d4af37;
    font-size: 1.3rem;
}

.summary-header h4 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    letter-spacing: 1px;
}

.summary-content {
    padding: 25px;
}

.summary-items {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    color: #666;
    font-size: 0.95rem;
}

.item-name {
    flex: 1;
}

.item-qty {
    color: #d4af37;
    font-weight: 600;
}

.summary-more {
    text-align: center;
    color: #999;
    font-size: 0.85rem;
    font-style: italic;
    margin-top: 10px;
}

.summary-details {
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    color: #666;
    font-size: 0.95rem;
}

.free-shipping {
    color: #27ae60;
    font-weight: 600;
}

.promo-row {
    color: #d4af37;
}

.promo-value {
    font-weight: 700;
}

.summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    font-size: 1.2rem;
    font-weight: 700;
}

.total-amount {
    color: #d4af37;
    font-size: 1.8rem;
}

/* Sécurité */
.summary-security {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
}

.security-badge {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #27ae60;
    font-weight: 600;
    margin-bottom: 10px;
}

.security-badge i {
    font-size: 1.2rem;
}

.security-note {
    font-size: 0.8rem;
    color: #999;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    font-style: italic;
}

.security-note i {
    color: #d4af37;
}

/* Footer */
.checkout-footer {
    margin-top: 40px;
    text-align: center;
}

.back-to-cart {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #888;
    text-decoration: none;
    transition: color 0.3s ease;
    font-size: 0.95rem;
}

.back-to-cart:hover {
    color: #d4af37;
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
    .checkout-grid {
        grid-template-columns: 1fr;
    }
    
    .checkout-summary {
        position: static;
        max-width: 500px;
        margin: 0 auto;
        width: 100%;
    }
    
    .checkout-form-container {
        max-width: 600px;
        margin: 0 auto;
    }
}

@media (max-width: 768px) {
    .checkout-header {
        margin-bottom: 30px;
    }
    
    .checkout-title {
        font-size: 1.8rem;
    }
    
    .checkout-steps {
        gap: 8px;
    }
    
    .step {
        padding: 6px 15px;
        font-size: 0.8rem;
    }
    
    .checkout-form-container {
        padding: 25px;
    }
    
    .payment-card {
        padding: 15px;
        gap: 12px;
    }
    
    .payment-logo {
        width: 40px;
        height: 40px;
    }
    
    .payment-name {
        font-size: 0.9rem;
    }
    
    .payment-desc {
        font-size: 0.75rem;
    }
    
    .btn-payment {
        padding: 15px;
        font-size: 1rem;
    }
    
    .total-amount {
        font-size: 1.5rem;
    }
}

@media (max-width: 480px) {
    .checkout-title {
        font-size: 1.5rem;
        flex-direction: column;
        gap: 5px;
    }
    
    .section-header {
        flex-wrap: wrap;
    }
    
    .payment-card {
        flex-wrap: wrap;
    }
    
    .payment-info {
        width: 100%;
        text-align: center;
    }
    
    .summary-content {
        padding: 15px;
    }
    
    .total-amount {
        font-size: 1.3rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>