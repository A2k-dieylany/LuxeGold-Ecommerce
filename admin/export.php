<?php
session_start();
require_once 'check_auth.php';
require_once '../config/db.php';

// 1. Définir le nom du fichier
$nom_fichier = "Export_Ventes_LuxeGold_" . date('Y-m-d_H-i') . ".csv";

// 2. Entêtes HTTP strictes
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nom_fichier . '"');
header('Pragma: no-cache');
header('Expires: 0');

// 3. Ouvrir le flux de sortie
$output = fopen('php://output', 'w');

// Ajouter le BOM UTF-8 indispensable pour Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// 4. Définir les colonnes du tableau (Séparateur ';')
fputcsv($output, ['ID Commande', 'Date', 'Client', 'Téléphone', 'Adresse', 'Statut', 'Montant Total', 'Devise'], ';');

// 5. Récupérer les données de la base
try {
    $query = $pdo->query("
        SELECT 
            c.id, 
            c.nom_client, 
            c.telephone_client, 
            c.created_at, 
            c.date_creation, 
            c.statut, 
            c.total, 
            c.devise, 
            u.adresse, 
            u.telephone AS tel_utilisateur 
        FROM commandes c 
        LEFT JOIN utilisateurs u ON c.id_utilisateur = u.id 
        ORDER BY c.id DESC
    ");
    
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        
        $id_facture = 'CMD-' . str_pad($row['id'], 4, '0', STR_PAD_LEFT);
        
        // --- DATE ---
        $raw_date = !empty($row['created_at']) ? $row['created_at'] : (!empty($row['date_creation']) ? $row['date_creation'] : null);
        $date_format = $raw_date ? date('d/m/Y H:i', strtotime($raw_date)) : 'Non renseignée';
        
        $nom = !empty($row['nom_client']) ? $row['nom_client'] : 'Client Anonyme';
        
        // --- TÉLÉPHONE (LE HACK EXCEL ULTIME) ---
        $telephone = !empty($row['telephone_client']) ? $row['telephone_client'] : (!empty($row['tel_utilisateur']) ? $row['tel_utilisateur'] : 'Non renseigné');
        
        // Si on a un numéro, on l'enveloppe dans une formule Excel pour forcer le format TEXTE
        // Ex: 771234567 devient ="771234567" dans le CSV
        if ($telephone !== 'Non renseigné') {
            $telephone_excel = '="' . $telephone . '"';
        } else {
            $telephone_excel = $telephone;
        }

        $adresse = !empty($row['adresse']) ? $row['adresse'] : 'Non renseignée';
        $statut = mb_strtoupper($row['statut'], 'UTF-8');
        $montant = number_format($row['total'], 0, '', ''); 
        
        // On écrit la ligne
        fputcsv($output, [
            $id_facture,
            $date_format,
            $nom,
            $telephone_excel,
            $adresse,
            $statut,
            $montant,
            $row['devise']
        ], ';');
    }
} catch (PDOException $e) {
    error_log("Erreur Export CSV : " . $e->getMessage());
    fputcsv($output, ['Erreur technique lors de la génération de l\'export'], ';');
}

fclose($output);
exit();
?>