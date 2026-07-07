<?php
session_start();
require_once 'config.php';
requireLogin(); // Redirige vers login.php si pas connecté
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — SNI Hôtel</title>
    <link rel="stylesheet" href="index.css">
    <style>
        .dashboard {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 80px);
            text-align: center;
            padding: 40px;
        }
        .card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 40px 50px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .card h1 { font-size: 1.8rem; margin-bottom: 10px; }
        .card p  { font-size: 1rem; opacity: 0.8; margin-bottom: 6px; }
        .badge {
            display: inline-block;
            background: #7C3B9A;
            color: #fff;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-top: 10px;
            margin-bottom: 25px;
        }
        .logout-btn {
            display: inline-block;
            background: #c0392b;
            color: #fff;
            padding: 10px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 1rem;
            transition: background 0.3s;
        }
        .logout-btn:hover { background: #922b21; }
    </style>
</head>
<body>
    
    <?php require_once 'navbar.php'; ?>

    <div class="dashboard">
        <div class="card">
            <h1>Bienvenue, <?= htmlspecialchars($_SESSION['user_nom']) ?></h1>
            <p>Vous êtes connecté avec le rôle :</p>
            <span class="badge"><?= htmlspecialchars($_SESSION['user_role']) ?></span>

            <br>
        </div>
    </div>
</body>
</html>