<?php
session_start();
if (!isset($_GET['cmd']) || !isset($_GET['montant'])) {
    header('Location: index.php');
    exit();
}

$cmd_id = (int)$_GET['cmd'];
$montant = (float)$_GET['montant'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Sécurisé - Jewelry Beug Fallou</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .payment-card { background: white; width: 100%; max-width: 450px; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); overflow: hidden; }
        .header { background: #1a1a1a; color: #d4af37; padding: 25px; text-align: center; }
        .header h1 { margin: 0; font-size: 1.2rem; text-transform: uppercase; letter-spacing: 2px; }
        .header .amount { font-size: 2.5rem; color: white; margin: 15px 0 0 0; }
        .body { padding: 30px; }
        .secure-badge { display: flex; align-items: center; justify-content: center; gap: 10px; color: #27ae60; background: #e8f5e9; padding: 10px; border-radius: 8px; font-size: 0.9rem; font-weight: 600; margin-bottom: 25px; }
        .payment-methods { display: flex; gap: 10px; margin-bottom: 25px; }
        .method-btn { flex: 1; padding: 12px; border: 2px solid #eee; border-radius: 8px; background: white; cursor: pointer; font-weight: 600; transition: 0.3s; display: flex; flex-direction: column; align-items: center; gap: 5px; color: #666; }
        .method-btn i { font-size: 1.5rem; }
        .method-btn.active { border-color: #d4af37; color: #1a1a1a; background: #fffbf0; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 0.9rem; font-weight: 600; color: #444; }
        .form-group input, .form-group select { width: 100%; padding: 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; box-sizing: border-box; font-family: inherit; transition: 0.3s; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #d4af37; box-shadow: 0 0 0 3px rgba(212,175,55,0.1); }
        .form-group select { appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23131313%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right .7em top 50%; background-size: .65em auto; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .pay-btn { width: 100%; padding: 18px; background: #d4af37; color: white; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: 0.3s; display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 10px; }
        .pay-btn:hover { background: #b8960c; }
        .footer { text-align: center; padding: 20px; font-size: 0.8rem; color: #999; border-top: 1px solid #eee; }
        .spinner { display: none; width: 20px; height: 20px; border: 3px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: white; animation: spin 1s ease-in-out infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        #form-cb { display: none; } 
        .error-message { color: #e74c3c; font-size: 0.85rem; margin-top: 5px; display: none; }
    </style>
</head>
<body>

    <div class="payment-card">
        <div class="header">
            <h1>Jewelry Beug Fallou</h1>
            <h2 class="amount"><?= number_format($montant, 0, ',', ' ') ?> FCFA</h2>
            <p style="margin: 5px 0 0 0; color: #888; font-size: 0.9rem;">Commande #CMD-<?= str_pad($cmd_id, 4, '0', STR_PAD_LEFT) ?></p>
        </div>
        
        <div class="body">
            <div class="secure-badge">
                <i class="fas fa-lock"></i> Environnement 100% sécurisé (SSL)
            </div>

            <div class="payment-methods">
                <button class="method-btn active" id="btn-mm" type="button">
                    <i class="fas fa-mobile-alt" style="color: #ff6600;"></i>
                    Mobile Money
                </button>
                <button class="method-btn" id="btn-cb" type="button">
                    <i class="far fa-credit-card" style="color: #3498db;"></i>
                    Carte Bancaire
                </button>
            </div>

            <form id="paymentForm">
                
                <div id="form-mm">
                    <div class="form-group">
                        <label>Choisissez votre opérateur</label>
                        <select required id="select-operateur">
                            <option value="">Sélectionnez...</option>
                            <option value="wave">Wave</option>
                            <option value="orange">Orange Money</option>
                            <option value="free">Free Money</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Numéro de téléphone</label>
                        <input type="tel" placeholder="Ex: 77 123 45 67" required id="input-mm" pattern="[0-9\s]{9,15}" title="Veuillez entrer un numéro valide">
                    </div>
                </div>

                <div id="form-cb">
                    <div class="form-group">
                        <label>Numéro de la carte</label>
                        <input type="text" placeholder="0000 0000 0000 0000" id="input-cb1" maxlength="19" pattern="^([0-9]{4}\s?){4}$" title="Veuillez entrer un numéro de carte à 16 chiffres">
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Expiration (MM/AA)</label>
                            <input type="text" placeholder="MM/AA" id="input-cb2" maxlength="5" pattern="^(0[1-9]|1[0-2])\/([0-9]{2})$" title="Le format doit être MM/AA (ex: 12/26)">
                        </div>
                        <div class="form-group">
                            <label>CVC / CVV</label>
                            <input type="text" placeholder="123" id="input-cb3" maxlength="3" pattern="^[0-9]{3}$" title="Veuillez entrer le cryptogramme à 3 chiffres">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="pay-btn" id="btnSubmit">
                    <span id="btnText">Payer <?= number_format($montant, 0, ',', ' ') ?> FCFA</span>
                    <div class="spinner" id="spinner"></div>
                </button>
            </form>
        </div>
        
        <div class="footer">
            <i class="fas fa-shield-alt"></i> Vos données sont chiffrées de bout en bout.
        </div>
    </div>

    <script>
        // --- LOGIQUE DES ONGLETS ---
        const btnMm = document.getElementById('btn-mm');
        const btnCb = document.getElementById('btn-cb');
        const formMm = document.getElementById('form-mm');
        const formCb = document.getElementById('form-cb');
        
        const selectOp = document.getElementById('select-operateur');
        const inputMm = document.getElementById('input-mm');
        const inputCb1 = document.getElementById('input-cb1');
        const inputCb2 = document.getElementById('input-cb2');
        const inputCb3 = document.getElementById('input-cb3');

        btnMm.addEventListener('click', () => {
            btnMm.classList.add('active');
            btnCb.classList.remove('active');
            formMm.style.display = 'block';
            formCb.style.display = 'none';
            
            // On rend les champs MM requis et on retire ceux de la CB
            selectOp.setAttribute('required', 'required');
            inputMm.setAttribute('required', 'required');
            inputCb1.removeAttribute('required');
            inputCb2.removeAttribute('required');
            inputCb3.removeAttribute('required');
        });

        btnCb.addEventListener('click', () => {
            btnCb.classList.add('active');
            btnMm.classList.remove('active');
            formCb.style.display = 'block';
            formMm.style.display = 'none';
            
            // On rend les champs CB requis et on retire ceux du MM
            inputCb1.setAttribute('required', 'required');
            inputCb2.setAttribute('required', 'required');
            inputCb3.setAttribute('required', 'required');
            selectOp.removeAttribute('required');
            inputMm.removeAttribute('required');
        });

        // --- FORMATAGE AUTOMATIQUE (UX) ---
        // Numéro de carte (ajout d'espaces)
        inputCb1.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Garde que les chiffres
            let formattedValue = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) formattedValue += ' ';
                formattedValue += value[i];
            }
            e.target.value = formattedValue;
        });

        // Expiration (ajout du slash)
        inputCb2.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });

        // CVC (Que des chiffres)
        inputCb3.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });

        // --- SOUMISSION DU FORMULAIRE ---
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validation Front-end supplémentaire pour la date d'expiration
            if (formCb.style.display === 'block') {
                const expParts = inputCb2.value.split('/');
                if (expParts.length === 2) {
                    const month = parseInt(expParts[0], 10);
                    if (month < 1 || month > 12) {
                        alert("Mois d'expiration invalide.");
                        return;
                    }
                    // Tu pourrais aussi vérifier l'année ici (ex: > 24)
                }
            }

            const btnText = document.getElementById('btnText');
            const spinner = document.getElementById('spinner');
            const btn = document.getElementById('btnSubmit');

            // Animation
            btnText.innerText = 'Traitement en cours...';
            spinner.style.display = 'block';
            btn.style.opacity = '0.8';
            btn.disabled = true;

            // Envoi des données (on simule l'appel API)
            // Dans un vrai cas, on enverrait les données via fetch/AJAX ici
            
            setTimeout(() => {
                window.location.href = 'retour_paiement.php?cmd=<?= $cmd_id ?>';
            }, 2500);
        });
    </script>
</body>
</html>