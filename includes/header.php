<?php 
require_once 'config/db.php'; 

// --- DÉMARRAGE SÉCURISÉ DE LA SESSION ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- CHARGEMENT DU DICTIONNAIRE ---
$lang_file = (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') ? 'lang/en.php' : 'lang/fr.php';
include_once file_exists($lang_file) ? $lang_file : 'lang/fr.php';

// --- COMPTEUR DE WISHLIST ---
$session_id = session_id();
$stmtWish = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE session_id = ?");
$stmtWish->execute([$session_id]);
$wishCount = $stmtWish->fetchColumn();
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="<?= htmlspecialchars($config_site['nom_boutique']) ?> - Bijouterie de luxe au Sénégal et en Gambie">
    <title><?= htmlspecialchars($config_site['nom_boutique']) ?> - LuxeGold</title>
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    
    <style>
        /* =============================================
           STYLES DU HEADER - ORGANISÉS ET OPTIMISÉS
           ============================================= */
        
        /* --- Icônes utilisateur --- */
        .user-actions i {
            color: #ffffff !important;
            transition: color 0.3s ease, transform 0.3s ease;
            font-size: 1.3rem;
        }
        
        .user-actions i:hover {
            color: #d4af37 !important;
            transform: translateY(-2px);
        }

        /* Highlight de l'icône quand connecté */
        .user-actions .user-connected i {
            color: #d4af37 !important;
        }
        
        /* --- Badges de compteurs --- */
        .badge-count {
            position: absolute;
            top: -8px;
            right: -10px;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 50%;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
            transition: transform 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .badge-count:hover {
            transform: scale(1.1);
        }
        
        .badge-count.cart-badge {
            background-color: #d4af37 !important;
            color: #000 !important;
            border: 1px solid #000;
        }
        
        .badge-count.wish-badge {
            background-color: #ff4d4d !important;
            color: #fff !important;
            border: 1px solid #fff;
        }
        
        /* --- Texte du panier --- */
        .cart-text {
            color: #ffffff !important;
            font-size: 13px;
            margin-left: 5px;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .action-link:hover .cart-text {
            color: #d4af37 !important;
        }
        
        /* --- Barre de recherche --- */
        .search-container {
            position: relative;
        }
        
        #live-search {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(212,175,55,0.3);
            color: #fff;
            padding: 10px 20px;
            border-radius: 30px;
            width: 200px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }
        
        #live-search::placeholder {
            color: rgba(255,255,255,0.6);
            font-style: italic;
        }
        
        #live-search:focus {
            outline: none;
            border-color: #d4af37;
            background: rgba(255,255,255,0.15);
            width: 250px;
            box-shadow: 0 0 0 3px rgba(212,175,55,0.2);
        }
        
        /* --- Résultats de recherche --- */
        #search-results {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: white !important;
            border: 1px solid #ddd !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
            z-index: 99999 !important;
            max-height: 400px;
            overflow-y: auto;
            border-radius: 0 0 15px 15px;
            margin-top: 5px;
            animation: slideDown 0.3s ease;
        }
        
        .search-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            text-decoration: none;
            color: #333;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.3s ease;
        }
        
        .search-item:hover {
            background: #fafafa;
        }
        
        .search-item img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #eee;
        }
        
        .search-item-info {
            flex: 1;
        }
        
        .search-item-name {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 3px;
        }
        
        .search-item-price {
            color: #d4af37;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        /* --- Sélecteur de pays --- */
        .country-switcher {
            display: flex;
            gap: 15px;
            font-weight: 600;
        }
        
        .country-switcher a {
            color: #888;
            text-decoration: none;
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 20px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .country-switcher a.active {
            color: #d4af37 !important;
            background: rgba(212,175,55,0.1);
        }
        
        .country-switcher a:hover {
            color: #d4af37;
            background: rgba(212,175,55,0.05);
        }
        
        .country-switcher a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: #d4af37;
            transition: width 0.3s ease;
        }
        
        .country-switcher a:hover::after {
            width: 20px;
        }
        
        /* --- Séparateur --- */
        .header-divider {
            width: 1px;
            height: 30px;
            background: linear-gradient(to bottom, transparent, rgba(212,175,55,0.5), transparent);
            margin: 0 15px;
        }
        
        /* --- Animation --- */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* --- Responsive --- */
        @media (max-width: 992px) {
            #live-search {
                width: 150px;
            }
            
            #live-search:focus {
                width: 200px;
            }
        }
        
        @media (max-width: 768px) {
            .user-actions {
                gap: 12px !important;
            }
            
            #live-search {
                width: 120px;
                padding: 8px 12px;
            }
            
            #live-search:focus {
                width: 150px;
            }
            
            .cart-text {
                display: none;
            }
            
            .country-switcher a {
                font-size: 0.8rem;
                padding: 3px 6px;
            }
        }
        
        @media (max-width: 480px) {
            .user-actions {
                gap: 8px !important;
            }
            
            #live-search {
                width: 100px;
                font-size: 0.8rem;
            }
            
            .badge-count {
                font-size: 8px;
                min-width: 15px;
                padding: 2px 4px;
            }
            
            .country-switcher {
                gap: 8px;
            }
            
            .country-switcher a {
                font-size: 0.75rem;
            }
        }

        /* 1. On ancre le lien pour que le badge ne s'envole pas */
