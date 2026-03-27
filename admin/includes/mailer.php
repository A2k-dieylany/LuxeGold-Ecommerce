<?php
// admin/includes/mailer.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Chemins vers PHPMailer
require_once dirname(__FILE__) . '/PHPMailer/src/Exception.php';
require_once dirname(__FILE__) . '/PHPMailer/src/PHPMailer.php';
require_once dirname(__FILE__) . '/PHPMailer/src/SMTP.php';

/**
 * Fonction pour envoyer l'email avec la facture PDF en pièce jointe
 */
function envoyerEmailLivraison($pdo, $email_client, $nom_client, $id_commande) {
    if (empty($email_client)) return false;

    $mail = new PHPMailer(true);

    try {
        // 1. Configuration du serveur SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        
       
        // --- ⚠️ À MODIFIER AVEC TES INFOS ---
        $mail->Username   = 'bayedjilykhoumakhouma@gmail.com'; 
        // Colle ici le mot de passe de 16 caractères généré par Google (sans espaces)
        $mail->Password   = 'yygfhwbfqtrpjnmo'; 
        // ------------------------------------
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587; 
        $mail->CharSet    = 'UTF-8';

        // 2. Destinataire et Expéditeur (Nom de la boutique corrigé)
        $mail->setFrom($mail->Username, 'Jewelry Beug Fallou');
        $mail->addAddress($email_client, $nom_client);

        // =========================================================
        // 3. LA MAGIE DE LA FACTURE EN PIÈCE JOINTE
        // =========================================================
        $chemin_generateur = dirname(__FILE__) . '/generateur_facture.php';
        if (file_exists($chemin_generateur)) {
            require_once $chemin_generateur;
            if (function_exists('genererLaFacturePDF')) {
                // On génère la facture en mémoire (Mode 'S')
                $facture_pdf = genererLaFacturePDF($pdo, $id_commande, 'S');
                
                if ($facture_pdf) {
                    $nom_fichier = 'Facture_Jawelry_CMD_' . $id_commande . '.pdf';
                    // On attache le PDF virtuel au mail
                    $mail->addStringAttachment($facture_pdf, $nom_fichier, 'base64', 'application/pdf');
                }
            }
        }
        // =========================================================

        // 4. Contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = "💎 Votre commande Jewelry #$id_commande a été livrée !";
        
        // Le corps de l'email avec le bon nom de boutique
        $mail->Body = "
        <div style='font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif; background-color: #f9f9f9; padding: 40px 0;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);'>
                
                <div style='background-color: #1a1a1a; padding: 30px; text-align: center;'>
                    <h1 style='color: #d4af37; margin: 0; font-size: 24px; letter-spacing: 2px;'>JEWELRY</h1>
                    <p style='color: #d4af37; margin: 5px 0 0 0; font-size: 14px; letter-spacing: 1px;'>BEUG FALLOU</p>
                </div>
                
                <div style='padding: 40px 30px; color: #333333; line-height: 1.6;'>
                    <h2 style='font-size: 20px; color: #1a1a1a; margin-top: 0;'>Bonjour $nom_client,</h2>
                    
                    <p>Nous avons le plaisir de vous informer que votre commande <strong>#$id_commande</strong> a été marquée comme <strong>livrée</strong> avec succès.</p>
                    
                    <div style='background-color: #fdfaf0; border-left: 4px solid #d4af37; padding: 15px 20px; margin: 25px 0;'>
                        <p style='margin: 0; color: #888;'><em>Vous trouverez la facture officielle de votre achat en pièce jointe de cet email.</em></p>
                    </div>
                    
                    <p>Nous espérons que cette pièce d'exception vous apportera entière satisfaction. Si vous avez la moindre question concernant votre bijou, notre service client reste à votre entière disposition.</p>
                </div>
                
                <div style='background-color: #f4f4f4; padding: 20px; text-align: center; color: #888888; font-size: 12px;'>
                    <p style='margin: 0;'>Avec toute notre gratitude,<br><strong>L'équipe Jewelry Beug Fallou</strong></p>
                </div>
                
            </div>
        </div>";

        // Version texte brut
        $mail->AltBody = "Bonjour $nom_client. Votre commande Jewelry #$id_commande a été livrée. Vous trouverez votre facture en pièce jointe. Nous espérons que vous en êtes satisfait. L'équipe Jewelry Beug Fallou.";

        // 5. Envoi
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Erreur Mailer Jewelry (Cmd #$id_commande) : {$mail->ErrorInfo}");
        return false;
    }
}
?>