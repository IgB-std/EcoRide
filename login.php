<?php
session_start();
include("config/db.php");
include("header.php");

//traitement connexion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ? AND suspendu=0 LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 1) {
        $user = $res->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            //connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['pseudo'] = $user['pseudo'];

            echo "<p>✅ Bienvenue {$user['pseudo']} !</p>";

            if ($user['role'] == "admin") {
                echo "<a href='admin.php'><button>Accéder au panneau Admin</button></a>";
            } elseif ($user['role'] == "employe") {
                echo "<a href='employe.php'><button>Accéder à l’espace Employé</button></a>";
            } else {
                echo "<a href='dashboard.php'><button>Accéder à mon espace</button></a>";
            }
        } else {
            echo "<p>❌ Mot de passe invalide.</p>";
        }
    } else {
        echo "<p>❌ Aucun compte trouvé avec cet e-mail ou le compte est suspendu.</p>";
    }
}
?>

<h2>Connexion 🔑</h2>
<form method="post" class="form-container">
    <input type="hidden" name="csrf_token" value="<?= generateCsrf(); ?>">
    <label>Email :</label>
    <input type="email" name="email" required><br>

    <label>Mot de passe :</label>
    <input type="password" name="password" required><br>

    <button type="submit">Se connecter</button>
</form>

<p><center>Pas encore inscrit ? <a href="register.php">Créer un compte</a></center></p>

<?php include("footer.php"); ?>