.user-actions .action-link {
    position: relative; /* CRUCIAL : C'est l'ancre du badge */
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* 2. On repositionne le badge avec précision sur l'icône */
.badge-count {
    position: absolute;
    top: -5px;      /* Ajuste pour descendre le chiffre */
    right: -8px;    /* Ajuste pour le décaler à droite de l'icône */
    background-color: #d4af37 !important;
    color: #000 !important;
    font-size: 11px;
    font-weight: bold;
    height: 18px;
    width: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #1a1a1a;
    z-index: 10;
}
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-container">
            
            <div class="logo">
                <a href="index.php" aria-label="Accueil <?= htmlspecialchars($config_site['nom_boutique']) ?>">
                <img src="assets/img/<?= htmlspecialchars($config_site['logo_filename'] ?? 'logo.png') ?>" alt="<?= htmlspecialchars($config_site['nom_boutique']) ?>" width="auto" height="50">                </a>
            </div>

            <nav class="main-nav" aria-label="Navigation principale">
                <ul class="nav-menu">
                    <li><a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>"><?= $lang['menu_home'] ?></a></li>
                    <li><a href="catalogue.php" class="<?= basename($_SERVER['PHP_SELF']) == 'catalogue.php' ? 'active' : '' ?>"><?= $lang['menu_collection'] ?></a></li>
                    <li><a href="contact.php" class="<?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>"><?= $lang['menu_contact'] ?></a></li>
                </ul>
            </nav>

            <div class="user-actions">
                
                <div class="search-container">
                    <input type="text" id="live-search" 
                           placeholder="Rechercher..." 
                           aria-label="Rechercher un produit"
                           title="Rechercher un produit">
                    <div id="search-results" role="listbox" aria-label="Résultats de recherche"></div>
                </div>

                <?php if (isset($_SESSION['client_id'])): ?>
                    <a href="mon_compte.php" class="action-link user-connected" aria-label="Mon compte" title="Mon Espace">
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <a href="connexion.php" class="action-link" aria-label="Se connecter" title="Se connecter">
                        <i class="fa-regular fa-user" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
                <a href="wishlist.php" class="action-link" aria-label="Ma liste de souhaits">
                    <i class="fa-solid fa-heart" aria-hidden="true"></i>
                   <span id="wish-count" class="badge-count wish-badge"><?= $wishCount ?></span>
                </a>

                <a href="panier.php" class="action-link" aria-label="Mon panier">
                    <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
                    <span class="badge-count cart-badge"><?= (isset($_SESSION['panier'])) ? array_sum($_SESSION['panier']) : 0; ?></span>
                </a>

                <div class="header-divider" aria-hidden="true"></div>

                <div class="country-switcher" role="group" aria-label="Sélection du pays">
                    <a href="switch_all.php?country=SN" 
                       class="<?= $_SESSION['devise'] == 'FCFA' ? 'active' : '' ?>"
                       title="Sénégal - FCFA">
                        SN
                    </a>
                    <a href="switch_all.php?country=GM" 
                       class="<?= $_SESSION['devise'] == 'GMD' ? 'active' : '' ?>"
                       title="Gambie - GMD">
                        GM
                    </a>
                </div>
            </div>

            <button class="mobile-menu-toggle" aria-label="Menu" aria-expanded="false">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </header>
<script>
// ==========================================
// 1. FONCTION NOTIFICATION (TOAST)
// ==========================================
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

    setTimeout(() => { toast.style.opacity = '1'; toast.style.transform = 'translateY(0)'; }, 10);
    
    setTimeout(() => {
        toast.style.opacity = '0'; toast.style.transform = 'translateY(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ==========================================
// 2. WISHLIST AJAX
// ==========================================
window.toggleWishlist = function(id, btn, event) {
    if(event) { event.preventDefault(); event.stopPropagation(); }
    
    const icon = btn.querySelector('i');
    const badge = document.getElementById('wish-count'); 

    const formData = new FormData();
    formData.append('produit_id', id);

    fetch('api/wishlist.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'added') {
            icon.classList.replace('far', 'fas');
            if(btn.classList) btn.classList.add('active');
            afficherToast("Ajouté à vos favoris ! ❤️", "success");
        } else if (data.status === 'removed') {
            icon.classList.replace('fas', 'far');
            if(btn.classList) btn.classList.remove('active');
            afficherToast("Retiré de vos favoris 🤍", "success");
        }

        if (badge && data.count !== undefined) {
            badge.innerText = data.count;
            badge.style.display = 'inline-block';
            badge.style.transform = 'scale(1.5)';
            setTimeout(() => { badge.style.transform = 'scale(1)'; }, 300);
        }
    })
    .catch(err => console.error("Erreur Wishlist:", err));
};

// ==========================================
// 3. PANIER AJAX
// ==========================================
window.ajouterPanierAJAX = function(id_produit, event) {
    if(event) { event.preventDefault(); event.stopPropagation(); }

    const formData = new FormData();
    formData.append('produit_id', id_produit);
    formData.append('quantite', 1);

    fetch('api/panier.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            const cartBadge = document.querySelector('.cart-badge');
            if (cartBadge) {
                cartBadge.innerText = data.total;
                cartBadge.style.transform = 'scale(1.5)';
                cartBadge.style.backgroundColor = '#fff';
                setTimeout(() => {
                    cartBadge.style.transform = 'scale(1)';
                    cartBadge.style.backgroundColor = '#d4af37';
                }, 300);
            }
            afficherToast("Bijou ajouté au panier ! 🛍️", "success");
        }
    })
    .catch(err => console.error("Erreur panier:", err));
};

