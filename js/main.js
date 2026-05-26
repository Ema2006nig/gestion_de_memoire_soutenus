/* ============================================================
   GESTION DES MÉMOIRES SOUTENUS
   Script JavaScript principal
   Auteur : Chef de projet
   Convention : const par défaut, commentaires en français,
                une fonction = un seul rôle
   ============================================================ */

/* === INITIALISATION AU CHARGEMENT DU DOM === */
document.addEventListener('DOMContentLoaded', () => {
  initialiserSidebar();
  initialiserModals();
  initialiserAlertes();
  initialiserValidationFormulaires();
  initialiserRecherche();
  initialiserConfirmationSuppression();
  initialiserLienActif();
});

/* ============================================================
   SIDEBAR — Ouverture/fermeture sur mobile
   ============================================================ */

/**
 * Gère le toggle de la sidebar sur les petits écrans.
 * Ajoute/supprime la classe "ouverte" sur l'élément .sidebar.
 */
const initialiserSidebar = () => {
  const boutonToggle = document.getElementById('btn-sidebar');
  const sidebar      = document.querySelector('.sidebar');
  const fond         = document.getElementById('fond-sidebar');

  if (!boutonToggle || !sidebar) return;

  /* Ouvrir la sidebar */
  boutonToggle.addEventListener('click', () => {
    sidebar.classList.toggle('ouverte');
    if (fond) fond.classList.toggle('visible');
  });

  /* Fermer en cliquant sur le fond semi-transparent */
  if (fond) {
    fond.addEventListener('click', () => {
      sidebar.classList.remove('ouverte');
      fond.classList.remove('visible');
    });
  }
};

/**
 * Met en surbrillance le lien actif dans la sidebar
 * en comparant l'URL courante avec le href de chaque lien.
 */
const initialiserLienActif = () => {
  const liens     = document.querySelectorAll('.sidebar__lien');
  const urlActuel = window.location.pathname;

  liens.forEach(lien => {
    if (lien.getAttribute('href') === urlActuel) {
      lien.classList.add('actif');
    }
  });
};

/* ============================================================
   MODALS — Ouverture, fermeture, clic extérieur
   ============================================================ */

/**
 * Initialise tous les modals de la page.
 * Convention HTML attendue :
 *   - Bouton déclencheur  : data-ouvre-modal="id-du-modal"
 *   - Fond du modal       : .modal-fond (id = id-du-modal)
 *   - Bouton fermeture    : .modal__fermer (à l'intérieur du modal)
 */
const initialiserModals = () => {
  /* Ouvrir */
  document.querySelectorAll('[data-ouvre-modal]').forEach(bouton => {
    bouton.addEventListener('click', () => {
      const idModal = bouton.dataset.ouvreModal;
      ouvrirModal(idModal);
    });
  });

  /* Fermer via le bouton × */
  document.querySelectorAll('.modal__fermer').forEach(bouton => {
    bouton.addEventListener('click', () => {
      const modal = bouton.closest('.modal-fond');
      if (modal) fermerModal(modal.id);
    });
  });

  /* Fermer en cliquant en dehors du modal */
  document.querySelectorAll('.modal-fond').forEach(fond => {
    fond.addEventListener('click', (e) => {
      if (e.target === fond) fermerModal(fond.id);
    });
  });

  /* Fermer avec la touche Échap */
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-fond.ouvert').forEach(fond => {
        fermerModal(fond.id);
      });
    }
  });
};

/**
 * Ouvre un modal par son identifiant.
 * @param {string} id - L'identifiant du modal à ouvrir
 */
const ouvrirModal = (id) => {
  const modal = document.getElementById(id);
  if (modal) {
    modal.classList.add('ouvert');
    document.body.style.overflow = 'hidden';
  }
};

/**
 * Ferme un modal par son identifiant.
 * @param {string} id - L'identifiant du modal à fermer
 */
const fermerModal = (id) => {
  const modal = document.getElementById(id);
  if (modal) {
    modal.classList.remove('ouvert');
    document.body.style.overflow = '';
  }
};

/* ============================================================
   ALERTES — Fermeture automatique et manuelle
   ============================================================ */

/**
 * Gère la fermeture des alertes.
 * - Automatique après 5 secondes
 * - Manuelle via le bouton [data-ferme-alerte]
 */
