<?php 
// index.php - Version Luxe, Bilingue & Promotions

require_once 'config/db.php';
require_once 'includes/fonctions.php';

// Initialisation PDO avec gestion d'erreur
try {
  // Récupération des catégories actives uniquement
    $stmtCat = $pdo->query("SELECT * FROM categories WHERE deleted = 0 ORDER BY nom ASC");
    $categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupération des nouveautés actives uniquement
    $stmtNew = $pdo->query("SELECT id, nom, prix, image, reduction FROM produits WHERE deleted = 0 ORDER BY created_at DESC LIMIT 4");
    $nouveautes = $stmtNew->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur base de données index.php : " . $e->getMessage());
    $categories = [];
    $nouveautes = [];
}

// Images par défaut pour les catégories
$catImages = [
    'Femme'         => 'femme.jpg',
    'Homme'         => 'homme.jpg',
    'Enfant'        => 'enfant.jpg',
    'Couple'        => 'couple.jpg',
    'Cadeaux'       => 'cadeau.jpg',
    'Personnalisés' => 'custom.jpg'
    
];

include 'includes/header.php'; 
?>

<section class="hero-section" style="background-image: url('assets/img/hero-bg.jpg');">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <span class="hero-subtitle"><?= ($_SESSION['lang'] == 'en' ? 'Luxury Collection' : 'Collection Luxe') ?></span>
        <h1 class="hero-title"><?= htmlspecialchars($lang['hero_title']) ?></h1>
        <p class="hero-text"><?= htmlspecialchars($lang['hero_text']) ?></p>
        <div class="hero-buttons">
            <a href="catalogue.php" class="btn btn-gold btn-large">
                <?= htmlspecialchars($lang['btn_collection']) ?>
            </a>
        </div>
    </div>
    <div class="hero-decoration"></div>
</section>

