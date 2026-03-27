<?php
session_start();
require_once '../config/db.php';

$message = "";
$lien_simulation = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant']);
    
    if (!empty($identifiant)) {
        // Vérifier si l'admin existe
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE identifiant = ?");
        $stmt->execute([$identifiant]);
        $admin = $stmt->fetch();
        
        if ($admin) {
            // Création d'un Jeton de sécurité (Token) très sécurisé
            $token = bin2hex(random_bytes(32));
            
            // Stockage du token et de l'ID admin en session
            $_SESSION['reset_token'] = $token;
            $_SESSION['reset_admin_id'] = $admin['id'];
            
            // Simulation de l'email pour l'environnement local (XAMPP)
            $lien = "reinitialiser_mdp.php?token=" . $token;
            
            $lien_simulation = "
                <div style='background:#e8f5e9; border:1px solid #27ae60; padding:15px; border-radius:8px; margin-top:20px; color:#27ae60; font-size:0.9rem; text-align:left;'>
                    <p>Un email de réinitialisation aurait été envoyé avec ce lien :</p>
                    <a href='$lien' style='color:#1a1a1a; font-weight:bold; text-decoration:underline;'>Cliquez ici pour changer votre mot de passe</a>
                </div>";
        } else {
            // Fausse erreur pour des raisons de sécurité (ne pas révéler si un compte existe ou non)
            $message = "Si cet identifiant existe, un lien de réinitialisation a été généré.";
        }
    } else {
        $message = "Veuillez entrer votre identifiant.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié | Jewelry Beug Fallou</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%); font-family: 'Poppins', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; color: white; }
        .login-box { background: rgba(34, 34, 34, 0.95); padding: 45px 40px; border-radius: 20px; width: 100%; max-width: 420px; text-align: center; border: 1px solid rgba(212, 175, 55, 0.2); box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
        .login-box h2 { color: #d4af37; margin-bottom: 20px; font-weight: 500; }
        .login-box p { color: #aaa; font-size: 0.9rem; margin-bottom: 30px; }
        .form-group input { width: 100%; padding: 15px; background: rgba(51, 51, 51, 0.8); border: 2px solid #444; border-radius: 10px; color: white; outline: none; margin-bottom: 20px; box-sizing: border-box; }
        .form-group input:focus { border-color: #d4af37; }
        .btn-submit { width: 100%; padding: 15px; background: #d4af37; border: none; border-radius: 10px; color: #000; font-weight: bold; cursor: pointer; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; }
        .btn-submit:hover { background: #b8960c; }
        .back-link { margin-top: 20px; display: inline-block; color: #888; text-decoration: none; font-size: 0.85rem; }
        .back-link:hover { color: #d4af37; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2><i class="fas fa-unlock-alt"></i> Mot de passe oublié</h2>
        <p>Entrez votre identifiant administrateur. Un lien sécurisé sera généré.</p>
        
        <?php if($message && empty($lien_simulation)): ?>
            <div style="background: rgba(231, 76, 60, 0.1); color: #e74c3c; padding: 10px; border-radius: 8px; margin-bottom: 15px;"><?= $message ?></div>
        <?php endif; ?>

        <?php if(!empty($lien_simulation)): ?>
            <?= $lien_simulation ?>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="identifiant" required placeholder="Votre Identifiant (ex: admin)">
                </div>
                <button type="submit" class="btn-submit">Générer le lien</button>
            </form>
        <?php endif; ?>
        
        <a href="login.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour à la connexion</a>
    </div>
</body>
</html>