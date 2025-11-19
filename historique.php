<?php
session_start();
include("config/db.php");
include("header.php");

// vérifier connexion
if (!isset($_SESSION['user_id'])) {
    echo "<p>❌ Vous devez être connecté pour accéder à votre historique.</p>";
    echo "<a href='login.php'><button>Connexion</button></a>";
    include("footer.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// annulation d'un trajet PASSAGER
if (isset($_GET['annuler_participation'])) {
    $participation_id = intval($_GET['annuler_participation']);

    // recup infos participation
    $sqlP = "SELECT p.*, r.prix, r.id as ride_id FROM participations p 
             JOIN rides r ON p.ride_id = r.id
             WHERE p.id = ? AND p.passager_id = ?";
    $stmtP = $conn->prepare($sqlP);
    $stmtP->bind_param("ii", $participation_id, $user_id);
    $stmtP->execute();
    $part = $stmtP->get_result()->fetch_assoc();

    if ($part) {
        $ride_id = $part['ride_id'];
        $prix = $part['prix'];

        // rembourse crédit passager
        $sql1 = "UPDATE users SET credits = credits + ? WHERE id = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("ii", $prix, $user_id);
        $stmt1->execute();

        // rend une place au trajet
        $sql2 = "UPDATE rides SET places_restantes = places_restantes + 1 WHERE id = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("i", $ride_id);
        $stmt2->execute();

        //statut participation => annulée
        $sql3 = "UPDATE participations SET statut = 'annule' WHERE id = ?";
        $stmt3 = $conn->prepare($sql3);
        $stmt3->bind_param("i", $participation_id);
        $stmt3->execute();

        echo "<p>✅ Vous avez annulé votre participation. Crédit remboursé.</p>";
    }
}

//Annulation d'un trajet par CHAUFFEUR
if (isset($_GET['annuler_trajet'])) {
    $ride_id = intval($_GET['annuler_trajet']);

    //Vérif que user est chauffeur du trajet
    $sqlR = "SELECT * FROM rides WHERE id = ? AND chauffeur_id = ?";
    $stmtR = $conn->prepare($sqlR);
    $stmtR->bind_param("ii", $ride_id, $user_id);
    $stmtR->execute();
    $ride = $stmtR->get_result()->fetch_assoc();

    if ($ride) {
        //rendr crédits aux passagers + notifier
        $sqlP = "SELECT p.*, u.email, u.id as passager_id FROM participations p
                 JOIN users u ON u.id = p.passager_id
                 WHERE p.ride_id = ? AND p.statut = 'confirme'";
        $stmtP = $conn->prepare($sqlP);
        $stmtP->bind_param("i", $ride_id);
        $stmtP->execute();
        $participants = $stmtP->get_result();

        while ($p = $participants->fetch_assoc()) {
            $passager_id = $p['passager_id'];
            $prix = $ride['prix'];

            //rembourser crédits passagers
            $sqlC = "UPDATE users SET credits = credits + ? WHERE id = ?";
            $stmtC = $conn->prepare($sqlC);
            $stmtC->bind_param("ii", $prix, $passager_id);
            $stmtC->execute();

            //statut participation annulée
            $sqlU = "UPDATE participations SET statut = 'annule' WHERE id = ?";
            $stmtU = $conn->prepare($sqlU);
            $stmtU->bind_param("i", $p['id']);
            $stmtU->execute();

            echo "<p>📧 Notification envoyée à {$p['email']} : Votre trajet a été annulé.</p>";
        }

        //changer statut du trajet
        $sqlRide = "UPDATE rides SET statut = 'annule' WHERE id = ?";
        $stmtRide = $conn->prepare($sqlRide);
        $stmtRide->bind_param("i", $ride_id);
        $stmtRide->execute();

        echo "<p>✅ Trajet annulé avec succès. Passagers remboursés.</p>";
    }
}
?>

    <h2>📜 Mon Historique</h2>

    <h3>🚘 Mes trajets en tant que Chauffeur</h3>
<?php
$sqlCh = "SELECT * FROM rides WHERE chauffeur_id = ?";
$stmtCh = $conn->prepare($sqlCh);
$stmtCh->bind_param("i", $user_id);
$stmtCh->execute();
$trajets = $stmtCh->get_result();

if ($trajets->num_rows > 0) {
    echo "<ul>";
    while ($t = $trajets->fetch_assoc()) {
        echo "<li>
            [{$t['statut']}] {$t['ville_depart']} → {$t['ville_arrivee']} le {$t['date_depart']} ({$t['heure_depart']} - {$t['heure_arrivee']}) 
            - Places restantes : {$t['places_restantes']} - Prix : {$t['prix']} crédits
            ";
        if ($t['statut'] == "planifie") {
            echo "<a href='us11.php?action=start&id={$t['id']}'><button>▶️ Démarrer</button></a> ";
            echo "<a href='historique.php?annuler_trajet={$t['id']}'><button>❌ Annuler</button></a>";
        }elseif ($t['statut'] == "en_cours") {
            echo "<a href='us11.php?action=end&id={$t['id']}'><button>⏹️ Arrivée à destination</button></a>";
        }
        echo "</li><br>";
    }
    echo "</ul>";
} else {
    echo "<p>Aucun trajet en tant que chauffeur.</p>";
}
?>

    <hr>
    <h3>👥 Mes participations en tant que Passager</h3>
<?php
$sqlPa = "SELECT p.*, r.ville_depart, r.ville_arrivee, r.date_depart, r.prix, r.statut as statut_trajet
          FROM participations p
          JOIN rides r ON p.ride_id = r.id
          WHERE p.passager_id = ?";
$stmtPa = $conn->prepare($sqlPa);
$stmtPa->bind_param("i", $user_id);
$stmtPa->execute();
$participe = $stmtPa->get_result();

if ($participe->num_rows > 0) {
    echo "<ul>";
    while ($p = $participe->fetch_assoc()) {
        echo "<li>
            [{$p['statut_trajet']}] {$p['ville_depart']} → {$p['ville_arrivee']} le {$p['date_depart']} 
            - Prix payé : {$p['prix']} crédits (Votre statut : {$p['statut']})
            ";
        if ($p['statut_trajet'] == "planifie" && $p['statut'] == "confirme") {
            echo "<a href='historique.php?annuler_participation={$p['id']}'><button>❌ Annuler</button></a>";
        }
        if ($p['statut_trajet'] == "termine" && $p['statut'] == "confirme") {
            echo "<a href='valider_trajet.php?id={$p['ride_id']}'><button>✍️ Donner un avis</button></a>";
        }
        echo "</li><br>";
    }
    echo "</ul>";
} else {
    echo "<p>Aucune participation en tant que passager.</p>";
}
?>

<?php include("footer.php"); ?>