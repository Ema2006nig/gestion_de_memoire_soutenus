<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Gestion Mémoires | <?= $title ?? 'Accueil' ?></title>
    <!-- Google Fonts + Reset CSS léger -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{
            font-family: 'Inter', sans-serif;
            background: #f4f7fc;
            color: #1a2c3e;
            line-height: 1.5;
        }
        .app-wrapper{
            display: flex;
            min-height: 100vh;
        }
        .main-content{
            flex:1;
            padding: 2rem;
            transition: all 0.2s;
        }
        /* Notifications / badges */
        .badge-notif{
            background: #e74c3c;
            color:white;
            border-radius: 50px;
            padding: 0.2rem 0.6rem;
            font-size: 0.7rem;
            margin-left: 0.5rem;
        }
        /* Responsive */
        @media (max-width:768px){
            .main-content{ padding:1rem; }
        }
    </style>
</head>
<body>
<div class="app-wrapper">
    <?php if(isset($_SESSION['user_id'])): ?>
        <?php include_once __DIR__ . '/sidebar.php'; ?>
    <?php endif; ?>
    <main class="main-content">