<section class="collections-section">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle"><?= ($_SESSION['lang'] == 'en' ? 'Our categories' : 'Nos catégories') ?></span>
            <h2 class="section-title"><?= htmlspecialchars($lang['section_collections']) ?></h2>
            <div class="section-divider"></div>
        </div>
        
        <?php if (empty($categories)): ?>
            <div class="alert alert-info"><?= ($_SESSION['lang'] == 'en' ? 'No categories available.' : 'Aucune catégorie disponible.') ?></div>
        <?php else: ?>
            <div class="category-grid">
                <?php foreach($categories as $categorie): ?>
                    <?php 
                        $img = isset($catImages[$categorie['nom']]) ? $catImages[$categorie['nom']] : 'default.jpg';
                        $hasVideo = !empty($categorie['video']); 
                    ?>
                    
                    <a href="catalogue.php?cat=<?= (int)$categorie['id'] ?>" 
                       class="category-card"
                       style="<?= !$hasVideo ? "background-image: url('assets/img/categories/{$img}');" : "" ?>">
                        
                        <?php if ($hasVideo): ?>
                            <video autoplay loop muted playsinline 
                                   style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0;">
                                <source src="assets/videos/<?= htmlspecialchars($categorie['video']) ?>" type="video/mp4">
                                <source src="assets/videos/<?= htmlspecialchars($categorie['video']) ?>" type="video/webm">
                            </video>
                        <?php endif; ?>
                        <div class="category-card-content">
                            <h3><?= htmlspecialchars($categorie['nom']) ?></h3>
                            <span class="category-link">
                                <?= ($_SESSION['lang'] == 'en' ? 'Explore' : 'Découvrir') ?> 
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="nouveautes-section">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle"><?= ($_SESSION['lang'] == 'en' ? 'Latest arrivals' : 'Dernières créations') ?></span>
            <h2 class="section-title"><?= htmlspecialchars($lang['section_new']) ?></h2>
            <div class="section-divider"></div>
        </div>
        
        <?php if (empty($nouveautes)): ?>
            <div class="alert alert-info"><?= ($_SESSION['lang'] == 'en' ? 'No new products.' : 'Aucun nouveau produit.') ?></div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach($nouveautes as $produit): ?>
                    <div class="product-card">
                        <?php 
                            // Logique de promotion
                            $reduction = (int)($produit['reduction'] ?? 0);
                            $aUneReduc = ($reduction > 0);
                            $prixFinal = $aUneReduc ? ($produit['prix'] * (1 - ($reduction / 100))) : $produit['prix'];
                        ?>

                        <div class="product-badges">
                            <?php if ($aUneReduc): ?>
                                <span class="badge badge-promo">-<?= $reduction ?>%</span>
                            <?php else: ?>
                                <span class="badge badge-new"><?= htmlspecialchars($lang['badge_new']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <a href="produit.php?id=<?= (int)$produit['id'] ?>" class="product-link">
                            <div class="product-image-wrapper">
                                <img src="assets/img/produits/<?= htmlspecialchars($produit['image']) ?>" 
                                     alt="<?= htmlspecialchars($produit['nom']) ?>"
                                     class="product-image" loading="lazy">
                                <div class="product-overlay">
                                    <span class="product-quick-view"><?= ($_SESSION['lang'] == 'en' ? 'Quick view' : 'Aperçu rapide') ?></span>
                                </div>
                            </div>
                        </a>
                        
                        <div class="product-details">
                            <h3 class="product-title"><?= htmlspecialchars($produit['nom']) ?></h3>
                            
                            <div class="product-price-wrapper">
                                <?php if ($aUneReduc): ?>
                                    <span class="old-price"><?= formaterPrix($produit['prix']) ?></span>
                                    <span class="new-price"><?= formaterPrix($prixFinal) ?></span>
                                <?php else: ?>
                                    <span class="regular-price"><?= formaterPrix($produit['prix']) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <a href="produit.php?id=<?= (int)$produit['id'] ?>" 
                               class="btn btn-gold-outline btn-small btn-full">
                                <?= htmlspecialchars($lang['btn_view']) ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-5">
                <a href="catalogue.php" class="btn btn-gold btn-medium">
                    <?= htmlspecialchars($lang['btn_see_all']) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="services-section">
    <div class="container">
        <div class="services-grid">
            <div class="service-item">
                <div class="service-icon-wrapper">
                    <i class="fas fa-gem service-icon"></i>
                </div>
                <h4 class="service-title"><?= htmlspecialchars($lang['service_quality']) ?></h4>
                <p class="service-description"><?= ($_SESSION['lang'] == 'en' ? 'Certified jewelry with guarantee' : 'Bijoux certifiés avec garantie') ?></p>
            </div>
            
            <div class="service-item">
                <div class="service-icon-wrapper">
                    <i class="fab fa-whatsapp service-icon"></i>
                </div>
                <h4 class="service-title"><?= htmlspecialchars($lang['service_easy']) ?></h4>
                <p class="service-description"><?= ($_SESSION['lang'] == 'en' ? 'Quick order via WhatsApp' : 'Commande rapide par WhatsApp') ?></p>
            </div>
            
            <div class="service-item">
                <div class="service-icon-wrapper">
                    <i class="fas fa-truck service-icon"></i>
                </div>
                <h4 class="service-title"><?= htmlspecialchars($lang['service_location']) ?></h4>
                <p class="service-description"><?= ($_SESSION['lang'] == 'en' ? 'Free delivery in Senegal & Gambia' : 'Livraison gratuite Sénégal & Gambie') ?></p>
            </div>
        </div>
    </div>
</section>

<style>
/* ============================================
   STYLES SPÉCIFIQUES À LA PAGE D'ACCUEIL
   ============================================ */

/* Section Header */
.section-header {
    text-align: center;
    margin-bottom: 50px;
}

.section-subtitle {
    display: inline-block;
    color: #d4af37;
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 3px;
    margin-bottom: 10px;
    font-weight: 500;
}

.section-divider {
    width: 80px;
    height: 2px;
    background: linear-gradient(90deg, transparent, #d4af37, transparent);
    margin: 20px auto 0;
}

/* Hero Section */
.hero-section {
    position: relative;
    min-height: 90vh;
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: white;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.5) 100%);
    z-index: 1;
}

.hero-decoration {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 100px;
    background: linear-gradient(to top, rgba(0,0,0,0.3), transparent);
    z-index: 1;
}

.hero-content {
    position: relative;
    z-index: 2;
    max-width: 800px;
    padding: 0 20px;
    animation: fadeInUp 1s ease;
}

.hero-subtitle {
    display: inline-block;
    color: #d4af37;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 4px;
    margin-bottom: 20px;
    font-weight: 300;
}

.hero-title {
    font-size: clamp(2.5rem, 8vw, 5rem);
    margin-bottom: 20px;
    color: #d4af37;
    text-shadow: 0 0 30px rgba(212, 175, 55, 0.3);
    line-height: 1.1;
    font-family: 'Playfair Display', serif;
}

.hero-text {
    font-size: clamp(1rem, 4vw, 1.3rem);
    margin-bottom: 40px;
    font-weight: 300;
    opacity: 0.95;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.hero-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Category Cards */
.category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
}

