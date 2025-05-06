# B2BTravel

## Description du Projet

**B2BTravel** est une application web développée avec Symfony, destinée à faciliter la gestion des voyages professionnels pour les entreprises. Elle centralise les besoins liés aux utilisateurs, hébergements, réclamations, événements et fidélité, tout en offrant une interface intuitive et sécurisée.

### Objectifs :
- Simplifier la gestion des voyages d'affaires.
- Offrir une solution tout-en-un pour les gestionnaires RH, employés et prestataires de services.
- Automatiser les processus de réservation, de réclamation et de suivi de fidélité.

## Table des Matières

- [Installation](#installation)
- [Utilisation](#utilisation)
- [Modules du Projet](#modules-du-projet)
- [Contribution](#contribution)
- [Licence](#licence)

## Installation

1. Clonez le repository :
git clone https://github.com/ckthiri1/B2BTravelWeb.git
cd B2BTravelWeb
2.Installez les dépendances :
-composer install
3.Configurez votre base de données :
cp .env .env.local
# Modifier les paramètres DATABASE_URL dans .env.local
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
4.Démarrez le serveur :
symfony server:start


## 🛠️ Utilisation
Le projet repose sur les technologies suivantes :

PHP 8.1+

Symfony 6.4

Twig (moteur de templates)

MySQL (base de données relationnelle)

Webpack Encore (gestion des assets)

Composer (gestionnaire de dépendances PHP)

Naviguez ensuite vers http://b2btravel:8000 pour accéder à l'application.


## 📦 Modules du Projet

👤 Gestion des Utilisateurs
Connexion / Inscription

Rôles (admin, client)

Profil utilisateur

Nombre de voyages réalisés

reconaissance facial

🏨 Gestion des Hébergements
Ajout, modification, suppression d’hébergements

Réservation par les utilisateurs

Filtres par type, ville, prix

📬 Gestion des Réclamations
Envoi de réclamations avec pièces jointes

Suivi de statut (en attente, traité, rejeté)

Interface d'administration pour modération

✈️ Gestion des Voyages
Planification de voyages

Affectation de participants

Intégration avec hébergements et événements

🎉 Gestion des Événements
Création d'événements liés à un voyage

Inscription utilisateurs

Calendrier des événements

🎁 Gestion de la Fidélité
Attribution de points en fonction des voyages

Échange de points contre des récompenses

Historique de fidélité

##  Contribution
Les contributions sont les bienvenues pour améliorer ce projet. Pour contribuer :

Forkez le projet.

Créez une branche (git checkout -b feature/ma-nouvelle-fonctionnalite).

Commitez vos changements (git commit -m "Ajout de nouvelle fonctionnalité").

Poussez votre branche (git push origin feature/ma-nouvelle-fonctionnalite).

Créez une Pull Request.

📄 ##  License
Ce projet est sous licence MIT. Vous pouvez l’utiliser, le modifier et le redistribuer librement.

🔗 Dépôt GitHub : https://github.com/ckthiri1/B2BTravelWeb
