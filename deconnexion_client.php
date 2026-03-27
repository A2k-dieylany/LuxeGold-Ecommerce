<?php
session_start();

// On ne détruit QUE les variables de session du client
// On garde le panier et on ne touche pas à la session Admin si tu es connecté en Admin !
unset($_SESSION['client_id']);
unset($_SESSION['client_nom']);
unset($_SESSION['client_email']);

header('Location: connexion.php');
exit();
?>