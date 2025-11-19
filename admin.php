<?php
session_start();
include("config/db.php");
include("header.php");

//vérifier si connecté et role admin
if (!isset($_SESSION['user_id'])) {
    echo "<p>❌ Accès réservé. Vous devez être connecté.</p>";
    include("footer.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT role FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$role = $stmt->get_result()->fetch_assoc();

if (!$role || $role['role'] != "admin") {
    echo "<p>❌ Accès refusé. Seuls les administrateurs peuvent accéder.</p>";
    include("footer.php");
    exit;
}

echo "<h2>Espace Administrateur</h2>";

//creation compte employe
if (isset($_POST['create_employee'])) {
    $pseudo = $_POST['pseudo'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sqlI = "INSERT INTO users (pseudo, email, password, role, credits) VALUES (?, ?, ?, 'employe', 0)";
    $stmtI = $conn->prepare($sqlI);
    $stmtI->bind_param("sss", $pseudo, $email, $password);

    if ($stmtI->execute()) {
        echo "<p>✅ Employé créé avec succès.</p>";
    } else {
        echo "<p>❌ Erreur lors de la création de l’employé.</p>";
    }
}

//suspendre compte
if (isset($_GET['suspend'])) {
    $id = intval($_GET['suspend']);
    $sqlS = "UPDATE users SET suspendu=1 WHERE id=?";
    $stmtS = $conn->prepare($sqlS);
    $stmtS->bind_param("i", $id);
    $stmtS->execute();
    echo "<p>❌ Compte #$id suspendu.</p>";
}

//reactiver compte
if (isset($_GET['reactiver'])) {
    $id = intval($_GET['reactiver']);
    $sqlS = "UPDATE users SET suspendu=0 WHERE id=?";
    $stmtS = $conn->prepare($sqlS);
    $stmtS->bind_param("i", $id);
    $stmtS->execute();
    echo "<p>✅ Compte #$id réactivé.</p>";
}
?>

    <!-- formulaire creation compte employe -->
    <h3>Créer un compte employé</h3>
    <form method="post" class="form-container">
        <input type="hidden" name="csrf_token" value="<?= generateCsrf(); ?>">
        <label>Pseudo :</label><input type="text" name="pseudo" required><br>
        <label>Email :</label><input type="email" name="email" required><br>
        <label>Mot de passe :</label><input type="password" name="password" required><br>
        <button type="submit" name="create_employee">Créer employé</button>
    </form>

    <hr>
    <h3>Statistiques</h3>
    <h4>Nombre de covoiturages par jour</h4>
<?php
$sql = "SELECT date_depart, COUNT(*) as nb_trajets FROM rides GROUP BY date_depart ORDER BY date_depart";
$res = $conn->query($sql);
if ($res->num_rows > 0) {
    echo "<ul>";
    while ($r = $res->fetch_assoc()) {
        echo "<li>{$r['date_depart']} : {$r['nb_trajets']} trajets</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Aucun trajet enregistré.</p>";
}
?>
    <h4>Crédits gagnés par jour</h4>
<?php
$sql = "SELECT date_depart, COUNT(*)*2 as credits_gagnes FROM rides GROUP BY date_depart ORDER BY date_depart";
$res = $conn->query($sql);
if ($res->num_rows > 0) {
    echo "<ul>";
    while ($r = $res->fetch_assoc()) {
        echo "<li>{$r['date_depart']} : +{$r['credits_gagnes']} crédits</li>";
    }
    echo "</ul>";
}
?>
    <h4>Total des crédits gagnés</h4>
<?php
$sql = "SELECT COUNT(*)*2 as total_credits FROM rides";
$res = $conn->query($sql);
$row = $res->fetch_assoc();
echo "<p>Total : {$row['total_credits']} crédits cumulés.</p>";
?>

    <hr>
    <h3>Gestion des comptes</h3>
<?php
$sql = "SELECT id, pseudo, email, role, suspendu FROM users";
$res = $conn->query($sql);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Pseudo</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Action</th></tr>";
while ($u = $res->fetch_assoc()) {
    $status = $u['suspendu'] ? "❌ Suspendu" : "✅ Actif";
    echo "<tr>
            <td>{$u['id']}</td>
            <td>{$u['pseudo']}</td>
            <td>{$u['email']}</td>
            <td>{$u['role']}</td>
            <td>$status</td>
            <td>";
    if ($u['suspendu']) {
        echo "<a href='admin.php?reactiver={$u['id']}'>✅ Réactiver</a>";
    } else {
        echo "<a href='admin.php?suspend={$u['id']}'>❌ Suspendre</a>";
    }
    echo "</td></tr>";
}
echo "</table>";
requireAdmin();
?>

<?php include("footer.php"); ?>