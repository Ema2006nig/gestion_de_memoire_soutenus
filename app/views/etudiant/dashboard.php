<!-- app/views/etudiant/dashboard.php -->
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard Étudiant</h1>
                <a href="/etudiant/soumettre" class="btn btn-primary">+ Nouveau mémoire</a>
            </div>
            
            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total mémoires</h5>
                            <p class="card-text display-4"><?= $stats['total'] ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">En attente</h5>
                            <p class="card-text display-4"><?= $stats['en_attente'] ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Validés</h5>
                            <p class="card-text display-4"><?= $stats['valide'] ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <h5 class="card-title">À corriger</h5>
                            <p class="card-text display-4"><?= $stats['a_corriger'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Liste des mémoires -->
            <div class="card">
                <div class="card-header">
                    <h5>Mes mémoires</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Date soumission</th>
                                    <th>Statut</th>
                                    <th>Commentaires</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($memoires)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Aucun mémoire soumis</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($memoires as $memoire): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($memoire['titre']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($memoire['soumis_le'])) ?></td>
                                            <td>
                                                <?php
                                                $badgeClass = [
                                                    'en_attente' => 'warning',
                                                    'en_cours' => 'info',
                                                    'valide' => 'success',
                                                    'refuse' => 'danger',
                                                    'a_corriger' => 'warning'
                                                ][$memoire['statut']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $badgeClass ?>">
                                                    <?= str_replace('_', ' ', $memoire['statut']) ?>
                                                </span>
                                            </td>
                                            <td><?= $memoire['nb_commentaires'] ?></td>
                                            <td>
                                                <a href="/etudiant/details/<?= $memoire['id'] ?>" class="btn btn-sm btn-info">Voir</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>