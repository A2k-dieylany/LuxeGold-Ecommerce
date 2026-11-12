<?php 
// equipe.php - Page Notre Équipe / Our Team - LuxeGold (Jewelry Beug Fallou)
require_once 'config/db.php';
require_once 'includes/fonctions.php';
include 'includes/header.php'; 

// Détection de la langue active
$isEn = (isset($_SESSION['lang']) && $_SESSION['lang'] === 'en');

// Numéro WhatsApp de la boutique
$waPhoneRaw = $config_site['telephone_whatsapp'] ?? '2202657822';
$waPhone = preg_replace('/[^0-9]/', '', $waPhoneRaw);
if (empty($waPhone)) {
    $waPhone = '2202657822';
}

// Données de l'équipe LuxeGold
$teamMembers = [
    [
        'name' => 'Omar Ba',
        'initials' => 'OB',
        'role' => $isEn ? 'Founder' : 'Fondateur',
        'badge' => $isEn ? 'Vision & Leadership' : 'Vision & Leadership',
        'icon' => 'fa-crown',
        'bio' => $isEn 
            ? 'Visionary founder of LuxeGold, Omar Ba built a luxury jewelry house rooted in Senegambian heritage and modern excellence. His passion for gold craftsmanship drives every creation.'
            : 'Fondateur visionnaire de LuxeGold, Omar Ba a bâti une maison de joaillerie de luxe ancrée dans l\'héritage sénégambien et l\'excellence moderne. Sa passion pour l\'orfèvrerie guide chaque création.',
        'quote' => $isEn 
            ? 'Gold is not merely a noble metal, it is an emotion sculpted to withstand time.'
            : 'L\'or n\'est pas seulement un métal précieux, c\'est une émotion sculptée pour traverser le temps.',
        'whatsapp_msg' => $isEn 
            ? 'Hello, I would like to speak with the founder Omar Ba regarding a project.'
            : 'Bonjour, je souhaite échanger avec le fondateur Omar Ba au sujet d\'un projet.'
    ],
    [
        'name' => 'Mame Thierno',
        'initials' => 'MT',
        'role' => $isEn ? 'General Director' : 'Directeur Général',
        'badge' => $isEn ? 'Strategy & Management' : 'Stratégie & Direction',
        'icon' => 'fa-gem',
        'bio' => $isEn 
            ? 'As General Director, Mame Thierno orchestrates the daily operations of LuxeGold with precision and ambition, ensuring the highest standards of quality and customer satisfaction across Senegal and The Gambia.'
            : 'En tant que Directeur Général, Mame Thierno orchestre les opérations quotidiennes de LuxeGold avec précision et ambition, garantissant les plus hauts standards de qualité et de satisfaction client au Sénégal et en Gambie.',
        'quote' => $isEn 
            ? 'Excellence is not a goal, it is a daily discipline that defines our maison.'
            : 'L\'excellence n\'est pas un objectif, c\'est une discipline quotidienne qui définit notre maison.',
        'whatsapp_msg' => $isEn 
            ? 'Hello, I would like to contact the General Director Mame Thierno.'
            : 'Bonjour, je souhaite contacter le Directeur Général Mame Thierno.'
    ],
    [
        'name' => 'Baye Omar',
        'initials' => 'BO',
        'role' => $isEn ? 'Sales Advisor' : 'Commercial',
        'badge' => $isEn ? 'Client Advisory' : 'Conseil Client',
        'icon' => 'fa-handshake',
        'bio' => $isEn 
            ? 'With a deep knowledge of fine jewelry and a talent for understanding client desires, Baye Omar guides each customer toward the perfect piece, offering personalized advice and exceptional service.'
            : 'Doté d\'une connaissance approfondie de la joaillerie et d\'un talent pour comprendre les désirs de chaque client, Baye Omar accompagne vers la pièce parfaite avec un conseil personnalisé et un service d\'exception.',
        'quote' => $isEn 
            ? 'Every client deserves to find the jewel that tells their unique story.'
            : 'Chaque client mérite de trouver le bijou qui raconte son histoire unique.',
        'whatsapp_msg' => $isEn 
            ? 'Hello Baye Omar, I would like advice on choosing a piece of jewelry.'
            : 'Bonjour Baye Omar, je souhaite des conseils pour choisir un bijou.'
    ],
    [
        'name' => 'Mansour',
        'initials' => 'MA',
        'role' => $isEn ? 'Sales Advisor' : 'Commercial',
        'badge' => $isEn ? 'VIP Relations' : 'Relations VIP',
        'icon' => 'fa-star',
        'bio' => $isEn 
            ? 'Mansour brings warmth and expertise to every interaction, building lasting relationships with LuxeGold\'s clientele. His dedication to customer satisfaction makes every purchase a memorable experience.'
            : 'Mansour apporte chaleur et expertise à chaque interaction, tissant des liens durables avec la clientèle de LuxeGold. Son dévouement à la satisfaction client fait de chaque achat une expérience mémorable.',
        'quote' => $isEn 
            ? 'A satisfied client is the most beautiful reward for our work.'
            : 'Un client satisfait est la plus belle récompense de notre travail.',
        'whatsapp_msg' => $isEn 
            ? 'Hello Mansour, I am interested in your jewelry collection.'
            : 'Bonjour Mansour, je suis intéressé(e) par votre collection de bijoux.'
    ],
    [
        'name' => 'Baye Thierno',
        'initials' => 'BT',
        'role' => $isEn ? 'Sales Advisor' : 'Commercial',
        'badge' => $isEn ? 'Custom Orders' : 'Commandes Sur-Mesure',
        'icon' => 'fa-heart',
        'bio' => $isEn 
            ? 'Specialist in custom and bespoke orders, Baye Thierno works closely with clients to bring their dream jewelry to life. From engagement rings to traditional wedding sets, he ensures every detail is perfect.'
            : 'Spécialiste des commandes sur-mesure, Baye Thierno travaille étroitement avec les clients pour donner vie à leurs rêves de bijoux. Des bagues de fiançailles aux parures de mariage traditionnelles, chaque détail est soigné.',
        'quote' => $isEn 
            ? 'Turning a dream into a jewel is the most noble art there is.'
            : 'Transformer un rêve en bijou, c\'est le plus noble des arts.',
        'whatsapp_msg' => $isEn 
            ? 'Hello Baye Thierno, I would like to discuss a custom jewelry order.'
            : 'Bonjour Baye Thierno, je souhaite discuter d\'une commande de bijou sur-mesure.'
    ]
];

