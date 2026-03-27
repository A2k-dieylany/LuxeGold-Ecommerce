<footer class="site-footer">
    <div class="container">
        <div class="grid grid-3" style="text-align: left; gap: var(--spacing-xl);">
            
            <div class="footer-about">
                <h3 style="color: var(--gold); margin-bottom: var(--spacing-md);"><?= htmlspecialchars($config_site['nom_boutique'] ?? 'Jewelry Beug Fallou') ?></h3>
                <p style="font-size: 14px; opacity: 0.8;">
                    <?= $lang['footer_text'] ?>
                </p>
                <div class="social-links mt-3" style="font-size: 1.2rem; display: flex; gap: 15px;">
                    <a href="#" style="color: var(--gold);"><i class="fab fa-instagram"></i></a>
                    <a href="#" style="color: var(--gold);"><i class="fab fa-facebook"></i></a>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $config_site['telephone_whatsapp'] ?? '2202657822') ?>" style="color: var(--gold);"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>

            <div class="footer-contact">
                <h3 style="color: var(--gold); margin-bottom: var(--spacing-md);">
                    <?= $lang['contact_title'] ?>
                </h3>
                <p style="font-size: 14px; margin-bottom: 10px;">
                    <i class="fas fa-map-marker-alt" style="color: var(--gold);"></i> 
                    <?= htmlspecialchars($config_site['adresse_boutique'] ?? ($lang['service_location'] . ', Gambia')) ?>
                </p>
                <div style="font-size: 14px; opacity: 0.8;">
                    <p><i class="fas fa-phone"></i> +<?= htmlspecialchars($config_site['telephone_whatsapp'] ?? '220 265 7822') ?></p>
                    <p><i class="fas fa-phone"></i> +220 417 0895</p>
                    <p><i class="fas fa-phone"></i> +220 287 0748</p>
                    
                    <?php if(!empty($config_site['email_contact'])): ?>
                    <p style="margin-top: 10px;"><i class="fas fa-envelope"></i> <a href="mailto:<?= htmlspecialchars($config_site['email_contact']) ?>" style="color: white; text-decoration: none;"><?= htmlspecialchars($config_site['email_contact']) ?></a></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="footer-links">
                <h3 style="color: var(--gold); margin-bottom: var(--spacing-md);">Menu</h3>
                <ul style="list-style: none; padding: 0; font-size: 14px;">
                    <li style="margin-bottom: 10px;">
                        <a href="index.php" style="color: white; text-decoration: none; transition: 0.3s;">
                            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--gold);"></i> <?= $lang['menu_home'] ?>
                        </a>
                    </li>
                    <li style="margin-bottom: 10px;">
                        <a href="catalogue.php" style="color: white; text-decoration: none; transition: 0.3s;">
                            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--gold);"></i> <?= $lang['menu_collection'] ?>
                        </a>
                    </li>
                    <li style="margin-bottom: 10px;">
                        <a href="contact.php" style="color: white; text-decoration: none; transition: 0.3s;">
                            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--gold);"></i> <?= $lang['menu_contact'] ?>
                        </a>
                    </li>
                    <li style="margin-top: 20px;">
                        <a href="admin/login.php" style="color: #444; text-decoration: none; font-size: 11px; letter-spacing: 1px;">
                            <i class="fas fa-lock"></i> ESPACE ADMINISTRATION
                        </a>
                    </li>
                </ul>
            </div>

        </div>

        <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05); font-size: 12px; opacity: 0.5;">
            <p>&copy; <?= date('Y') ?> **<?= htmlspecialchars($config_site['nom_boutique'] ?? 'Jewelry Beug Fallou') ?>**. <?= ($_SESSION['lang'] == 'en' ? 'All rights reserved.' : 'Tous droits réservés.') ?></p>
        </div>
    </div>
</footer>

<script src="assets/js/script.js"></script>

</body>
</html>