const initialiserAlertes = () => {
  const alertes = document.querySelectorAll('.alerte');

  alertes.forEach(alerte => {
    /* Fermeture automatique */
    setTimeout(() => masquerAlerte(alerte), 5000);

    /* Fermeture manuelle */
    const btnFermer = alerte.querySelector('[data-ferme-alerte]');
    if (btnFermer) {
      btnFermer.addEventListener('click', () => masquerAlerte(alerte));
    }
  });
};

/**
 * Masque une alerte avec une animation de fondu.
 * @param {HTMLElement} alerte - L'élément alerte à masquer
 */
const masquerAlerte = (alerte) => {
  alerte.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
  alerte.style.opacity    = '0';
  alerte.style.transform  = 'translateY(-8px)';
  setTimeout(() => alerte.remove(), 400);
};

/* ============================================================
   VALIDATION DES FORMULAIRES — Côté client
   ============================================================ */

/**
 * Attache la validation en temps réel à tous les formulaires
 * portant l'attribut data-valider="true".
 */
const initialiserValidationFormulaires = () => {
  document.querySelectorAll('form[data-valider="true"]').forEach(formulaire => {
    const champs = formulaire.querySelectorAll('[required], [data-min], [data-type]');

    /* Validation à la soumission */
    formulaire.addEventListener('submit', (e) => {
      let valide = true;

      champs.forEach(champ => {
        if (!validerChamp(champ)) valide = false;
      });

      if (!valide) e.preventDefault();
    });

    /* Validation en temps réel sur chaque champ */
    champs.forEach(champ => {
      champ.addEventListener('blur', () => validerChamp(champ));
      champ.addEventListener('input', () => {
        if (champ.classList.contains('erreur')) validerChamp(champ);
      });
    });
  });
};

/**
 * Valide un champ de formulaire selon ses attributs.
 * Affiche ou supprime le message d'erreur correspondant.
 * @param {HTMLElement} champ - Le champ à valider
 * @returns {boolean} true si le champ est valide
 */
const validerChamp = (champ) => {
  const valeur      = champ.value.trim();
  const typeSpecial = champ.dataset.type;
  const longueurMin = parseInt(champ.dataset.min || '0', 10);
  let erreur        = '';

  /* Champ obligatoire vide */
  if (champ.hasAttribute('required') && !valeur) {
    erreur = 'Ce champ est obligatoire.';
  }
  /* Longueur minimale */
  else if (valeur && longueurMin && valeur.length < longueurMin) {
    erreur = `Minimum ${longueurMin} caractères requis.`;
  }
  /* Validation email */
  else if (typeSpecial === 'email' && valeur && !estEmailValide(valeur)) {
    erreur = 'Adresse email invalide.';
  }
  /* Validation fichier PDF uniquement */
  else if (typeSpecial === 'pdf' && champ.files?.length) {
    const fichier = champ.files[0];
    if (fichier.type !== 'application/pdf') {
      erreur = 'Seuls les fichiers PDF sont acceptés.';
    } else if (fichier.size > 20 * 1024 * 1024) {
      erreur = 'Le fichier ne doit pas dépasser 20 Mo.';
    }
  }

  /* Appliquer ou retirer l'état d'erreur */
  afficherErreurChamp(champ, erreur);
  return !erreur;
};

/**
 * Affiche ou supprime le message d'erreur sous un champ.
 * @param {HTMLElement} champ  - Le champ concerné
 * @param {string}      erreur - Le message d'erreur (vide = aucune erreur)
 */
const afficherErreurChamp = (champ, erreur) => {
  const parent         = champ.closest('.groupe-champ');
  let   msgErreur      = parent?.querySelector('.message-erreur');

  if (erreur) {
    champ.classList.add('erreur');
    if (parent && !msgErreur) {
      msgErreur = document.createElement('p');
      msgErreur.className = 'message-erreur';
      parent.appendChild(msgErreur);
    }
    if (msgErreur) msgErreur.textContent = erreur;
  } else {
    champ.classList.remove('erreur');
    if (msgErreur) msgErreur.remove();
  }
};

/**
 * Vérifie si une adresse email est valide.
 * @param {string} email - L'email à tester
 * @returns {boolean}
 */
const estEmailValide = (email) => {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
};

/* ============================================================
   RECHERCHE EN TEMPS RÉEL — Filtrage côté client
   ============================================================ */

/**
 * Active la recherche en temps réel dans les tableaux.
 * Convention HTML attendue :
 *   - Champ de recherche  : data-recherche="id-du-tableau"
 *   - Tableau cible       : id = id-du-tableau (les <tr> du tbody seront filtrés)
 */
