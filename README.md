# 💎 LuxeGold (Jewelry Beug Fallou)

Une plateforme e-commerce complète spécialisée dans la vente de bijoux haut de gamme. Ce projet a été développé dans le cadre de mon projet academique, avec pour objectif de créer une expérience d'achat fluide pour le client et un outil de gestion puissant pour l'administrateur.

## 🚀 Fonctionnalités Principales

### 🛍️ Espace Client (Front-Office)
* **Catalogue dynamique :** Navigation fluide dans les collections de bijoux (bagues, colliers, bracelets, etc.).
* **Fiches produits détaillées :** Affichage des stocks en temps réel, galerie d'images et calcul automatique des réductions.
* **Preuve sociale :** Système d'avis et de notation (1 à 5 étoiles) soumis à modération.
* **Panier intelligent :** Gestion dynamique du panier avec contrôle strict des quantités disponibles en stock.

### ⚙️ Espace Administrateur (Back-Office)
* **Tableau de Bord (Dashboard) :** Centre de pilotage avec indicateurs clés (KPI) et alertes de rupture de stock.
* **Moteur d'Analyse Financière :** * Calcul automatique du Chiffre d'Affaires Brut et du Bénéfice Net.
  * Gestion multi-devises intégrée (FCFA et Dalasi Gambien - GMD).
  * Génération du Top 5 des meilleurs clients du mois.
* **Graphiques interactifs :** Suivi des tendances de vente sur 7 jours et répartition du CA par catégorie (via Chart.js).
* **Modération des avis :** Validation ou suppression des retours clients avant publication.
* **Exportation comptable :** Génération de rapports financiers aux formats PDF et Excel.

## 💻 Technologies Utilisées

* **Backend :** PHP (Architecture modulaire)
* **Base de données :** MySQL (Requêtes préparées via PDO pour une sécurité maximale)
* **Frontend :** HTML5, CSS3, JavaScript (Vanilla)
* **Librairies externes :** * [FontAwesome](https://fontawesome.com/) (Icônes)
  * [Chart.js](https://www.chartjs.org/) (Visualisation de données)

## 🛠️ Installation en local

Si vous souhaitez tester le projet sur votre machine (via XAMPP, WAMP, etc.) :

1. Clonez ce dépôt dans votre dossier serveur (`htdocs` ou `www`) :
   ```bash
   git clone [https://github.com/VOTRE-NOM/LuxeGold-Ecommerce.git](https://github.com/VOTRE-NOM/LuxeGold-Ecommerce.git)