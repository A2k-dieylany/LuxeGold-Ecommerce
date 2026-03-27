<?php 
require_once 'config/db.php';
require_once 'includes/fonctions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Récupération du produit actuel
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

// Si le produit n'existe pas, retour au catalogue
if(!$p) { header('Location: catalogue.php'); exit(); }

// Calcul de la promotion pour l'affichage
$reduction = (int)($p['reduction'] ?? 0);
$aUneReduc = ($reduction > 0);
$prixFinal = $aUneReduc ? ($p['prix'] * (1 - ($reduction / 100))) : $p['prix'];

// 2. LOGIQUE DES PRODUITS SIMILAIRES
$stmtSim = $pdo->prepare("SELECT * FROM produits WHERE id_categorie = ? AND id != ? ORDER BY RAND() LIMIT 4");
$stmtSim->execute([$p['id_categorie'], $id]);
$similaires = $stmtSim->fetchAll();

// 3. VÉRIFICATION WISHLIST
$session_id = session_id();
$wish_check = $pdo->prepare("SELECT id FROM wishlist WHERE session_id = ? AND produit_id = ?");
$wish_check->execute([$session_id, $p['id']]);
$is_favorite = $wish_check->fetch();

include 'includes/header.php'; 
?>

<main class="product-page">
    <div class="container">
        <div class="breadcrumb">
            <a href="catalogue.php" class="breadcrumb-link">
                <i class="fas fa-arrow-left"></i> 
                <?= $lang['cat_back'] ?? 'Retour au catalogue' ?>
            </a>
        </div>

        <section class="produit-detail">
            <div class="produit-image-wrapper">
                <div class="produit-image-container">
                    <?php if($aUneReduc): ?>
                        <span class="badge-promo-detail">
                            <span class="promo-text">PROMO</span>
                            <span class="promo-value">-<?= $reduction ?>%</span>
                        </span>
                    <?php endif; ?>

                    <button class="wishlist-btn-detail <?= $is_favorite ? 'active' : '' ?>" 
                            onclick="toggleWishlist(<?= $p['id'] ?>, this)" 
                            aria-label="Ajouter aux favoris">
                        <i class="<?= $is_favorite ? 'fas' : 'far' ?> fa-heart"></i>
                    </button>

                    <?php if(!empty($p['modele_3d'])): ?>
                    <div style="margin-bottom: 20px; background: #fdfdfd; border-radius: 12px; overflow: hidden; position: relative;">
                        <span style="position: absolute; top: 15px; right: 80px; z-index: 5; font-size: 0.75rem; background: rgba(26,26,26,0.8); color: #d4af37; padding: 5px 10px; border-radius: 20px; font-weight: bold; backdrop-filter: blur(4px);">
                            <i class="fas fa-hand-pointer"></i> 360° INTERACTIF
                        </span>
                        <model-viewer 
                            src="assets/3d/<?= htmlspecialchars($p['modele_3d']) ?>" 
                            auto-rotate 
                            camera-controls 
                            shadow-intensity="1"
                            style="width: 100%; height: 400px; background-color: #f9f9f9;">
                        </model-viewer>
                    </div>
                    <?php endif; ?>

                    <?php if(!empty($p['video'])): ?>
                    <div style="margin-bottom: 20px; border-radius: 12px; overflow: hidden; position: relative;">
                        <span style="position: absolute; top: 15px; right: 80px; z-index: 5; font-size: 0.75rem; background: rgba(26,26,26,0.8); color: white; padding: 5px 10px; border-radius: 20px; font-weight: bold; backdrop-filter: blur(4px);">
                            <i class="fas fa-play"></i> VIDÉO
                        </span>
                        <video autoplay loop muted playsinline style="width: 100%; display: block; background: #000; max-height: 400px; object-fit: cover;">
                            <source src="assets/videos/<?= htmlspecialchars($p['video']) ?>" type="video/mp4">
                            <source src="assets/videos/<?= htmlspecialchars($p['video']) ?>" type="video/webm">
                        </video>
                    </div>
                    <?php endif; ?>

                    <div style="<?php echo (!empty($p['modele_3d']) || !empty($p['video'])) ? 'border-top: 1px dashed #ddd; padding-top: 20px;' : ''; ?>">
                        <?php if(!empty($p['modele_3d']) || !empty($p['video'])): ?>
                            <h4 style="font-size: 0.9rem; color: #888; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px;">Galerie photo</h4>
                        <?php endif; ?>
                        
                        <div class="produit-image-main">
                            <img src="assets/img/produits/<?= htmlspecialchars($p['image']) ?>" 
                                 alt="<?= htmlspecialchars($p['nom']) ?>" 
                                 class="produit-image"
                                 id="main-product-image">
                        </div>

                        <div class="produit-thumbnails">
                            <div class="thumbnail active">
                                <img src="assets/img/produits/<?= htmlspecialchars($p['image']) ?>" 
                                     alt="Miniature" 
                                     onclick="document.getElementById('main-product-image').src = this.src">
                            </div>
                            <?php if(file_exists('assets/img/produits/thumb_'.htmlspecialchars($p['image']))): ?>
                                <div class="thumbnail">
                                    <img src="assets/img/produits/thumb_<?= htmlspecialchars($p['image']) ?>" 
                                         alt="Miniature" 
                                         onclick="document.getElementById('main-product-image').src = this.src">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
            
            <div class="produit-info-wrapper">
                <div class="produit-info">
                    <?php
                    $stmtCat = $pdo->prepare("SELECT nom FROM categories WHERE id = ?");
                    $stmtCat->execute([$p['id_categorie']]);
                    $categorie = $stmtCat->fetchColumn();
                    ?>
                    <span class="produit-categorie"><?= htmlspecialchars($categorie) ?></span>
                    
                    <h1 class="produit-titre"><?= htmlspecialchars($p['nom']) ?></h1>
                    
                    <div class="produit-prix-container">
                        <?php if ($aUneReduc): ?>
                            <span class="old-prix"><?= formaterPrix($p['prix']) ?></span>
                            <span class="new-prix"><?= formaterPrix($prixFinal) ?></span>
                            <span class="economie">Économisez <?= formaterPrix($p['prix'] - $prixFinal) ?></span>
                        <?php else: ?>
                            <span class="prix"><?= formaterPrix($p['prix']) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="produit-stock">
                        <?php if($p['stock'] > 0): ?>
                            <span class="stock-disponible">
                                <i class="fas fa-check-circle"></i> 
                                <?= $lang['prod_stock_ok'] ?? 'En stock' ?> 
                                <span class="stock-quantite">(<?= $p['stock'] ?> <?= $lang['prod_qty'] ?? 'unités' ?>)</span>
                            </span>
                        <?php else: ?>
                            <span class="stock-rupture">
                                <i class="fas fa-times-circle"></i> 
                                <?= $lang['prod_stock_low'] ?? 'Rupture de stock' ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="produit-description">
                        <h3 class="description-title">Description du bijou</h3>
                        <div class="description-content">
                            <?= nl2br(htmlspecialchars($p['description'])) ?>
                        </div>
                    </div>
                    
                    <?php if(!empty($p['metal'])): ?>
                    <div class="produit-metal">
                        <span class="metal-label">Métal :</span>
                        <span class="metal-value"><?= htmlspecialchars($p['metal']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($p['stock'] > 0): ?>
                        <form onsubmit="event.preventDefault(); ajouterAuPanierAvecQte(<?= $p['id'] ?>);" class="ajout-panier-form">
                            <div class="quantite-selector">
                                <label for="qte_<?= $p['id'] ?>">Quantité</label>
                                <div class="quantite-controls">
                                    <button type="button" class="qte-btn" onclick="decrementerQuantite(<?= $p['id'] ?>)">-</button>
                                    <input type="number" name="qte" id="qte_<?= $p['id'] ?>" 
                                           value="1" min="1" max="<?= $p['stock'] ?>" 
                                           class="qte-input" readonly>
                                    <button type="button" class="qte-btn" onclick="incrementerQuantite(<?= $p['id'] ?>, <?= $p['stock'] ?>)">+</button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-gold btn-large btn-full">
                                <i class="fas fa-shopping-cart"></i> 
                                <?= $lang['prod_add_cart'] ?? 'Ajouter au panier' ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <button disabled class="btn btn-black btn-large btn-full btn-disabled">
                            <i class="fas fa-times-circle"></i>
                            <?= $lang['prod_stock_low'] ?? 'Rupture de stock' ?>
                        </button>
                    <?php endif; ?>

                    <div class="produit-garanties">
                        <div class="garantie-item">
                            <i class="fas fa-certificate"></i>
                            <span>Or certifié et poinçonné</span>
                        </div>
                        <div class="garantie-item">
                            <i class="fab fa-whatsapp"></i>
                            <span>Commande directe WhatsApp</span>
                        </div>
                        <div class="garantie-item">
                            <i class="fas fa-truck"></i>
                            <span>Livraison gratuite</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php if(count($similaires) > 0): ?>
        <section class="similaires-section">
            <h2 class="section-title"><?= $lang['prod_similar'] ?? 'Vous aimerez aussi' ?></h2>
            <div class="product-grid">
                <?php foreach($similaires as $s): 
                    $sReduc = (int)($s['reduction'] ?? 0);
                    $sFinal = ($sReduc > 0) ? ($s['prix'] * (1 - ($sReduc / 100))) : $s['prix'];
                ?>
                <div class="product-card">
                    <?php if($sReduc > 0): ?>
                        <span class="badge badge-promo">-<?= $sReduc ?>%</span>
                    <?php endif; ?>
                    
                    <a href="produit.php?id=<?= $s['id'] ?>" class="product-link">
                        <div class="product-image-wrapper">
                            <img src="assets/img/produits/<?= htmlspecialchars($s['image']) ?>" 
                                 alt="<?= htmlspecialchars($s['nom']) ?>" 
                                 class="product-image">
                            <div class="product-overlay">
                                <span class="quick-view">Voir</span>
                            </div>
                        </div>
                    </a>
                    
                    <div class="product-details">
                        <h3 class="product-title"><?= htmlspecialchars($s['nom']) ?></h3>
                        <div class="product-price-wrapper">
                            <?php if($sReduc > 0): ?>
                                <span class="old-price"><?= formaterPrix($s['prix']) ?></span>
                                <span class="new-price"><?= formaterPrix($sFinal) ?></span>
                            <?php else: ?>
                                <span class="regular-price"><?= formaterPrix($s['prix']) ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="produit.php?id=<?= $s['id'] ?>" class="btn btn-gold-outline btn-small btn-full">
                            <?= $lang['cat_details'] ?? 'Voir détails' ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <section class="avis-section">
            <h2 class="section-title">Avis de nos clients</h2>
            
            <div class="avis-grid">
                <div class="avis-liste">
                    <?php
                    $stmtAvis = $pdo->prepare("SELECT * FROM avis WHERE produit_id = ? AND statut = 'valide' ORDER BY created_at DESC");
                    $stmtAvis->execute([$p['id']]);
                    $lesAvis = $stmtAvis->fetchAll();

                    if (empty($lesAvis)): ?>
                        <div class="no-avis">
                            <i class="far fa-star"></i>
                            <p>Soyez le premier à donner votre avis sur ce bijou !</p>
                        </div>
                    <?php else: 
                        foreach($lesAvis as $unAvis): ?>
                        <div class="avis-card">
                            <div class="avis-header">
                                <strong class="avis-nom"><?= htmlspecialchars($unAvis['client_nom']) ?></strong>
                                <span class="avis-date"><?= date('d/m/Y', strtotime($unAvis['created_at'])) ?></span>
                            </div>
                            <div class="avis-notes">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa<?= $i <= $unAvis['note'] ? 's' : 'r' ?> fa-star <?= $i <= $unAvis['note'] ? 'active' : '' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="avis-commentaire"><?= nl2br(htmlspecialchars($unAvis['commentaire'])) ?></p>
                        </div>
                    <?php endforeach; endif; ?>
                </div>

                <div class="avis-formulaire">
                    <h3 class="form-title">Laissez une note</h3>
                    <form id="formAvis" class="avis-form">
                        <input type="hidden" name="produit_id" value="<?= $p['id'] ?>">
                        
                        <div class="form-group">
                            <label for="nom_avis">Votre nom</label>
                            <input type="text" name="nom" id="nom_avis" required 
                                   placeholder="Ex: Marie C.">
                        </div>
                        
                        <div class="form-group">
                            <label for="note_avis">Votre note</label>
                            <div class="rating-selector">
                                <select name="note" id="note_avis" class="rating-select">
                                    <option value="5">5 ★ - Excellent</option>
                                    <option value="4">4 ★ - Très bien</option>
                                    <option value="3">3 ★ - Bien</option>
                                    <option value="2">2 ★ - Moyen</option>
                                    <option value="1">1 ★ - Déçu</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="com_avis">Votre commentaire</label>
                            <textarea name="commentaire" id="com_avis" rows="4" required 
                                      placeholder="Partagez votre expérience avec ce bijou..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-gold btn-large btn-full">
                            <i class="fas fa-paper-plane"></i> Envoyer mon avis
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</main>

<style>
/* ============================================
   PAGE PRODUIT - STYLES AMÉLIORÉS
   ============================================ */

.product-page {
    padding: 40px 0;
    background: #fafafa;
}

/* Fil d'Ariane */
.breadcrumb {
    margin-bottom: 30px;
}

.breadcrumb-link {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: #888;
    text-decoration: none;
    transition: color 0.3s ease;
    font-size: 0.95rem;
}

.breadcrumb-link:hover {
    color: #d4af37;
}

.breadcrumb-link i {
    font-size: 0.9rem;
}

/* Section Détail Produit */
.produit-detail {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 50px;
    margin-bottom: 60px;
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.03);
    border: 1px solid #f0f0f0;
}

