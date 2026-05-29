// Protections cote client (dissuasives) : pas de menu contextuel, pas de raccourcis
// d'impression/sauvegarde, pas de capture via PrintScreen (efface le presse-papier),
// brouillage en cas de perte de focus. Ces mesures sont contournables : la vraie
// protection reste serveur (acces controle, en-tetes no-store, pas de telechargement).
(function () {
  document.addEventListener('contextmenu', e => e.preventDefault());
  document.addEventListener('keydown', e => {
    const k = e.key.toLowerCase();
    if ((e.ctrlKey || e.metaKey) && ['s','p','u','c'].includes(k)) e.preventDefault();
    if (k === 'printscreen') {
      try { navigator.clipboard.writeText(''); } catch (_) {}
      alert('Capture d\'ecran desactivee.');
    }
  });
  document.addEventListener('visibilitychange', () => {
    const v = document.getElementById('pdfViewer');
    if (!v) return;
    v.style.filter = document.hidden ? 'blur(20px)' : 'none';
  });
  window.addEventListener('blur', () => {
    const v = document.getElementById('pdfViewer');
    if (v) v.style.filter = 'blur(20px)';
  });
  window.addEventListener('focus', () => {
    const v = document.getElementById('pdfViewer');
    if (v) v.style.filter = 'none';
  });
})();
