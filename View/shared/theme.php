<?php
/**
 * Thème Tailwind partagé.
 * Charge Tailwind via CDN, applique notre design system et redéfinit
 * toutes les classes custom (.btn, .card, .badge, .stat, .alert, .input,
 * .nav-item, .table-wrap, .mcard, .b-ok, .frow, .fi, etc.) via @apply
 * pour que toutes les vues existantes héritent du nouveau look sans
 * modifier leur markup PHP.
 *
 * À inclure dans le <head> des layouts (layout.php, _auth_layout.php,
 * index.php).
 */
?>
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: {
          sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'sans-serif'],
        },
        colors: {
          ink:       { DEFAULT: '#0f172a', soft: '#334155' },
          brand: {
            50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',
            400:'#60a5fa',500:'#3b82f6',600:'#1d4ed8',700:'#1e40af',
            800:'#1e3a8a',900:'#0b1220', DEFAULT: '#1d4ed8'
          },
          accent: {
            50:'#fffbeb',100:'#fef3c7',200:'#fde68a',300:'#fcd34d',
            400:'#fbbf24',500:'#f59e0b',600:'#d97706',700:'#b45309',
            DEFAULT:'#d97706'
          },
        },
        borderRadius: { 'xl2': '14px' },
        boxShadow: {
          'soft':  '0 2px 6px rgba(15,23,42,.05)',
          'card':  '0 6px 18px -8px rgba(15,23,42,.12)',
          'pop':   '0 24px 60px -28px rgba(15,23,42,.25)',
          'brand': '0 8px 20px -10px rgba(29,78,216,.65)',
          'amber': '0 8px 20px -10px rgba(217,119,6,.55)',
        },
      },
    },
  };
