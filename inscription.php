<?php
require_once 'config/db.php';
require_once 'includes/fonctions.php';

$message = "";
$style_class = "";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si le client est déjà connecté, on l'envoie sur son compte
if (isset($_SESSION['client_id'])) {
    header('Location: mon_compte.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = htmlspecialchars(trim($_POST['nom']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $telephone = htmlspecialchars(trim($_POST['telephone']));
    $password = $_POST['mot_de_passe'];
    $password_confirm = $_POST['mot_de_passe_confirm'];

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if ($password === $password_confirm) {
            if (strlen($password) >= 6) {
                // Vérifier si l'email existe déjà dans la table utilisateurs
                $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
                $check->execute([$email]);
                
                if ($check->rowCount() == 0) {
                    // Hashage du mot de passe (Sécurité maximale)
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    
                    // Insertion dans la table utilisateurs
                    $insert = $pdo->prepare("INSERT INTO utilisateurs (nom, email, telephone, mot_de_passe) VALUES (?, ?, ?, ?)");
                    
                    if ($insert->execute([$nom, $email, $telephone, $hashed_password])) {
                        // Connecter le client automatiquement après inscription
                        $_SESSION['client_id'] = $pdo->lastInsertId();
                        $_SESSION['client_nom'] = $nom;
                        header('Location: mon_compte.php?welcome=1');
                        exit();
                    } else {
                        $message = "Une erreur est survenue lors de la création de votre compte.";
                        $style_class = "error";
                    }
                } else {
                    $message = "Cet email est déjà utilisé. Veuillez vous connecter.";
                    $style_class = "error";
                }
            } else {
                $message = "Le mot de passe doit contenir au moins 6 caractères.";
                $style_class = "error";
            }
        } else {
            $message = "Les mots de passe ne correspondent pas.";
            $style_class = "error";
        }
    } else {
        $message = "Format d'email invalide.";
        $style_class = "error";
    }
}

include 'includes/header.php';
?>

<main class="auth-page">
    <div class="auth-container">
        
        <div class="auth-header">
            <h1 class="auth-title">Créer un compte</h1>
            <p class="auth-subtitle">Rejoignez notre collection exclusive</p>
        </div>

        <?php if($message): ?>
            <div class="auth-alert <?= $style_class ?>">
                <i class="fas fa-exclamation-circle"></i> <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="inscription.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="nom">Nom complet *</label>
                <input type="text" id="nom" name="nom" required placeholder="Ex: Awa Ndiaye" value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="email">Adresse Email *</label>
                <input type="email" id="email" name="email" required placeholder="Ex: awa@email.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="telephone">Numéro WhatsApp (Optionnel)</label>
                <input type="tel" id="telephone" name="telephone" placeholder="Ex: +221 77 000 00 00" value="<?= isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : '' ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="mdp">Mot de passe *</label>
                    <input type="password" id="mdp" name="mot_de_passe" required placeholder="6 caractères minimum">
                </div>
                <div class="form-group">
                    <label for="mdp_conf">Confirmer *</label>
                    <input type="password" id="mdp_conf" name="mot_de_passe_confirm" required placeholder="Répétez le mot de passe">
                </div>
            </div>

            <button type="submit" class="btn btn-gold btn-full auth-btn">
                M'INSCRIRE <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <div class="auth-footer">
            Vous avez déjà un compte ? <a href="connexion.php">Connectez-vous ici</a>
        </div>
    </div>
</main>

<style>
/* ============================================
   STYLES AUTHENTIFICATION (INSCRIPTION / CONNEXION)
   ============================================ */
.auth-page {
    padding: 60px 20px;
    background: linear-gradient(135deg, #fafafa 0%, #f0f0f0 100%);
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.auth-container {
    background: white;
    max-width: 500px;
    width: 100%;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.05);
    border: 1px solid #eee;
    animation: fadeInUp 0.5s ease;
}

.auth-header {
    text-align: center;
    margin-bottom: 30px;
}

.auth-title {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    color: #1a1a1a;
    margin-bottom: 5px;
}

.auth-subtitle {
    color: #d4af37;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.auth-alert {
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.auth-alert.error {
    background: #fee;
    color: #e74c3c;
    border: 1px solid #e74c3c;
}

.auth-form .form-group {
    margin-bottom: 20px;
}

.auth-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.auth-form label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
    font-size: 0.9rem;
}

.auth-form input {
    width: 100%;
    padding: 14px 15px;
    border: 1px solid #ddd;
    border-radius: 10px;
    background: #f9f9f9;
    font-family: 'Poppins', sans-serif;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.auth-form input:focus {
    outline: none;
    border-color: #d4af37;
    background: white;
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
}

.auth-btn {
    padding: 15px !important;
    font-size: 1.1rem !important;
    margin-top: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
}

.auth-footer {
    text-align: center;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #eee;
    color: #666;
    font-size: 0.95rem;
}

.auth-footer a {
    color: #d4af37;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
}

.auth-footer a:hover {
    color: #1a1a1a;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 480px) {
    .auth-container { padding: 30px 20px; }
    .auth-form .form-row { grid-template-columns: 1fr; gap: 0; }
}
</style>

<?php include 'includes/footer.php'; ?>