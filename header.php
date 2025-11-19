<?php
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
        include("config/security.php");
        include("config/auth.php");
    ?>
    <meta charset="UTF-8">
    <title>EcoRide</title>
    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
    <!-- google font -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
<nav>
    <ul>
        <li><a href="index.php">Accueil</a></li>
        <li><a href="covoiturages.php">Covoiturages</a></li>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <!-- menu visiteur -->
            <li><a href="login.php">Connexion</a></li>
            <li><a href="register.php">Créer un compte</a></li>

        <?php else: ?>
            <!-- menu utilisateur/employé/admin -->
            <?php if ($_SESSION['role'] === 'utilisateur'): ?>
                <li><a href="dashboard.php">Mon espace</a></li>
                <li><a href="saisir_trajet.php">Saisir un trajet</a></li>
                <li><a href="historique.php">Mon historique</a></li>
            <?php elseif ($_SESSION['role'] === 'employe'): ?>
                <li><a href="employe.php">Espace Employé</a></li>
            <?php elseif ($_SESSION['role'] === 'admin'): ?>
                <li><a href="admin.php">Espace Admin</a></li>
            <?php endif; ?>

            <!-- déconnexion pour tout le monde -->
            <li><a href="logout.php">Déconnexion</a></li>
        <?php endif; ?>
    </ul>
</nav>
<hr>