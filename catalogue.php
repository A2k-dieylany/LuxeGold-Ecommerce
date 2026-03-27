<?php 
require_once 'config/db.php';
require_once 'includes/fonctions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Récupération des paramètres (avec 'trim' pour nettoyer les espaces invisibles)
$parPage = 12;
$pageCourante = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($pageCourante <= 0) $pageCourante = 1;
$offset = ($pageCourante - 1) * $parPage;

$search = isset($_GET['q']) ? trim($_GET['q']) : ''; // Nettoyage des espaces
$cat_id = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

// Prix sécurisé (évite le bug du zéro)
$min_prix = (isset($_GET['min']) && is_numeric($_GET['min'])) ? (int)$_GET['min'] : 0;
$max_prix = (isset($_GET['max']) && is_numeric($_GET['max'])) ? (int)$_GET['max'] : 10000000;

$metal = isset($_GET['metal']) ? trim($_GET['metal']) : '';

// 2. Récupération des catégories actives
$stmtCat = $pdo->query("SELECT * FROM categories WHERE deleted = 0 ORDER BY nom ASC");
$categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

// 3. CONSTRUCTION SÉCURISÉE ET INTELLIGENTE DE LA REQUÊTE
$sqlConditions = " AND deleted = 0"; 
$params = [];

if ($cat_id > 0) {
    $sqlConditions .= " AND id_categorie = ?"; 
    $params[] = $cat_id;
}

if (!empty($search)) {
    // Utilisation de LIKE pour trouver le mot même s'il est au milieu d'une phrase
    $sqlConditions .= " AND (nom LIKE ? OR description LIKE ?)";
    $params[] = "%" . $search . "%";
    $params[] = "%" . $search . "%";
}

if ($min_prix > 0 || $max_prix < 10000000) {
    $sqlConditions .= " AND prix >= ? AND prix <= ?";
    $params[] = $min_prix;
    $params[] = $max_prix;
}

if (!empty($metal)) {
    // L'astuce magique : LIKE au lieu de '=' pour ignorer les erreurs de casse ou d'espaces dans la BDD
    $sqlConditions .= " AND metal LIKE ?";
    $params[] = "%" . $metal . "%";
}

// A. Calculer le nombre total de pages
$sqlCount = "SELECT COUNT(*) FROM produits WHERE 1=1" . $sqlConditions;
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$totalArticles = $stmtCount->fetchColumn();
$totalPages = ceil($totalArticles / $parPage);

// B. Récupérer les produits
$sql = "SELECT *, reduction FROM produits WHERE 1=1" . $sqlConditions . " ORDER BY created_at DESC LIMIT $parPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Récupération des IDs en wishlist
$session_id = session_id();
$wishlist_stmt = $pdo->prepare("SELECT produit_id FROM wishlist WHERE session_id = ?");
$wishlist_stmt->execute([$session_id]);
$wishlist_ids = $wishlist_stmt->fetchAll(PDO::FETCH_COLUMN);

include 'includes/header.php'; 
?>

