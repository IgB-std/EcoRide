<?php
session_start();
include("config/db.php");
include("header.php");

//verification si connecté
if (!isset($_SESSION['user_id'])) {
    echo "<p>❌ Vous devez être connecté pour accéder à votre espace.</p>";
    echo "<a href='login.php'><button>Connexion</button></a>";
    include("footer.php");
    exit;
}

$user_id = $_SESSION['user_id'];

//gestion formulaire mise à jour rôle utilisateur
if (isset($_POST['update_role'])) {
    $type_role = $_POST['type_role'];
    $sql = "UPDATE users SET type_role = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $type_role, $user_id);
    $stmt->execute();
    echo "<p>✅ Rôle mis à jour avec succès.</p>";
}

//gestion ajout véhicule
if (isset($_POST['add_vehicle'])) {
    $marque = $_POST['marque'];
    $modele = $_POST['modele'];
    $couleur = $_POST['couleur'];
    $immatriculation = $_POST['immatriculation'];
    $date_immat = $_POST['date_immat'];
    $energie = $_POST['energie'];
    $places = intval($_POST['places']);

    $sql = "INSERT INTO vehicles (user_id, marque, modele, couleur, immatriculation, date_immatriculation, energie, places_disponibles) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssi", $user_id, $marque, $modele, $couleur, $immatriculation, $date_immat, $energie, $places);
    $stmt->execute();
    echo "<p>✅ Véhicule ajouté avec succès.</p>";
}

//gestion ajout préférence chauffeur
if (isset($_POST['add_pref'])) {
    $type_pref = $_POST['type_pref'];
    $valeur = $_POST['valeur'];

    $sql = "INSERT INTO preferences (user_id, type_preference, valeur) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $type_pref, $valeur);
    $stmt->execute();
    echo "<p>✅ Préférence ajoutée.</p>";
}

//recuperation info utilisateur
$sql = "SELECT pseudo, credits, type_role FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

//recuperation véhicules utilisateur
$sql = "SELECT * FROM vehicles WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$vehicles = $stmt->get_result();

//recuperation préférences
$sql = "SELECT * FROM preferences WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$prefs = $stmt->get_result();
?>

<h2>👤 Mon Espace Utilisateur</h2>
<p><b>Pseudo :</b> <?= $user['pseudo'] ?></p>
<p><b>Crédits :</b> <?= $user['credits'] ?> 💰</p>

<hr>
<h3>⚙️ Mon rôle</h3>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= generateCsrf(); ?>">
    <select name="type_role" required>
        <option value="passager" <?= ($user['type_role']=="passager"?"selected":"") ?>>Passager</option>
        <option value="chauffeur" <?= ($user['type_role']=="chauffeur"?"selected":"") ?>>Chauffeur</option>
        <option value="les deux" <?= ($user['type_role']=="les deux"?"selected":"") ?>>Les deux</option>
    </select>
    <button type="submit" name="update_role">Mettre à jour</button>
</form>

<hr>
<h3>🚘 Mes véhicules</h3>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= generateCsrf(); ?>">
    <input type="text" name="marque" placeholder="Marque" required>
    <input type="text" name="modele" placeholder="Modèle" required>
    <input type="text" name="couleur" placeholder="Couleur">
    <input type="text" name="immatriculation" placeholder="Immatriculation" required>
    <label>Date 1ère immat :</label><input type="date" name="date_immat" required><br>
    Energie :
    <select name="energie" required>
        <option value="essence">Essence</option>
        <option value="diesel">Diesel</option>
        <option value="hybride">Hybride</option>
        <option value="electrique">Électrique</option>
    </select>
    <input type="number" name="places" placeholder="Nb places" required>
    <button type="submit" name="add_vehicle">Ajouter véhicule</button>
</form>

<ul>
    <?php while ($v = $vehicles->fetch_assoc()) { ?>
        <li><?= $v['marque']." ".$v['modele']." (".$v['immatriculation'].") - ".$v['places_disponibles']." places - ".$v['energie'] ?></li>
    <?php } ?>
</ul>

<hr>
<h3>🔧 Mes préférences chauffeur</h3>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= generateCsrf(); ?>">
    <label>Type : <input type="text" name="type_pref" placeholder="ex: Fumeur, Animaux, Musique" required></label>
    <label>Valeur : <input type="text" name="valeur" placeholder="Oui / Non" required></label>
    <button type="submit" name="add_pref">Ajouter préférence</button>
</form>

<ul>
    <?php while ($p = $prefs->fetch_assoc()) { ?>
        <li><?= $p['type_preference']." : ".$p['valeur'] ?></li>
    <?php } ?>
</ul>

<?php include("footer.php"); ?>
