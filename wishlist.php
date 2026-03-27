<?php 
require_once 'config/db.php';
require_once 'includes/fonctions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$session_id = session_id();

// Récupérer les détails des produits aimés
$sql = "SELECT p.* FROM produits p 
        INNER JOIN wishlist w ON p.id = w.produit_id 
        WHERE w.session_id = ? 
        ORDER BY w.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$session_id]);
$favoris = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php'; 
?>

<main class="wishlist-page">
    <div class="container">
        
        <div class="wishlist-header">
            <h1 class="wishlist-title">
                <i class="fas fa-heart"></i>
                <?= ($_SESSION['lang'] == 'en') ? 'My Wishlist' : 'Mes Favoris' ?>
            </h1>
            <p class="wishlist-subtitle">
                <?= ($_SESSION['lang'] == 'en') ? 'Your loved pieces' : 'Vos coups de cœur' ?>
            </p>
        </div>
        
        <?php if (empty($favoris)): ?>
            <div class="empty-wishlist">
                <div class="empty-wishlist-icon">
                    <i class="far fa-heart"></i>
                </div>
                <h2><?= ($_SESSION['lang'] == 'en') ? 'Your wishlist is empty.' : 'Votre liste de favoris est vide.' ?></h2>
                <p><?= ($_SESSION['lang'] == 'en') ? 'Discover our collection and find your perfect match.' : 'Découvrez notre collection et trouvez la perle rare.' ?></p>
                <a href="catalogue.php" class="btn btn-gold btn-large mt-3" style="display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-gem"></i> <?= ($_SESSION['lang'] == 'en') ? 'Discover the collection' : 'Découvrir la collection' ?>
                </a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach($favoris as $p): 
                    $reduction = (int)($p['reduction'] ?? 0);
                    $aUneReduc = ($reduction > 0);
                    $prixFinal = $aUneReduc ? ($p['prix'] * (1 - ($reduction / 100))) : $p['prix'];
                ?>
                <div class="product-card" id="wish-card-<?= $p['id'] ?>">
                    
                    <button onclick="retirerFavoriDeLaPage(<?= $p['id'] ?>, this, event)" class="wishlist-btn" aria-label="Retirer des favoris">
                        <i class="fas fa-times" style="color: #666;"></i>
                    </button>

                    <a href="produit.php?id=<?= $p['id'] ?>" class="product-link">
                        <div class="product-image-wrapper">
                            <img src="assets/img/produits/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['nom']) ?>" class="product-image" loading="lazy">
                            <div class="product-overlay"><span class="quick-view"><?= ($_SESSION['lang'] == 'en') ? 'Quick View' : 'Aperçu rapide' ?></span></div>
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
                            
                            <?php if(($p['stock'] ?? 1) > 0): ?>
                                <button onclick="ajouterPanierAJAX(<?= $p['id'] ?>, event)" class="btn btn-gold btn-small" style="flex: 1; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 5px;">
                                    <i class="fas fa-cart-plus"></i> <?= $lang['add_to_cart'] ?? 'Ajouter' ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
/* =======================================================
   STYLES IDENTIQUES AU CATALOGUE POUR UNE COHÉRENCE TOTALE
   ======================================================= */
.wishlist-page { padding: 40px 0; background: #fafafa; min-height: 80vh; }
.wishlist-header { text-align: center; margin-bottom: 50px; }
.wishlist-title { font-family: 'Playfair Display', serif; font-size: 2.5rem; color: #000; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; gap: 15px; }
.wishlist-title i { color: #ff4d4d; }
.wishlist-subtitle { color: #888; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 2px; }

/* Bloc Favoris Vides */
.empty-wishlist { text-align: center; padding: 80px 20px; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #f0f0f0; max-width: 600px; margin: 0 auto; }
.empty-wishlist-icon { font-size: 5rem; color: #ff4d4d; margin-bottom: 30px; opacity: 0.3; }
.empty-wishlist h2 { font-family: 'Playfair Display', serif; font-size: 1.8rem; color: #333; margin-bottom: 15px; }
.empty-wishlist p { color: #999; margin-bottom: 30px; }

/* Grille de produits */
.product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; }
.product-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.02); transition: all 0.4s ease; position: relative; border: 1px solid #f0f0f0; }
.product-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(212, 175, 55, 0.1); border-color: #d4af37; }

/* Bouton Retirer */
.wishlist-btn { position: absolute; top: 15px; right: 15px; background: white; border: none; border-radius: 50%; width: 40px; height: 40px; cursor: pointer; z-index: 10; box-shadow: 0 3px 10px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; border: 1px solid #eee; }
.wishlist-btn:hover { transform: scale(1.1) rotate(90deg); background: #fee; border-color: #ff4d4d; }
.wishlist-btn:hover i { color: #ff4d4d !important; }

/* Image */
.product-image-wrapper { position: relative; overflow: hidden; aspect-ratio: 1/1; }
.product-image { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
.product-card:hover .product-image { transform: scale(1.1); }
.product-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease; }
.product-card:hover .product-overlay { opacity: 1; }
.quick-view { color: white; border: 2px solid #d4af37; padding: 8px 20px; border-radius: 30px; font-size: 0.9rem; font-weight: 500; transform: translateY(20px); transition: transform 0.3s ease; background: rgba(0,0,0,0.5); }
.product-card:hover .quick-view { transform: translateY(0); }

/* Détails */
.product-details { padding: 20px; text-align: center; }
.product-title { font-size: 1.1rem; margin-bottom: 10px; color: #333; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.product-price-wrapper { margin-bottom: 15px; display: flex; align-items: center; justify-content: center; gap: 10px; }
.old-price { color: #999; font-size: 1rem; text-decoration: line-through; }
.new-price { color: #e74c3c; font-size: 1.3rem; font-weight: 700; }
.regular-price { color: #d4af37; font-size: 1.3rem; font-weight: 700; }
</style>

<script>
/**
 * Animation fluide pour retirer un favori de la grille
 */
function retirerFavoriDeLaPage(id_produit, btn, event) {
    if(event) { event.preventDefault(); event.stopPropagation(); }
    
    // 1. On appelle la fonction AJAX globale pour dire à la BDD de l'effacer
    if(typeof window.toggleWishlist === 'function') {
        window.toggleWishlist(id_produit, btn, event);
    }

    // 2. On fait disparaître la carte doucement de l'écran
    const card = document.getElementById('wish-card-' + id_produit);
    if(card) {
        card.style.transform = 'scale(0.8)';
        card.style.opacity = '0';
        
        // On attend la fin de l'animation pour supprimer l'élément HTML
        setTimeout(() => {
            card.remove();
            
            // Si c'était le dernier produit de la liste, on recharge pour afficher le message "Panier vide"
            const remainingCards = document.querySelectorAll('.product-card');
            if(remainingCards.length === 0) {
                location.reload();
            }
        }, 400); // 400ms correspond au 'transition: all 0.4s ease'
    }
}
</script>

<?php include 'includes/footer.php'; ?>