<main class="catalogue-page">
    <div class="container">
        <div class="catalogue-header">
            <h1 class="catalogue-title"><?= $lang['cat_title'] ?? 'Collection Complète' ?></h1>
            <?php if($cat_id > 0): 
                $cat_nom = '';
                foreach($categories as $c) {
                    if($c['id'] == $cat_id) $cat_nom = $c['nom'];
                }
            ?>
                <p class="catalogue-subtitle"><?= htmlspecialchars($cat_nom) ?></p>
            <?php endif; ?>
        </div>

        <section class="filters-section">
            <div class="filters-container">
                <div class="filters-header" onclick="toggleFilters(this)">
                    <span class="filters-title">
                        <i class="fas fa-sliders-h"></i> 
                        <?= $lang['cat_filter_btn'] ?? 'Filtrer' ?>
                    </span>
                    <span class="filters-toggle">
                        <i class="fas fa-chevron-down"></i>
                    </span>
                </div>

                <form action="catalogue.php" method="GET" class="filters-form">
                    <?php if($cat_id > 0): ?>
                        <input type="hidden" name="cat" value="<?= $cat_id ?>">
                    <?php endif; ?>

                    <div class="filters-grid">
                        <div class="filter-group">
                            <label class="filter-label" for="search-input">
                                <i class="fas fa-search"></i> <?= ($_SESSION['lang'] == 'en') ? 'Search' : 'Recherche' ?>
                            </label>
                            <input type="text" id="search-input" name="q" class="filter-input" placeholder="<?= $lang['cat_search_placeholder'] ?? 'Chercher un bijou...' ?>" value="<?= htmlspecialchars($search) ?>">
                        </div>

                        <div class="filter-group">
                            <label class="filter-label" for="metal-select">
                                <i class="fas fa-ring"></i> <?= ($_SESSION['lang'] == 'en') ? 'Metal' : 'Métal' ?>
                            </label>
                            <select name="metal" id="metal-select" class="filter-select">
                                <option value=""><?= ($_SESSION['lang'] == 'en') ? 'All metals' : 'Tous les métaux' ?></option>
                                <option value="Or" <?= $metal == 'Or' ? 'selected' : '' ?>><?= ($_SESSION['lang'] == 'en') ? 'Gold' : 'Or' ?></option>
                                <option value="Argent" <?= $metal == 'Argent' ? 'selected' : '' ?>><?= ($_SESSION['lang'] == 'en') ? 'Silver' : 'Argent' ?></option>
                                <option value="Platine" <?= $metal == 'Platine' ? 'selected' : '' ?>><?= ($_SESSION['lang'] == 'en') ? 'Platinum' : 'Platine' ?></option>
                            </select>
                        </div>

                        <div class="filter-group filter-price">
                            <label class="filter-label">
                                <i class="fas fa-tag"></i> <?= ($_SESSION['lang'] == 'en') ? 'Price' : 'Prix' ?> (FCFA)
                            </label>
                            <div class="price-inputs">
                                <div class="price-input-wrapper">
                                    <span class="price-symbol"><?= $lang['cat_price_min'] ?? 'Min' ?></span>
                                    <input type="number" name="min" class="filter-input price-input" placeholder="0" value="<?= $min_prix > 0 ? $min_prix : '' ?>">
                                </div>
                                <span class="price-separator">-</span>
                                <div class="price-input-wrapper">
                                    <span class="price-symbol"><?= $lang['cat_price_max'] ?? 'Max' ?></span>
                                    <input type="number" name="max" class="filter-input price-input" placeholder="10M" value="<?= $max_prix < 10000000 ? $max_prix : '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="filters-actions">
                        <button type="submit" class="btn btn-gold">
                            <i class="fas fa-filter"></i> <?= ($_SESSION['lang'] == 'en') ? 'Apply' : 'Appliquer' ?>
                        </button>
                        <?php if(!empty($search) || $min_prix > 0 || $max_prix < 10000000 || !empty($metal)): ?>
                            <a href="catalogue.php<?= $cat_id > 0 ? '?cat='.$cat_id : '' ?>" class="btn-reset">
                                <i class="fas fa-times"></i> <?= $lang['cat_reset'] ?? 'Réinitialiser' ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </section>

        <div class="products-section">
            <?php if(count($produits) > 0): ?>
                <div class="product-grid">
                    <?php foreach($produits as $p): 
                        $in_wishlist = in_array($p['id'], $wishlist_ids);
                        $reduction = (int)($p['reduction'] ?? 0);
                        $aUneReduc = ($reduction > 0);
                        $prixFinal = $aUneReduc ? ($p['prix'] * (1 - ($reduction / 100))) : $p['prix'];
                    ?>
                    <div class="product-card" data-product-id="<?= $p['id'] ?>">
                        <div class="product-badges">
                            <?php if($aUneReduc): ?>
                                <span class="badge badge-promo">-<?= $reduction ?>%</span>
                            <?php endif; ?>
                            <?php if($p['stock'] < 1): ?>
                                <span class="badge badge-soldout"><?= $lang['prod_stock_low'] ?? 'Rupture' ?></span>
                            <?php endif; ?>
                        </div>

                        <button class="wishlist-btn <?= $in_wishlist ? 'active' : '' ?>" onclick="toggleWishlist(<?= $p['id'] ?>, this)" aria-label="Ajouter aux favoris">
                            <i class="<?= $in_wishlist ? 'fas' : 'far' ?> fa-heart"></i>
                        </button>

                        <a href="produit.php?id=<?= $p['id'] ?>" class="product-link">
                            <div class="product-image-wrapper">
                                <img src="assets/img/produits/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['nom']) ?>" class="product-image" loading="lazy">
                                
                                <?php if(!empty($p['modele_3d']) || !empty($p['video'])): ?>
                                    <div style="position: absolute; bottom: 10px; right: 10px; display: flex; gap: 5px; z-index: 5;">
                                        <?php if(!empty($p['video'])): ?>
                                            <span style="background: rgba(26,26,26,0.8); color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; backdrop-filter: blur(2px);" title="Vidéo disponible">
                                                <i class="fas fa-play"></i>
                                            </span>
                                        <?php endif; ?>
                                        <?php if(!empty($p['modele_3d'])): ?>
                                            <span style="background: rgba(212,175,55,0.9); color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; backdrop-filter: blur(2px); box-shadow: 0 2px 5px rgba(0,0,0,0.2);" title="Vue 3D 360°">
                                                <i class="fas fa-cube"></i> 3D
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="product-overlay"><span class="quick-view"><?= ($_SESSION['lang'] == 'en') ? 'Quick view' : 'Aperçu rapide' ?></span></div>
                            </div>
                        </a>
                        
                        <div class="product-details">
                            <h3 class="product-title"><?= htmlspecialchars($p['nom']) ?></h3>
                            <div class="product-price-wrapper">
                                <?php if($aUneReduc): ?>
                                    <span class="old-price"><?= formaterPrix($p['prix']) ?></span>
                                    <span class="new-price"><?= formaterPrix($prixFinal) ?></span>
                                <?php else: ?>
                                    <span class="regular-price"><?= formaterPrix($p['prix']) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div style="display: flex; gap: 10px; margin-top: 10px;">
                                <a href="produit.php?id=<?= $p['id'] ?>" class="btn btn-gold-outline btn-small" style="flex: 1; padding: 10px 0; text-align: center;">
                                    <i class="fas fa-eye"></i> <?= $lang['cat_details'] ?? 'Détails' ?>
                                </a>
                                <?php if($p['stock'] > 0): ?>
                                    <button onclick="ajouterPanierAJAX(<?= $p['id'] ?>, event)" class="btn btn-gold btn-small" style="flex: 1; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 5px;">
                                        <i class="fas fa-cart-plus"></i> <?= $lang['btn_add'] ?? 'Ajouter' ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                            </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                <div class="pagination-container" style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 50px;">
                    <?php 
                        // Générer l'URL de base pour garder les filtres actifs quand on change de page
                        $urlParams = $_GET;
                        unset($urlParams['p']); // On enlève la page actuelle de l'URL
                        $baseUrl = 'catalogue.php?' . http_build_query($urlParams) . (!empty($urlParams) ? '&' : '');
                    ?>

                    <?php if ($pageCourante > 1): ?>
                        <a href="<?= $baseUrl ?>p=<?= $pageCourante - 1 ?>" style="padding: 10px 15px; border: 1px solid #d4af37; color: #1a1a1a; text-decoration: none; border-radius: 5px; transition: 0.3s;" onmouseover="this.style.background='#d4af37'; this.style.color='white';" onmouseout="this.style.background='transparent'; this.style.color='#1a1a1a';">
                            <i class="fas fa-chevron-left"></i> <?= $lang['pagination_prev'] ?? 'Précédent' ?>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="<?= $baseUrl ?>p=<?= $i ?>" style="padding: 10px 18px; text-decoration: none; border-radius: 5px; border: 1px solid #d4af37; font-weight: bold; transition: 0.3s; <?= ($i == $pageCourante) ? 'background: #d4af37; color: white;' : 'background: white; color: #d4af37;' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($pageCourante < $totalPages): ?>
                        <a href="<?= $baseUrl ?>p=<?= $pageCourante + 1 ?>" style="padding: 10px 15px; border: 1px solid #d4af37; color: #1a1a1a; text-decoration: none; border-radius: 5px; transition: 0.3s;" onmouseover="this.style.background='#d4af37'; this.style.color='white';" onmouseout="this.style.background='transparent'; this.style.color='#1a1a1a';">
                            <?= $lang['pagination_next'] ?? 'Suivant' ?> <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-results">
                    <div class="no-results-icon"><i class="fas fa-gem"></i></div>
                    <h3><?= ($_SESSION['lang'] == 'en') ? 'No jewelry found' : 'Aucun bijou trouvé' ?></h3>
                    <p><?= $lang['cat_empty'] ?? 'Aucun bijou ne correspond à vos critères de recherche.' ?></p>
                    <a href="catalogue.php" class="btn btn-gold">
                        <i class="fas fa-arrow-left"></i> <?= $lang['cat_back'] ?? 'Retour au catalogue' ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
