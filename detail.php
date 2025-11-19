<?php
include("header.php");
include("config/db.php");

//récupération 'id' du trajet
$ride_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($ride_id > 0) {
    //récupération infos trajet + chauffeur + véhicule
    $sql = "SELECT r.*, u.pseudo, u.id as chauffeur_id, v.marque, v.modele, v.couleur, v.energie,
                   (SELECT AVG(note) FROM reviews rw WHERE rw.chauffeur_id = u.id AND rw.valide=1) AS note_moyenne
            FROM rides r
            JOIN users u ON r.chauffeur_id = u.id
            JOIN vehicles v ON r.vehicle_id = v.id
            WHERE r.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ride_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $ride = $result->fetch_assoc();

        echo "<h2>Détails du covoiturage 🚗</h2>";
        echo "<div class='trajet-card'>";
        echo "<h3>{$ride['ville_depart']} → {$ride['ville_arrivee']}</h3>";
        echo "<p><b>Chauffeur :</b> {$ride['pseudo']}</p>";
        echo "<p><b>Note chauffeur :</b> ".($ride['note_moyenne'] ? round($ride['note_moyenne'],1)."/5" : "Pas encore noté")."</p>";
        echo "<p><b>Date :</b> {$ride['date_depart']}</p>";
        echo "<p><b>Départ :</b> {$ride['heure_depart']} | <b>Arrivée :</b> {$ride['heure_arrivee']}</p>";
        echo "<p><b>Prix :</b> {$ride['prix']} crédits</p>";
        echo "<p><b>Places restantes :</b> {$ride['places_restantes']}</p>";

        if ($ride['energie'] === 'electrique') {
            echo "<p style='color:green;'><b>✅ Voyage écologique</b></p>";
        } else {
            echo "<p style='color:gray;'>Non écologique</p>";
        }

        echo "<hr>";
        echo "<h3>🚘 Véhicule</h3>";
        echo "<p>Marque : {$ride['marque']}</p>";
        echo "<p>Modèle : {$ride['modele']}</p>";
        echo "<p>Couleur : {$ride['couleur']}</p>";
        echo "<p>Énergie : {$ride['energie']}</p>";

        echo "<hr>";
        echo "<h3>⚙️ Préférences du chauffeur</h3>";

        //préférences chauffeur
        $sqlPref = "SELECT * FROM preferences WHERE user_id = ?";
        $stmtPref = $conn->prepare($sqlPref);
        $stmtPref->bind_param("i", $ride['chauffeur_id']);
        $stmtPref->execute();
        $prefs = $stmtPref->get_result();

        if ($prefs->num_rows > 0) {
            echo "<ul>";
            while ($row = $prefs->fetch_assoc()) {
                echo "<li>{$row['type_preference']} : {$row['valeur']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Aucune préférence renseignée.</p>";
        }

        echo "<hr>";
        echo "<h3>💬 Avis sur le chauffeur</h3>";

        //avis des passagers validés
        $sqlAvis = "SELECT rw.*, u.pseudo AS auteur 
                    FROM reviews rw
                    JOIN users u ON rw.passager_id = u.id
                    WHERE rw.chauffeur_id = ? AND rw.valide = 1";
        $stmtAvis = $conn->prepare($sqlAvis);
        $stmtAvis->bind_param("i", $ride['chauffeur_id']);
        $stmtAvis->execute();
        $avis = $stmtAvis->get_result();

        if ($avis->num_rows > 0) {
            echo "<ul>";
            while ($row = $avis->fetch_assoc()) {
                echo "<li><b>{$row['auteur']}</b> : ⭐ {$row['note']}/5 — {$row['commentaire']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Aucun avis pour le moment.</p>";
        }

        echo "<hr>";
        echo "<a href='participer.php?id={$ride['id']}'><button>Participer à ce covoiturage</button></a>";

        echo "</div>";
    } else {
        echo "<p>Aucun détail trouvé pour ce covoiturage.</p>";
    }
} else {
    echo "<p>ID covoiturage invalide</p>";
}

include("footer.php");
?>
