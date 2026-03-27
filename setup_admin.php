<?php
// setup_admin.php - FICHIER À SUPPRIMER APRÈS UTILISATION !!!
require_once 'config/db.php';

// 1. Définis les identifiants de ton client ici
$identifiant = "dieylany";
$mot_de_passe = "khouma"; // Mets un mot de passe fort

// 2. On hache le mot de passe (Sécurité maximale)
$mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_DEFAULT);

try {
    // 3. On insère dans la base de données
    $stmt = $pdo->prepare("INSERT INTO admins (identifiant, mot_de_passe) VALUES (?, ?)");
    $stmt->execute([$identifiant, $mot_de_passe_hache]);
    
    echo "<h1 style='color: green;'>✅ Compte Administrateur créé avec succès !</h1>";
    echo "<p>Identifiant : <strong>$identifiant</strong></p>";
    echo "<p>⚠️ <strong>IMPORTANT :</strong> Supprime immédiatement ce fichier (setup_admin.php) de ton dossier pour des raisons de sécurité.</p>";
    echo "<a href='admin/login.php'>Aller à la page de connexion</a>";
    
} catch (PDOException $e) {
    echo "<h1 style='color: red;'>❌ Erreur :</h1> " . $e->getMessage();
}
?>