/* Colonne Image */
.produit-image-wrapper {
    position: relative;
}

.produit-image-container {
    position: relative;
    background: #fff;
    border-radius: 15px;
    overflow: hidden;
}

.badge-promo-detail {
    position: absolute;
    top: 20px;
    left: 20px;
    background: #e74c3c;
    color: white;
    padding: 0;
    border-radius: 8px;
    font-weight: bold;
    z-index: 5;
    display: flex;
    flex-direction: column;
    align-items: center;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
}

.promo-text {
    background: rgba(255,255,255,0.2);
    padding: 5px 15px;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.promo-value {
    padding: 8px 15px;
    font-size: 1.2rem;
    background: #c0392b;
}

.wishlist-btn-detail {
    position: absolute;
    top: 20px;
    right: 20px;
    background: white;
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    cursor: pointer;
    z-index: 10;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border: 1px solid #eee;
}

.wishlist-btn-detail:hover {
    transform: scale(1.1);
    border-color: #ff4d4d;
}

.wishlist-btn-detail.active {
    background: #ff4d4d;
    border-color: #ff4d4d;
}

.wishlist-btn-detail.active i {
    color: white !important;
}

.wishlist-btn-detail i {
    color: #ff4d4d;
    font-size: 1.5rem;
    transition: all 0.3s ease;
}

.produit-image-main {
    aspect-ratio: 1/1;
    overflow: hidden;
}

.produit-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.produit-image:hover {
    transform: scale(1.05);
}

.produit-thumbnails {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.thumbnail {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.3s ease;
    opacity: 0.7;
}

.thumbnail.active,
.thumbnail:hover {
    border-color: #d4af37;
    opacity: 1;
}

.thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Colonne Informations */
.produit-info-wrapper {
    padding: 20px;
}

.produit-categorie {
    display: inline-block;
    color: #d4af37;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 10px;
}

.produit-titre {
    font-size: 2rem;
    color: #000;
    margin-bottom: 20px;
    font-family: 'Playfair Display', serif;
    line-height: 1.2;
}

.produit-prix-container {
    margin-bottom: 25px;
}

.prix {
    font-size: 2.5rem;
    font-weight: 700;
    color: #d4af37;
}

.old-prix {
    font-size: 1.5rem;
    color: #999;
    text-decoration: line-through;
    margin-right: 15px;
}

.new-prix {
    font-size: 2.5rem;
    font-weight: 700;
    color: #e74c3c;
}

.economie {
    display: block;
    margin-top: 5px;
    color: #27ae60;
    font-size: 0.9rem;
    font-weight: 600;
}

.produit-stock {
    margin-bottom: 25px;
}

.stock-disponible {
    color: #27ae60;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 15px;
    background: #e8f5e9;
    border-radius: 30px;
    font-size: 0.95rem;
}

.stock-disponible i {
    font-size: 1rem;
}

.stock-quantite {
    color: #666;
    font-weight: 400;
    margin-left: 5px;
}

.stock-rupture {
    color: #e74c3c;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 15px;
    background: #fdeaea;
    border-radius: 30px;
    font-size: 0.95rem;
}

.produit-description {
    margin: 30px 0;
}

.description-title {
    font-size: 1.2rem;
    margin-bottom: 15px;
    color: #000;
    font-weight: 600;
}

.description-content {
    color: #666;
    line-height: 1.8;
    font-size: 0.95rem;
}

.produit-metal {
    margin: 20px 0;
    padding: 15px;
    background: #f8f8f8;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.metal-label {
    font-weight: 600;
    color: #666;
}

.metal-value {
    color: #d4af37;
    font-weight: 600;
}

/* Formulaire d'ajout au panier */
.ajout-panier-form {
    margin: 30px 0;
}

.quantite-selector {
    margin-bottom: 15px;
}

.quantite-selector label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.quantite-controls {
    display: flex;
    align-items: center;
    gap: 5px;
}

.qte-btn {
    width: 40px;
    height: 40px;
    border: 1px solid #ddd;
    background: white;
    border-radius: 8px;
    font-size: 1.2rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.qte-btn:hover {
    background: #d4af37;
    border-color: #d4af37;
    color: white;
}

.qte-input {
    width: 80px;
    height: 40px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    background: #f8f8f8;
}

.btn-full {
    width: 100%;
    padding: 15px !important;
    font-size: 1.1rem !important;
}

.btn-disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Garanties */
.produit-garanties {
    margin-top: 30px;
    padding-top: 25px;
    border-top: 1px solid #eee;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

.garantie-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-align: center;
    color: #666;
    font-size: 0.85rem;
}

.garantie-item i {
    font-size: 1.3rem;
    color: #d4af37;
}

/* Section Similaires */
.similaires-section {
    margin: 60px 0;
}

/* Section Avis */
.avis-section {
    margin: 60px 0;
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.03);
    border: 1px solid #f0f0f0;
}

.avis-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
}

.avis-liste {
    max-height: 500px;
    overflow-y: auto;
    padding-right: 20px;
}

.avis-liste::-webkit-scrollbar {
    width: 5px;
}

.avis-liste::-webkit-scrollbar-track {
    background: #f0f0f0;
    border-radius: 10px;
}

.avis-liste::-webkit-scrollbar-thumb {
    background: #d4af37;
    border-radius: 10px;
}

.no-avis {
    text-align: center;
    padding: 40px 20px;
    background: #f8f8f8;
    border-radius: 15px;
}

.no-avis i {
    font-size: 3rem;
    color: #ddd;
    margin-bottom: 15px;
}

.no-avis p {
    color: #999;
}

.avis-card {
    padding: 20px;
    background: #f8f8f8;
    border-radius: 12px;
    margin-bottom: 15px;
    transition: transform 0.3s ease;
}

.avis-card:hover {
    transform: translateX(5px);
}

.avis-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.avis-nom {
    font-weight: 600;
    color: #333;
}

.avis-date {
    font-size: 0.8rem;
    color: #999;
}

.avis-notes {
    margin-bottom: 10px;
}

.avis-notes i {
    color: #ddd;
    margin-right: 2px;
}

.avis-notes i.active {
    color: #d4af37;
}

.avis-commentaire {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.6;
    margin: 0;
}

/* Formulaire avis */
.avis-formulaire {
    background: #f8f8f8;
    border-radius: 15px;
    padding: 30px;
}

.form-title {
    font-size: 1.3rem;
    margin-bottom: 25px;
    color: #000;
    font-family: 'Playfair Display', serif;
}

.avis-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.rating-select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: white;
    font-size: 0.95rem;
}

