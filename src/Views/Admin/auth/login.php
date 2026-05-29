<?php
session_start();
if(isset($_SESSION['user_id'])){
    header('Location: /dashboard-'.$_SESSION['role'].'.php');
    exit;
}
$title = "Connexion";
include_once __DIR__ . '/../layouts/header.php';
?>
<div style="max-width: 450px; margin: 3rem auto; background: white; border-radius: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); padding: 2rem;">
    <h2 style="font-size: 1.8rem; margin-bottom: 0.5rem;">Bienvenue 👋</h2>
    <p style="color: #5a6e7c; margin-bottom: 2rem;">Connectez-vous pour accéder à votre espace</p>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert" style="background: #fee2e2; color:#b91c1c; padding:0.75rem; border-radius: 12px; margin-bottom:1.5rem;">⚠️ <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form action="index.php?action=login" method="POST">
        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Email professionnel</label>
            <input type="email" name="email" required style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius: 16px; font-size:1rem;">
        </div>
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Mot de passe</label>
            <input type="password" name="password" required style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius: 16px; font-size:1rem;">
        </div>
        <button type="submit" style="background: #1e3a5f; color:white; width:100%; padding:0.8rem; border:none; border-radius: 40px; font-weight:600; cursor:pointer; font-size:1rem;">Se connecter →</button>
    </form>
    <p style="text-align:center; margin-top:1.5rem;">Pas encore de compte ? <a href="register.php" style="color:#1e3a5f;">Créer un compte</a></p>
</div>
<?php include_once __DIR__ . '/../layouts/footer.php'; ?>