/* ... (Garde exactement tout ton CSS ici, je ne l'ai pas touché pour ne pas casser ton design) ... */
.catalogue-page { padding: 40px 0; background: #fafafa; min-height: 80vh; }
.catalogue-header { text-align: center; margin-bottom: 40px; }
.catalogue-title { font-family: 'Playfair Display', serif; font-size: 2.5rem; color: #000; margin-bottom: 10px; position: relative; display: inline-block; }
.catalogue-title::after { content: ''; position: absolute; bottom: -10px; left: 50%; transform: translateX(-50%); width: 60px; height: 2px; background: #d4af37; }
.catalogue-subtitle { color: #d4af37; font-size: 1.1rem; margin-top: 20px; font-weight: 500; }
.filters-section { margin-bottom: 40px; }
.filters-container { background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.03); border: 1px solid #eee; overflow: hidden; }
.filters-header { background: #f8f8f8; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; border-bottom: 1px solid #eee; transition: background 0.3s ease; }
.filters-header:hover { background: #f0f0f0; }
.filters-title { font-weight: 600; color: #333; display: flex; align-items: center; gap: 10px; }
.filters-title i { color: #d4af37; }
.filters-toggle { transition: transform 0.3s ease; }
.filters-container.collapsed .filters-toggle { transform: rotate(180deg); }
.filters-form { padding: 25px; background: white; max-height: 1000px; transition: all 0.3s ease; }
.filters-container.collapsed .filters-form { max-height: 0; padding: 0 25px; overflow: hidden; }
.filters-grid { display: grid; grid-template-columns: 2fr 1fr 2fr; gap: 20px; margin-bottom: 20px; }
.filter-group { display: flex; flex-direction: column; gap: 8px; }
.filter-label { font-weight: 600; font-size: 0.9rem; color: #666; display: flex; align-items: center; gap: 5px; }
.filter-label i { color: #d4af37; font-size: 0.9rem; }
.filter-input, .filter-select { width: 100%; padding: 12px 15px; border: 2px solid #eee; border-radius: 10px; font-size: 0.95rem; transition: all 0.3s ease; background: white; }
.filter-input:focus, .filter-select:focus { outline: none; border-color: #d4af37; box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1); }
.filter-price { grid-column: auto; }
.price-inputs { display: flex; align-items: center; gap: 10px; }
.price-input-wrapper { flex: 1; position: relative; }
.price-symbol { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999; font-size: 0.8rem; font-weight: 600; }
.price-input { padding-left: 40px !important; }
.price-separator { color: #d4af37; font-weight: bold; }
.filters-actions { display: flex; justify-content: flex-end; gap: 15px; align-items: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
.btn-reset { color: #999; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 5px; transition: color 0.3s ease; }
.btn-reset:hover { color: #d4af37; }
.products-section { margin-top: 30px; }
.product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; }
.product-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.02); transition: all 0.3s ease; position: relative; border: 1px solid #f0f0f0; animation: fadeInUp 0.6s ease forwards; opacity: 0; }
.product-card:nth-child(1) { animation-delay: 0.1s; } .product-card:nth-child(2) { animation-delay: 0.2s; } .product-card:nth-child(3) { animation-delay: 0.3s; } .product-card:nth-child(4) { animation-delay: 0.4s; } .product-card:nth-child(5) { animation-delay: 0.5s; } .product-card:nth-child(6) { animation-delay: 0.6s; } .product-card:nth-child(7) { animation-delay: 0.7s; } .product-card:nth-child(8) { animation-delay: 0.8s; }
.product-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(212, 175, 55, 0.1); border-color: #d4af37; }
.product-badges { position: absolute; top: 15px; left: 15px; z-index: 10; display: flex; gap: 10px; }
.badge { padding: 5px 15px; border-radius: 25px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); background: white; color: #333; }
.badge-promo { background: #e74c3c; color: white; }
.badge-soldout { background: #666; color: white; }
.wishlist-btn { position: absolute; top: 15px; right: 15px; background: white; border: none; border-radius: 50%; width: 40px; height: 40px; cursor: pointer; z-index: 10; box-shadow: 0 3px 10px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; border: 1px solid #eee; }
.wishlist-btn:hover { transform: scale(1.1); border-color: #ff4d4d; }
.wishlist-btn.active { background: #ff4d4d; border-color: #ff4d4d; }
.wishlist-btn.active i { color: white !important; }
.wishlist-btn i { color: #ff4d4d; font-size: 1.2rem; transition: all 0.3s ease; }
.product-image-wrapper { position: relative; overflow: hidden; aspect-ratio: 1/1; }
.product-image { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
.product-card:hover .product-image { transform: scale(1.1); }
.product-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease; }
.product-card:hover .product-overlay { opacity: 1; }
.quick-view { color: white; border: 2px solid #d4af37; padding: 8px 20px; border-radius: 30px; font-size: 0.9rem; font-weight: 500; transform: translateY(20px); transition: transform 0.3s ease; background: rgba(0,0,0,0.5); }
.product-card:hover .quick-view { transform: translateY(0); }
.product-details { padding: 20px; text-align: center; }
.product-title { font-size: 1.1rem; margin-bottom: 10px; color: #333; font-weight: 600; }
.product-price-wrapper { margin-bottom: 15px; display: flex; align-items: center; justify-content: center; gap: 10px; }
.old-price { color: #999; font-size: 1rem; text-decoration: line-through; }
.new-price { color: #e74c3c; font-size: 1.3rem; font-weight: 700; }
.regular-price { color: #d4af37; font-size: 1.3rem; font-weight: 700; }
.btn-full { width: 100%; box-sizing: border-box; }
.no-results { text-align: center; padding: 60px 20px; background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.02); border: 1px solid #eee; }
.no-results-icon { font-size: 4rem; color: #d4af37; margin-bottom: 20px; opacity: 0.5; }
.no-results h3 { font-size: 1.5rem; color: #333; margin-bottom: 10px; font-family: 'Playfair Display', serif; }
.no-results p { color: #999; margin-bottom: 30px; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
@media (max-width: 992px) { .filters-grid { grid-template-columns: 1fr; } .filter-price { grid-column: auto; } }
@media (max-width: 768px) { .catalogue-title { font-size: 2rem; } .filters-actions { flex-direction: column; } .btn-reset { align-self: center; } .price-inputs { flex-direction: column; } .price-separator { display: none; } }
@media (max-width: 480px) { .product-grid { grid-template-columns: 1fr; max-width: 300px; margin: 0 auto; } .catalogue-title { font-size: 1.8rem; } .filters-header { padding: 12px 15px; } .filters-form { padding: 15px; } }
</style>

<script>
function toggleFilters(header) { const container = header.closest('.filters-container'); container.classList.toggle('collapsed'); }
document.addEventListener('DOMContentLoaded', function() { 
    const searchResults = document.getElementById('search-results'); 
    if (searchResults) { 
        document.addEventListener('click', function(e) { 
            if (!e.target.closest('.search-container')) { searchResults.style.display = 'none'; } 
        }); 
    } 
});

// ========================================================
// NOUVEAU : MAGIE AJAX POUR PANIER ET FAVORIS (ZÉRO BUG)
// ========================================================

// 1. Fonction pour afficher la notification (Toast)
function afficherToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if(!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.position = 'fixed';
        container.style.bottom = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.gap = '10px';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.style.background = type === 'success' ? '#1a1a1a' : '#e74c3c';
    toast.style.color = type === 'success' ? '#d4af37' : '#fff';
    toast.style.padding = '15px 25px';
    toast.style.borderRadius = '10px';
    toast.style.boxShadow = '0 10px 30px rgba(0,0,0,0.2)';
    toast.style.fontFamily = "'Poppins', sans-serif";
    toast.style.fontSize = '0.9rem';
    toast.style.fontWeight = '600';
    toast.style.display = 'flex';
    toast.style.alignItems = 'center';
    toast.style.gap = '10px';
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(20px)';
    toast.style.transition = 'all 0.3s ease';

    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
    container.appendChild(toast);

    // Animation d'entrée
    setTimeout(() => { toast.style.opacity = '1'; toast.style.transform = 'translateY(0)'; }, 10);
    
    // Disparition automatique après 3 secondes
    setTimeout(() => {
        toast.style.opacity = '0'; toast.style.transform = 'translateY(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// 2. Fonction AJAX pour Ajouter au Panier (Version Finale)
function ajouterPanierAJAX(id_produit, event) {
    if(event) { event.preventDefault(); event.stopPropagation(); }

    const formData = new FormData();
    formData.append('produit_id', id_produit);
    formData.append('quantite', 1);

    fetch('api/panier.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            // On cible spécifiquement la classe de ton header
            const cartBadge = document.querySelector('.cart-badge');
            if (cartBadge) {
                cartBadge.innerText = data.total;
                
                // Animation de pulsation
                cartBadge.style.transition = '0.3s';
                cartBadge.style.transform = 'scale(1.8)';
                setTimeout(() => {
                    cartBadge.style.transform = 'scale(1)';
                }, 300);
            }
            // Affiche ton joli message de confirmation dynamique
            const successMsg = "<?= ($_SESSION['lang'] == 'en') ? 'Jewelry added to cart! 🛍️' : 'Bijou ajouté au panier ! 🛍️' ?>";
            afficherToast(successMsg, "success");
        }
    })
    .catch(err => console.error("Erreur de mise à jour du panier:", err));
}
// 3. Correction de la fonction Wishlist pour l'incrémentation réelle
window.toggleWishlist = function(id, btn) {
    const icon = btn.querySelector('i');
    // On cherche le badge par son ID (défini dans ton header)
    const badge = document.getElementById('wish-count'); 
    
    if (!badge) {
        console.error("Erreur : Le badge 'wish-count' est introuvable dans le header.");
        return;
    }

    const formData = new FormData();
    formData.append('produit_id', id);

    fetch('api/wishlist.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        // On récupère la valeur actuelle (0 si le badge est vide ou contient du texte bizarre)
        let count = parseInt(badge.innerText.trim()) || 0;

        if (data.status === 'added') {
            // 1. Changement d'icône immédiat
            icon.classList.replace('far', 'fas');
            btn.classList.add('active');
            
            // 2. Incrémentation temps réel
            count++;
            badge.innerText = count;
            
            // 3. Animation de succès
            badge.style.display = 'flex'; // Au cas où il était caché
            badge.style.transform = 'scale(1.5)';
            setTimeout(() => { badge.style.transform = 'scale(1)'; }, 300);
            
            const msgAdded = "<?= ($_SESSION['lang'] == 'en') ? 'Added to favorites! ❤️' : 'Ajouté à vos favoris ! ❤️' ?>";
            afficherToast(msgAdded, "success");
            
        } else if (data.status === 'removed') {
            // 1. Changement d'icône immédiat
            icon.classList.replace('fas', 'far');
            btn.classList.remove('active');
            
            // 2. Décrémentation temps réel
            count = Math.max(0, count - 1);
            badge.innerText = count;
            
            const msgRemoved = "<?= ($_SESSION['lang'] == 'en') ? 'Removed from favorites 🤍' : 'Retiré de vos favoris 🤍' ?>";
            afficherToast(msgRemoved, "success");
        }
    })
    .catch(err => {
        console.error("Erreur Wishlist:", err);
        // En cas d'erreur, on ne change rien au compteur pour rester cohérent
    });
};
</script>

<?php include 'includes/footer.php'; ?>