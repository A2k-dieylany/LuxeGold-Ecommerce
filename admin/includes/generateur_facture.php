<?php
// admin/includes/generateur_facture.php

// --- SÉCURISATION DES CHEMINS (Fonctionne peu importe d'où on l'appelle) ---
if (!defined('FPDF_FONTPATH')) {
    define('FPDF_FONTPATH', dirname(__FILE__) . '/../../includes/font/');
}
// Inclusion de FPDF depuis la racine du site
require_once dirname(__FILE__) . '/../../includes/fpdf.php'; 
// -------------------------------------------------------------------------

// On englobe ta classe PDF pour éviter les conflits si on l'appelle 2 fois
if (!class_exists('PDF_Facture')) {
    class PDF_Facture extends FPDF {
        function Header() {
            // Nom de l'entreprise (Gauche)
            $this->SetFont('Arial', 'B', 24);
            $this->SetTextColor(212, 175, 55); // Or
            $this->Cell(100, 10, 'JEWELRY', 0, 0, 'L');
            
            // Titre FACTURE (Droite)
            $this->SetFont('Arial', 'B', 22);
            $this->SetTextColor(26, 26, 26); // Noir profond
            $this->Cell(90, 10, 'FACTURE', 0, 1, 'R');
            
            // Sous-titre entreprise
            $this->SetFont('Arial', '', 12);
            $this->SetTextColor(212, 175, 55);
            $this->Cell(100, 5, 'BEUG FALLOU', 0, 0, 'L');
            $this->Ln(10);
            
            // Coordonnées entreprise
            $this->SetFont('Arial', '', 10);
            $this->SetTextColor(100, 100, 100);
            $this->Cell(100, 5, utf8_decode('Marché Serekunda, Gambie / Dakar, Sénégal'), 0, 0, 'L');
            $this->Cell(90, 5, '', 0, 1, 'R'); // Espace
            $this->Cell(100, 5, '+220 2657822', 0, 0, 'L');
            $this->Ln(15);
            
            // Ligne de séparation dorée
            $this->SetDrawColor(212, 175, 55);
            $this->SetLineWidth(0.5);
            $this->Line(10, 45, 200, 45);
            $this->SetLineWidth(0.2); // Remise à zéro
            $this->Ln(5);
        }

        function Footer() {
            $this->SetY(-25);
            $this->SetDrawColor(220, 220, 220);
            $this->Line(10, $this->GetY(), 200, $this->GetY());
            $this->Ln(5);
            
            $this->SetFont('Arial', 'I', 9);
            $this->SetTextColor(150, 150, 150);
            $this->Cell(0, 5, utf8_decode('Page ') . $this->PageNo() . '/{nb} - Merci de votre confiance !', 0, 0, 'C');
        }
    }
}

/**
 * Fonction principale qui génère ton design
 * $mode = 'I' (Navigateur) ou 'S' (Email)
 */
