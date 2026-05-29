<?php
session_start();
if(isset($_SESSION['user_id'])) header('Location: /dashboard-'.$_SESSION['role'].'.php');
$title = "Inscription";
include_once __DIR__ . '/../layouts/header.php';
?>
<div style="max-width: 500px; margin: 2rem auto; background:white; border-radius: 24px; padding: 2rem;">
    <h2>Créer un compte</h2>
    <p style="margin-bottom:1.5rem;">Remplissez ces informations pour débuter</p>

    <?php if(isset($_SESSION['register_error'])): ?>
        <div class="alert" style="background:#fee2e2; padding:0.75rem; border-radius:12px; margin-bottom:1rem;">❌ <?= $_SESSION['register_error']; unset($_SESSION['register_error']); ?></div>
    <?php endif; ?>

    <form action="index.php?action=register" method="POST">
        <div style="margin-bottom:1rem;">
            <label>Nom complet</label>
            <input type="text" name="fullname" required style="width:100%; padding:0.75rem; border-radius:16px; border:1px solid #cbd5e1;">
        </div>
        <div style="margin-bottom:1rem;">
            <label>Email</label>
            <input type="email" name="email" required style="width:100%; padding:0.75rem; border-radius:16px; border:1px solid #cbd5e1;">
        </div>
        <div style="margin-bottom:1rem;">
            <label>Mot de passe</label>
            <input type="password" name="password" required style="width:100%; padding:0.75rem; border-radius:16px; border:1px solid #cbd5e1;">
        </div>
        <div style="margin-bottom:1.5rem;">
            <label>Rôle</label>
            <select name="role" style="width:100%; padding:0.75rem; border-radius:16px;">
                <option value="etudiant">Étudiant</option>
                <option value="professeur">Professeur</option>
                <option value="direction">Direction</option>
            </select>
        </div>
        <button type="submit" style="background:#1e3a5f; color:white; width:100%; padding:0.8rem; border-radius:40px;">S'inscrire</button>
    </form>
    <p style="text-align:center; margin-top:1.5rem;">Déjà inscrit ? <a href="login.php">Se connecter</a></p>
</div>
<?php include_once __DIR__ . '/../layouts/footer.php'; ?>