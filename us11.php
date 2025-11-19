<?php
session_start();
include("config/db.php");
include("header.php");

if (!isset($_SESSION['user_id'])) {
    echo "<p>❌ Vous devez être connecté.</p>";
    include("footer.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';
$ride_id = intval($_GET['id'] ?? 0);

//vérif trajet appartenant au chauffeur
$sql = "SELECT * FROM rides WHERE id = ? AND chauffeur_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $ride_id, $user_id);
$stmt->execute();
$ride = $stmt->get_result()->fetch_assoc();

if (!$ride) {
    echo "<p>❌ Trajet introuvable ou non autorisé.</p>";
    include("footer.php");
    exit;
}

if ($action == "start" && $ride['statut'] == "planifie") {
    $sqlUpdate = "UPDATE rides SET statut='en_cours' WHERE id=?";
    $stmtU = $conn->prepare($sqlUpdate);
    $stmtU->bind_param("i", $ride_id);
    $stmtU->execute();
    echo "<p>✅ Trajet démarré avec succès.</p>";
}

if ($action == "end" && $ride['statut'] == "en_cours") {
    $sqlUpdate = "UPDATE rides SET statut='termine' WHERE id=?";
    $stmtU = $conn->prepare($sqlUpdate);
    $stmtU->bind_param("i", $ride_id);
    $stmtU->execute();

    echo "<h3>✅ Trajet terminé !</h3>";
    echo "<p>Les passagers doivent maintenant valider leur avis sur ce trajet.</p>";
}

echo "<a href='historique.php'><button>⬅ Retour à l'historique</button></a>";

include("footer.php");
?>