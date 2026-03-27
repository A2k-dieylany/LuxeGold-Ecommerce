<?php
// 1. Démarrage impératif de la session
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once '../config/db.php';

// Si déjà connecté, on évite de se reconnecter
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // On utilise trim() pour éviter les erreurs d'espaces
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // On s'adapte aux noms de colonnes de TA base de données (identifiant / mot_de_passe)
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE identifiant = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['mot_de_passe'])) {
        session_regenerate_id(true);
        // ON ALIGNE LES CLÉS : admin_id pour check_auth.php et admin_name pour le dashboard
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['identifiant'];
        
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Identifiants incorrects.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Admin - Jewelry Beug Fallou</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background: #1a1a1a; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: 'Poppins', sans-serif;">

    <div style="background: #222; padding: 40px; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.5); width: 320px; border: 1px solid #333;">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <i class="fas fa-user-shield" style="font-size: 3rem; color: #d4af37;"></i>
            <h2 style="color: white; margin-top: 15px; letter-spacing: 2px; font-weight: 300;">ADMIN LOGIN</h2>
        </div>

        <?php if($error): ?> 
            <div style="background: rgba(231, 76, 60, 0.1); color: #e74c3c; padding: 10px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #e74c3c; font-size: 13px; text-align: center;">
                <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
            </div> 
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom: 20px;">
                <label style="color: #888; font-size: 12px; display: block; margin-bottom: 5px;">Identifiant</label>
                <input type="text" name="username" placeholder="admin" required 
                       style="width: 100%; padding: 12px; background: #333; border: 1px solid #444; border-radius: 8px; color: white; box-sizing: border-box; outline: none;">
            </div>

            <div style="margin-bottom: 25px;">
                <label style="color: #888; font-size: 12px; display: block; margin-bottom: 5px;">Mot de passe</label>
                <input type="password" name="password" placeholder="••••••••" required 
                       style="width: 100%; padding: 12px; background: #333; border: 1px solid #444; border-radius: 8px; color: white; box-sizing: border-box; outline: none;">
            </div>

            <button type="submit" class="btn-gold" 
                    style="width: 100%; padding: 14px; background: #d4af37; color: black; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; text-transform: uppercase;">
                Accéder au Dashboard
            </button>
        </form>

        <div style="text-align: center; margin-top: 25px;">
            <a href="../index.php" style="color: #666; text-decoration: none; font-size: 12px;"><i class="fas fa-arrow-left"></i> Retour boutique</a>
        </div>
    </div>

</body>
</html>