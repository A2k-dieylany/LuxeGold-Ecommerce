<?php
// On s'assure que la session est bien démarrée (via db.php)
require_once 'config/db.php';

// On récupère l'ID et la QTE soit via POST (formulaire) soit via GET (lien/ajax)
$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$qte = isset($_REQUEST['qte']) ? (int)$_REQUEST['qte'] : 1;

if($id > 0 && $qte > 0) {
    // Initialisation du panier si inexistant
    if(!isset($_SESSION['panier'])) { 
        $_SESSION['panier'] = []; 
    }
    
    // Si le bijou est déjà présent, on incrémente
    if(isset($_SESSION['panier'][$id])) {
        $_SESSION['panier'][$id] += $qte;
    } else {
        $_SESSION['panier'][$id] = $qte;
    }
    
    // SI C'EST UNE REQUÊTE AJAX (JavaScript)
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode([
            'success' => true, 
            'nouveauTotal' => array_sum($_SESSION['panier'])
        ]);
        exit;
    } 
    
    // SI C'EST UN FORMULAIRE CLASSIQUE (notre bouton actuel)
    header('Location: panier.php');
    exit();

} else {
    // Gestion d'erreur
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo json_encode(['success' => false, 'message' => 'ID ou quantité invalide']);
    } else {
        header('Location: index.php');
    }
    exit();
}