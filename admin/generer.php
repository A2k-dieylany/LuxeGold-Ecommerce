<?php
// On génère un nouveau hash propre sur TON serveur
echo "Copie ce code dans ta base de données : <br><br>";
echo password_hash("admin123", PASSWORD_BCRYPT);
?>