// ==========================================
// 4. RECHERCHE LIVE & MENU MOBILE
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('live-search');
    const resultsBox = document.getElementById('search-results');

    if(searchInput) {
        searchInput.addEventListener('keyup', function() {
            let query = this.value.trim();
            if (query.length > 1) {
                fetch('api/search.php?q=' + encodeURIComponent(query))
                    .then(res => res.json())
                    .then(data => {
                        resultsBox.innerHTML = '';
                        if (data.length > 0) {
                            resultsBox.style.display = 'block';
                            data.forEach(item => {
                                resultsBox.innerHTML += `
                                    <a href="produit.php?id=${item.id}" class="search-item">
                                        <img src="assets/img/produits/${item.image}" alt="${item.nom}">
                                        <div class="search-item-info">
                                            <div class="search-item-name">${item.nom}</div>
                                            <div class="search-item-price">${item.prix_formate}</div>
                                        </div>
                                    </a>`;
                            });
                        } else { 
                            resultsBox.style.display = 'none'; 
                        }
                    });
            } else { 
                resultsBox.style.display = 'none'; 
            }
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
                resultsBox.style.display = 'none';
            }
        });
    }

    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    
    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            mainNav.classList.toggle('active');
            document.body.classList.toggle('menu-open');
        });
    }
});
</script>