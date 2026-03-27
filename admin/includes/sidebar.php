<?php
// On récupère le nom du fichier actuel (ex: "dashboard.php" ou "ajouter_admin.php")
$page_active = basename($_SERVER['PHP_SELF']);
?>

<aside class="admin-sidebar">
    <div class="sidebar-header">
        <i class="fas fa-gem" style="color: #d4af37; font-size: 1.5rem;"></i>
        <span class="logo-text"><?= htmlspecialchars($config_site['nom_boutique'] ?? 'Jewelry Beug Fallou') ?></span>
    </div>

    <nav class="sidebar-nav">
        <p class="nav-title">GÉNÉRAL</p>
        
        <a href="dashboard.php" class="nav-link <?= ($page_active == 'dashboard.php') ? 'active' : '' ?>">
            <i class="fas fa-home"></i> 
            <span>Tableau de Bord</span>
        </a>

        <a href="commandes.php" class="nav-link <?= ($page_active == 'commandes.php') ? 'active' : '' ?>">
            <i class="fas fa-shopping-bag"></i> 
            <span>Commandes</span>
        </a>

        <p class="nav-title">INVENTAIRE</p>

        <a href="ajouter_produit.php" class="nav-link <?= ($page_active == 'ajouter_produit.php') ? 'active' : '' ?>">
            <i class="fas fa-plus-circle"></i> 
            <span>Nouveau Bijou</span>
        </a>

        <p class="nav-title">SÉCURITÉ</p>

        <a href="ajouter_admin.php" class="nav-link <?= ($page_active == 'ajouter_admin.php') ? 'active' : '' ?>">
            <i class="fas fa-user-shield"></i> 
            <span>Administrateurs</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="nav-link logout-link">
            <i class="fas fa-sign-out-alt"></i> 
            <span>Déconnexion</span>
        </a>
        <div class="retour-site">
            <a href="../index.php" target="_blank"><i class="fas fa-globe"></i> Voir la boutique</a>
        </div>
    </div>
</aside>

<style>
    /* Structure de la sidebar (Fixe à gauche sur Ordinateur) */
    .admin-sidebar {
        width: 260px;
        height: 100vh;
        background-color: #1a1a1a;
        color: white;
        position: fixed;
        top: 0;
        left: 0;
        display: flex;
        flex-direction: column;
        box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        z-index: 1000;
        font-family: 'Poppins', sans-serif;
    }

    /* Logo et Titre */
    .sidebar-header {
        padding: 30px 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        border-bottom: 1px solid #333;
    }
    .sidebar-header .logo-text {
        font-family: 'Playfair Display', serif;
        font-size: 1.3rem;
        font-weight: bold;
        letter-spacing: 1px;
    }

    /* Navigation */
    .sidebar-nav {
        flex: 1; /* Prend tout l'espace disponible au milieu */
        padding: 20px 0;
        overflow-y: auto;
    }
    .nav-title {
        color: #666;
        font-size: 0.75rem;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin: 20px 25px 10px;
    }

    /* Liens du menu */
    .nav-link {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px 25px;
        color: #bbb;
        text-decoration: none;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }
    .nav-link i {
        font-size: 1.1rem;
        width: 20px;
        text-align: center;
    }
    
    /* Effet au survol */
    .nav-link:hover {
        color: white;
        background-color: #2a2a2a;
    }

    /* LA MAGIE DE LA PAGE ACTIVE */
    .nav-link.active {
        color: #d4af37;
        background-color: rgba(212, 175, 55, 0.1);
        border-left-color: #d4af37;
        font-weight: 600;
    }

    /* Footer de la Sidebar */
    .sidebar-footer {
        padding: 20px 0;
        border-top: 1px solid #333;
        background-color: #111;
    }
    .logout-link {
        color: #e74c3c;
    }
    .logout-link:hover {
        background-color: rgba(231, 76, 60, 0.1);
        color: #ff6b6b;
    }
    .retour-site {
        text-align: center;
        margin-top: 15px;
    }
    .retour-site a {
        color: #888;
        font-size: 0.8rem;
        text-decoration: none;
        transition: 0.3s;
    }
    .retour-site a:hover {
        color: #d4af37;
    }

    /* 📱 RESPONSIVE - Pour les téléphones */
    @media (max-width: 768px) {
        .admin-sidebar {
            width: 100%;
            height: auto;
            position: relative; /* Ne flotte plus, s'affiche en haut de page */
            flex-direction: column;
        }
        .sidebar-nav {
            display: flex;
            flex-wrap: wrap;
            padding: 10px;
            justify-content: space-between;
        }
        .nav-title { display: none; /* On cache les titres sur mobile pour gagner de la place */ }
        .nav-link {
            padding: 10px;
            font-size: 0.85rem;
            flex: 1;
            justify-content: center;
            flex-direction: column; /* Icône au-dessus du texte */
            gap: 5px;
            border-left: none;
            border-bottom: 2px solid transparent;
        }
        .nav-link.active {
            border-left: none;
            border-bottom-color: #d4af37;
            background: transparent;
        }
        .sidebar-footer { display: flex; justify-content: space-around; align-items: center; padding: 10px; }
    }
</style>




 