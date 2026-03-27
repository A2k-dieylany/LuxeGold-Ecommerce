<?php 
require_once 'config/db.php';
require_once 'includes/fonctions.php';
include 'includes/header.php'; 
?>

<main class="container">
    <section class="p-5">
        <h1 class="section-title"><?= $lang['contact_title'] ?></h1>
        <p class="text-center mb-4" style="max-width: 700px; margin-left: auto; margin-right: auto;">
            <?= $lang['contact_text'] ?>
        </p>

        <div class="form-commande">
            <form id="contactForm">
                <div class="form-group">
                    <label for="nom"><?= $lang['contact_name'] ?></label>
                    <input type="text" id="nom" placeholder="<?= ($_SESSION['lang'] == 'en') ? 'Your full name' : 'Votre nom complet' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="message"><?= $lang['contact_message'] ?></label>
                    <textarea id="message" rows="5" placeholder="<?= ($_SESSION['lang'] == 'en') ? 'How can we help you?' : 'Comment pouvons-nous vous aider ?' ?>" required></textarea>
                </div>
                
                <button type="button" onclick="envoyerContact()" class="btn btn-gold" style="width: 100%;">
                    <i class="fab fa-whatsapp"></i> <?= $lang['contact_send'] ?>
                </button>
            </form>
        </div>

        <div class="grid grid-3 mt-5 text-center">
            <div class="service-item">
                <i class="fas fa-map-marker-alt service-icon"></i>
                <h4 class="text-black">Showroom</h4>
                <p class="text-gray">Dakar, Sénégal / Serrekunda, Gambia</p>
            </div>
            <div class="service-item">
                <i class="fas fa-phone service-icon"></i>
                <h4 class="text-black">Phone</h4>
                <p class="text-gray">+220 265 7822</p>
            </div>
            <div class="service-item">
                <i class="fas fa-envelope service-icon"></i>
                <h4 class="text-black">Email</h4>
                <p class="text-gray">contact@jewelrybeugfallou.com</p>
            </div>
        </div>
    </section>
</main>

<script>
function envoyerContact() {
    const nom = document.getElementById('nom').value.trim();
    const msg = document.getElementById('message').value.trim();
    
    if(nom === "" || msg === "") {
        alert("<?= ($_SESSION['lang'] == 'en') ? 'Please fill all fields' : 'Veuillez remplir tous les champs' ?>");
        return;
    }

    // Détection de la langue pour le message WhatsApp
    const isEn = "<?= $_SESSION['lang'] ?>";
    const salutation = (isEn === 'en') ? 'Hello' : 'Bonjour';
    const intro = (isEn === 'en') ? 'I am' : 'je suis';
    const msgFinal = (isEn === 'en') ? 'Message:' : 'Message :';

    const texte = `✨ *Contact Client - Jewelry Beug Fallou* ✨\n\n${salutation}, ${intro} *${nom}*.\n\n${msgFinal}\n${msg}`;
    
    // Redirection vers WhatsApp
    window.open(`https://wa.me/2202657822?text=${encodeURIComponent(texte)}`, '_blank');
}
</script>

<?php include 'includes/footer.php'; ?>