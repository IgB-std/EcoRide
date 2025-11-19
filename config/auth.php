<?php

// démarrer la session si pas encore lancée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



//gestion sécurité de la session


// protection contre session hijacking (vol d’identifiant de session)
if (!empty($_SESSION['user_id'])) {
    if (!isset($_SESSION['USER_AGENT'])) {
        $_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT']; // navigateur utilisé
    } elseif ($_SESSION['USER_AGENT'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_unset();
        session_destroy();
        die("Session invalide (sécurité)");
    }
}

// timeout(déconnexion après 30 min d’inactivité)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

//Fonctions de contrôle d’accès

//Vérifie si quelqu’un est connecté
function requireLogin()
{
    if (empty($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

//Pour les Utilisateurs (chauffeurs, passagers)
function requireUser()
{
    requireLogin();
    if ($_SESSION['role'] !== 'utilisateur') {
        die("❌ Accès refusé. Page réservée aux utilisateurs.");
    }
}

//Pour les Employés
function requireEmployee()
{
    requireLogin();
    if ($_SESSION['role'] !== 'employe' && $_SESSION['role'] !== 'admin') {
        die("❌ Accès refusé. Page réservée aux employés.");
    }
}

//Pour les Administrateurs
function requireAdmin()
{
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        die("❌ Accès refusé. Page réservée aux administrateurs.");
    }
}
