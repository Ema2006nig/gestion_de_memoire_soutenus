
<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Memoire.php';
require_once __DIR__ . '/../models/Commentaire.php';
require_once __DIR__ . '/../models/Validation.php';

class ProfesseurController extends Controller {
    
    public function __construct() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'professeur') {
            header('Location: /login');
            exit();
        }
    }
    
    public function dashboard() {
        $memoireModel = new Memoire();
        $memoires = $memoireModel->getByProfesseur();
        
        $stats = [
            'total' => count($memoires),
            'en_attente' => 0,
            'en_cours' => 0,
            'valide' => 0,
            'refuse' => 0,
            'a_corriger' => 0
        ];
        
        foreach ($memoires as $memoire) {
            $stats[$memoire['statut']]++;
        }
        
        $this->view('professeur/dashboard.php', [
            'memoires' => $memoires,
            'stats' => $stats
        ]);
    }
    
    public function validation($id = null) {
        $memoireModel = new Memoire();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $decision = $_POST['decision'] ?? '';
            $commentaire = $_POST['commentaire'] ?? '';
            
            $validDecisions = ['valide', 'refuse', 'a_corriger'];
            if (!in_array($decision, $validDecisions)) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Décision invalide'];
                header('Location: /professeur/validation/' . $id);
                exit();
            }
            
            // Mettre à jour le statut du mémoire
            $memoireModel->updateStatus($id, $decision);
            
            // Enregistrer la validation
            $validationModel = new Validation();
            $validationData = [
                'memoire_id' => $id,
                'professeur_id' => $_SESSION['user']['id'],
                'decision' => $decision,
                'commentaire' => $commentaire
            ];
            $validationModel->create($validationData);
            
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Mémoire validé avec succès'];
            header('Location: /professeur/dashboard');
            exit();
        }
        
        $memoire = $memoireModel->getWithDetails($id);
        if (!$memoire) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Mémoire non trouvé'];
            header('Location: /professeur/dashboard');
            exit();
        }
        
        $this->view('professeur/validation.php', ['memoire' => $memoire]);
    }
    
    public function commentaires($id = null) {
        $memoireModel = new Memoire();
        $commentaireModel = new Commentaire();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $commentaire = $_POST['commentaire'] ?? '';
            
            if (empty($commentaire)) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Le commentaire est requis'];
            } else {
                $data = [
                    'memoire_id' => $id,
                    'professeur_id' => $_SESSION['user']['id'],
                    'commentaire' => $commentaire
                ];
                
                if ($commentaireModel->create($data)) {
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Commentaire ajouté avec succès'];
                } else {
                    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Erreur lors de l\'ajout du commentaire'];
                }
            }
            
            header('Location: /professeur/commentaires/' . $id);
            exit();
        }
        
        $memoire = $memoireModel->getWithDetails($id);
        if (!$memoire) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Mémoire non trouvé'];
            header('Location: /professeur/dashboard');
            exit();
        }
        
        $commentaires = $commentaireModel->getByMemoire($id);
        
        $this->view('professeur/commentaires.php', [
            'memoire' => $memoire,
            'commentaires' => $commentaires
        ]);
    }
    
    public function details($id) {
        $memoireModel = new Memoire();
        $memoire = $memoireModel->getWithDetails($id);
        
        if (!$memoire) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Mémoire non trouvé'];
            header('Location: /professeur/dashboard');
            exit();
        }
        
        require_once __DIR__ . '/../models/Commentaire.php';
        $commentaireModel = new Commentaire();
        $commentaires = $commentaireModel->getByMemoire($id);
        
        require_once __DIR__ . '/../models/Validation.php';
        $validationModel = new Validation();
        $validation = $validationModel->getByMemoire($id);
        
        $this->view('professeur/details.php', [
            'memoire' => $memoire,
            'commentaires' => $commentaires,
            'validation' => $validation
        ]);
    }
}