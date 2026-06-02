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
