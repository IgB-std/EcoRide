<?php include("header.php"); ?>

    <main>
        <h1>Bienvenue sur EcoRide</h1>

        <section>
            <p>Notre entreprise met en relation passagers et conducteurs pour des trajets écologiques et économiques.</p>
        </section>

        <section class="trajet-card">
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
        </section>
    </main>
<?php include("footer.php"); ?>