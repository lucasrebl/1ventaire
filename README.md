# 1ventaire - Système de gestion d'inventaires

1ventaire est une application web permettant de gérer différents types d'inventaires, avec la possibilité de partager des inventaires entre utilisateurs, de suivre les dates d'expiration des articles et d'organiser les items par catégorie et emplacement.

## Fonctionnalités

- Création et gestion d'inventaires personnels
- Partage d'inventaires avec d'autres utilisateurs
- Suivi des dates d'expiration des articles
- Catégorisation des articles
- Gestion des emplacements
- Recherche et filtrage des articles
- Système d'authentification et de gestion des comptes utilisateurs

## Technologies utilisées

- **Backend** : PHP 8.2 avec Symfony 7.2
- **Base de données** : MySQL 8.0
- **Conteneurisation** : Docker & Docker Compose
- **Serveur web** : Nginx
- **Frontend** : Twig, Stimulus, Turbo (Symfony UX)
- **ORM** : Doctrine

## Prérequis

- Docker et Docker Compose installés sur votre machine
- Git pour cloner le dépôt

## Installation

### 1. Cloner le dépôt

```bash
git clone <url-du-repo>
cd 1ventaire
```

### 2. Créer un fichier .env.local

Créez un fichier `.env.local` à la racine du projet avec le contenu suivant :

```dotenv
DATABASE_URL="mysql://1ventaire_user:1ventaire_password@database:3306/1ventaire_db?serverVersion=8.0.32&charset=utf8mb4"
```

### 3. Démarrer les containers Docker

```bash
docker-compose up -d
```

Cette commande va créer et démarrer les containers pour l'application PHP, la base de données MySQL et le serveur Nginx.

### 4. Installation des dépendances

```bash
docker exec -it 1ventaire_app composer install
```

### 5. Configuration de la base de données

Si nécessaire, connectez-vous au container MySQL pour vérifier que l'utilisateur et la base de données sont correctement configurés :

```bash
docker exec -it 1ventaire_mysql mysql -u root -p
```
(Le mot de passe est "1ventaire_root_password")

Vous pouvez créer l'utilisateur MySQL si nécessaire :

```mysql
CREATE USER '1ventaire_user'@'%' IDENTIFIED BY '1ventaire_password';
GRANT ALL PRIVILEGES ON 1ventaire_db.* TO '1ventaire_user'@'%';
FLUSH PRIVILEGES;
```

Pour voir les bases de données :
```mysql
SHOW DATABASES;
```

### 6. Créer le schéma de base de données

```bash
docker exec -it 1ventaire_app php bin/console doctrine:migrations:migrate
```

### 7. (Optionnel) Charger les données de test

```bash
docker exec -it 1ventaire_app php bin/console doctrine:fixtures:load
```

## Accès à l'application

Une fois l'installation terminée, vous pouvez accéder à l'application via :

- http://localhost:8000

## Structure du projet

- `src/Controller/` : Contrôleurs Symfony qui gèrent les requêtes HTTP
- `src/Entity/` : Entités Doctrine qui représentent les tables de la base de données
- `src/Form/` : Classes de formulaires Symfony
- `src/Repository/` : Classes pour effectuer des requêtes sur les entités
- `templates/` : Templates Twig pour l'interface utilisateur
- `migrations/` : Fichiers de migration de base de données
- `public/` : Fichiers accessibles publiquement
- `assets/` : Fichiers sources JavaScript et CSS

## Développement

### Commandes utiles

- Créer une nouvelle migration : `php bin/console make:migration`
- Exécuter les migrations : `php bin/console doctrine:migrations:migrate`
- Créer une entité : `php bin/console make:entity`
- Créer un contrôleur : `php bin/console make:controller`
- Vider le cache : `php bin/console cache:clear`

### Personnalisation

Le projet utilise les composants Symfony UX pour améliorer l'interface utilisateur. Vous pouvez personnaliser les styles dans le répertoire `assets/styles/`.

## Contributions

Les contributions sont les bienvenues ! N'hésitez pas à ouvrir une issue ou une pull request.

## Licence

Tous droits réservés.