// Valeurs et philosophie de la maison
$values = [
    [
        'icon' => 'fa-solid fa-gem',
        'title' => $lang['team_value_craft'] ?? ($isEn ? 'Craftsmanship' : 'Savoir-Faire'),
        'desc' => $lang['team_value_craft_desc'] ?? ($isEn ? 'A craft passed down through generations, blending tradition and modernity.' : 'Un artisanat transmis de génération en génération, alliant tradition et modernité.'),
        'num' => '01'
    ],
    [
        'icon' => 'fa-solid fa-crown',
        'title' => $lang['team_value_quality'] ?? ($isEn ? 'Excellence' : 'Excellence'),
        'desc' => $lang['team_value_quality_desc'] ?? ($isEn ? 'Every piece of jewelry is created with meticulous attention to detail and quality.' : 'Chaque bijou est créé avec une attention méticuleuse aux détails et à la qualité.'),
        'num' => '02'
    ],
    [
        'icon' => 'fa-solid fa-fire',
        'title' => $lang['team_value_passion'] ?? ($isEn ? 'Passion' : 'Passion'),
        'desc' => $lang['team_value_passion_desc'] ?? ($isEn ? 'The love for our craft guides every creation, for your greatest pleasure.' : 'L\'amour du métier guide chacune de nos créations, pour votre plus grand plaisir.'),
        'num' => '03'
    ],
    [
        'icon' => 'fa-solid fa-shield-halved',
        'title' => $lang['team_value_trust'] ?? ($isEn ? 'Trust' : 'Confiance'),
        'desc' => $lang['team_value_trust_desc'] ?? ($isEn ? 'A trusted relationship built with our clients over many years.' : 'Une relation de confiance bâtie avec nos clients depuis des années.'),
        'num' => '04'
    ]
];
?>

<!-- ============================================
     1. HERO BANNER CINÉMATIQUE LUXE
     ============================================ -->
<section class="team-hero">
    <div class="team-hero-backdrop"></div>
    <div class="team-hero-particles" aria-hidden="true">
        <span class="particle p-1"></span>
        <span class="particle p-2"></span>
        <span class="particle p-3"></span>
        <span class="particle p-4"></span>
        <span class="particle p-5"></span>
    </div>

    <div class="container team-hero-content">
        <!-- Fil d'Ariane -->
        <nav class="team-breadcrumbs" aria-label="Breadcrumb">
            <a href="index.php"><i class="fas fa-home"></i> <?= htmlspecialchars($lang['menu_home'] ?? 'Accueil') ?></a>
            <span class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
            <span class="breadcrumb-current"><?= htmlspecialchars($lang['team_title'] ?? ($isEn ? 'Our Team' : 'Notre Équipe')) ?></span>
        </nav>

        <!-- Badge Joaillerie -->
        <div class="team-hero-badge">
            <i class="fas fa-gem"></i>
            <span><?= $isEn ? 'House of High Jewelry · Senegal & The Gambia' : 'Maison de Haute Joaillerie · Sénégal & Gambie' ?></span>
            <i class="fas fa-gem"></i>
        </div>

        <!-- Titre et sous-titres -->
        <h1 class="team-hero-title"><?= htmlspecialchars($lang['team_title'] ?? ($isEn ? 'Our Team' : 'Notre Équipe')) ?></h1>
        <p class="team-hero-subtitle"><?= htmlspecialchars($lang['team_subtitle'] ?? ($isEn ? 'The artisans behind your jewelry' : 'Les artisans derrière vos bijoux')) ?></p>
        <p class="team-hero-lead"><?= htmlspecialchars($lang['team_hero_text'] ?? ($isEn ? 'Discover the passionate talents who create your exceptional pieces' : 'Découvrez les talents passionnés qui créent vos pièces d\'exception')) ?></p>

        <!-- Ornement doré -->
        <div class="team-ornament-divider">
            <span class="ornament-line"></span>
            <span class="ornament-symbol">✦ ❖ ✦</span>
            <span class="ornament-line"></span>
        </div>
    </div>
