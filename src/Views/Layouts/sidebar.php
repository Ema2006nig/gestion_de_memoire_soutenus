<?php
$role = $_SESSION['role'] ?? '';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside style="width: 260px; background: white; border-right: 1px solid #e2e8f0; padding: 2rem 1rem;">
    <div style="margin-bottom: 2rem; font-weight: 700; font-size: 1.3rem;">📘 Gestion Mémoires</div>
    <nav style="display: flex; flex-direction: column; gap: 0.75rem;">
        <?php if($role === 'etudiant'): ?>
            <a href="/dashboard-etudiant.php" style="text-decoration: none; color: #2c3e50; padding: 0.5rem; border-radius: 8px; background: <?= $current_page == 'dashboard-etudiant.php' ? '#eef2ff' : 'transparent' ?>;">📊 Tableau de bord</a>
            <a href="/deposer-memoire.php" style="text-decoration: none; color: #2c3e50; padding: 0.5rem; border-radius: 8px;">📤 Déposer mon mémoire</a>
        <?php elseif($role === 'professeur'): ?>
            <a href="/dashboard-professeur.php" style="text-decoration: none; color: #2c3e50; padding: 0.5rem; border-radius: 8px;">📋 Mémoires à évaluer</a>
            <a href="/mes-validations.php" style="text-decoration: none; color: #2c3e50; padding: 0.5rem; border-radius: 8px;">✍️ Mes validations</a>
        <?php elseif($role === 'direction'): ?>
            <a href="/dashboard-direction.php" style="text-decoration: none; color: #2c3e50; padding: 0.5rem; border-radius: 8px;">📈 Statistiques & archives</a>
            <a href="/validation-finale.php" style="text-decoration: none; color: #2c3e50; padding: 0.5rem; border-radius: 8px;">✅ Validation finale</a>
        <?php elseif($role === 'admin'): ?>
            <a href="/dashboard-admin.php" style="text-decoration: none; color: #2c3e50; padding: 0.5rem; border-radius: 8px;">👥 Gestion utilisateurs</a>
            <a href="/permissions.php" style="text-decoration: none; color: #2c3e50; padding: 0.5rem; border-radius: 8px;">🔐 Permissions</a>
        <?php endif; ?>
        <a href="/logout.php" style="margin-top: 2rem; text-decoration: none; color: #e74c3c; padding: 0.5rem;">🚪 Déconnexion</a>
    </nav>
</aside>