<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="Administration - <?= htmlspecialchars($config_site['nom_boutique'] ?? 'Jewelry Beug Fallou') ?>">
    
    <title>Administration - <?= htmlspecialchars($config_site['nom_boutique'] ?? 'Jewelry Beug Fallou') ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <link rel="icon" type="image/png" href="../assets/img/favicon-admin.png">
    
    <style>
        /* ============================================
           STYLES DE BASE POUR L'ADMINISTRATION
           ============================================ */
        
        :root { 
            --gold: #d4af37; 
            --gold-light: #f5e7c8;
            --gold-dark: #b8960c;
            --black: #1a1a1a; 
            --black-light: #2d2d2d;
            --gray: #666; 
            --gray-light: #f4f7f6;
            --white: #ffffff;
            --danger: #e74c3c;
            --success: #27ae60;
            --warning: #f39c12;
            --info: #3498db;
            
            /* Ombres */
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
            --shadow-md: 0 5px 15px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
            --shadow-gold: 0 5px 20px rgba(212, 175, 55, 0.15);
            
            /* Espacements */
            --spacing-xs: 5px;
            --spacing-sm: 10px;
            --spacing-md: 15px;
            --spacing-lg: 20px;
            --spacing-xl: 25px;
            --spacing-xxl: 30px;
            
            /* Rayons */
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 15px;
            --radius-xxl: 20px;
        }
        
        body { 
            font-family: 'Poppins', sans-serif; 
            background: linear-gradient(135deg, #f4f7f6 0%, #e9ecef 100%);
            margin: 0; 
            min-height: 100vh;
            color: #333;
            line-height: 1.6;
        }
        
        /* Barre de navigation admin */
        .admin-navbar {
            background: linear-gradient(135deg, var(--black) 0%, var(--black-light) 100%);
            color: white;
            padding: 0 var(--spacing-xl);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-lg);
            border-bottom: 2px solid var(--gold);
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
        }
        
        /* Logo */
        .nav-logo {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .nav-logo img {
            height: 40px;
            width: auto;
            transition: transform 0.3s ease;
        }
        
        .nav-logo:hover img {
            transform: scale(1.05);
        }
        
        .nav-logo span {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--gold);
            letter-spacing: 1px;
        }
        
        /* Menu de navigation */
        .nav-menu {
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .nav-item {
            position: relative;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #fff;
            text-decoration: none;
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-md);
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        .nav-link i {
            color: var(--gold);
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }
        
        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .nav-link:hover i {
            transform: translateY(-2px);
        }
        
        .nav-link.active {
            background: rgba(212, 175, 55, 0.15);
            border-left: 3px solid var(--gold);
        }
        
        /* Badge de notification */
        .nav-badge {
            position: absolute;
            top: 0;
            right: -5px;
            background: var(--danger);
            color: white;
            font-size: 0.7rem;
            font-weight: bold;
            min-width: 18px;
            height: 18px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            box-shadow: 0 2px 5px rgba(231, 76, 60, 0.3);
        }
        
        /* Profil utilisateur */
        .nav-profile {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            padding-left: var(--spacing-lg);
            border-left: 1px solid rgba(255,255,255,0.1);
        }
        
        .profile-info {
            text-align: right;
        }
        
        .profile-name {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--gold);
        }
        
        .profile-role {
            display: block;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.6);
        }
        
        .profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--black);
            font-weight: bold;
            font-size: 1.2rem;
            border: 2px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }
        
        .profile-avatar:hover {
            transform: scale(1.1);
            border-color: var(--gold);
        }
        
        /* Menu mobile toggle */
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: var(--spacing-sm);
        }
        
        /* Breadcrumb */
        .breadcrumb {
            padding: var(--spacing-md) var(--spacing-xl);
            background: transparent;
            font-size: 0.9rem;
        }
        
        .breadcrumb-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--gray);
        }
        
        .breadcrumb a {
            color: var(--gray);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .breadcrumb a:hover {
            color: var(--gold);
        }
        
        .breadcrumb i {
            font-size: 0.7rem;
            color: var(--gold);
        }
        
        .breadcrumb-current {
            color: var(--gold);
            font-weight: 500;
        }
        
        /* Alertes */
        .admin-alert {
            max-width: 1400px;
            margin: var(--spacing-md) auto;
            padding: var(--spacing-md) var(--spacing-lg);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            animation: slideDown 0.5s ease;
        }
        
        .admin-alert.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .admin-alert.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .admin-alert.warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        
        .admin-alert.info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        
        /* Animations */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .nav-menu {
                gap: var(--spacing-sm);
            }
            
            .nav-link span:not(.nav-badge) {
                display: none;
            }
            
            .nav-link i {
                font-size: 1.3rem;
            }
            
            .profile-info {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .nav-container {
                flex-wrap: wrap;
                height: auto;
                padding: var(--spacing-sm) 0;
            }
            
            .mobile-toggle {
                display: block;
                order: 3;
            }
            
            .nav-menu {
                display: none;
                width: 100%;
                flex-direction: column;
                padding: var(--spacing-md) 0;
                order: 4;
            }
            
            .nav-menu.active {
                display: flex;
            }
            
            .nav-item {
                width: 100%;
            }
            
            .nav-link {
                width: 100%;
                justify-content: flex-start;
                padding: var(--spacing-md);
            }
            
            .nav-link span:not(.nav-badge) {
                display: inline;
            }
            
            .nav-profile {
                order: 2;
                border-left: none;
            }
            
            .nav-logo {
                order: 1;
            }
        }
    </style>
</head>
<body>
    <div class="admin-navbar">
        <div class="nav-container">
            <div class="nav-logo">
            <img src="../assets/img/<?= htmlspecialchars($config_site['logo_filename'] ?? 'logo.png') ?>" alt="<?= htmlspecialchars($config_site['nom_boutique'] ?? 'Jewelry Beug Fallou') ?>" style="height: 40px; width: auto;">            </div>
            
            <ul class="nav-menu" id="adminNavMenu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                        <i class="fas fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="commandes.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'commandes.php' ? 'active' : '' ?>">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Commandes</span>
                        <?php
                        // Vérifier les commandes en attente
                        try {
                            $stmt = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut = 'en_attente'");
                            $pendingOrders = $stmt->fetchColumn();
                            if ($pendingOrders > 0) {
                                echo '<span class="nav-badge">' . $pendingOrders . '</span>';
                            }
                        } catch (Exception $e) {}
                        ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="produits.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'produit') !== false ? 'active' : '' ?>">
                        <i class="fas fa-gem"></i>
                        <span>Produits</span>
                        <?php
                        // Vérifier les produits en stock faible
                        try {
                            $stmt = $pdo->query("SELECT COUNT(*) FROM produits WHERE stock <= 2 AND stock > 0");
                            $lowStock = $stmt->fetchColumn();
                            if ($lowStock > 0) {
                                echo '<span class="nav-badge" style="background: #f39c12;">' . $lowStock . '</span>';
                            }
                        } catch (Exception $e) {}
                        ?>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="categories.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'categorie') !== false ? 'active' : '' ?>">
                        <i class="fas fa-folder-open"></i>
                        <span>Catégories</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="clients.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'clients.php' ? 'active' : '' ?>">
                        <i class="fas fa-users"></i>
                        <span>Clients</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="gestion_avis.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'gestion_avis.php' ? 'active' : '' ?>">
                        <i class="fas fa-star"></i>
                        <span>Avis</span>
                        <?php
                        // Badge pour les avis en attente
                        try {
                            $stmtAvisBadge = $pdo->query("SELECT COUNT(*) FROM avis WHERE statut = 'en_attente'");
                            $pendingAvis = $stmtAvisBadge->fetchColumn();
                            if ($pendingAvis > 0) {
                                echo '<span class="nav-badge" style="background: #e74c3c;">' . $pendingAvis . '</span>';
                            }
                        } catch (Exception $e) {}
                        ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="promos.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'promos.php' ? 'active' : '' ?>">
                        <i class="fas fa-tag"></i>
                        <span>Promos</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="ajouter_admin.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'ajouter_admin.php' ? 'active' : '' ?>">
                        <i class="fas fa-user-shield"></i>
                        <span>Admins</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="parametres.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'parametres.php' ? 'active' : '' ?>">
                        <i class="fas fa-cog"></i>
                        <span>Paramètres</span>
                    </a>
                </li>
                
            </ul>
            
            <div class="nav-profile">
                <div class="profile-info">
                    <?php
                    $admin_nom = $_SESSION['admin_name'] ?? 'Admin';
                    $initial = strtoupper(substr($admin_nom, 0, 1));
                    ?>
                    <span class="profile-name"><?= htmlspecialchars($admin_nom) ?></span>
                    <span class="profile-role">Administrateur</span>
                </div>
                <div class="profile-avatar" title="<?= htmlspecialchars($admin_nom) ?>">
                    <?= $initial ?>
                </div>
            </div>
            
            <button class="mobile-toggle" onclick="toggleMobileMenu()" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
    
    <div class="breadcrumb">
        <div class="breadcrumb-container">
            <a href="dashboard.php"><i class="fas fa-home"></i> Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <?php
            // Fil d'Ariane dynamique basé sur la page courante
            $current_page = basename($_SERVER['PHP_SELF'], '.php');
            $page_titles = [
                'dashboard' => 'Tableau de bord',
                'liste' => 'Liste des produits',
                'ajouter' => 'Ajouter un produit',
                'modifier' => 'Modifier un produit',
                'categories' => 'Gestion des catégories',
                'ajouter_categorie' => 'Créer une catégorie',
                'modifier_categorie' => 'Modifier une catégorie',
                'commandes' => 'Gestion des commandes',
                'promos' => 'Codes promo',
                'clients' => 'Clients',
                'gestion_avis' => 'Modération des avis', // Ajout du titre pour le fil d'Ariane
                'ajouter_admin' => 'Administrateurs',
                'parametres' => 'Paramètres'
            ];
            $page_title = $page_titles[$current_page] ?? ucfirst($current_page);
            ?>
            <span class="breadcrumb-current"><?= htmlspecialchars($page_title) ?></span>
        </div>
    </div>
    
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="admin-alert <?= $_SESSION['flash']['type'] ?>">
            <i class="fas fa-<?= $_SESSION['flash']['type'] == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <span><?= htmlspecialchars($_SESSION['flash']['message']) ?></span>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
    
    <script>
    function toggleMobileMenu() {
        const menu = document.getElementById('adminNavMenu');
        const icon = document.querySelector('.mobile-toggle i');
        menu.classList.toggle('active');
        
        if (menu.classList.contains('active')) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-times');
        } else {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    }
    
    // Fermer le menu si on clique en dehors
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('adminNavMenu');
        const toggle = document.querySelector('.mobile-toggle');
        
        if (menu.classList.contains('active') && 
            !menu.contains(event.target) && 
            !toggle.contains(event.target)) {
            menu.classList.remove('active');
            const icon = document.querySelector('.mobile-toggle i');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    });
    </script>