</section>

<!-- ============================================
     2. BANDEAU DE STATS & HÉRITAGE
     ============================================ -->
<section class="team-stats-section">
    <div class="container">
        <div class="team-stats-card">
            <div class="team-stat-item">
                <div class="stat-number">25<span class="stat-plus">+</span></div>
                <div class="stat-label"><?= $isEn ? 'Years of Heritage' : 'Ans d\'Héritage' ?></div>
                <div class="stat-sub"><?= $isEn ? 'Master goldsmithing' : 'Orfèvrerie d\'art' ?></div>
            </div>
            <div class="stat-divider" aria-hidden="true"></div>
            <div class="team-stat-item">
                <div class="stat-number">18K<span class="stat-plus">& 24K</span></div>
                <div class="stat-label"><?= $isEn ? 'Certified Pure Gold' : 'Or Pur Certifié' ?></div>
                <div class="stat-sub"><?= $isEn ? 'Precious metals & gems' : 'Métaux nobles & gemmes' ?></div>
            </div>
            <div class="stat-divider" aria-hidden="true"></div>
            <div class="team-stat-item">
                <div class="stat-number">100<span class="stat-plus">%</span></div>
                <div class="stat-label"><?= $isEn ? 'Handcrafted' : 'Fait Main' ?></div>
                <div class="stat-sub"><?= $isEn ? 'Dakar & Serrekunda' : 'Dakar & Serrekunda' ?></div>
            </div>
            <div class="stat-divider" aria-hidden="true"></div>
            <div class="team-stat-item">
                <div class="stat-number">5 000<span class="stat-plus">+</span></div>
                <div class="stat-label"><?= $isEn ? 'Delighted Clients' : 'Clients Comblés' ?></div>
                <div class="stat-sub"><?= $isEn ? 'Senegambia & Diaspora' : 'Sénégambie & Diaspora' ?></div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     3. CARTES DES MEMBRES DE L'ÉQUIPE
     ============================================ -->
<section class="team-members-section">
    <div class="container">
        <div class="team-section-header">
            <span class="section-badge-gold">
                <?= $isEn ? 'The Masters of Elegance' : 'Les Maîtres de l\'Élégance' ?>
            </span>
            <h2 class="section-title-luxe">
                <?= $isEn ? 'The Passion Behind Every Creation' : 'La Passion Derrière Chaque Création' ?>
            </h2>
            <div class="section-gold-bar"></div>
            <p class="section-desc-lead">
                <?= $isEn 
                    ? 'United by an unwavering passion for fine jewelry, our team blends centuries of traditional Senegambian metalwork with contemporary luxury standards to handcraft pieces of eternal beauty.'
                    : 'Unis par une passion inébranlable pour la haute joaillerie, nos artisans conjuguent la noblesse des traditions orfèvres ouest-africaines et les exigences du luxe moderne pour sublimer vos moments les plus précieux.' ?>
            </p>
        </div>

        <div class="team-grid">
            <?php foreach ($teamMembers as $index => $member): ?>
                <div class="team-card" style="--card-index: <?= $index ?>;">
                    <div class="team-card-inner">
                        <div class="team-card-shimmer" aria-hidden="true"></div>

                        <!-- Avatar circulaire luxueux avec initiales -->
                        <div class="team-avatar-wrapper">
                            <div class="team-avatar-glow"></div>
                            <div class="team-avatar-ring">
                                <div class="team-avatar-inner">
                                    <span class="team-initials"><?= htmlspecialchars($member['initials']) ?></span>
                                </div>
                            </div>
                            <span class="team-avatar-badge" title="<?= htmlspecialchars($member['role']) ?>">
                                <i class="fa-solid <?= htmlspecialchars($member['icon']) ?>"></i>
                            </span>
                        </div>

                        <!-- Badge de spécialité -->
                        <div class="team-member-badge">
                            <span><?= htmlspecialchars($member['badge']) ?></span>
                        </div>

                        <!-- Identité & Rôle -->
                        <h3 class="team-member-name"><?= htmlspecialchars($member['name']) ?></h3>
                        <p class="team-member-role"><?= htmlspecialchars($member['role']) ?></p>

                        <!-- Micro-séparateur avec gemme -->
                        <div class="team-card-separator">
                            <span></span>
                            <i class="fa-solid fa-gem"></i>
                            <span></span>
                        </div>

                        <!-- Bio narrative -->
                        <p class="team-member-bio">
                            <?= htmlspecialchars($member['bio']) ?>
                        </p>

                        <!-- Citation / Devise de l'artisan -->
                        <div class="team-member-quote">
                            <i class="fa-solid fa-quote-left quote-icon"></i>
                            <span><?= htmlspecialchars($member['quote']) ?></span>
                        </div>

                        <!-- Bouton d'action / Consultation -->
                        <a href="https://wa.me/<?= $waPhone ?>?text=<?= urlencode($member['whatsapp_msg']) ?>" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="btn-team-contact">
                            <i class="fa-brands fa-whatsapp"></i>
                            <span><?= $isEn ? 'Consult on WhatsApp' : 'Consulter sur WhatsApp' ?></span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================
     4. SECTION VALEURS & PHILOSOPHIE
     ============================================ -->
