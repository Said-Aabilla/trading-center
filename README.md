# Trading Center - Plateforme de Suivi et d'Analyse des Investissements



## Description

Trading-center est une plateforme web dédiée à la gestion et à l'analyse des investissements financiers, permettant aux utilisateurs de suivre en temps réel leurs portefeuilles d'actions boursières et de cryptomonnaies. Elle offre des outils avancés pour analyser les performances, calculer les gains réalisés et non réalisés via la méthode FIFO, et détecter les tendances du marché grâce à l'intégration d'OpenAI.


## Prérequis

Avant de commencer, assurez-vous d'avoir les éléments suivants installés sur votre machine :

- **PHP 8.2+**
- **Composer** (gestionnaire de dépendances PHP)
- **MySQL 8+** (ou un autre SGBD compatible avec Laravel)
- **Node.js 18+**
- **Git** (gestion de version)

## Installation

1. Clonez le dépôt :

   ```bash
   git https://github.com/Said-Aabilla/trading-center.git
   cd trading-center
   ```

2. Installez les dépendances :

   ```bash
   composer install
   ```

3. Configurez les variables d'environnement :

   - Créez un fichier `.env` à la racine du projet.
   - Ajoutez les variables d'environnement nécessaires. Par exemple :

     ```plaintext
     # Base de données
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=trading_center
        DB_USERNAME=root
        DB_PASSWORD=yourpassword

        # Clé d'application
        APP_KEY=base64:...

        # API OpenAI (pour l'analyse de tendances)
        OPENAI_API_KEY=your-openai-api-key
     ```

## Lancement

Pour démarrer le serveur de développement, exécutez :

```bash
php artisan serve
```

Exécutez la commande suivante pour synchroniser les prix des actifs (manuelle) :

```bash
php artisan sync:prices
```

Exécutez la commande suivante pour synchroniser les prix des actifs (automatique) :

```bash
php artisan schedule:work
```

## Documentation

Pour accéder à la documentation interactive, exécutez :


Le serveur sera accessible à l'adresse `http://localhost:8000`.

## Documentation API

La documentation de l'API est générée automatiquement par Swagger. Une fois le serveur démarré, vous pouvez y accéder à l'adresse suivante :

`http://localhost:8000/docs/api`.



