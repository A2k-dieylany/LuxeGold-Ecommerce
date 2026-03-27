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
    $nom = trim($_POST['nom'] ?? 'Client Web');
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    
    $email = trim($_POST['email'] ?? ''); 
    $email_final = !empty($email) ? $email : null; 
    
    if (empty($telephone) || empty($nom)) {
        die("<h2 style='color:red; text-align:center;'>Le nom et le numéro de téléphone sont obligatoires. <br><a href='panier.php'>Retour au panier</a></h2>");
    }

    try {
        $pdo->beginTransaction();

        // ==========================================
        // ÉTAPE A : GESTION DU CLIENT (Blindage Anti-Doublon)
        // ==========================================
        $id_client = null;

        // PRIORITÉ AU COMPTE CONNECTÉ
        if (isset($_SESSION['client_id'])) {
            $id_client = $_SESSION['client_id'];
            
            $stmtUpdate = $pdo->prepare("UPDATE utilisateurs SET nom = ?, adresse = ?, telephone = ? WHERE id = ?");
            $stmtUpdate->execute([$nom, $adresse, $telephone, $id_client]);
        } else {
            // Logique pour les visiteurs non connectés
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
        } 

        // ==========================================
        // ÉTAPE B : ENREGISTREMENT DE LA COMMANDE ET DES DÉTAILS
        // ==========================================
        $total_commande = 0;

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

                // Ton insertion parfaite d'origine :
                $stmtDetail = $pdo->prepare("INSERT INTO details_commande (id_commande, id_produit, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
                $stmtDetail->execute([$id_commande, $produit['id'], $quantite, $prix_final]);
            }
        }

        // Remise du Code Promo (Si le client en a utilisé un)
        if (isset($_SESSION['promo'])) {
            $total_commande -= ($total_commande * ($_SESSION['promo']['remise'] / 100));
        }

        $stmtUpdateTotal = $pdo->prepare("UPDATE commandes SET total = ? WHERE id = ?");
        $stmtUpdateTotal->execute([$total_commande, $id_commande]);

        $pdo->commit();

        // ==========================================
        // ÉTAPE C : REDIRECTION VERS LE PAIEMENT EN LIGNE
        // ==========================================
        // On ne vide plus le panier ici (il sera vidé dans retour_paiement.php si le paiement réussit)
        
        header("Location: simulateur_paiement.php?cmd=" . $id_commande . "&montant=" . $total_commande);
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