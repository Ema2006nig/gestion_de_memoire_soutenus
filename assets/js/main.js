// Toggle all checkboxes
function toggleAll(checkbox) {
  const checkboxes = document.querySelectorAll('input[type="checkbox"]');
  checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

// Show/hide tabs
function showTab(tab) {
  document.getElementById('content-selection').style.display = tab === 'selection' ? 'block' : 'none';
  document.getElementById('content-csv').style.display = tab === 'csv' ? 'block' : 'none';
  document.getElementById('tab-selection').style.background = tab === 'selection' ? 'var(--navy)' : 'transparent';
  document.getElementById('tab-selection').style.color = tab === 'selection' ? '#fff' : 'var(--gray-600)';
  document.getElementById('tab-csv').style.background = tab === 'csv' ? 'var(--navy)' : 'transparent';
  document.getElementById('tab-csv').style.color = tab === 'csv' ? '#fff' : 'var(--gray-600)';
}

// Charger étudiant dans le formulaire
function chargerEtudiant(idUtilisateur, nom, prenom, email, dateNaissance, filiere, option_etu, niveau) {
  document.getElementById('idUtilisateur_original').value = idUtilisateur;
  document.getElementById('idUtilisateur').value = idUtilisateur;
  document.getElementById('nom').value = nom;
  document.getElementById('prenom').value = prenom;
  document.getElementById('email').value = email;
  document.getElementById('dateNaissance').value = dateNaissance;
  document.getElementById('filiere').value = filiere;
  document.getElementById('option_etu').value = option_etu;
  document.getElementById('niveau').value = niveau;

  document.getElementById('btn_enregistrer_etu').textContent = 'Modifier';
  document.getElementById('btn_enregistrer_etu').name = 'btn_modifier_etu';
  document.getElementById('btn_supprimer_etu').style.display = 'inline-flex';
  document.getElementById('btn_annuler_etu').style.display = 'inline-flex';
  document.querySelector('.card-title').textContent = '✏️ Modifier l\'étudiant';
}

// Charger professeur dans le formulaire
function chargerProf(idUtilisateur, nom, prenom, email, specialite) {
  document.getElementById('idUtilisateur_original_prof').value = idUtilisateur;
  document.getElementById('idUtilisateur_prof').value = idUtilisateur;
  document.getElementById('nom_prof').value = nom;
  document.getElementById('prenom_prof').value = prenom;
  document.getElementById('email_prof').value = email;
  document.getElementById('specialite_prof').value = specialite;
  
  document.getElementById('btn_enregistrer_prof').textContent = 'Modifier';
  document.getElementById('btn_enregistrer_prof').name = 'btn_modifier_prof';
  document.getElementById('btn_supprimer_prof').style.display = 'inline-flex';
  document.getElementById('btn_annuler_prof').style.display = 'inline-flex';
  document.querySelector('.card-title').textContent = '✏️ Modifier le professeur';
}

// Empêcher le clic droit uniquement sur le contenu du mémoire
document.addEventListener('contextmenu', function(e) {
  if (e.target.closest('.memoire-content')) {
    e.preventDefault();
  }
});

// Empêcher les raccourcis clavier pour copier uniquement sur le contenu du mémoire
document.addEventListener('keydown', function(e) {
  if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'C' || e.key === 'x' || e.key === 'X' || e.key === 'a' || e.key === 'A')) {
    if (e.target.closest('.memoire-content')) {
      e.preventDefault();
    }
  }
});

// Event listeners pour les boutons annuler (chargés au DOM ready)
document.addEventListener('DOMContentLoaded', function() {
  // Bouton annuler étudiant
  const btnAnnulerEtu = document.getElementById('btn_annuler_etu');
  if (btnAnnulerEtu) {
    btnAnnulerEtu.addEventListener('click', function() {
      document.getElementById('form_etudiant').reset();
      document.getElementById('idUtilisateur_original').value = '';
      document.getElementById('btn_enregistrer_etu').textContent = 'Enregistrer';
      document.getElementById('btn_enregistrer_etu').name = 'btn_enregistrer_etu';
      document.getElementById('btn_supprimer_etu').style.display = 'none';
      document.getElementById('btn_annuler_etu').style.display = 'none';
      document.querySelector('.card-title').textContent = '➕ Enregistrer un étudiant';
    });
  }

  // Bouton annuler professeur
  const btnAnnulerProf = document.getElementById('btn_annuler_prof');
  if (btnAnnulerProf) {
    btnAnnulerProf.addEventListener('click', function() {
      document.getElementById('form_prof').reset();
      document.getElementById('idUtilisateur_original_prof').value = '';
      document.getElementById('btn_enregistrer_prof').textContent = 'Enregistrer';
      document.getElementById('btn_enregistrer_prof').name = 'btn_enregistrer_prof';
      document.getElementById('btn_supprimer_prof').style.display = 'none';
      document.getElementById('btn_annuler_prof').style.display = 'none';
      document.querySelector('.card-title').textContent = '➕ Enregistrer un professeur';
    });
  }

  // Comment input pour detail_memoire
  const commentInput = document.getElementById('commentInput');
  const commentContent = document.getElementById('commentContent');
  if (commentInput && commentContent) {
    commentInput.addEventListener('input', function() {
      commentContent.value = this.value;
    });
  }

  // Note input pour detail_memoire
  const noteInput = document.querySelector('input[name="note"]');
  const commentNote = document.getElementById('commentNote');
  if (noteInput && commentNote) {
    noteInput.addEventListener('input', function() {
      commentNote.value = this.value;
    });
  }
});