<section class="team-values-section">
    <div class="team-values-backdrop"></div>
    <div class="container team-values-content">
        <div class="team-section-header team-section-header-dark">
            <span class="section-badge-gold">
                <?= htmlspecialchars($lang['team_values_subtitle'] ?? ($isEn ? 'What drives us every day' : 'Ce qui nous anime au quotidien')) ?>
            </span>
            <h2 class="section-title-luxe section-title-light">
                <?= htmlspecialchars($lang['team_values_title'] ?? ($isEn ? 'Our Values' : 'Nos Valeurs')) ?>
            </h2>
            <div class="section-gold-bar"></div>
            <p class="section-desc-lead section-desc-light">
                <?= $isEn 
                    ? 'Our philosophy rests upon four timeless pillars that guide every hammer blow, every gemstone setting, and every sacred relationship we build with our patrons.'
                    : 'Notre philosophie repose sur quatre piliers intemporels qui guident chaque coup de marteau, chaque sertissage de pierre fine et chaque lien sacré tissé avec notre clientèle.' ?>
            </p>
        </div>

        <div class="values-grid">
            <?php foreach ($values as $val): ?>
                <div class="value-card">
                    <div class="value-card-inner">
                        <span class="value-card-num" aria-hidden="true"><?= $val['num'] ?></span>
                        <div class="value-icon-wrapper">
                            <i class="<?= htmlspecialchars($val['icon']) ?>"></i>
                        </div>
                        <h3 class="value-title"><?= htmlspecialchars($val['title']) ?></h3>
                        <p class="value-desc"><?= htmlspecialchars($val['desc']) ?></p>
                        <div class="value-accent-line"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================
     5. BANNIÈRE ATELIER SUR-MESURE (CTA)
     ============================================ -->
<section class="team-cta-section">
    <div class="container">
        <div class="team-cta-box">
            <div class="cta-corners" aria-hidden="true">
                <span class="corner top-left"></span>
                <span class="corner top-right"></span>
                <span class="corner bottom-left"></span>
                <span class="corner bottom-right"></span>
            </div>

            <div class="team-cta-content">
                <div class="cta-icon-wrapper">
                    <i class="fa-solid fa-crown"></i>
                </div>
                <span class="cta-pretitle">
                    <?= $isEn ? 'BESPOKE HIGH JEWELRY' : 'ATELIER DE CRÉATION SUR-MESURE' ?>
                </span>
                <h2 class="cta-title">
                    <?= $isEn ? 'Have a Dream Jewelry Project in Mind?' : 'Un Projet de Bijou Unique en Tête ?' ?>
                </h2>
                <p class="cta-text">
                    <?= $isEn 
                        ? 'Entrust your vision to our master goldsmiths. Whether it is an engagement ring, a traditional heirloom wedding parure, or a custom personalized pendant, we turn pure gold into your eternal legacy.'
                        : 'Confiez votre vision à nos maîtres orfèvres. Bague de fiançailles, parure de mariage traditionnelle, ou pendentif personnalisé : nous forgeons votre héritage dans l\'or le plus noble.' ?>
                </p>

                <div class="cta-buttons">
                    <a href="https://wa.me/<?= $waPhone ?>?text=<?= urlencode($isEn ? 'Hello LuxeGold, I would like to discuss a custom bespoke jewelry project.' : 'Bonjour LuxeGold, je souhaite discuter d\'un projet de bijou sur-mesure.') ?>" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="btn btn-gold btn-large">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span><?= $isEn ? 'Talk to Master Jeweler' : 'Échanger avec l\'Atelier' ?></span>
                    </a>
                    <a href="catalogue.php" class="btn btn-gold-outline btn-large">
                        <i class="fa-solid fa-gem"></i>
                        <span><?= htmlspecialchars($lang['btn_collection'] ?? ($isEn ? 'Shop Collection' : 'Voir la Collection')) ?></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     STYLES DE LA PAGE ÉQUIPE (DESIGN LUXE PREMIER)
     ============================================ -->
<style>
/* --- VARIABLES LOCALES & HARMONIE LUXEGOLD --- */
:root {
    --gold-metallic: linear-gradient(135deg, #fdf8e1 0%, #d4af37 50%, #aa820a 100%);
    --card-glass-bg: rgba(255, 255, 255, 0.88);
    --card-glass-border: rgba(212, 175, 55, 0.28);
}

/* ==============================================
   1. HERO BANNER
   ============================================== */
.team-hero {
    position: relative;
    min-height: 520px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #ffffff;
    overflow: hidden;
    padding: 90px 20px 80px;
    background: #0d0d0d;
}

.team-hero-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 50% 15%, rgba(212, 175, 55, 0.22) 0%, rgba(13, 13, 13, 0.95) 65%, #080808 100%),
        linear-gradient(180deg, rgba(0, 0, 0, 0.8) 0%, rgba(13, 13, 13, 0.95) 100%);
    z-index: 1;
}

.team-hero-backdrop::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
        radial-gradient(rgba(212, 175, 55, 0.15) 1px, transparent 1px);
    background-size: 32px 32px;
    opacity: 0.35;
    pointer-events: none;
}

/* Particules d'or flottantes */
.team-hero-particles {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: hidden;
    z-index: 2;
    pointer-events: none;
}

