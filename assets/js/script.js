/**
 * assets/js/script.js - Jawelry Beug Fallou
 * Gestion du panier AJAX et du menu Responsive
 */

// 1. GESTION DU MENU MOBILE (Responsive)
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const mainNav = document.querySelector('.main-nav');

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function() {
            const expanded = this.getAttribute('aria-expanded') === 'true' || false;
            this.setAttribute('aria-expanded', !expanded);
            mainNav.classList.toggle('active');
        });
    }
});

// 2. AJOUT AU PANIER AVEC QUANTITÉ (Page Produit)
function ajouterAuPanierAvecQte(idProduit) {
    const qteInput = document.getElementById('qte_' + idProduit);
    const quantite = parseInt(qteInput.value);

    // Détection de la langue pour les messages
    const isEn = document.documentElement.lang === 'en';
    const msgError = isEn ? "Please choose a valid quantity." : "Veuillez choisir une quantité valide.";
    const msgSuccess = isEn ? "item(s) added to your collection!" : "article(s) ajouté(s) à votre collection !";
    const msgFail = isEn ? "An error occurred." : "Une erreur est survenue.";

    if (isNaN(quantite) || quantite < 1) {
        alert(msgError);
        return;
    }

    // Appel AJAX
    fetch(`ajouter_au_panier.php?id=${idProduit}&qte=${quantite}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartBadge(data.nouveauTotal);
            alert(`✨ ${quantite} ${msgSuccess}`);
        } else {
            alert(msgFail);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}

// 3. AJOUT RAPIDE (Page Catalogue)
function ajouterAuPanier(idProduit) {
    const isEn = document.documentElement.lang === 'en';
    const msgSuccess = isEn ? "✨ Added to your collection!" : "✨ Ajouté à votre collection !";

    fetch(`ajouter_au_panier.php?id=${idProduit}&qte=1`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartBadge(data.nouveauTotal);
            alert(msgSuccess);
        }
    });
}

/**
 * Met à jour le compteur du panier (Badge)
 */
function updateCartBadge(total) {
    // On cherche l'ID cart-count OU la classe cart-badge
    const cartBadge = document.getElementById('cart-count') || document.querySelector('.cart-badge');
    if (cartBadge) {
        cartBadge.innerText = total;
        // Petit effet d'animation luxe
        cartBadge.style.transform = 'scale(1.2)';
        setTimeout(() => cartBadge.style.transform = 'scale(1)', 200);
    }
}

function toggleWishlist(id, btn) {
    const icon = btn.querySelector('i');
    const formData = new FormData();
    formData.append('produit_id', id);

    fetch('api/wishlist.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'added') {
            icon.classList.replace('far', 'fas'); // Cœur plein
            alert("❤ " + data.message);
        } else {
            icon.classList.replace('fas', 'far'); // Cœur vide
            alert("💔 " + data.message);
        }
    });
}