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
