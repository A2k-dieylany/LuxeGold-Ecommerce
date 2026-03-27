<?php
// On force l'affichage de toutes les erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/db.php';
require_once 'includes/generateur_facture.php';

echo "<h2>Test de Génération de Facture (Moteur PDF)</h2>";

// Remplacer "1" par l'ID d'une vraie commande qui existe dans ta base de données !
$id_commande_test = 46; 

echo "Tentative de génération en mémoire pour la commande #$id_commande_test...<br><br>";

// On appelle le moteur en mode 'S' (celui utilisé par l'email)
$resultat = genererLaFacturePDF($pdo, $id_commande_test, 'S');

if ($resultat) {
    echo "<h3 style='color:green;'>✅ SUCCÈS ! Le moteur PDF fonctionne parfaitement en arrière-plan.</h3>";
    echo "<p>Si tu vois ce message, l'erreur AJAX ne vient pas du PDF, mais sûrement de l'envoi PHPMailer.</p>";
} else {
    echo "<h3 style='color:red;'>❌ ÉCHEC : Le moteur a renvoyé 'false' (La commande #$id_commande_test n'existe peut-être pas).</h3>";
}
?>