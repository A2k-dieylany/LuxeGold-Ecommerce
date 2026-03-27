<footer class="admin-footer">
    <div class="footer-container">
        <div class="footer-copyright">
            <i class="fas fa-copyright"></i>
            <span><?= date('Y') ?> <?= htmlspecialchars($config_site['nom_boutique'] ?? 'Jewelry Beug Fallou') ?> - LuxeGold Administration</span>
        </div>
        
        <div class="footer-version">
            <i class="fas fa-code-branch"></i>
            <span>Version 2.0.0</span>
        </div>
        
        <div class="footer-links">
            <a href="#" class="footer-link" title="Aide">
                <i class="fas fa-question-circle"></i>
            </a>
            <a href="#" class="footer-link" title="Documentation">
                <i class="fas fa-book"></i>
            </a>
            <a href="#" class="footer-link" title="Support">
                <i class="fas fa-headset"></i>
            </a>
            <a href="logout.php" class="footer-link logout-link" title="Déconnexion" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                <i class="fas fa-power-off"></i>
            </a>
        </div>
    </div>
    
    <div class="session-timer" id="sessionTimer" style="display: none;"></div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Afficher l'heure actuelle dans le footer
    const timerElement = document.getElementById('sessionTimer');
    if (timerElement) {
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit' 
            });
            timerElement.textContent = `⏱️ Session: ${timeString}`;
            timerElement.style.display = 'block';
        }
        updateTime();
        setInterval(updateTime, 1000);
    }
});
</script>

<style>
/* ============================================
   FOOTER ADMIN - STYLES AMÉLIORÉS
   ============================================ */

.admin-footer {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    color: #888;
    font-size: 0.85rem;
    padding: 0;
    margin-top: 40px;
    border-top: 2px solid #d4af37;
    position: relative;
    box-shadow: 0 -5px 20px rgba(0,0,0,0.1);
}

.footer-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px 30px;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

/* Partie copyright */
.footer-copyright {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #aaa;
    transition: color 0.3s ease;
}

.footer-copyright i {
    color: #d4af37;
    font-size: 0.9rem;
}

.footer-copyright:hover {
    color: #fff;
}

/* Version */
.footer-version {
    display: flex;
    align-items: center;
    gap: 6px;
    background: rgba(212, 175, 55, 0.1);
    padding: 5px 12px;
    border-radius: 20px;
    border: 1px solid rgba(212, 175, 55, 0.2);
    transition: all 0.3s ease;
}

.footer-version:hover {
    background: rgba(212, 175, 55, 0.2);
    border-color: #d4af37;
    transform: translateY(-2px);
}

.footer-version i {
    color: #d4af37;
    font-size: 0.9rem;
}

.footer-version span {
    color: #ccc;
    font-weight: 500;
}

/* Liens du footer */
.footer-links {
    display: flex;
    align-items: center;
    gap: 12px;
}

.footer-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255,255,255,0.05);
    color: #888;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.footer-link:hover {
    background: #d4af37;
    color: #000;
    transform: translateY(-3px) rotate(360deg);
    border-color: #d4af37;
    box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
}

.footer-link.logout-link:hover {
    background: #e74c3c;
    color: #fff;
    border-color: #e74c3c;
    box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
}

/* Timer de session (optionnel) */
.session-timer {
    position: fixed;
    bottom: 10px;
    right: 10px;
    background: rgba(0,0,0,0.8);
    color: #d4af37;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    border: 1px solid #d4af37;
    backdrop-filter: blur(5px);
    z-index: 1000;
    animation: fadeIn 0.5s ease;
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .footer-container {
        flex-direction: column;
        text-align: center;
        padding: 15px 20px;
    }
    
    .footer-copyright {
        order: 2;
    }
    
    .footer-version {
        order: 3;
    }
    
    .footer-links {
        order: 1;
        margin-bottom: 10px;
    }
    
    .footer-link {
        width: 40px;
        height: 40px;
    }
}

@media (max-width: 480px) {
    .footer-links {
        gap: 8px;
    }
    
    .footer-link {
        width: 35px;
        height: 35px;
    }
    
    .session-timer {
        bottom: 5px;
        right: 5px;
        font-size: 0.7rem;
        padding: 3px 8px;
    }
}
</style>

</body>
</html>