# Mario — Application web d'administration RFTG

Application d'administration web développée dans le cadre du projet **RFTG (Raise From The Grave)**, un système de gestion de location de DVDs.

## Démonstration

<video src="https://github.com/user-attachments/assets/950bc273-bacd-4413-90a6-0c4efbf539ab" controls width="800"></video>

---

## Projet RFTG

RFTG est composé de trois applications qui communiquent toutes avec la même base de données **Peach** (MySQL) via l'API REST **Toad** :

| Application | Technologie | Rôle |
|---|---|---|
| **Mario** (ce dépôt) | Laravel (PHP) | Interface d'administration web |
| **Luigi** | Android | Application mobile de réservation client |
| **Toad** | Spring Boot | API REST — seul point d'accès à Peach |
| **Peach** | MySQL | Base de données partagée |

- Dépôt Toad + dump SQL Peach : 
- Dépôt Luigi : 

### Fonctionnement avec Toad

Mario ne se connecte **jamais directement** à la base de données. Toutes les opérations passent par Toad via des appels HTTP. Le token JWT est obtenu au login (`POST /staffs/verify`) puis stocké en session et joint en header `Authorization: Bearer` à chaque requête.

---

## Fonctionnalités

- Authentification via les comptes staff de Toad
- Catalogue de films — liste paginée, ajout, modification, suppression
- Gestion du stock DVD — ajout et suppression d'exemplaires par film et par magasin
- Gestion des clients — fiche, historique de locations, modification, suppression
- Gestion des locations — liste paginée, changement de statut, suppression

---

## Prérequis

- PHP 8.2+
- Composer
- Node.js 18+ et npm
- L'API **Toad** doit être démarrée et accessible

---

## Installation

```bash
git clone <url-du-repo>
cd CMA_RFTG_Mario

composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate
```

Puis édite `.env` avec l'URL de Toad (voir section suivante).

---

## Configuration `.env`

```env
APP_NAME="Mario RFTG"
APP_URL=http://localhost:8000

TOAD_API_URL=http://localhost
TOAD_API_PORT=8180
TOAD_API_TOKEN=          # optionnel — token statique de secours
```

> Mario n'a pas de base de données locale, les variables `DB_*` ne sont pas utilisées.

---

## Lancer l'application

```bash
# serveur de développement
php artisan serve

# compilation des assets en temps réel (développement)
npm run dev
```

Accès sur [http://localhost:8000](http://localhost:8000) — connecte-toi avec un compte **staff** enregistré dans Toad/Peach.

---

## Structure du projet

```
app/
├── Auth/               # ToadUser et ToadUserProvider (auth sans base locale)
├── Http/Controllers/   # FilmController, CustomerController, RentalController, InventoryController
│   └── Auth/           # LoginController personnalisé
└── Services/           # un service par ressource — wrappent les appels HTTP vers Toad
resources/views/
├── layouts/app.blade.php   # layout principal (navbar, flash messages)
├── films/
├── customers/
├── rentals/
└── dvds/
routes/web.php              # toutes les routes de l'application
```