const initialiserRecherche = () => {
  document.querySelectorAll('[data-recherche]').forEach(champ => {
    const idTableau = champ.dataset.recherche;
    const tableau   = document.getElementById(idTableau);
    if (!tableau) return;

    champ.addEventListener('input', () => {
      const terme = champ.value.toLowerCase().trim();
      const lignes = tableau.querySelectorAll('tbody tr');

      lignes.forEach(ligne => {
        const texte = ligne.textContent.toLowerCase();
        ligne.style.display = texte.includes(terme) ? '' : 'none';
      });

      afficherEtatVide(tableau, terme);
    });
  });
};

/**
 * Affiche un message "aucun résultat" si toutes les lignes sont masquées.
 * @param {HTMLElement} tableau - Le tableau concerné
 * @param {string}      terme   - Le terme de recherche
 */
const afficherEtatVide = (tableau, terme) => {
  const lignesVisibles = tableau.querySelectorAll('tbody tr:not([style*="none"])');
  let   etatVide       = tableau.querySelector('.ligne-vide');

  if (lignesVisibles.length === 0 && terme) {
    if (!etatVide) {
      const colSpan = tableau.querySelectorAll('thead th').length || 5;
      etatVide = document.createElement('tr');
      etatVide.className = 'ligne-vide';
      etatVide.innerHTML = `
        <td colspan="${colSpan}" style="text-align:center; padding:2rem; color:var(--texte-discret);">
          Aucun résultat pour "<strong>${terme}</strong>"
        </td>`;
      tableau.querySelector('tbody').appendChild(etatVide);
    }
  } else if (etatVide) {
    etatVide.remove();
  }
};

/* ============================================================
   CONFIRMATION DE SUPPRESSION
   ============================================================ */

/**
 * Demande une confirmation avant toute suppression.
 * Convention HTML : ajouter data-confirmer="Texte de confirmation"
 * sur le bouton ou le lien de suppression.
 */
const initialiserConfirmationSuppression = () => {
  document.querySelectorAll('[data-confirmer]').forEach(element => {
    element.addEventListener('click', (e) => {
      const message = element.dataset.confirmer || 'Confirmer la suppression ?';
      if (!confirm(message)) {
        e.preventDefault();
      }
    });
  });
};

/* ============================================================
   UTILITAIRES — Fonctions réutilisables exportables
   ============================================================ */

/**
 * Affiche une notification toast temporaire.
 * @param {string} message - Le texte à afficher
 * @param {'succes'|'danger'|'warning'|'info'} type - Le type de toast
 */
const afficherToast = (message, type = 'info') => {
  const conteneur = obtenirConteneurToast();
  const toast     = document.createElement('div');

  toast.className = `alerte alerte--${type}`;
  toast.style.cssText = 'margin:0; min-width:280px; animation:entreeModal 0.2s ease;';
  toast.innerHTML = `<span>${message}</span>`;

  conteneur.appendChild(toast);
  setTimeout(() => masquerAlerte(toast), 4000);
};

/**
 * Crée ou récupère le conteneur des toasts en haut à droite.
 * @returns {HTMLElement}
 */
const obtenirConteneurToast = () => {
  let conteneur = document.getElementById('toast-conteneur');
  if (!conteneur) {
    conteneur = document.createElement('div');
    conteneur.id = 'toast-conteneur';
    conteneur.style.cssText = `
      position: fixed;
      top: 80px; right: 1.5rem;
      z-index: 9999;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    `;
    document.body.appendChild(conteneur);
  }
  return conteneur;
};

/**
 * Formate une date ISO en format lisible en français.
 * @param {string} dateISO - La date au format ISO (ex: "2024-03-15")
 * @returns {string} La date formatée (ex: "15 mars 2024")
 */
const formaterDate = (dateISO) => {
  return new Date(dateISO).toLocaleDateString('fr-FR', {
    day: '2-digit', month: 'long', year: 'numeric'
  });
};

/**
 * Tronque un texte long et ajoute des points de suspension.
 * @param {string} texte    - Le texte à tronquer
 * @param {number} longueur - La longueur maximale (défaut : 80)
 * @returns {string}
 */
const tronquer = (texte, longueur = 80) => {
  return texte.length > longueur ? texte.slice(0, longueur) + '…' : texte;
};

/* Rendre les utilitaires accessibles globalement si besoin */
window.App = { ouvrirModal, fermerModal, afficherToast, formaterDate, tronquer };
