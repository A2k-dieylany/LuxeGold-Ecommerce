<?php
require_once '../config/db.php';
require_once '../includes/fonctions.php';

// Toujours démarrer la session au tout début
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Si l'admin est déjà connecté, on l'envoie direct aux stats
if (isset($_SESSION['admin_id'])) {
    enregistrerLog($pdo, "CONNEXION", "L'administrateur s'est connecté au panel.");
    header('Location: dashboard.php');
    exit();
}

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage strict des entrées (enlever les espaces avant/après)
    $user = trim($_POST['identifiant']);
    $mdp = trim($_POST['mdp']); 

    if (!empty($user) && !empty($mdp)) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE identifiant = ?");
        $stmt->execute([$user]);
        $admin = $stmt->fetch();

        // password_verify est très sensible aux espaces invisibles
        if ($admin && password_verify($mdp, $admin['mot_de_passe'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['identifiant'];
            
            // On force l'enregistrement de la session avant de quitter
            session_write_close(); 
            header('Location: dashboard.php');
            exit();
        } else {
            $erreur = "Identifiants invalides. Veuillez réessayer.";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration | Jewelry Beug Fallou</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* ============================================
           PAGE DE CONNEXION ADMIN - DESIGN PREMIUM
           ============================================ */
        
        :root { 
            --gold: #d4af37; 
            --gold-light: #f5e7c8;
            --gold-dark: #b8960c;
            --dark: #1a1a1a; 
            --dark-light: #222;
            --dark-soft: #2d2d2d;
            --gray: #333;
            --gray-light: #444;
            --error: #ff4d4d;
            --error-bg: rgba(255, 77, 77, 0.1);
            --success: #27ae60;
        }
        
        body { 
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
            font-family: 'Poppins', sans-serif; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            margin: 0; 
            color: white; 
            position: relative;
            overflow: hidden;
        }
        
        /* Éléments décoratifs */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.03) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        body::after {
            content: '✦ ✦ ✦';
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: var(--gold);
            font-size: 1rem;
            letter-spacing: 10px;
            opacity: 0.2;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Conteneur principal */
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
            position: relative;
            z-index: 10;
            animation: fadeInUp 0.8s ease;
        }
        
        .login-box { 
            background: rgba(34, 34, 34, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 45px 40px; 
            border-radius: 20px; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.5), 0 0 0 1px rgba(212, 175, 55, 0.1);
            width: 100%; 
            border: 1px solid rgba(212, 175, 55, 0.2); 
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Effet de brillance */
        .login-box::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, 
                transparent, 
                rgba(212, 175, 55, 0.03), 
                transparent);
            transform: rotate(45deg);
            animation: shine 6s infinite;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }
        
        /* Logo */
        .logo-wrapper {
            margin-bottom: 25px;
            position: relative;
        }
        
        .login-box img { 
            width: 120px; 
            filter: drop-shadow(0 0 15px rgba(212, 175, 55, 0.3));
            transition: transform 0.5s ease;
            animation: float 3s ease-in-out infinite;
        }
        
        .login-box img:hover {
            transform: scale(1.05) rotate(5deg);
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        
        /* Titre */
        .login-box h2 { 
            margin-bottom: 35px; 
            font-weight: 300; 
            letter-spacing: 4px; 
            font-size: 1.1rem; 
            color: var(--gold); 
            position: relative;
            display: inline-block;
            padding: 0 20px;
        }
        
        .login-box h2::before,
        .login-box h2::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 30px;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
        }
        
        .login-box h2::before {
            left: -30px;
        }
        
        .login-box h2::after {
            right: -30px;
        }
        
        /* Formulaire */
        .form-group { 
            margin-bottom: 25px; 
            text-align: left; 
        }
        
        .form-group label { 
            color: #aaa; 
            display: block; 
            margin-bottom: 8px; 
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gold);
            font-size: 1rem;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }
        
        .form-group input { 
            width: 100%; 
            padding: 14px 14px 14px 45px; 
            background: rgba(51, 51, 51, 0.8);
            border: 2px solid #444; 
            border-radius: 12px; 
            color: white; 
            outline: none; 
            box-sizing: border-box; 
            transition: all 0.3s ease; 
            font-size: 0.95rem;
        }
        
        .form-group input:focus { 
            border-color: var(--gold); 
            background: rgba(51, 51, 51, 1);
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1); 
        }
        
        .form-group input:focus + i {
            opacity: 1;
            color: var(--gold);
        }
        
        .form-group input::placeholder {
            color: #666;
            font-style: italic;
        }
        
        /* Options de connexion */
        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            font-size: 0.85rem;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #888;
            cursor: pointer;
        }
        
        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--gold);
            cursor: pointer;
        }
        
        .forgot-password {
            color: #888;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .forgot-password:hover {
            color: var(--gold);
        }
        
        /* Bouton de connexion */
        .btn-login { 
            width: 100%; 
            padding: 16px; 
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            border: none; 
            border-radius: 12px; 
            color: #000; 
            font-weight: 700; 
            cursor: pointer; 
            transition: all 0.3s ease; 
            margin-top: 15px; 
            text-transform: uppercase; 
            letter-spacing: 2px; 
            font-size: 0.9rem;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s ease;
        }
        
        .btn-login:hover { 
            background: linear-gradient(135deg, var(--gold-dark), var(--gold));
            transform: translateY(-3px); 
            box-shadow: 0 10px 25px rgba(212, 175, 55, 0.4); 
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:active {
            transform: translateY(-1px);
        }
        
        /* Message d'erreur */
        .error-msg { 
            background: rgba(231, 76, 60, 0.1); 
            color: #e74c3c; 
            padding: 15px; 
            border-radius: 12px; 
            font-size: 0.9rem; 
            margin-bottom: 25px; 
            border-left: 4px solid #e74c3c; 
            animation: shake 0.4s ease-in-out;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
        }
        
        .error-msg i {
            font-size: 1.2rem;
        }
        
        @keyframes shake { 
            0%, 100% {transform: translateX(0);} 
            25% {transform: translateX(-5px);} 
            75% {transform: translateX(5px);} 
        }
        
        /* Lien retour */
        .back-link { 
            margin-top: 30px; 
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.05);
        }
        
        .back-link a { 
            color: #666; 
            text-decoration: none; 
            font-size: 0.85rem; 
            transition: all 0.3s ease; 
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-link a i {
            transition: transform 0.3s ease;
        }
        
        .back-link a:hover { 
            color: var(--gold); 
        }
        
        .back-link a:hover i {
            transform: translateX(-3px);
        }
        
        /* Animation d'entrée */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 10px;
            }
            
            .login-box {
                padding: 35px 25px;
            }
            
            .login-box h2 {
                font-size: 1rem;
                letter-spacing: 2px;
            }
            
            .login-box h2::before,
            .login-box h2::after {
                width: 20px;
            }
            
            .form-group input {
                padding: 12px 12px 12px 40px;
                font-size: 0.9rem;
            }
            
            .login-options {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
            
            .btn-login {
                padding: 14px;
                font-size: 0.85rem;
            }
        }
        
        /* Petite touche de luxe supplémentaire */
        .login-box::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
        }
        
        /* Tooltip pour le placeholder (optionnel) */
        .input-wrapper:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 0.7rem;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo-wrapper">
                <img src="../assets/img/logo.png" alt="Jewelry Beug Fallou - LuxeGold">
            </div>
            
            <h2>ACCÈS ADMIN</h2>
            
            <?php if($erreur): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?= htmlspecialchars($erreur) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="identifiant">
                        <i class="fas fa-user"></i> Identifiant
                    </label>
                    <div class="input-wrapper" data-tooltip="Votre nom d'utilisateur">
                        <i class="fas fa-user"></i>
                        <input type="text" 
                               name="identifiant" 
                               id="identifiant"
                               required 
                               placeholder="admin" 
                               value="<?= isset($_POST['identifiant']) ? htmlspecialchars($_POST['identifiant']) : '' ?>"
                               autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="mdp">
                        <i class="fas fa-lock"></i> Mot de passe
                    </label>
                    <div class="input-wrapper" data-tooltip="Votre mot de passe secret">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               name="mdp" 
                               id="mdp"
                               required 
                               placeholder="••••••••">
                    </div>
                </div>
                
                <div class="login-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember"> 
                        <span>Se souvenir de moi</span>
                    </label>
                        <a href="mot_de_passe_oublie.php" class="forgot-password">Mot de passe oublié ?</a>                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    S'authentifier
                </button>
            </form>
            
            <div class="back-link">
                <a href="../index.php">
                    <i class="fas fa-arrow-left"></i>
                    Retour à la bijouterie
                </a>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ajouter un effet de focus sur les inputs
        const inputs = document.querySelectorAll('.form-group input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    });
    </script>
</body>
</html>