.particle {
    position: absolute;
    border-radius: 50%;
    background: radial-gradient(circle, #fdf8e1 0%, #d4af37 60%, transparent 100%);
    opacity: 0.5;
    animation: floatGlow 8s infinite ease-in-out;
}

.p-1 { width: 6px; height: 6px; top: 20%; left: 15%; animation-duration: 9s; }
.p-2 { width: 10px; height: 10px; top: 35%; right: 18%; animation-duration: 12s; animation-delay: 1s; }
.p-3 { width: 4px; height: 4px; bottom: 25%; left: 22%; animation-duration: 7s; animation-delay: 2s; }
.p-4 { width: 8px; height: 8px; bottom: 30%; right: 28%; animation-duration: 11s; animation-delay: 3s; }
.p-5 { width: 5px; height: 5px; top: 60%; left: 80%; animation-duration: 8s; animation-delay: 1.5s; }

@keyframes floatGlow {
    0%, 100% { transform: translateY(0) scale(1); opacity: 0.3; }
    50% { transform: translateY(-25px) scale(1.3); opacity: 0.75; }
}

.team-hero-content {
    position: relative;
    z-index: 3;
    max-width: 860px;
    margin: 0 auto;
}

/* Fil d'Ariane */
.team-breadcrumbs {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 25px;
    background: rgba(255, 255, 255, 0.05);
    padding: 6px 18px;
    border-radius: 30px;
    border: 1px solid rgba(212, 175, 55, 0.2);
    backdrop-filter: blur(8px);
}

.team-breadcrumbs a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: color 0.3s ease;
}

.team-breadcrumbs a:hover {
    color: #d4af37;
}

.breadcrumb-separator {
    color: #d4af37;
    font-size: 0.7rem;
    opacity: 0.8;
}

.breadcrumb-current {
    color: #d4af37;
    font-weight: 500;
}

/* Badge Joaillerie */
.team-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 6px 20px;
    background: rgba(212, 175, 55, 0.08);
    border: 1px solid rgba(212, 175, 55, 0.35);
    border-radius: 30px;
    color: #d4af37;
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.15);
}

.team-hero-badge i {
    font-size: 0.75rem;
    animation: pulse 2.5s infinite;
}

/* Titre principal */
.team-hero-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.6rem, 6vw, 4.8rem);
    font-weight: 700;
    line-height: 1.15;
    margin-bottom: 15px;
    letter-spacing: 1px;
    background: linear-gradient(135deg, #ffffff 0%, #fdf8e1 35%, #d4af37 70%, #aa820a 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 4px 30px rgba(212, 175, 55, 0.25);
}

.team-hero-subtitle {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(1.3rem, 3vw, 1.9rem);
    font-style: italic;
    color: #f5e7c8;
    margin-bottom: 15px;
    font-weight: 400;
    letter-spacing: 1px;
}

.team-hero-lead {
    font-family: 'Poppins', sans-serif;
    font-size: clamp(0.95rem, 2vw, 1.12rem);
    color: rgba(255, 255, 255, 0.85);
    max-width: 640px;
    margin: 0 auto 30px;
    line-height: 1.8;
    font-weight: 300;
}

/* Ornement doré */
.team-ornament-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    margin-top: 10px;
}

.ornament-line {
    width: 60px;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.8), transparent);
}

.ornament-symbol {
    color: #d4af37;
    font-size: 0.95rem;
    letter-spacing: 5px;
}

/* ==============================================
   2. STATS & HÉRITAGE
   ============================================== */
.team-stats-section {
    position: relative;
    z-index: 10;
    margin-top: -45px;
    padding: 0 15px;
}

.team-stats-card {
    background: rgba(26, 26, 26, 0.92);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(212, 175, 55, 0.35);
    border-radius: 20px;
    padding: 30px 40px;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 25px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35), 0 0 25px rgba(212, 175, 55, 0.12);
}

.team-stat-item {
    text-align: center;
    padding: 10px;
    transition: transform 0.3s ease;
}

.team-stat-item:hover {
    transform: translateY(-4px);
}

.stat-number {
    font-family: 'Playfair Display', serif;
    font-size: 2.4rem;
    font-weight: 700;
    color: #d4af37;
    line-height: 1;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 2px;
}

.stat-plus {
    font-size: 1.3rem;
    color: #f5e7c8;
}

.stat-label {
    font-family: 'Poppins', sans-serif;
    font-size: 0.92rem;
    font-weight: 600;
    color: #ffffff;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}

.stat-sub {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.55);
    font-weight: 300;
}

.stat-divider {
    width: 1px;
    height: 60px;
    background: linear-gradient(180deg, transparent, rgba(212, 175, 55, 0.4), transparent);
    align-self: center;
}

/* ==============================================
   3. SECTION HEADER COMMUN
   ============================================== */
.team-section-header {
    text-align: center;
    max-width: 780px;
    margin: 0 auto 55px;
}

.section-badge-gold {
    display: inline-block;
    color: #b8960c;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 3px;
    padding: 5px 18px;
    background: rgba(212, 175, 55, 0.1);
    border: 1px solid rgba(212, 175, 55, 0.3);
    border-radius: 30px;
    margin-bottom: 15px;
}