</script>
<style type="text/tailwindcss">
  @layer base {
    html { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
    body { @apply bg-slate-50 text-ink antialiased; font-size: 14px; }
    a { @apply no-underline text-inherit; }
    h1,h2,h3,h4,h5,h6 { @apply m-0; }
    p { @apply m-0; }
    *::-webkit-scrollbar { width: 10px; height: 10px; }
    *::-webkit-scrollbar-thumb { @apply bg-slate-300 rounded-full; border: 2px solid #f8fafc; }
    *::-webkit-scrollbar-thumb:hover { @apply bg-slate-400; }
    *::-webkit-scrollbar-track { background: transparent; }
  }

  @layer components {
    /* ============ Cartes & sections ============ */
    .card {
      @apply bg-white border border-slate-200 rounded-2xl p-5 md:p-6 shadow-soft mb-4;
    }
    .card-head { @apply flex items-center justify-between gap-3 flex-wrap mb-4 -mt-1; }
    .card-title { @apply text-[15px] font-bold text-ink tracking-tight flex items-center gap-2; }
    .card-sub { @apply text-slate-500 text-[12.5px] mt-0.5; }

    /* ============ Bannière dashboard ============ */
    .banner {
      @apply relative overflow-hidden rounded-2xl px-6 py-7 md:px-8 md:py-8 mb-5 text-white;
      background:
        radial-gradient(700px 380px at 100% 0%, rgba(217,119,6,.25), transparent 60%),
        radial-gradient(700px 380px at 0% 100%, rgba(14,165,233,.20), transparent 60%),
        linear-gradient(135deg, #0b1220 0%, #1e3a8a 100%);
    }
    .banner h2, .banner-title {
      @apply m-0 font-bold tracking-tight leading-tight;
      font-size: clamp(20px, 2.4vw, 26px);
    }
    .banner p, .banner-sub {
      @apply mt-2 text-white/75 text-sm max-w-2xl leading-relaxed;
    }
    .banner .pill {
      @apply inline-flex items-center gap-1.5 bg-white/15 text-white px-3 py-1 rounded-full
             text-[11px] font-bold uppercase tracking-widest mb-3;
      backdrop-filter: blur(4px);
    }

    /* ============ Statistiques ============ */
    .stat-grid, .stats {
      @apply grid gap-3.5 mb-5;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    .stat {
      @apply bg-white border border-slate-200 rounded-2xl px-5 py-4 transition-all duration-150
             hover:-translate-y-0.5 hover:shadow-card hover:border-slate-300 cursor-default;
    }
    .stat .label, .stat-l {
      @apply text-slate-500 text-[11px] uppercase tracking-wider font-bold mt-1;
    }
    .stat .value, .stat-n {
      @apply text-ink text-[28px] font-bold mt-1 leading-none tracking-tight;
    }
    .stat .ico {
      @apply w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center mb-3;
    }

    /* ============ Boutons ============ */
    .btn {
      @apply inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-[10px]
             text-[13px] font-semibold border border-transparent leading-none whitespace-nowrap
             transition-all duration-150 select-none cursor-pointer
             focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/25;
    }
    .btn:active { transform: translateY(1px); }
    .btn[disabled], .btn.is-disabled { @apply opacity-50 pointer-events-none; }

    .btn-primary { @apply bg-brand-600 text-white shadow-brand hover:bg-brand-700; }
    .btn-accent  { @apply bg-accent-600 text-white shadow-amber hover:bg-accent-700; }
    .btn-success, .btn-emerald { @apply bg-emerald-700 text-white hover:bg-emerald-800; }
    .btn-danger  { @apply bg-red-700 text-white hover:bg-red-800; }
    .btn-warning { @apply bg-amber-600 text-white hover:bg-amber-700; }
    .btn-info    { @apply bg-brand-600 text-white hover:bg-brand-700; }
    .btn-ghost   { @apply bg-white text-ink-soft border-slate-200 hover:bg-slate-100 hover:border-slate-300 hover:text-ink; }
    .btn-sm      { @apply px-3 py-1.5 text-[12px] rounded-lg; }
    .btn-full, .btn-block { @apply w-full; }

    /* ============ Formulaires ============ */
    .field, .fg { @apply mb-4; }
    .field-row, .frow {
      @apply grid gap-3;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }
    .frow3 {
      @apply grid gap-3;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    }
    .label, .fl {
      @apply block text-[11.5px] font-bold text-ink-soft mb-1.5 uppercase tracking-wide;
    }
    .input, .select, .textarea, .fi, .fsel, .ft {
      @apply w-full px-3.5 py-2.5 border border-slate-200 rounded-[10px] bg-white text-ink text-sm leading-snug
             transition-all duration-150 hover:border-slate-300
             focus:outline-none focus:border-brand-600 focus:ring-4 focus:ring-brand-500/15;
    }
    .input::placeholder, .textarea::placeholder,
    .fi::placeholder, .ft::placeholder { @apply text-slate-400; }
    .textarea, .ft { @apply min-h-[110px] resize-y; }
    .select, .fsel {
      appearance: none;
      background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8' fill='none'><path d='M1 1L6 6L11 1' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/></svg>");
      background-repeat: no-repeat;
      background-position: right 14px center;
      padding-right: 38px;
    }
    .input-with-icon { @apply relative; }
    .input-with-icon .input,
    .input-with-icon .fi { @apply pl-11; }
    .input-with-icon .ico {
      @apply absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none inline-flex;
    }
    .help { @apply text-slate-500 text-xs mt-1.5; }

    /* ============ Badges ============ */
    .badge {
      @apply inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold
             uppercase tracking-wide border border-transparent;
    }
    .badge-success, .b-ok   { @apply bg-emerald-100 text-emerald-700; }
    .badge-warning, .b-wait { @apply bg-amber-100   text-amber-700; }
    .badge-danger,  .b-ko   { @apply bg-red-100     text-red-700; }
    .badge-info,    .b-anc  { @apply bg-brand-100   text-brand-700; }
    .badge-muted            { @apply bg-slate-100   text-slate-500; }

    /* ============ Alertes ============ */
    .alert {
      @apply flex items-start gap-3 px-4 py-3 rounded-xl text-sm mb-4 border leading-relaxed;
    }
    .alert-success { @apply bg-emerald-50 text-emerald-800 border-emerald-200; }
    .alert-danger  { @apply bg-red-50     text-red-800     border-red-200; }
    .alert-warning { @apply bg-amber-50   text-amber-800   border-amber-200; }
    .alert-info    { @apply bg-brand-50   text-brand-800   border-brand-200; }

    /* ============ Tableaux ============ */
    .table-wrap {
      @apply bg-white border border-slate-200 rounded-2xl overflow-hidden mb-4 shadow-soft;
    }
    .table-wrap > div { @apply overflow-x-auto; }
    .table-wrap table { @apply w-full border-collapse text-[13.5px] min-w-[520px]; }
    .table-wrap th, .table-wrap td { @apply px-4 py-3 text-left align-middle; }
    .table-wrap thead th {
      @apply bg-slate-50 text-slate-500 text-[11px] font-bold uppercase tracking-wider
             border-b border-slate-200;
    }
    .table-wrap tbody tr { @apply border-b border-slate-100 transition-colors hover:bg-slate-50; }
    .table-wrap tbody tr:last-child { @apply border-b-0; }
    .table-wrap td { @apply text-ink; }

    /* ============ Cartes mémoires ============ */
    .memoire, .mcard {
      @apply bg-white border border-slate-200 rounded-2xl p-5 mb-3 flex flex-col gap-3
             transition-all duration-150 hover:-translate-y-0.5 hover:shadow-card hover:border-brand-600;
    }
    .memoire .title, .mcard-title {
      @apply text-[15.5px] font-bold text-ink leading-snug tracking-tight;
    }
    .memoire .meta, .mcard-meta { @apply flex flex-wrap gap-1.5; }
    .tag, .mtag {
      @apply inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-slate-100 text-ink-soft
             rounded-full text-[11.5px] font-semibold;
    }
    .memoire .foot, .mcard-foot {
      @apply flex justify-between items-center pt-3 border-t border-slate-100
             text-xs text-slate-500 flex-wrap gap-2;
    }
    .mactions { @apply flex gap-2 flex-wrap; }
    .like-btn {
      @apply inline-flex items-center gap-1.5 text-red-700 font-semibold bg-red-50
             px-3 py-1 rounded-full text-xs transition-all hover:bg-red-100 hover:scale-105;
    }

    .memoire-content {
      @apply bg-white border border-slate-200 rounded-2xl p-6 md:p-8 leading-relaxed text-ink-soft;
    }
    .memoire-content h2, .memoire-content h3 {
      @apply text-ink tracking-tight my-4;
    }

    /* ============ Sidebar / nav ============ */
    .nav-item {
      @apply relative flex items-center gap-3 px-3 py-2.5 rounded-[10px] text-slate-400
             text-[13.5px] font-medium transition-colors duration-150
             hover:bg-white/5 hover:text-slate-100;
    }
    .nav-item.is-active {
      @apply text-white shadow-brand;
      background: linear-gradient(135deg, #1d4ed8, #1e40af);
    }
    .nav-item.is-active::before {
      content: '';
      @apply absolute -left-3 top-1/2 w-[3px] h-5 bg-accent-500 rounded-r;
      transform: translateY(-50%);
    }
    .nav-icon { @apply shrink-0 inline-flex; }
    .nav-label { @apply flex-1 min-w-0 truncate; }
    .nav-badge {
      @apply bg-accent-600 text-white text-[10.5px] font-bold px-2 py-0.5
             rounded-full min-w-[22px] text-center leading-tight;
    }

    /* ============ Pagination ============ */
    .pagination { @apply flex justify-center gap-1.5 mt-5 flex-wrap; }
    .pagination a, .pagination span {
      @apply px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-[13px]
             font-semibold text-ink-soft transition-all hover:bg-slate-100 hover:border-slate-300;
    }
    .pagination .is-active {
      @apply bg-brand-600 text-white border-brand-600 hover:bg-brand-700;
    }

    /* ============ Divers ============ */
    .empty-state { @apply text-center py-12 px-6 text-slate-500 text-sm; }
    .fade-in { animation: fadeIn .25s ease; }

    /* ============ Auth form (titres + switch) ============ */
    .auth-form h1 { @apply text-[28px] font-bold tracking-tight text-ink m-0 mb-1.5; }
    .auth-form .lead { @apply text-slate-500 text-sm m-0 mb-7; }
    .auth-form .switch { @apply mt-6 text-center text-slate-500 text-[13.5px]; }
    .auth-form .switch a { @apply text-brand-600 font-semibold hover:underline; }

    /* Legacy : neutraliser anciens fragments */
    .sitem { display: none; }
    .app-wrap { display: contents; }
    .app-wrap > .sidebar { display: none; }
    .app-wrap > .main    { display: contents; }
  }

  @keyframes fadeIn { from { opacity: 0 } to { opacity: 1 } }

  /* Mobile drawer pour sidebar */
  @media (max-width: 860px) {
    .app { grid-template-columns: 1fr !important; }
    .sidebar {
      position: fixed; left: 0; top: 0;
      width: 280px; height: 100vh;
      transform: translateX(-100%);
      transition: transform .25s ease;
      z-index: 50;
      box-shadow: 0 24px 60px -28px rgba(15,23,42,.4);
    }
    .app.is-open .sidebar { transform: translateX(0); }
    .app.is-open::before {
      content: '';
      position: fixed; inset: 0;
      background: rgba(15,23,42,.5);
      z-index: 40;
      animation: fadeIn .2s ease;
    }
    .menu-toggle { display: inline-flex !important; }
  }

  @media print {
    .sidebar, .topbar, .actions, .menu-toggle { display: none !important; }
    .app { grid-template-columns: 1fr !important; }
    .content { padding: 0 !important; max-width: 100% !important; }
    body { background: #fff !important; }
  }
</style>
