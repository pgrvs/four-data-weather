# Application Météo

## Stack Technique

- **Backend** : Symfony 7, PHP 8.2+, SQLite.
- **Frontend** : Vue.js 3, TypeScript, Tailwind CSS, Vite.
- **API Externe** : Open-Meteo.

---

## 1. Installation et Lancement du Backend (API)

Le backend utilise une base de données SQLite (fichier local dans `var/`).
Ouvrez un terminal dans le dossier `backend`.

```bash
cd backend

# Installation des dépendances
composer install

# Création de la base de données et des tables
php bin/console doctrine:migrations:migrate
yes

# Démarrage du serveur
symfony server:start
```

L'API sera accessible sur : http://127.0.0.1:8000

---

## 2. Installation et Lancement du Frontend (Interface)

Ouvrez un **nouveau terminal** dans le dossier `frontend`.

```bash
cd frontend

# Installation des dépendances
npm install

# Démarrage du serveur de développement
npm run dev
```

L'interface sera accessible sur : http://localhost:5173

---

## 3. Exécution des Tests (Backend)

Les tests s'exécutent sur une base de données dédiée (`var/test.db`) pour ne pas écraser vos données.

```bash
cd backend

# Lancement des tests
php bin/phpunit
```