.section-title-luxe {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.8rem, 4vw, 2.7rem);
    color: #1a1a1a;
    font-weight: 700;
    line-height: 1.25;
    margin-bottom: 15px;
}

.section-gold-bar {
    width: 70px;
    height: 3px;
    background: linear-gradient(90deg, transparent, #d4af37, transparent);
    margin: 15px auto;
    border-radius: 3px;
}

.section-desc-lead {
    font-family: 'Poppins', sans-serif;
    font-size: 0.98rem;
    line-height: 1.8;
    color: #666666;
    font-weight: 300;
    margin: 0;
}

/* Header pour section sombre */
.team-section-header-dark .section-badge-gold {
    color: #d4af37;
    background: rgba(212, 175, 55, 0.12);
    border-color: rgba(212, 175, 55, 0.4);
}

.section-title-light {
    color: #ffffff;
}

.section-desc-light {
    color: rgba(255, 255, 255, 0.75);
}

/* ==============================================
   4. SECTION MEMBRES & CARTES
   ============================================== */
.team-members-section {
    padding: 80px 0 90px;
    background: linear-gradient(180deg, #ffffff 0%, #faf7f2 50%, #ffffff 100%);
    position: relative;
}

.team-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
}

/* Carte Membre Individuelle */
.team-card {
    position: relative;
    border-radius: 22px;
    transition: all 0.45s cubic-bezier(0.165, 0.84, 0.44, 1);
    opacity: 0;
    transform: translateY(20px);
    animation: cardEntrance 0.8s cubic-bezier(0.165, 0.84, 0.44, 1) forwards;
    animation-delay: calc(var(--card-index, 0) * 0.15s + 0.2s);
}

@keyframes cardEntrance {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.team-card-inner {
    position: relative;
    height: 100%;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(212, 175, 55, 0.25);
    border-radius: 22px;
    padding: 38px 24px 30px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    transition: all 0.4s ease;
}

/* Bordure lumineuse sur le dessus */
.team-card-inner::before {
    content: '';
    position: absolute;
    top: 0;
    left: 20%;
    right: 20%;
    height: 3px;
    background: linear-gradient(90deg, transparent, #d4af37, transparent);
    opacity: 0.6;
    transition: all 0.4s ease;
}

/* Effet de reflet de lumière qui balaie la carte au survol */
.team-card-shimmer {
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(
        45deg,
        transparent 35%,
        rgba(212, 175, 55, 0.08) 50%,
        transparent 65%
    );
    transform: rotate(25deg) translateY(-100%);
    transition: transform 0.8s ease;
    pointer-events: none;
    z-index: 1;
}

.team-card:hover .team-card-shimmer {
    transform: rotate(25deg) translateY(100%);
}

.team-card:hover {
    transform: translateY(-10px);
}

.team-card:hover .team-card-inner {
    border-color: rgba(212, 175, 55, 0.65);
    box-shadow: 0 20px 45px rgba(212, 175, 55, 0.16), 0 8px 20px rgba(0, 0, 0, 0.08);
    background: #ffffff;
}

.team-card:hover .team-card-inner::before {
    left: 5%;
    right: 5%;
    opacity: 1;
    box-shadow: 0 0 12px #d4af37;
}

/* ==============================================
   AVATAR PLACEHOLDER CIRCULAIRE PREMIUM
   ============================================== */
.team-avatar-wrapper {
    position: relative;
    width: 124px;
    height: 124px;
    margin: 0 auto 18px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Lueur dorée douce derrière l'avatar */
.team-avatar-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 120px;
    height: 120px;
    transform: translate(-50%, -50%);
    border-radius: 50%;
    background: radial-gradient(circle, rgba(212, 175, 55, 0.35) 0%, transparent 70%);
    opacity: 0.6;
    transition: all 0.4s ease;
}

.team-card:hover .team-avatar-glow {
    width: 140px;
    height: 140px;
    opacity: 1;
}

/* Anneau extérieur métallisé doré avec dégradé conique */
.team-avatar-ring {
    position: relative;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    padding: 3px;
    background: conic-gradient(
        from 180deg at 50% 50%,
        #d4af37 0deg,
        #f5e7c8 90deg,
        #b8960c 180deg,
        #fdf8e1 270deg,
        #d4af37 360deg
    );
    box-shadow: 0 6px 20px rgba(212, 175, 55, 0.28);
    transition: transform 0.5s ease;
}

.team-card:hover .team-avatar-ring {
    transform: rotate(180deg) scale(1.04);
}

/* Cœur de l'avatar : fond sombre luxe */
.team-avatar-inner {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: radial-gradient(circle at 35% 35%, #2a2a2a 0%, #171717 65%, #0d0d0d 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(255, 255, 255, 0.1);
    box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.85);
    overflow: hidden;
    position: relative;
}

/* Reflet spéculaire interne */
.team-avatar-inner::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 50%;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.12) 0%, transparent 100%);
    border-radius: 50% 50% 0 0;
    pointer-events: none;
}

