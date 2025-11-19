<?php
session_start();
include("config/db.php");
include("header.php");

//traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pseudo = trim($_POST['pseudo']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    //vérifs basiques
    if (empty($pseudo) || empty($email) || empty($password)) {
        $errors[] = "⚠️ Tous les champs doivent être remplis.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "⚠️ Adresse email invalide.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "⚠️ Les mots de passe ne correspondent pas.";
    }

    if (strlen($password) < 6) {
        $errors[] = "⚠️ Le mot de passe doit contenir au moins 6 caractères.";
    }

    // Vérifier si email déjà existant
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $check = $stmt->get_result();
    if ($check->num_rows > 0) {
        $errors[] = "⚠️ Cet email est déjà utilisé.";
    }

    //si pas d'erreurs =>> insertion
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (pseudo, email, password, credits, role) VALUES (?, ?, ?, 20, 'utilisateur')");
        $stmt->bind_param("sss", $pseudo, $email, $hashedPassword);

        if ($stmt->execute()) {
            echo "<p>✅ Compte créé avec succès ! Vous pouvez maintenant vous connecter.</p>";
            echo "<a href='login.php'><button>Se connecter</button></a>";
        } else {
            echo "<p>❌ Erreur lors de la création du compte.</p>";
        }
    } else {
        foreach ($errors as $err) {
            echo "<p style='color:red;'>$err</p>";
        }
    }
}
?>

<h2>Créer un compte 📝</h2>
<form method="post" class="form-container">
    <input type="hidden" name="csrf_token" value="<?= generateCsrf(); ?>">
    <label>Pseudo :</label>
    <input type="text" name="pseudo" required><br>

    <label>Email :</label>
    <input type="email" name="email" required><br>

    <label>Mot de passe :</label>
    <input type="password" name="password" required><br>

    <label>Confirmer mot de passe :</label>
    <input type="password" name="confirm_password" required><br>

    <button type="submit">Créer mon compte</button>
</form>

<?php include("footer.php"); ?>
