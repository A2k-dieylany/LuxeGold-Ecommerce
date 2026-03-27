<?php
// Affiche toutes les erreurs à l'écran
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test de PHPMailer</h2>";

// 1. Vérification du fichier mailer
if (!file_exists('includes/mailer.php')) {
    die("❌ ERREUR : Le fichier includes/mailer.php est introuvable.");
}
require_once 'includes/mailer.php';

// 2. Vérification du dossier PHPMailer
if (!file_exists('includes/PHPMailer/src/PHPMailer.php')) {
    die("❌ ERREUR : Tu n'as pas téléchargé ou mal placé le dossier PHPMailer.");
}

echo "Dossiers trouvés. Tentative de connexion à Gmail...<br><br>";

// Remplace par TON VRAI EMAIL pour recevoir le test
$email_test = "ton.email.perso@gmail.com"; 

$resultat = envoyerEmailLivraison($email_test, "Client Test", 999);

if ($resultat) {
    echo "<br><br><h3 style='color:green;'>✅ SUCCÈS ! L'email est parti. Vérifie ta boîte de réception.</h3>";
} else {
    echo "<br><br><h3 style='color:red;'>❌ ÉCHEC DE L'ENVOI. Lis les lignes au-dessus pour voir l'erreur de Gmail.</h3>";
}
?>