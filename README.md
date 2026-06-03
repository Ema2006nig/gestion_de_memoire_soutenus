
# Gestion des memoires de soutenance

Application web simple en **PHP / MySQL** suivant une architecture **MVC**.

## Architecture (MVC)

```
memoires/
├── config/          # Config + connexion PDO
├── core/            # Helpers (session, CSRF, mail, vues)
├── models/          # Acces base de donnees (Utilisateur, Memoire, Commentaire, Notification)
├── controllers/     # Logique applicative (Auth, Memoire, Admin)
├── views/           # Pages HTML (par role)
│   ├── layout/      # header / footer
│   ├── auth/ etudiant/ professeur/ direction/ memoire/
├── public/          # SEUL dossier exposé (index.php, css, js)
├── uploads/         # PDF (acces interdit en direct via .htaccess)
├── sql/schema.sql   # Schema de la base
└── install.php      # Script d'installation (a supprimer apres usage)
```

## Installation

1. Copier le dossier dans le `htdocs` / `www` d'un serveur Apache + PHP 8.1+ + MySQL (XAMPP, WAMP, MAMP).
2. Editer `config/config.php` (identifiants MySQL + `BASE_URL`).
3. Ouvrir `http://localhost/gestion_de_memoire_soutenus/install.php` une fois, puis **supprimer** `install.php`.
4. Aller sur `http://localhost/gestion_de_memoire_soutenus/public/index.php`.

## Comptes de test (mot de passe `Passw0rd!`)

| Role | Email |
|---|---|
| Etudiant | etudiant@univ.local |
| Professeur | prof@univ.local |
| Direction | direction@univ.local |
| Commentateur | commentateur@univ.local |
| Service technique | admin@univ.local |

## Fonctionnalites (selon les diagrammes)

- **Etudiant** : depot d'un memoire PDF, suivi du statut, lecture des commentaires.
- **Professeur** : liste de ses encadrements, validation / refus, commentaires.
- **Direction** : supervision, statistiques, validation finale (mise en ligne).
- **Commentateur** : consultation des memoires en ligne et annotation.
- **Service technique** : gestion des comptes utilisateurs.
- **Notifications par email** apres chaque etape importante (depot, validation, refus, mise en ligne, commentaire). Les mails partent via `mail()` ; en cas d'echec, ils sont consignes dans `uploads/mail.log`.

## Securite

- Mots de passe **hashes** (`password_hash` / `password_verify`).
- **Requetes preparees** (PDO) partout — aucune concatenation SQL.
- **Jetons CSRF** sur tous les formulaires.
- Sessions **httpOnly** + `samesite=Lax` + `session_regenerate_id` apres connexion.
- En-tetes : `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`.
- Dossier `uploads/` **inaccessible directement** (`.htaccess Require all denied`) ; les PDF passent par un controleur qui verifie le role.
- Validation MIME serveur des fichiers (`application/pdf` uniquement, max 20 Mo).
- Controle d'acces par role pour chaque action.

## Anti-telechargement / anti-capture

- Le PDF est envoye `Content-Disposition: inline` (pas de telechargement direct).
- En-tetes `Cache-Control: no-store` + `Pragma: no-cache`.
- Affichage en iframe avec `#toolbar=0&navpanes=0` (masque la barre PDF du navigateur).
- Couche `shield` au dessus du PDF.
- `protect.js` : desactive le menu contextuel, les raccourcis Ctrl+S / Ctrl+P / Ctrl+U / Ctrl+C / PrintScreen, et brouille le PDF quand la fenetre perd le focus (anti-screenshot dissuasif).

> Note honnete : aucune protection cote client n'est infaillible (un utilisateur peut photographier son ecran). Ces mesures sont **dissuasives** ; la vraie defense reste **serveur** (acces controle, pas de telechargement, traces).
=======
# Mémoires Soutenus — UATM GASA Formation

Plateforme web (PHP 8 / MySQL) de gestion et de consultation des mémoires de
fin d'études de l'université UATM GASA.

## Acteurs et rôles

