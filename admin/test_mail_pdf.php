<?php
// On force l'affichage de l'erreur cachée
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/db.php';
require_once 'includes/mailer.php';

echo "<h2>Test Final : Envoi de l'Email + Facture PDF</h2>";

// On utilise la commande 46 (puisqu'on sait que son PDF marche !)
$id_commande_test = 46; 

// ⚠️ METS TON VRAI EMAIL ICI POUR RECEVOIR LE TEST ⚠️
$email_test = "ton.email.perso@gmail.com"; 

echo "Tentative d'envoi à $email_test pour la commande #$id_commande_test...<br><br>";

// On appelle la fonction (avec le $pdo !)
$resultat = envoyerEmailLivraison($pdo, $email_test, "Client Test", $id_commande_test);

if ($resultat) {
    echo "<h3 style='color:green;'>✅ SUCCÈS TOTAL ! Va vérifier ta boîte mail, la facture est en pièce jointe !</h3>";
} else {
    echo "<h3 style='color:red;'>❌ ÉCHEC : Lis l'erreur ci-dessus (C'est sûrement le mot de passe Gmail ou l'email qui bloque).</h3>";
}
?>