/* Lettres du monogramme / initiales */
.team-initials {
    font-family: 'Playfair Display', serif;
    font-size: 2.3rem;
    font-weight: 700;
    letter-spacing: 2px;
    background: linear-gradient(135deg, #ffffff 0%, #fdf8e1 35%, #d4af37 70%, #aa820a 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 2px 12px rgba(212, 175, 55, 0.4);
    z-index: 2;
    transition: transform 0.5s ease;
}

/* Inverser la rotation de l'anneau pour garder le texte droit */
.team-card:hover .team-initials {
    transform: rotate(-180deg) scale(1.1);
}

/* Badge flottant avec icône de fonction (couronne, gemme, etc.) */
.team-avatar-badge {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 34px;
    height: 34px;
    background: linear-gradient(135deg, #d4af37 0%, #b8960c 100%);
    color: #0d0d0d;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    border: 2.5px solid #ffffff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
    z-index: 3;
    transition: transform 0.3s ease;
}

.team-card:hover .team-avatar-badge {
    transform: scale(1.15) rotate(10deg);
}

/* Badge de spécialité */
.team-member-badge {
    display: inline-block;
    padding: 3px 12px;
    font-size: 0.72rem;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    font-weight: 600;
    color: #b8960c;
    background: rgba(212, 175, 55, 0.1);
    border: 1px solid rgba(212, 175, 55, 0.28);
    border-radius: 20px;
    margin-bottom: 12px;
}

/* Nom et rôle */
.team-member-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.35rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 4px;
    line-height: 1.2;
}

.team-member-role {
    font-family: 'Poppins', sans-serif;
    font-size: 0.84rem;
    font-weight: 600;
    color: #d4af37;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 14px;
}

/* Séparateur interne de la carte */
.team-card-separator {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    margin-bottom: 14px;
}

.team-card-separator span {
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.3), transparent);
}

.team-card-separator i {
    font-size: 0.65rem;
    color: #d4af37;
    opacity: 0.8;
}

/* Bio */
.team-member-bio {
    font-family: 'Poppins', sans-serif;
    font-size: 0.86rem;
    line-height: 1.65;
    color: #555555;
    font-weight: 300;
    margin-bottom: 16px;
    flex-grow: 1;
}

/* Citation / Devise */
.team-member-quote {
    position: relative;
    width: 100%;
    background: rgba(212, 175, 55, 0.05);
    border-left: 2px solid #d4af37;
    border-radius: 0 8px 8px 0;
    padding: 10px 14px 10px 28px;
    margin-bottom: 20px;
    text-align: left;
}

.team-member-quote .quote-icon {
    position: absolute;
    top: 10px;
    left: 8px;
    color: #d4af37;
    font-size: 0.75rem;
    opacity: 0.7;
}

.team-member-quote span {
    font-family: 'Cormorant Garamond', serif;
    font-size: 0.95rem;
    font-style: italic;
    color: #666666;
    line-height: 1.45;
    display: block;
}

/* Bouton Contact WhatsApp */
.btn-team-contact {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    width: 100%;
    padding: 11px 18px;
    background: #1a1a1a;
    color: #d4af37;
    border: 1px solid rgba(212, 175, 55, 0.35);
    border-radius: 30px;
    font-family: 'Poppins', sans-serif;
    font-size: 0.84rem;
    font-weight: 600;
    text-decoration: none;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.btn-team-contact i {
    font-size: 1rem;
    color: #25d366;
    transition: transform 0.3s ease;
}

.btn-team-contact:hover {
    background: #d4af37;
    color: #0d0d0d;
    border-color: #d4af37;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(212, 175, 55, 0.35);
}

.btn-team-contact:hover i {
    color: #0d0d0d;
    transform: scale(1.15);
}

/* ==============================================
   5. SECTION VALEURS DE L'ENTREPRISE
   ============================================== */
.team-values-section {
    position: relative;
    padding: 95px 0 100px;
    background: #0d0d0d;
    color: #ffffff;
    overflow: hidden;
}

.team-values-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 80% 20%, rgba(212, 175, 55, 0.15) 0%, transparent 60%),
        radial-gradient(circle at 20% 80%, rgba(212, 175, 55, 0.12) 0%, transparent 55%),
        linear-gradient(180deg, #0d0d0d 0%, #151515 50%, #0d0d0d 100%);
    z-index: 1;
}

.team-values-backdrop::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
        linear-gradient(rgba(212, 175, 55, 0.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(212, 175, 55, 0.05) 1px, transparent 1px);
    background-size: 60px 60px;
    opacity: 0.4;
    pointer-events: none;
}

.team-values-content {
    position: relative;
    z-index: 2;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 28px;
}

/* Carte de valeur */
.value-card {
    position: relative;
    border-radius: 20px;
    transition: transform 0.4s ease;
}

