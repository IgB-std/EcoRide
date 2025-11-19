<?php
session_start();
include("config/db.php");
include("header.php");

//verif si utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo "<p>❌ Vous devez être connecté pour participer.</p>";
    echo "<a href='login.php'>Se connecter</a>";
    include("footer.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$ride_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

//étape 1 : récup infos trajet et utilisateur
$sql = "SELECT r.*, u.credits FROM rides r
        JOIN users u ON u.id = ?
        WHERE r.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $ride_id);
$stmt->execute();
$trajet = $stmt->get_result()->fetch_assoc();

if (!$trajet) {
    echo "<p>❌ Trajet inexistant.</p>";
    include("footer.php");
    exit;
}

//vérifications
if ($trajet['places_restantes'] <= 0) {
    echo "<p>❌ Plus de places disponibles pour ce covoiturage.</p>";
    include("footer.php");
    exit;
}

if ($trajet['credits'] < $trajet['prix']) {
    echo "<p>❌ Vous n’avez pas assez de crédits. Rechargez votre compte !</p>";
    include("footer.php");
    exit;
}

//double confirmation
if (!isset($_POST['confirm'])) {
    echo "<h2>Confirmation participation</h2>";
    echo "<p>Voulez-vous confirmer votre participation au trajet :</p>";
    echo "<p><b>{$trajet['ville_depart']} → {$trajet['ville_arrivee']}</b></p>";
    echo "<p>Prix : {$trajet['prix']} crédits</p>";
    echo "<form method='post'>
            <input type='hidden' name='csrf_token' value='<?= generateCsrf(); ?>'>
            <input type='hidden' name='confirm' value='1'>
            <button type='submit'>✅ Oui, je confirme</button>
          </form>";
    echo "<a href='detail.php?id=$ride_id'><button>❌ Annuler</button></a>";
    include("footer.php");
    exit;
}

//si confirmé => mise à jour bdd
$conn->begin_transaction();

try {
    // 1 - décrémenter crédits utilisateur
    $sql1 = "UPDATE users SET credits = credits - ? WHERE id = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("ii", $trajet['prix'], $user_id);
    $stmt1->execute();

    // 2 - décrémenter place trajet
    $sql2 = "UPDATE rides SET places_restantes = places_restantes - 1 WHERE id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $ride_id);
    $stmt2->execute();

    // 3 - ajouter participation
    $sql3 = "INSERT INTO participations (ride_id, passager_id) VALUES (?, ?)";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param("ii", $ride_id, $user_id);
    $stmt3->execute();

    $conn->commit();

    echo "<h2>✅ Participation confirmée !</h2>";
    echo "<p>Vous êtes bien inscrit au covoiturage {$trajet['ville_depart']} → {$trajet['ville_arrivee']}.</p>";
    echo "<a href='historique.php'><button>Voir mon historique</button></a>";

} catch (Exception $e) {
    $conn->rollback();
    echo "<p>❌ Une erreur est survenue : " . $e->getMessage() . "</p>";
}

include("footer.php");
?>
