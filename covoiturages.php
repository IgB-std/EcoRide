<?php
include("header.php");
include("config/db.php");

$depart = $_GET['depart'] ?? '';
$arrivee = $_GET['arrivee'] ?? '';
$date = $_GET['date'] ?? '';

//recuperation filtres
$filtre_eco   = isset($_GET['eco']) ? true : false;
$filtre_prix  = $_GET['prix_max'] ?? null;
$filtre_duree = $_GET['duree_max'] ?? null;
$filtre_note  = $_GET['note_min'] ?? null;

echo "<h1>Résultats de recherche</h1>";

if ($depart && $arrivee && $date) {

    //formulaire filtres
    echo "
    <section>
    <form method='get'>
        
      <input type='hidden' name='depart' value='$depart'>
      <input type='hidden' name='arrivee' value='$arrivee'>
      <input type='hidden' name='date' value='$date'>
      <input type='hidden' name='csrf_token' value='<?= generateCsrf(); ?>'>
        
      <label><input type='checkbox' name='eco' ".($filtre_eco?'checked':'')."><br><center>Écologique uniquement</center><br></label>
      <label>Prix max : <input type='number' name='prix_max' value='".($filtre_prix?$filtre_prix:'')."'></label>
      <label>Durée max (minutes) : <input type='number' min='0' name='duree_max' value='".($filtre_duree?$filtre_duree:'')."'></label>
      <label>Note chauffeur minimum : <input type='number' min='1' max='5' name='note_min' value='".($filtre_note?$filtre_note:'')."'></label>
      <button type='submit'>Filtrer</button>
    </form>
    </section>
    <hr>
    ";

    //requete sql proincipale
    $sql = "SELECT r.*, u.pseudo, v.energie,
                   TIMESTAMPDIFF(MINUTE, r.heure_depart, r.heure_arrivee) as duree,
                   (SELECT AVG(note) FROM reviews rw WHERE rw.chauffeur_id = u.id AND rw.valide=1) AS note_moyenne
            FROM rides r
            JOIN users u ON r.chauffeur_id = u.id
            JOIN vehicles v ON r.vehicle_id = v.id
            WHERE r.ville_depart = ? 
              AND r.ville_arrivee = ?
              AND r.date_depart = ?
              AND r.places_restantes > 0";

    // Construction dynamique des filtres
    $params = [$depart, $arrivee, $date];
    $types = "sss";

    if ($filtre_eco) {
        $sql .= " AND v.energie = 'electrique'";
    }

    if ($filtre_prix) {
        $sql .= " AND r.prix <= ?";
        $params[] = $filtre_prix;
        $types .= "i";
    }

    if ($filtre_duree) {
        $sql .= " HAVING duree <= ?";
        $params[] = $filtre_duree;
        $types .= "i";
    }

    if ($filtre_note) {
        if (strpos($sql, "HAVING") === false) {
            $sql .= " HAVING note_moyenne >= ?";
        } else {
            $sql .= " AND note_moyenne >= ?";
        }
        $params[] = $filtre_note;
        $types .= "i";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<div class='trajets'>";
        while ($row = $result->fetch_assoc()) {
            echo "<div class='trajet-card'>";
            echo "<h3>🚗 Trajet : {$row['ville_depart']} → {$row['ville_arrivee']}</h3>";
            echo "<p><b>Chauffeur :</b> {$row['pseudo']}</p>";
            echo "<p><b>Note chauffeur :</b> ".($row['note_moyenne'] ? round($row['note_moyenne'],1)."/5" : "Pas encore noté")."</p>";
            echo "<p><b>Date :</b> {$row['date_depart']} | <b>Départ :</b> {$row['heure_depart']} | <b>Arrivée :</b> {$row['heure_arrivee']}</p>";
            echo "<p><b>Prix :</b> {$row['prix']} crédits</p>";
            echo "<p><b>Durée :</b> {$row['duree']} min</p>";
            echo "<p><b>Places restantes :</b> {$row['places_restantes']}</p>";

            if ($row['energie'] === 'electrique') {
                echo "<p style='color:green;'><b>✅ Voyage écologique</b></p>";
            }

            echo "<a href='detail.php?id={$row['id']}'><button>Voir détails</button></a>";
            echo "</div><hr>";
        }
        echo "</div>";
    } else {
        echo "<p>Aucun covoiturage disponible avec ces critères.</p>";
    }
} else {
    echo '<section class="trajet-card">
            <h2>Rechercher un covoiturage :</h2>
            <form action="covoiturages.php" method="get">
                <input type="hidden" name="csrf_token" value="<?= generateCsrf(); ?>">
                <label>Ville de départ : <br>
                    <input type="text" name="depart" required>
                </label>
                <label>Ville d’arrivée : <br>
                    <input type="text" name="arrivee" required>
                </label>
                <label>Date : <br>
                    <input type="date" name="date" required>
                </label>
                <button type="submit">Rechercher</button>
            </form>
        </section>';
}

include("footer.php");
?>