.category-card {
    position: relative;
    height: 400px;
    background-size: cover;
    background-position: center;
    border-radius: 15px;
    overflow: hidden;
    display: flex;
    align-items: flex-end;
    text-decoration: none;
    color: white;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.category-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.9), rgba(0,0,0,0.2));
    transition: opacity 0.4s ease;
    z-index: 1; /* Assure que le gradient passe AU-DESSUS de la vidéo */
}

.category-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(212, 175, 55, 0.2);
}

.category-card:hover::before {
    opacity: 0.7;
}

.category-card-content {
    position: relative;
    z-index: 2;
    padding: 30px;
    width: 100%;
}

.category-card h3 {
    font-size: 2rem;
    margin-bottom: 10px;
    font-family: 'Playfair Display', serif;
    transform: translateY(0);
    transition: transform 0.3s ease;
}

.category-card:hover h3 {
    transform: translateY(-5px);
}

.category-link {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: #d4af37;
    font-weight: 600;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
}

.category-card:hover .category-link {
    opacity: 1;
    transform: translateY(0);
}

.category-link i {
    font-size: 0.9rem;
    transition: transform 0.3s ease;
}

.category-link:hover i {
    transform: translateX(5px);
}

/* Product Cards avec promotions */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
}

.product-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.03);
    transition: all 0.3s ease;
    position: relative;
    border: 1px solid #f0f0f0;
}

.product-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(212, 175, 55, 0.1);
    border-color: #d4af37;
}

.product-badges {
    position: absolute;
    top: 15px;
    left: 15px;
    z-index: 10;
    display: flex;
    gap: 10px;
}

.badge {
    padding: 5px 15px;
    border-radius: 25px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.badge-new {
    background: #d4af37;
    color: black;
}

.badge-promo {
    background: #e74c3c;
    color: white;
}

.product-image-wrapper {
    position: relative;
    overflow: hidden;
    aspect-ratio: 1/1;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.product-card:hover .product-image {
    transform: scale(1.1);
}

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.product-quick-view {
    color: white;
    border: 2px solid #d4af37;
    padding: 10px 20px;
    border-radius: 30px;
    font-size: 0.9rem;
    font-weight: 600;
    transform: translateY(20px);
    transition: transform 0.3s ease;
}

.product-card:hover .product-quick-view {
    transform: translateY(0);
}

.product-details {
    padding: 20px;
    text-align: center;
}

.product-title {
    font-size: 1.2rem;
    margin-bottom: 10px;
    color: #333;
    font-weight: 600;
}

.product-price-wrapper {
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.old-price {
    color: #999;
    font-size: 1rem;
    text-decoration: line-through;
}

.new-price {
    color: #e74c3c;
    font-size: 1.3rem;
    font-weight: 700;
}

.regular-price {
    color: #d4af37;
    font-size: 1.3rem;
    font-weight: 700;
}

.btn-full {
    width: 100%;
    box-sizing: border-box;
}

/* Services Section */
.services-section {
    background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
    color: white;
    padding: 80px 0;
    margin-top: 50px;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 40px;
}

.service-item {
    text-align: center;
    padding: 30px;
    border-radius: 15px;
    transition: all 0.3s ease;
}

.service-item:hover {
    transform: translateY(-10px);
    background: rgba(212, 175, 55, 0.05);
}

.service-icon-wrapper {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: rgba(212, 175, 55, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.service-item:hover .service-icon-wrapper {
    background: #d4af37;
    transform: rotate(360deg);
}

.service-icon {
    color: #d4af37;
    font-size: 2rem;
    transition: all 0.3s ease;
}

.service-item:hover .service-icon {
    color: black;
}

.service-title {
    color: #d4af37;
    font-size: 1.2rem;
    margin-bottom: 15px;
    font-family: 'Playfair Display', serif;
}

.service-description {
    font-size: 0.95rem;
    opacity: 0.8;
    line-height: 1.6;
}

/* Responsive */
@media (max-width: 992px) {
    .services-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .category-grid {
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    }
}

@media (max-width: 768px) {
    .services-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .product-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .category-card {
        height: 300px;
    }
    
    .section-subtitle {
        font-size: 0.9rem;
        letter-spacing: 2px;
    }
}

@media (max-width: 480px) {
    .product-grid {
        grid-template-columns: 1fr;
        max-width: 300px;
        margin: 0 auto;
    }
    
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-subtitle {
        font-size: 0.9rem;
        letter-spacing: 2px;
    }
    
    .category-card {
        height: 250px;
    }
    
    .service-item {
        padding: 20px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>