/* Responsive */
@media (max-width: 992px) {
    .produit-detail {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .avis-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .produit-garanties {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .produit-detail {
        padding: 20px;
    }
    
    .produit-titre {
        font-size: 1.6rem;
    }
    
    .prix, .new-prix {
        font-size: 2rem;
    }
    
    .produit-garanties {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .garantie-item {
        flex-direction: row;
        justify-content: center;
        gap: 10px;
    }
    
    .thumbnail {
        width: 60px;
        height: 60px;
    }
    
    .avis-section {
        padding: 25px;
    }
}

@media (max-width: 480px) {
    .produit-titre {
        font-size: 1.4rem;
    }
    
    .quantite-controls {
        width: 100%;
    }
    
    .qte-input {
        flex: 1;
    }
    
    .btn-full {
        padding: 12px !important;
        font-size: 1rem !important;
    }
}
</style>

<script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.1.1/model-viewer.min.js"></script>

<script>
// Gestionnaire du formulaire d'avis
document.getElementById('formAvis').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    // Animation du bouton
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
    submitBtn.disabled = true;
    
    fetch('api/avis.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if(data.success) {
            this.reset();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(err => {
        console.error('Erreur:', err);
        alert('Une erreur est survenue. Veuillez réessayer.');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

function incrementerQuantite(id, max) {
    const input = document.getElementById('qte_' + id);
    let val = parseInt(input.value) || 1;
    if (val < max) {
        input.value = val + 1;
    }
}

function decrementerQuantite(id) {
    const input = document.getElementById('qte_' + id);
    let val = parseInt(input.value) || 1;
    if (val > 1) {
        input.value = val - 1;
    }
}
</script>

<?php include 'includes/footer.php'; ?>