.value-card-inner {
    position: relative;
    height: 100%;
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1px solid rgba(212, 175, 55, 0.22);
    border-radius: 20px;
    padding: 40px 26px 35px;
    text-align: center;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Numéro de valeur en filigrane */
.value-card-num {
    position: absolute;
    top: 15px;
    right: 20px;
    font-family: 'Playfair Display', serif;
    font-size: 2.8rem;
    font-weight: 700;
    color: rgba(212, 175, 55, 0.08);
    line-height: 1;
    pointer-events: none;
    transition: color 0.4s ease;
}

.value-card:hover .value-card-num {
    color: rgba(212, 175, 55, 0.18);
}

/* Conteneur d'icône avec lueur dorée */
.value-icon-wrapper {
    width: 78px;
    height: 78px;
    margin: 0 auto 24px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(212, 175, 55, 0.18) 0%, rgba(212, 175, 55, 0.04) 70%);
    border: 1.5px solid rgba(212, 175, 55, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.85rem;
    color: #d4af37;
    transition: all 0.45s ease;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.value-card:hover .value-icon-wrapper {
    transform: scale(1.12) rotate(8deg);
    background: #d4af37;
    color: #0d0d0d;
    box-shadow: 0 0 25px rgba(212, 175, 55, 0.6);
    border-color: #d4af37;
}

.value-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.35rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 14px;
    letter-spacing: 0.5px;
}

.value-desc {
    font-family: 'Poppins', sans-serif;
    font-size: 0.88rem;
    line-height: 1.7;
    color: rgba(255, 255, 255, 0.72);
    font-weight: 300;
    margin-bottom: 20px;
    flex-grow: 1;
}

.value-accent-line {
    width: 35px;
    height: 2px;
    background: #d4af37;
    margin: 0 auto;
    transition: width 0.3s ease;
    opacity: 0.6;
}

.value-card:hover .value-accent-line {
    width: 60px;
    opacity: 1;
    box-shadow: 0 0 10px #d4af37;
}

.value-card:hover {
    transform: translateY(-8px);
}

.value-card:hover .value-card-inner {
    border-color: rgba(212, 175, 55, 0.6);
    background: rgba(255, 255, 255, 0.06);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 30px rgba(212, 175, 55, 0.15);
}

/* ==============================================
   6. BANNIÈRE ATELIER / CALL TO ACTION
   ============================================== */
.team-cta-section {
    padding: 85px 0 95px;
    background: #ffffff;
}

.team-cta-box {
    position: relative;
    background: radial-gradient(circle at 50% 30%, #1f1f1f 0%, #121212 70%, #0a0a0a 100%);
    border: 1px solid rgba(212, 175, 55, 0.4);
    border-radius: 24px;
    padding: 60px 40px;
    text-align: center;
    color: #ffffff;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25), 0 0 30px rgba(212, 175, 55, 0.15);
    overflow: hidden;
}

/* Motifs de coins luxueux */
.cta-corners .corner {
    position: absolute;
    width: 24px;
    height: 24px;
    border-color: #d4af37;
    border-style: solid;
    pointer-events: none;
}

.cta-corners .top-left {
    top: 15px;
    left: 15px;
    border-width: 2px 0 0 2px;
}

.cta-corners .top-right {
    top: 15px;
    right: 15px;
    border-width: 2px 2px 0 0;
}

.cta-corners .bottom-left {
    bottom: 15px;
    left: 15px;
    border-width: 0 0 2px 2px;
}

.cta-corners .bottom-right {
    bottom: 15px;
    right: 15px;
    border-width: 0 2px 2px 0;
}

.team-cta-content {
    position: relative;
    z-index: 2;
    max-width: 740px;
    margin: 0 auto;
}

.cta-icon-wrapper {
    width: 65px;
    height: 65px;
    margin: 0 auto 20px;
    border-radius: 50%;
    background: rgba(212, 175, 55, 0.15);
    border: 1px solid rgba(212, 175, 55, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.7rem;
    color: #d4af37;
    animation: bounceSlow 3s infinite ease-in-out;
}

@keyframes bounceSlow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

.cta-pretitle {
    display: inline-block;
    color: #d4af37;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 3px;
    margin-bottom: 12px;
}

.cta-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.9rem, 4vw, 2.8rem);
    font-weight: 700;
    line-height: 1.25;
    margin-bottom: 18px;
    background: linear-gradient(135deg, #ffffff 0%, #fdf8e1 40%, #d4af37 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.cta-text {
    font-family: 'Poppins', sans-serif;
    font-size: 1rem;
    line-height: 1.8;
    color: rgba(255, 255, 255, 0.8);
    font-weight: 300;
    margin-bottom: 35px;
}

.cta-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

/* ==============================================
   7. RESPONSIVE DESIGN
   ============================================== */
@media (max-width: 1200px) {
    .team-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 22px;
    }

    .values-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
    }
}

@media (max-width: 992px) {
    .team-stats-card {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        padding: 25px 20px;
    }

    .stat-divider:nth-child(4) {
        display: none;
    }
}

@media (max-width: 768px) {
    .team-hero {
        min-height: 440px;
        padding: 70px 15px 60px;
    }

    .team-stats-section {
        margin-top: -30px;
    }

    .team-stats-card {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .stat-divider {
        display: none;
    }

    .team-grid {
        grid-template-columns: 1fr;
        max-width: 480px;
        margin: 0 auto;
    }

    .values-grid {
        grid-template-columns: 1fr;
        max-width: 480px;
        margin: 0 auto;
    }

    .team-cta-box {
        padding: 45px 20px;
    }

    .cta-buttons {
        flex-direction: column;
        width: 100%;
    }

    .cta-buttons .btn {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .team-hero-title {
        font-size: 2.2rem;
    }

    .team-hero-badge {
        font-size: 0.7rem;
        letter-spacing: 1.5px;
        padding: 5px 12px;
    }

    .stat-number {
        font-size: 2rem;
    }

    .team-avatar-wrapper {
        width: 110px;
        height: 110px;
    }

    .team-initials {
        font-size: 2rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
