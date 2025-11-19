<?php
session_start();
include("config/db.php");
include("header.php");

//verifier que connecté et employé
if (!isset($_SESSION['user_id'])) {
    echo "<p>❌ Accès réservé. Vous devez être connecté.</p>";
    include("footer.php");
    exit;
}

$user_id = $_SESSION['user_id'];

//verifier rôle
$sql = "SELECT role FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$role = $stmt->get_result()->fetch_assoc();

if (!$role || $role['role'] != "employe") {
    echo "<p>❌ Accès refusé. Seuls les employés peuvent accéder.</p>";
    include("footer.php");
    exit;
}


// VALIDATION D'UN AVIS
if (isset($_GET['valider_avis'])) {
    $id = intval($_GET['valider_avis']);
    $sqlU = "UPDATE reviews SET valide=1 WHERE id=?";
    $stmtU = $conn->prepare($sqlU);
    $stmtU->bind_param("i", $id);
    $stmtU->execute();
    echo "<p>✅ Avis validé.</p>";
}

if (isset($_GET['refuser_avis'])) {
    $id = intval($_GET['refuser_avis']);
    $sqlD = "DELETE FROM reviews WHERE id=?";
    $stmtD = $conn->prepare($sqlD);
    $stmtD->bind_param("i", $id);
    $stmtD->execute();
    echo "<p>❌ Avis supprimé/refusé.</p>";
}

?>

<h2>🛠️ Espace Employé</h2>

<hr>
<h3>📋 Avis en attente de validation</h3>
<?php
$sql = "SELECT rw.*, u.pseudo as auteur 
        FROM reviews rw
        JOIN users u ON rw.passager_id = u.id
        WHERE rw.valide=0";
$res = $conn->query($sql);

if ($res->num_rows > 0) {
    echo "<ul>";
    while ($r = $res->fetch_assoc()) {
        echo "<li>
            <b>{$r['auteur']} :</b> Note {$r['note']}/5 <br>
            Commentaire : {$r['commentaire']}<br>
            [<a href='employe.php?valider_avis={$r['id']}'>✅ Valider</a> | 
             <a href='employe.php?refuser_avis={$r['id']}'>❌ Refuser</a>]
        </li><br>";
    }
    echo "</ul>";
} else {
    echo "<p>Aucun avis en attente.</p>";
}
?>

<hr>
<h3>⚠️ Trajets mal passés (signalements)</h3>
<?php
// On prend tous les avis non validés (valide=0) créés suite à "trajet KO"
$sql = "SELECT rw.*, r.ville_depart, r.ville_arrivee, r.date_depart, 
               uc.pseudo as chauffeur_name, uc.email as chauffeur_email,
               up.pseudo as passager_name, up.email as passager_email
        FROM reviews rw
        JOIN rides r ON rw.ride_id = r.id
        JOIN users uc ON rw.chauffeur_id = uc.id
        JOIN users up ON rw.passager_id = up.id
        WHERE rw.valide = 0";
$res = $conn->query($sql);

if ($res->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr>
            <th>ID trajet</th>
            <th>Départ</th>
            <th>Arrivée</th>
            <th>Date</th>
            <th>Chauffeur</th>
            <th>Email Chauffeur</th>
            <th>Passager</th>
            <th>Email Passager</th>
            <th>Commentaire</th>
          </tr>";
    while ($row = $res->fetch_assoc()) {
        echo "<tr>
                <td>{$row['ride_id']}</td>
                <td>{$row['ville_depart']}</td>
                <td>{$row['ville_arrivee']}</td>
                <td>{$row['date_depart']}</td>
                <td>{$row['chauffeur_name']}</td>
                <td>{$row['chauffeur_email']}</td>
                <td>{$row['passager_name']}</td>
                <td>{$row['passager_email']}</td>
                <td>{$row['commentaire']}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>Aucun trajet mal passé enregistré.</p>";
}
requireEmployee();
?>

<?php include("footer.php"); ?>
