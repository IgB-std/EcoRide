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
$ride_id = intval($_GET['id'] ?? 0);

//vérif que l'utilisateur est passager du trajet
$sql = "SELECT r.*, p.id as participation_id, r.chauffeur_id
        FROM rides r
        JOIN participations p ON r.id = p.ride_id
        WHERE r.id=? AND p.passager_id=? AND p.statut='confirme'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $ride_id, $user_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "<p>❌ Vous n'êtes pas passager de ce trajet.</p>";
    include("footer.php");
    exit;
}

$chauffeur_id = $data['chauffeur_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $note = intval($_POST['note']);
    $comment = $_POST['comment'];
    $etat = $_POST['etat'];

    if ($etat == "ok") {
        //créer avis validé (validé par employé)
        $sqlInsert = "INSERT INTO reviews (ride_id, chauffeur_id, passager_id, note, commentaire, valide) 
                      VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = $conn->prepare($sqlInsert);
        $stmt->bind_param("iiiis", $ride_id, $chauffeur_id, $user_id, $note, $comment);
        $stmt->execute();

        //crediter chauffeur
        $sqlC = "UPDATE users SET credits = credits + ? WHERE id = ?";
        $stmtC = $conn->prepare($sqlC);
        $stmtC->bind_param("ii", $data['prix'], $chauffeur_id);
        $stmtC->execute();

        echo "<p>✅ Merci pour votre avis ! Chauffeur crédité.</p>";

    } else {
        //trajet déclaré négatif => avis en attente de validation employé
        $sqlInsert = "INSERT INTO reviews (ride_id, chauffeur_id, passager_id, note, commentaire, valide) 
                      VALUES (?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sqlInsert);
        $stmt->bind_param("iiiis", $ride_id, $chauffeur_id, $user_id, $note, $comment);
        $stmt->execute();

        echo "<p>⚠️ Votre avis a été soumis. Un employé vous contactera pour ce trajet.</p>";
    }
}
?>

    <h2>✍️ Validation trajet</h2>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= generateCsrf(); ?>">
        <label>Comment s'est passé le trajet ?</label><br>
        <input type="radio" name="etat" value="ok" required> ✅ Tout s'est bien passé <br>
        <input type="radio" name="etat" value="ko"> ❌ Non, problème pendant le trajet <br><br>

        <label>Note du chauffeur (1 à 5) :</label>
        <input type="number" name="note" min="1" max="5" required><br><br>

        <label>Commentaire :</label><br>
        <textarea name="comment" rows="4" cols="40"></textarea><br><br>

        <button type="submit">Envoyer mon avis</button>
    </form>

<?php include("footer.php"); ?>