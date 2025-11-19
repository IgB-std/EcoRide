<?php
session_start();
include("config/db.php");
include("header.php");

//vérif si utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo "<p>❌ Vous devez être connecté pour saisir un voyage.</p>";
    echo "<a href='login.php'><button>Connexion</button></a>";
    include("footer.php");
    exit;
}

$user_id = $_SESSION['user_id'];

//vérif rôle utilisateur
$sql = "SELECT type_role, credits FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user['type_role'] != 'chauffeur' && $user['type_role'] != 'les deux') {
    echo "<p>❌ Vous devez être chauffeur pour saisir un trajet.</p>";
    include("footer.php");
    exit;
}

//traitement formulaire saisie
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $depart = $_POST['depart'];
    $arrivee = $_POST['arrivee'];
    $date = $_POST['date_depart'];
    $heure_depart = $_POST['heure_depart'];
    $heure_arrivee = $_POST['heure_arrivee'];
    $prix = intval($_POST['prix']);
    $vehicle_id = intval($_POST['vehicle_id']);

    //vérifier crédits chauffeur (>= 2 pour commission)
    if ($user['credits'] < 2) {
        echo "<p>❌ Vous n’avez pas assez de crédits pour créer un trajet. (minimum 2 crédits)</p>";
        include("footer.php");
        exit;
    }

    //récup nb places du véhicule
    $sqlVeh = "SELECT places_disponibles, energie FROM vehicles WHERE id = ? AND user_id = ?";
    $stmtVeh = $conn->prepare($sqlVeh);
    $stmtVeh->bind_param("ii", $vehicle_id, $user_id);
    $stmtVeh->execute();
    $veh = $stmtVeh->get_result()->fetch_assoc();

    if (!$veh) {
        echo "<p>❌ Véhicule invalide.</p>";
        include("footer.php");
        exit;
    }

    $places = $veh['places_disponibles'];
    $eco = ($veh['energie'] == "electrique") ? 1 : 0;

    //débit des 2 crédits commission
    $sqlCredits = "UPDATE users SET credits = credits - 2 WHERE id = ?";
    $stmtC = $conn->prepare($sqlCredits);
    $stmtC->bind_param("i", $user_id);
    $stmtC->execute();

    //insertion trajet
    $sqlInsert = "INSERT INTO rides (chauffeur_id, vehicle_id, ville_depart, ville_arrivee, date_depart, heure_depart, heure_arrivee, prix, places_restantes, eco) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtI = $conn->prepare($sqlInsert);
    $stmtI->bind_param("iisssssiii", $user_id, $vehicle_id, $depart, $arrivee, $date, $heure_depart, $heure_arrivee, $prix, $places, $eco);

    if ($stmtI->execute()) {
        echo "<h2>✅ Trajet ajouté avec succès !</h2>";
        echo "<p>Commission de 2 crédits prélevée !</p>";
        echo "<a href='historique.php'><button>Voir mes trajets</button></a>";
    } else {
        echo "<p>❌ Erreur lors de l’ajout du trajet.</p>";
    }
}

//récup véhicules utilisateur
$sql = "SELECT * FROM vehicles WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$vehicles = $stmt->get_result();
?>

    <h2>➕ Saisir un nouveau trajet</h2>
    <form method="post" class="form-container">
        <input type="hidden" name="csrf_token" value="<?= generateCsrf(); ?>">
        <label>Départ :</label>
        <input type="text" name="depart" required><br>

        <label>Arrivée :</label>
        <input type="text" name="arrivee" required><br>

        <label>Date :</label>
        <input type="date" name="date_depart" required><br>

        <label>Heure départ :</label>
        <input type="time" name="heure_depart" required><br>

        <label>Heure arrivée :</label>
        <input type="time" name="heure_arrivee" required><br>

        <label>Prix du trajet (en crédits) :</label>
        <input type="number" min='0' name="prix" required><br>

        <label>Véhicule :</label>
        <select name="vehicle_id" required>
            <?php while ($v = $vehicles->fetch_assoc()) { ?>
                <option value="<?= $v['id'] ?>"><?= $v['marque']." ".$v['modele']." (".$v['immatriculation'].")" ?></option>
            <?php } ?>
        </select><br>

        <button type="submit">Créer le trajet</button>
    </form>

<?php include("footer.php"); ?>