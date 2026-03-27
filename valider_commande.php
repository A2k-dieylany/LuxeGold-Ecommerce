<?php
session_start();
require_once 'config/db.php';

// 1. Sécurité : Vérifier que le panier n'est pas vide
if (empty($_SESSION['panier'])) {
    header('Location: panier.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. Récupérer les infos du client depuis le formulaire du panier
    $nom = trim($_POST['nom'] ?? 'Client WhatsApp');
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    
    $email = trim($_POST['email'] ?? ''); 
    $email_final = !empty($email) ? $email : null; 
    
    if (empty($telephone) || empty($nom)) {
        die("<h2 style='color:red; text-align:center;'>Le nom et le numéro de téléphone sont obligatoires. <br><a href='panier.php'>Retour au panier</a></h2>");
    }

    // --- SÉCURITÉ : VALIDATION STRICTE DU TÉLÉPHONE (International) ---
    // On nettoie les espaces et tirets pour vérifier la structure
    $tel_propre = preg_replace('/[\s\-]/', '', $telephone);
    if (!preg_match('/^\+?[0-9]{8,20}$/', $tel_propre)) {
        die("<div style='font-family:sans-serif; text-align:center; padding:50px; background:#fafafa; min-height:100vh;'>
                <h2 style='color:#e74c3c;'>Format de numéro de téléphone invalide.</h2>
                <p>Veuillez entrer un numéro valide composé de chiffres (ex: +221 77 123 45 67).</p>
                <a href='panier.php' style='padding:10px 20px; background:#25D366; color:white; text-decoration:none; border-radius:5px;'>Retour au panier</a>
             </div>");
    }
    // -------------------------------------------------

    try {
        $pdo->beginTransaction();

        // ==========================================
        // ÉTAPE A : GESTION DU CLIENT (Blindage Anti-Doublon)
        // ==========================================
        $id_client = null;

        // --- NOUVEAU : PRIORITÉ AU COMPTE CONNECTÉ ---
        if (isset($_SESSION['client_id'])) {
            // Le client est connecté à son Espace Client ! On prend directement son ID.
            $id_client = $_SESSION['client_id'];
            
            // On en profite pour mettre à jour sa dernière adresse de livraison
            $stmtUpdate = $pdo->prepare("UPDATE utilisateurs SET nom = ?, adresse = ?, telephone = ? WHERE id = ?");
            $stmtUpdate->execute([$nom, $adresse, $telephone, $id_client]);
        } else {
        // ---------------------------------------------
            // L'ancienne logique pour les visiteurs non connectés
            $stmtCheck = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? OR telephone = ? LIMIT 1");
            $stmtCheck->execute([$email_final, $telephone]);
            $client_existant = $stmtCheck->fetch();

            if ($client_existant) {
                $id_client = $client_existant['id'];
                $stmtUpdate = $pdo->prepare("UPDATE utilisateurs SET nom = ?, adresse = ? WHERE id = ?");
                $stmtUpdate->execute([$nom, $adresse, $id_client]);
            } else {
                try {
                    $stmtInsert = $pdo->prepare("INSERT INTO utilisateurs (nom, email, telephone, adresse) VALUES (?, ?, ?, ?)");
                    $stmtInsert->execute([$nom, $email_final, $telephone, $adresse]);
                    $id_client = $pdo->lastInsertId(); 
                } catch (PDOException $ex) {
                    if ($ex->getCode() == 23000) {
                        $stmtRecover = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? OR telephone = ? LIMIT 1");
                        $stmtRecover->execute([$email_final, $telephone]);
                        $id_client = $stmtRecover->fetchColumn();
                        if (!$id_client) throw $ex; 
                    } else {
                        throw $ex; 
                    }
                }
            }
        } // Fin du bloc connecté/visiteur

        // ==========================================
        // ÉTAPE B : ENREGISTREMENT DE LA COMMANDE ET DES DÉTAILS
        // ==========================================
        $total_commande = 0;
        $details_whatsapp = ""; 

        // Le code insère bien id_utilisateur, nom_client et telephone_client !
        $stmtCmd = $pdo->prepare("INSERT INTO commandes (id_utilisateur, nom_client, telephone_client, total, devise, statut) VALUES (?, ?, ?, 0, 'FCFA', 'en_attente')");
        $stmtCmd->execute([$id_client, $nom, $telephone]);
        $id_commande = $pdo->lastInsertId();

        foreach ($_SESSION['panier'] as $id_produit => $quantite) {
            $stmtProd = $pdo->prepare("SELECT id, nom, prix, reduction FROM produits WHERE id = ?");
            $stmtProd->execute([$id_produit]);
            $produit = $stmtProd->fetch();

            if ($produit) {
                $prix_final = $produit['reduction'] > 0 ? ($produit['prix'] * (1 - ($produit['reduction'] / 100))) : $produit['prix'];
                $sous_total = $prix_final * $quantite;
                $total_commande += $sous_total;

                $stmtDetail = $pdo->prepare("INSERT INTO details_commande (id_commande, id_produit, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
                $stmtDetail->execute([$id_commande, $produit['id'], $quantite, $prix_final]);

                $details_whatsapp .= "▪️ " . $produit['nom'] . " (x" . $quantite . ") : " . number_format($sous_total, 0, ',', ' ') . " FCFA\n";
            }
        }

        $stmtUpdateTotal = $pdo->prepare("UPDATE commandes SET total = ? WHERE id = ?");
        $stmtUpdateTotal->execute([$total_commande, $id_commande]);

        $pdo->commit();

        // ==========================================
        // ÉTAPE C : REDIRECTION VERS WHATSAPP
        // ==========================================
        unset($_SESSION['panier']);

        $stmtParam = $pdo->query("SELECT telephone_whatsapp FROM parametres WHERE id = 1");
        $param = $stmtParam->fetch();
        
        $numero_boutique = ($param && !empty($param['telephone_whatsapp'])) ? $param['telephone_whatsapp'] : "221770000000";
        $numero_boutique = preg_replace('/[^0-9]/', '', $numero_boutique); 
        
        $message = "💎 *NOUVELLE COMMANDE JEWELRY* 💎\n\n";
        $message .= "Réf de commande : #CMD-$id_commande\n\n";
        $message .= "*👤 Informations Client :*\n";
        $message .= "Nom : $nom\n";
        $message .= "Tél : $telephone\n";
        if (!empty($adresse)) $message .= "Lieu de livraison : $adresse\n";
        if ($email_final) $message .= "Email : $email_final\n";
        $message .= "\n*🛍️ Détails :*\n$details_whatsapp\n";
        $message .= "💰 *TOTAL À PAYER : " . number_format($total_commande, 0, ',', ' ') . " FCFA*";

        $url_whatsapp = "https://wa.me/" . $numero_boutique . "?text=" . urlencode($message);
        header("Location: " . $url_whatsapp);
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("<h2 style='color:red; font-family:sans-serif; padding: 20px; text-align:center;'>Erreur système : " . $e->getMessage() . "</h2>");
    }
} else {
    header('Location: panier.php');
    exit();
}
?>