function genererLaFacturePDF($pdo, $id_commande, $mode = 'I') {
    // 1. Récupérer la commande ET les infos de l'utilisateur (Adresse)
    $stmt = $pdo->prepare("
        SELECT c.*, u.adresse, u.telephone AS tel_utilisateur 
        FROM commandes c 
        LEFT JOIN utilisateurs u ON c.id_utilisateur = u.id 
        WHERE c.id = ?
    ");
    $stmt->execute([$id_commande]);
    $commande = $stmt->fetch();

    if (!$commande) { return false; } // Modifié pour ne pas bloquer l'email

    // 2. Récupérer les détails
    $stmtDetails = $pdo->prepare("
        SELECT d.*, p.nom 
        FROM details_commande d 
        JOIN produits p ON d.id_produit = p.id 
        WHERE d.id_commande = ?
    ");
    $stmtDetails->execute([$id_commande]);
    $details = $stmtDetails->fetchAll();

    // --- CRÉATION DU NUMÉRO DE FACTURE ---
    $numero_facture = 'CMD-' . str_pad($commande['id'], 4, '0', STR_PAD_LEFT);

    $pdf = new PDF_Facture(); // Utilisation de la nouvelle classe
    $pdf->AliasNbPages();
    $pdf->AddPage();

    // --- INFORMATIONS CLIENT ET FACTURE ---
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(150, 150, 150); // Gris clair
    $pdf->Cell(100, 5, utf8_decode('FACTURÉ À :'), 0, 0, 'L');
    $pdf->Cell(90, 5, utf8_decode('DÉTAILS DE LA FACTURE :'), 0, 1, 'R');
    $pdf->Ln(2);

    // Préparation des variables clients
    $nom_client = !empty($commande['nom_client']) ? $commande['nom_client'] : 'Client (Via WhatsApp)';
    $telephone = !empty($commande['telephone_client']) ? $commande['telephone_client'] : (!empty($commande['tel_utilisateur']) ? $commande['tel_utilisateur'] : 'Non renseigné');
    $adresse = !empty($commande['adresse']) ? $commande['adresse'] : 'Non renseignée';
    $statut = isset($commande['statut']) ? $commande['statut'] : 'En attente';

    // Ligne 1 : Nom et N° de facture
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(26, 26, 26);
    $pdf->Cell(100, 6, utf8_decode($nom_client), 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetTextColor(212, 175, 55);
    $pdf->Cell(90, 6, utf8_decode('N° : ') . $numero_facture, 0, 1, 'R');

    // Ligne 2 : Téléphone et Date
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetTextColor(26, 26, 26);
    $pdf->Cell(100, 6, utf8_decode('Tél : ' . $telephone), 0, 0, 'L');
    // Sécurité pour la date
    $date_cmd = !empty($commande['created_at']) ? $commande['created_at'] : (!empty($commande['date_creation']) ? $commande['date_creation'] : date('Y-m-d'));
    $pdf->Cell(90, 6, 'Date : ' . date('d/m/Y', strtotime($date_cmd)), 0, 1, 'R');

    // Ligne 3 : Adresse et Statut
    $pdf->Cell(100, 6, utf8_decode('Adresse : ' . $adresse), 0, 0, 'L');
    $pdf->Cell(90, 6, 'Statut : ' . utf8_decode($statut), 0, 1, 'R');

    $pdf->Ln(12);

    // --- TABLEAU DES PRODUITS ---
    $pdf->SetFillColor(212, 175, 55); 
    $pdf->SetTextColor(255, 255, 255); 
    $pdf->SetDrawColor(212, 175, 55); 
    $pdf->SetFont('Arial', 'B', 11);

    $pdf->Cell(90, 12, utf8_decode('  Désignation du produit'), 1, 0, 'L', true);
    $pdf->Cell(35, 12, 'Prix Unitaire', 1, 0, 'C', true);
    $pdf->Cell(25, 12, utf8_decode('Qté'), 1, 0, 'C', true);
    $pdf->Cell(40, 12, 'Total', 1, 1, 'C', true);

    $pdf->SetTextColor(26, 26, 26);
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetDrawColor(230, 230, 230); 
    $pdf->SetFillColor(248, 248, 248); 

    $fill = false;
    foreach ($details as $d) {
        $total_ligne = $d['prix_unitaire'] * $d['quantite'];
        
        $pdf->Cell(90, 10, '  ' . utf8_decode($d['nom']), 'LRB', 0, 'L', $fill);
        $pdf->Cell(35, 10, number_format($d['prix_unitaire'], 0, ',', ' ') . ' FCFA', 'LRB', 0, 'R', $fill);
        $pdf->Cell(25, 10, $d['quantite'], 'LRB', 0, 'C', $fill);
        $pdf->Cell(40, 10, number_format($total_ligne, 0, ',', ' ') . ' FCFA', 'LRB', 1, 'R', $fill);
        
        $fill = !$fill; 
    }
    $pdf->Ln(10);

    // --- BLOC DES TOTAUX ---
    $pdf->SetX(115);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(26, 26, 26);
    $pdf->Cell(35, 10, 'TOTAL :', 0, 0, 'R');
    $pdf->Cell(40, 10, number_format($commande['total'], 0, ',', ' ') . ' FCFA', 0, 1, 'R');

    $pdf->SetX(115);
    $pdf->SetDrawColor(212, 175, 55);
    $pdf->Cell(75, 0, '', 'T', 1);
    $pdf->Ln(2);

    $pdf->SetX(115);
    $taux_dalasi = 0.115;
    $total_gmd = ceil($commande['total'] * $taux_dalasi); 

    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(212, 175, 55); 
    $pdf->Cell(35, 10, 'En Dalasi :', 0, 0, 'R');
    $pdf->Cell(40, 10, number_format($total_gmd, 0, ',', ' ') . ' GMD', 0, 1, 'R');

    $pdf->SetX(115);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->Cell(75, 5, utf8_decode('* Taux indicatif : 1000 FCFA = 115 GMD'), 0, 1, 'R');

    $pdf->SetY(-60);
    $pdf->SetFont('Arial', 'I', 11);
    $pdf->SetTextColor(26, 26, 26);
    $pdf->Cell(0, 10, utf8_decode('L\'équipe Jewelry Beug Fallou vous remercie pour votre commande.'), 0, 1, 'C');

    // On sort le fichier selon le mode défini (I = Navigateur, S = Email)
    return $pdf->Output($mode, 'Facture-' . $numero_facture . '.pdf');
}
?>