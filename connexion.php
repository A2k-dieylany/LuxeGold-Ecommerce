<?php
require_once 'config/db.php';
require_once 'includes/fonctions.php';

$message = "";
$style_class = "";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si déjà connecté
if (isset($_SESSION['client_id'])) {
    header('Location: mon_compte.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['mot_de_passe'];

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // On vérifie si l'utilisateur existe ET si le mot de passe correspond
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['client_id'] = $user['id'];
            $_SESSION['client_nom'] = $user['nom'];
            $_SESSION['client_email'] = $user['email'];
            
            header('Location: mon_compte.php');
            exit();
        } else {
            $message = "Email ou mot de passe incorrect.";
            $style_class = "error";
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
        $style_class = "error";
    }
}

include 'includes/header.php';
?>

<main class="auth-page">
    <div class="auth-container">
        <div class="auth-header">
            <h1 class="auth-title">Bon retour</h1>
            <p class="auth-subtitle">Connectez-vous à votre espace</p>
        </div>

        <?php if($message): ?>
            <div class="auth-alert <?= $style_class ?>" style="background: #fee; color: #e74c3c; border: 1px solid #e74c3c; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i> <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="connexion.php" method="POST" class="auth-form">
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="email" style="display: block; font-weight: 600; margin-bottom: 8px;">Adresse Email *</label>
                <input type="email" id="email" name="email" required placeholder="Ex: awa@email.com" style="width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 10px; box-sizing: border-box;">
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label for="mdp" style="display: block; font-weight: 600; margin-bottom: 8px;">Mot de passe *</label>
                <input type="password" id="mdp" name="mot_de_passe" required placeholder="Votre mot de passe" style="width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 10px; box-sizing: border-box;">
            </div>

            <button type="submit" class="btn btn-gold btn-full auth-btn" style="width: 100%; padding: 15px; background: #d4af37; color: white; border: none; border-radius: 10px; font-weight: bold; font-size: 1.1rem; cursor: pointer;">
                ME CONNECTER <i class="fas fa-sign-in-alt"></i>
            </button>
        </form>

        <div class="auth-footer" style="text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee;">
            Pas encore de compte ? <a href="inscription.php" style="color: #d4af37; font-weight: bold; text-decoration: none;">Inscrivez-vous ici</a>
        </div>
    </div>
</main>

<style>
.auth-page { padding: 60px 20px; background: linear-gradient(135deg, #fafafa 0%, #f0f0f0 100%); min-height: 60vh; display: flex; align-items: center; justify-content: center; }
.auth-container { background: white; max-width: 450px; width: 100%; padding: 40px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); }
.auth-title { font-family: 'Playfair Display', serif; font-size: 2rem; color: #1a1a1a; margin-bottom: 5px; text-align: center; }
.auth-subtitle { color: #d4af37; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 2px; text-align: center; margin-bottom: 30px; }
</style>

<?php include 'includes/footer.php'; ?>