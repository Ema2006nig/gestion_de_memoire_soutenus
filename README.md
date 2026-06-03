
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

Directeur des Études (DE)

Identifiant : admin_de | Email : de@uatm.ga | Mot de passe : password | Nom : Jean-Pierre Moukouyou

Bibliothécaire

Identifiant : bibliothecaire | Email : biblio@uatm.ga | Mot de passe : password | Nom : Claire Ndong

Professeurs

Identifiant : prof.martin | Email : paul.martin@uatm.ga | Mot de passe : password | Nom : Paul Martin
Identifiant : prof.diallo | Email : aminata.diallo@uatm.ga | Mot de passe : password | Nom : Aminata Diallo
Identifiant : prof.tchibozo | Email : marcel.tchibozo@uatm.ga | Mot de passe : password | Nom : Marcel Tchibozo

Étudiants diplômés

Identifiant : jean.dupont | Email : jean.dupont@etud.uatm.ga | Mot de passe : password | Nom : Jean Dupont
Identifiant : marie.kone | Email : marie.kone@etud.uatm.ga | Mot de passe : password | Nom : Marie Koné
Identifiant : pierre.nze | Email : pierre.nze@etud.uatm.ga | Mot de passe : password | Nom : Pierre Nze
Identifiant : franck.mboumba | Email : franck.mboumba@etud.uatm.ga | Mot de passe : password | Nom : Franck Mboumba
Identifiant : bertrand.leyama | Email : bertrand.leyama@etud.uatm.ga | Mot de passe : password | Nom : Bertrand Leyama

Étudiants consultants

Identifiant : grace.assoumou | Email : grace.assoumou@etud.uatm.ga | Mot de passe : password | Nom : Grace Assoumou
Identifiant : aurelie.meye | Email : aurelie.meye@etud.uatm.ga | Mot de passe : password | Nom : Aurélie Meye
Identifiant : felix.magnagna | Email : felix.magnagna@etud.uatm.ga | Mot de passe : password | Nom : Felix Magnagna
Bertrand Leyama se connecte avec l'identifiant ** bertrand.leyama ** et le mot de passe password.

Étudiants consultants : 
Grace Assoumou se connecte avec l'identifiant ** grace.assoumou ** et le mot de passe password.
Aurélie Meye se connecte avec l'identifiant ** aurelie.meye ** et le mot de passe password.
Felix Magnagna se connecte avec l'identifiant ** felix.magnagna ** et le mot de passe password.
