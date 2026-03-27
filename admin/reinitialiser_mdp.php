<?php
session_start();
require_once '../config/db.php';

$message = "";
$succes = false;

// Vérification de sécurité : le token URL doit correspondre au token en Session
if (!isset($_GET['token']) || !isset($_SESSION['reset_token']) || $_GET['token'] !== $_SESSION['reset_token']) {
    die("<div style='background:#1a1a1a; color:white; text-align:center; padding:50px; font-family:sans-serif;'>
            <h2 style='color:#e74c3c;'>Lien invalide ou expiré.</h2>
            <a href='login.php' style='color:#d4af37;'>Retour à la connexion</a>
         </div>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mdp1 = $_POST['mdp1'];
    $mdp2 = $_POST['mdp2'];

    if (strlen($mdp1) < 6) {
        $message = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($mdp1 !== $mdp2) {
        $message = "Les mots de passe ne correspondent pas.";
    } else {
        // Sécurité : Hashage du nouveau mot de passe
        $mdp_hash = password_hash($mdp1, PASSWORD_DEFAULT);
        $admin_id = $_SESSION['reset_admin_id'];

        // Mise à jour dans la base de données
        $stmt = $pdo->prepare("UPDATE admins SET mot_de_passe = ? WHERE id = ?");
        $stmt->execute([$mdp_hash, $admin_id]);

        // Nettoyage de la session par sécurité
        unset($_SESSION['reset_token']);
        unset($_SESSION['reset_admin_id']);
        
        $succes = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau mot de passe | Jewelry Beug Fallou</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%); font-family: 'Poppins', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; color: white; }
        .login-box { background: rgba(34, 34, 34, 0.95); padding: 45px 40px; border-radius: 20px; width: 100%; max-width: 420px; text-align: center; border: 1px solid rgba(212, 175, 55, 0.2); box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
        .login-box h2 { color: #d4af37; margin-bottom: 20px; font-weight: 500; }
        .form-group input { width: 100%; padding: 15px; background: rgba(51, 51, 51, 0.8); border: 2px solid #444; border-radius: 10px; color: white; outline: none; margin-bottom: 15px; box-sizing: border-box; }
        .form-group input:focus { border-color: #d4af37; }
        .btn-submit { width: 100%; padding: 15px; background: #d4af37; border: none; border-radius: 10px; color: #000; font-weight: bold; cursor: pointer; text-transform: uppercase; transition: 0.3s; }
        .btn-submit:hover { background: #b8960c; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2><i class="fas fa-key"></i> Nouveau mot de passe</h2>
        
        <?php if($succes): ?>
            <div style="background: rgba(39, 174, 96, 0.1); color: #27ae60; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom:10px;"></i><br>
                Mot de passe modifié avec succès !
            </div>
            <a href="login.php" class="btn-submit" style="display:block; text-decoration:none; box-sizing:border-box;">Aller à la connexion</a>
        <?php else: ?>
            
            <?php if($message): ?>
                <div style="background: rgba(231, 76, 60, 0.1); color: #e74c3c; padding: 10px; border-radius: 8px; margin-bottom: 15px;"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <input type="password" name="mdp1" required placeholder="Nouveau mot de passe">
                </div>
                <div class="form-group">
                    <input type="password" name="mdp2" required placeholder="Confirmez le mot de passe">
                </div>
                <button type="submit" class="btn-submit">Sauvegarder</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>