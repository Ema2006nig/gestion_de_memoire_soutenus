<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';
AuthController::requireRole('DE');
require_once __DIR__ . '/../../classes/DE.php';
require_once __DIR__ . '/../../classes/Memoire.php';
require_once __DIR__ . '/../../classes/Notification.php';
require_once __DIR__ . '/../../Repository/MemoireRepository.php';
require_once __DIR__ . '/../../Repository/NotificationRepository.php';
require_once __DIR__ . '/../../Config/connexion.php';

$pageTitle = "Dashboard DE";
$de = new DE($_SESSION['idUtilisateur'], $_SESSION['nom'], $_SESSION['prenom'], '', '');
$nbEtudiants = count($de->listerEtudiants());
$nbProfs     = count($de->listerProfs());
$nbMemoires  = MemoireRepository::compterTotal();
$nbNotifs    = NotificationRepository::compterNonLus($_SESSION['idCompte']);

$pdo = getConnexion();

// --- STATISTIQUES REELLES ---

// 1. Répartition des étudiants par niveau
$stmt = $pdo->query("
    SELECT niveau, COUNT(*) as total 
    FROM etudiant 
    WHERE niveau IS NOT NULL AND niveau != ''
    GROUP BY niveau 
    ORDER BY FIELD(niveau, 'L1', 'L2', 'L3', 'M1', 'M2')
");
$niveauxData = $stmt->fetchAll(PDO::FETCH_ASSOC);
$niveauxLabels = array_column($niveauxData, 'niveau');
$niveauxCounts = array_column($niveauxData, 'total');

// 2. Répartition par filière
$stmt = $pdo->query("
    SELECT filiere, COUNT(*) as total 
    FROM etudiant 
    WHERE filiere IS NOT NULL AND filiere != ''
    GROUP BY filiere 
    ORDER BY total DESC LIMIT 5
");
$filieresData = $stmt->fetchAll(PDO::FETCH_ASSOC);
$filieresLabels = array_column($filieresData, 'filiere');
$filieresCounts = array_column($filieresData, 'total');

// 3. Mémoires par statut
$stmt = $pdo->query("
    SELECT statut, COUNT(*) as total 
    FROM memoire 
    GROUP BY statut
");
$statutsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
$statutsLabels = [];
$statutsCounts = [];
foreach ($statutsData as $s) {
    $label = match($s['statut']) {
        'VALIDE' => 'Publiés',
        'EN_ATTENTE' => 'En attente encadreur',
        'EN_ATTENTE_DIRECTION' => 'En attente Direction',
        'REJETE' => 'Rejetés',
        'BROUILLON' => 'Brouillons',
        default => $s['statut']
    };
    $statutsLabels[] = $label;
    $statutsCounts[] = $s['total'];
}

// 4. Mémoires par année académique (5 dernières années)
$stmt = $pdo->query("
    SELECT anneeAcademique, COUNT(*) as total 
    FROM memoire 
    WHERE anneeAcademique IS NOT NULL
    GROUP BY anneeAcademique 
    ORDER BY anneeAcademique DESC LIMIT 5
");
$anneesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
$anneesLabels = array_reverse(array_column($anneesData, 'anneeAcademique'));
$anneesCounts = array_reverse(array_column($anneesData, 'total'));

// 5. Mémoires par centre
$stmt = $pdo->query("
    SELECT centre, COUNT(*) as total 
    FROM memoire 
    WHERE centre IS NOT NULL AND centre != ''
    GROUP BY centre 
    ORDER BY total DESC
");
$centresData = $stmt->fetchAll(PDO::FETCH_ASSOC);
$centresLabels = array_column($centresData, 'centre');
$centresCounts = array_column($centresData, 'total');

// 6. Évolution mensuelle des soumissions (6 derniers mois)
$stmt = $pdo->query("
    SELECT DATE_FORMAT(dateUpload, '%Y-%m') as mois, COUNT(*) as total 
    FROM memoire 
    WHERE dateUpload >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(dateUpload, '%Y-%m')
    ORDER BY mois ASC
");
$evolutionData = $stmt->fetchAll(PDO::FETCH_ASSOC);
$evolutionLabels = [];
$evolutionCounts = [];
$moisFr = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
foreach ($evolutionData as $e) {
    $moisNum = (int)substr($e['mois'], 5);
    $evolutionLabels[] = $moisFr[$moisNum - 1] . ' ' . substr($e['mois'], 0, 4);
    $evolutionCounts[] = $e['total'];
}

// 7. Top professeurs avec le plus de mémoires encadrés
$stmt = $pdo->query("
    SELECT CONCAT(u.prenom, ' ', u.nom) as professeur, COUNT(m.idMemoire) as total
    FROM memoire m
    JOIN utilisateur u ON m.idProf = u.idUtilisateur
    GROUP BY m.idProf
    ORDER BY total DESC LIMIT 5
");
$topProfsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
$topProfsLabels = array_column($topProfsData, 'professeur');
$topProfsCounts = array_column($topProfsData, 'total');
?>
<?php
$pageTitle   = "Tableau de bord";
$pageSection = "Direction des études";
$activeRoute = "dashboard";
require_once __DIR__ . '/../shared/layout.php';
layout_open();
?>

<?php if (isset($_SESSION['succes'])): ?>
<div class="mb-4 p-4 rounded-lg bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 flex items-center gap-2">
  <i class="fas fa-check-circle text-emerald-500"></i>
  <span><?= htmlspecialchars($_SESSION['succes']) ?></span>
  <?php unset($_SESSION['succes']); ?>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['erreur'])): ?>
<div class="mb-4 p-4 rounded-lg bg-red-50 border-l-4 border-red-500 text-red-700 flex items-center gap-2">
  <i class="fas fa-exclamation-triangle text-red-500"></i>
  <span><?= htmlspecialchars($_SESSION['erreur']) ?></span>
  <?php unset($_SESSION['erreur']); ?>
</div>
<?php endif; ?>

<!-- Banner -->
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-900 via-emerald-700 to-emerald-600 p-6 mb-6 text-white shadow-lg">
  <div class="absolute right-0 top-0 -mt-10 -mr-10 opacity-10">
    <i class="fas fa-chart-line text-8xl"></i>
  </div>
  <div class="relative z-10">
    <div class="flex items-center gap-2 text-sm font-semibold text-emerald-200 mb-2">
      <i class="fas fa-chalkboard-user"></i>
      <span>Tableau de bord analytique</span>
    </div>
    <div class="text-xl font-bold mb-1">Direction des Études <i class="fas fa-graduation-cap ml-1"></i></div>
    <div class="text-emerald-100 text-sm">Statistiques et indicateurs clés · UATM GASA Formation</div>
  </div>
</div>

<!-- Cartes statistiques -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition group">
    <div class="flex items-center justify-between mb-2">
      <div class="w-10 h-10 rounded-full bg-navy-100 flex items-center justify-center group-hover:bg-navy-200 transition">
        <i class="fas fa-users text-navy-600 text-lg"></i>
      </div>
      <span class="text-2xl font-bold text-navy-600"><?= $nbEtudiants ?></span>
    </div>
    <div class="text-sm font-medium text-gray-600">Étudiants inscrits</div>
    <div class="text-xs text-gray-400 mt-1">Tous niveaux confondus</div>
  </div>

  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition group">
    <div class="flex items-center justify-between mb-2">
      <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition">
        <i class="fas fa-chalkboard-user text-emerald-600 text-lg"></i>
      </div>
      <span class="text-2xl font-bold text-emerald-600"><?= $nbProfs ?></span>
    </div>
    <div class="text-sm font-medium text-gray-600">Professeurs</div>
    <div class="text-xs text-gray-400 mt-1">Encadrants</div>
  </div>

  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition group">
    <div class="flex items-center justify-between mb-2">
      <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center group-hover:bg-amber-200 transition">
        <i class="fas fa-book text-amber-600 text-lg"></i>
      </div>
      <span class="text-2xl font-bold text-amber-600"><?= $nbMemoires ?></span>
    </div>
    <div class="text-sm font-medium text-gray-600">Mémoires</div>
    <div class="text-xs text-gray-400 mt-1">Publiés et archivés</div>
  </div>

  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition cursor-pointer group" onclick="location.href='../shared/notifications.php'">
    <div class="flex items-center justify-between mb-2">
      <div class="w-10 h-10 rounded-full <?= $nbNotifs > 0 ? 'bg-blue-100' : 'bg-gray-100' ?> flex items-center justify-center group-hover:bg-blue-200 transition">
        <i class="fas fa-bell <?= $nbNotifs > 0 ? 'text-blue-600' : 'text-gray-400' ?> text-lg"></i>
      </div>
      <span class="text-2xl font-bold <?= $nbNotifs > 0 ? 'text-blue-600' : 'text-gray-400' ?>"><?= $nbNotifs ?></span>
    </div>
    <div class="text-sm font-medium text-gray-600">Notifications</div>
    <div class="text-xs text-gray-400 mt-1"><?= $nbNotifs > 0 ? $nbNotifs . ' non lue(s)' : 'Aucune nouvelle' ?></div>
  </div>
</div>

<!-- Graphiques en grille -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
  
  <!-- Graphique 1 : Répartition par niveau -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
    <div class="border-b border-gray-200 pb-3 mb-4">
      <div class="flex items-center gap-2">
        <i class="fas fa-chart-pie text-emerald-600 text-sm"></i>
        <h3 class="font-semibold text-gray-800 text-sm">Étudiants par niveau</h3>
      </div>
    </div>
    <?php if (!empty($niveauxLabels)): ?>
    <canvas id="chartNiveaux" class="w-full h-64"></canvas>
    <?php else: ?>
    <div class="text-center py-8 text-gray-400 text-sm">Aucune donnée disponible</div>
    <?php endif; ?>
  </div>

  <!-- Graphique 2 : Répartition par filière -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
    <div class="border-b border-gray-200 pb-3 mb-4">
      <div class="flex items-center gap-2">
        <i class="fas fa-chart-bar text-emerald-600 text-sm"></i>
        <h3 class="font-semibold text-gray-800 text-sm">Top 5 filières</h3>
      </div>
    </div>
    <?php if (!empty($filieresLabels)): ?>
    <canvas id="chartFilieres" class="w-full h-64"></canvas>
    <?php else: ?>
    <div class="text-center py-8 text-gray-400 text-sm">Aucune donnée disponible</div>
    <?php endif; ?>
  </div>

  <!-- Graphique 3 : Statut des mémoires -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
    <div class="border-b border-gray-200 pb-3 mb-4">
      <div class="flex items-center gap-2">
        <i class="fas fa-chart-donut text-emerald-600 text-sm"></i>
        <h3 class="font-semibold text-gray-800 text-sm">Statut des mémoires</h3>
      </div>
    </div>
    <?php if (!empty($statutsLabels)): ?>
    <canvas id="chartStatuts" class="w-full h-64"></canvas>
    <?php else: ?>
    <div class="text-center py-8 text-gray-400 text-sm">Aucune donnée disponible</div>
    <?php endif; ?>
  </div>

  <!-- Graphique 4 : Mémoires par centre -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
    <div class="border-b border-gray-200 pb-3 mb-4">
      <div class="flex items-center gap-2">
        <i class="fas fa-map-marker-alt text-emerald-600 text-sm"></i>
        <h3 class="font-semibold text-gray-800 text-sm">Mémoires par centre</h3>
      </div>
    </div>
    <?php if (!empty($centresLabels)): ?>
    <canvas id="chartCentres" class="w-full h-64"></canvas>
    <?php else: ?>
    <div class="text-center py-8 text-gray-400 text-sm">Aucune donnée disponible</div>
    <?php endif; ?>
  </div>

  <!-- Graphique 5 : Évolution des soumissions -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 lg:col-span-2">
    <div class="border-b border-gray-200 pb-3 mb-4">
      <div class="flex items-center gap-2">
        <i class="fas fa-chart-line text-emerald-600 text-sm"></i>
        <h3 class="font-semibold text-gray-800 text-sm">Évolution des soumissions (6 derniers mois)</h3>
      </div>
    </div>
    <?php if (!empty($evolutionLabels)): ?>
    <canvas id="chartEvolution" class="w-full h-72"></canvas>
    <?php else: ?>
    <div class="text-center py-8 text-gray-400 text-sm">Aucune donnée disponible</div>
    <?php endif; ?>
  </div>

  <!-- Graphique 6 : Top professeurs -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 lg:col-span-2">
    <div class="border-b border-gray-200 pb-3 mb-4">
      <div class="flex items-center gap-2">
        <i class="fas fa-trophy text-emerald-600 text-sm"></i>
        <h3 class="font-semibold text-gray-800 text-sm">Top 5 professeurs encadrants</h3>
      </div>
    </div>
    <?php if (!empty($topProfsLabels)): ?>
    <canvas id="chartTopProfs" class="w-full h-72"></canvas>
    <?php else: ?>
    <div class="text-center py-8 text-gray-400 text-sm">Aucune donnée disponible</div>
    <?php endif; ?>
  </div>
</div>

<!-- Tableau récapitulatif -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
  <div class="border-b border-gray-200 px-5 py-3 bg-gradient-to-r from-gray-50 to-white">
    <div class="flex items-center gap-2">
      <i class="fas fa-table-list text-emerald-600 text-sm"></i>
      <h3 class="font-semibold text-gray-800 text-sm">Récapitulatif général</h3>
    </div>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full">
      <thead>
        <tr class="bg-navy-600 text-white">
          <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider rounded-tl-lg">Catégorie</th>
          <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider">Total</th>
          <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider rounded-tr-lg">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr class="hover:bg-gray-50 transition">
          <td class="px-5 py-3">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-full bg-navy-100 flex items-center justify-center">
                <i class="fas fa-users text-navy-600 text-xs"></i>
              </div>
              <span class="font-medium text-gray-700 text-sm">Étudiants</span>
            </div>
           </td>
          <td class="px-5 py-3 text-center">
            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-navy-100 text-navy-700 font-bold text-sm min-w-[50px]"><?= $nbEtudiants ?></span>
           </td>
          <td class="px-5 py-3 text-center">
            <a href="liste_etudiants.php" class="inline-flex items-center gap-1 text-emerald-600 hover:text-emerald-700 font-medium text-sm transition">
              Voir la liste <i class="fas fa-arrow-right text-xs"></i>
            </a>
           </td>
         </tr>
        <tr class="hover:bg-gray-50 transition">
          <td class="px-5 py-3">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center">
                <i class="fas fa-chalkboard-user text-emerald-600 text-xs"></i>
              </div>
              <span class="font-medium text-gray-700 text-sm">Professeurs</span>
            </div>
           </td>
          <td class="px-5 py-3 text-center">
            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-bold text-sm min-w-[50px]"><?= $nbProfs ?></span>
           </td>
          <td class="px-5 py-3 text-center">
            <a href="liste_profs.php" class="inline-flex items-center gap-1 text-emerald-600 hover:text-emerald-700 font-medium text-sm transition">
              Voir la liste <i class="fas fa-arrow-right text-xs"></i>
            </a>
           </td>
         </tr>
        <tr class="hover:bg-gray-50 transition">
          <td class="px-5 py-3">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-full bg-amber-100 flex items-center justify-center">
                <i class="fas fa-book text-amber-600 text-xs"></i>
              </div>
              <span class="font-medium text-gray-700 text-sm">Mémoires</span>
            </div>
           </td>
          <td class="px-5 py-3 text-center">
            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-bold text-sm min-w-[50px]"><?= $nbMemoires ?></span>
           </td>
          <td class="px-5 py-3 text-center">
            <a href="../shared/liste_memoires.php" class="inline-flex items-center gap-1 text-emerald-600 hover:text-emerald-700 font-medium text-sm transition">
              Consulter <i class="fas fa-arrow-right text-xs"></i>
            </a>
           </td>
         </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Actions rapides -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
  <div class="border-b border-gray-200 px-5 py-3 bg-gradient-to-r from-gray-50 to-white">
    <div class="flex items-center gap-2">
      <i class="fas fa-bolt text-emerald-600 text-sm"></i>
      <h3 class="font-semibold text-gray-800 text-sm">Actions rapides</h3>
    </div>
  </div>
  <div class="p-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
      <a href="activation_upload.php" class="group flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white rounded-lg transition-all duration-200 font-medium text-sm">
        <i class="fas fa-users-cog group-hover:scale-110 transition"></i>
        <span>Activation en masse</span>
      </a>
      <a href="upload_ancien.php" class="group flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white rounded-lg transition-all duration-200 font-medium text-sm">
        <i class="fas fa-upload group-hover:scale-110 transition"></i>
        <span>Uploader ancien mémoire</span>
      </a>
      <a href="valider_memoires.php" class="group flex items-center justify-center gap-2 px-4 py-2.5 bg-green-50 hover:bg-green-600 text-green-700 hover:text-white rounded-lg transition-all duration-200 font-medium text-sm">
        <i class="fas fa-check-double group-hover:scale-110 transition"></i>
        <span>Valider mémoires</span>
      </a>
      <a href="gestion_visibilite.php" class="group flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-600 text-amber-700 hover:text-white rounded-lg transition-all duration-200 font-medium text-sm">
        <i class="fas fa-eye group-hover:scale-110 transition"></i>
        <span>Gérer visibilité</span>
      </a>
    </div>
  </div>
</div>

<style>
.bg-navy-100 { background-color: #e0e8f0; }
.bg-navy-600 { background-color: #0a2540; }
.text-navy-600 { color: #0a2540; }
.text-navy-700 { color: #0a2a48; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Graphique 1 : Niveaux (camembert)
  <?php if (!empty($niveauxLabels)): ?>
  new Chart(document.getElementById('chartNiveaux'), {
    type: 'doughnut',
    data: {
      labels: <?= json_encode($niveauxLabels) ?>,
      datasets: [{
        data: <?= json_encode($niveauxCounts) ?>,
        backgroundColor: ['#0a2540', '#1b998b', '#f5a623', '#ef4444', '#3b82f6'],
        borderWidth: 0
      }]
    },
    options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
  });
  <?php endif; ?>

  // Graphique 2 : Filières (barres horizontales)
  <?php if (!empty($filieresLabels)): ?>
  new Chart(document.getElementById('chartFilieres'), {
    type: 'bar',
    data: {
      labels: <?= json_encode($filieresLabels) ?>,
      datasets: [{
        label: 'Nombre d\'étudiants',
        data: <?= json_encode($filieresCounts) ?>,
        backgroundColor: '#1b998b',
        borderRadius: 8
      }]
    },
    options: { responsive: true, maintainAspectRatio: true, indexAxis: 'y', plugins: { legend: { display: false } } }
  });
  <?php endif; ?>

  // Graphique 3 : Statuts mémoires (camembert)
  <?php if (!empty($statutsLabels)): ?>
  new Chart(document.getElementById('chartStatuts'), {
    type: 'pie',
    data: {
      labels: <?= json_encode($statutsLabels) ?>,
      datasets: [{
        data: <?= json_encode($statutsCounts) ?>,
        backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#9ca3af'],
        borderWidth: 0
      }]
    },
    options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
  });
  <?php endif; ?>

  // Graphique 4 : Centres (barres)
  <?php if (!empty($centresLabels)): ?>
  new Chart(document.getElementById('chartCentres'), {
    type: 'bar',
    data: {
      labels: <?= json_encode($centresLabels) ?>,
      datasets: [{
        label: 'Nombre de mémoires',
        data: <?= json_encode($centresCounts) ?>,
        backgroundColor: '#f5a623',
        borderRadius: 8
      }]
    },
    options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { display: false } } }
  });
  <?php endif; ?>

  // Graphique 5 : Évolution (ligne)
  <?php if (!empty($evolutionLabels)): ?>
  new Chart(document.getElementById('chartEvolution'), {
    type: 'line',
    data: {
      labels: <?= json_encode($evolutionLabels) ?>,
      datasets: [{
        label: 'Soumissions',
        data: <?= json_encode($evolutionCounts) ?>,
        borderColor: '#1b998b',
        backgroundColor: 'rgba(27, 153, 139, 0.1)',
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#0a2540'
      }]
    },
    options: { responsive: true, maintainAspectRatio: true }
  });
  <?php endif; ?>

  // Graphique 6 : Top professeurs (barres horizontales)
  <?php if (!empty($topProfsLabels)): ?>
  new Chart(document.getElementById('chartTopProfs'), {
    type: 'bar',
    data: {
      labels: <?= json_encode($topProfsLabels) ?>,
      datasets: [{
        label: 'Mémoires encadrés',
        data: <?= json_encode($topProfsCounts) ?>,
        backgroundColor: '#0a2540',
        borderRadius: 8
      }]
    },
    options: { responsive: true, maintainAspectRatio: true, indexAxis: 'y', plugins: { legend: { position: 'top' } } }
  });
  <?php endif; ?>
});
</script>

<?php layout_close(); ?>