| Rôle             | Rôle métier                                                   |
|------------------|---------------------------------------------------------------|
| Étudiant         | Consulte la bibliothèque, dépose son mémoire (si autorisé).   |
| Professeur       | Valide ou rejette les mémoires de ses étudiants encadrés.     |
| Direction (DE)   | Pré-enregistre les utilisateurs, valide la publication finale.|
| Bibliothécaire   | Publie les mémoires délégués par la Direction.                |

## Architecture (MVC + Repository)

```
Config/        — Paramètres globaux + connexion PDO (singleton)
classes/       — Modèles métier (Utilisateur, Etudiant, Memoire …)
Repository/    — Accès SQL (CompteRepository, MemoireRepository …)
Controller/    — Logique applicative (Auth, Memoire, Validation, Admin)
View/          — Vues PHP organisées par rôle
   ├── auth/        — Connexion + inscription (seuls formulaires publics)
   ├── shared/      — Layout, sidebar, icônes, listes, notifications
   ├── etudiant/    — Espace étudiant
   ├── professeur/  — Espace enseignant
   ├── de/          — Direction des études
   └── bibliothecaire/
assets/        — CSS et JS de la nouvelle charte graphique
uploads/       — Fichiers PDF des mémoires (jamais commités)
```

## Authentification (nouveau workflow)

- L'identifiant de connexion est l'**adresse e-mail** institutionnelle.
- Le compte doit avoir été **pré-enregistré par la Direction des Études**.
- Deux écrans seulement : **connexion** et **inscription**.
- Mot de passe stocké via `password_hash()` (BCRYPT).

## Installation locale

1. Importer `Config/database.sql` dans MySQL (XAMPP / MAMP / WAMP).
2. Adapter, si besoin, les constantes de `Config/connexion.php`.
3. Servir le dossier à la racine d'Apache (`htdocs/memoires_app`).
4. Ouvrir `http://localhost/memoires_app/`.

> Pour activer un compte de test, choisir n'importe quel utilisateur du jeu
> de données (table `utilisateur`) et l'inscrire via la page **Créer mon
> compte** en saisissant son e-mail.

## Design system

La charte visuelle a été entièrement refondue :

- typographie **Inter**,
- palette bleu profond + accent ambre,
- sidebar sombre persistante avec **icônes SVG inline** (style Lucide),
- composants à coins arrondis (14px), ombres très douces,
- mode responsive (la sidebar se replie en menu hors-canvas sur mobile).

Tous les styles sont concentrés dans `assets/css/style.css` ; aucun framework
externe n'est requis côté front (pas de Tailwind CDN, pas de Font Awesome).

- Directeur des Études : Jean-Pierre Moukouyou se connecte avec l'identifiant ** admin_de ** et le mot de passe ** password **.
- Bibliothécaire : Claire Ndong se connecte avec l'identifiant ** bibliothecaire ** et le mot de passe ** password **.
- Professeurs : 
Paul Martin se connecte avec l'identifiant ** prof.martin ** et le mot de passe **password **.
Aminata Diallo se connecte avec l'identifiant ** prof.diallo ** et le mot de passe ** password **.
Marcel Tchibozo se connecte avec l'identifiant ** prof.tchibozo ** et le mot de passe ** password **.

Étudiants diplômés : 
Jean Dupont se connecte avec l'identifiant ** jean.dupont ** et le mot de passe password.
Marie Koné se connecte avec l'identifiant ** marie.kone ** et le mot de passe password.
Pierre Nze se connecte avec l'identifiant ** pierre.nze ** et le mot de passe password.
Franck Mboumba se connecte avec l'identifiant ** franck.mboumba ** et le mot de passe password.
Bertrand Leyama se connecte avec l'identifiant ** bertrand.leyama ** et le mot de passe password.

Étudiants consultants : 
Grace Assoumou se connecte avec l'identifiant ** grace.assoumou ** et le mot de passe password.
Aurélie Meye se connecte avec l'identifiant ** aurelie.meye ** et le mot de passe password.
Felix Magnagna se connecte avec l'identifiant ** felix.magnagna ** et le mot de passe password.
