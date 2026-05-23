<?php
require_once '../php/config.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ressources - EduRessources</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/ressources.css">
</head>
<body>

    <header>
        <div class="logo">📚 EduRessources</div>
        <nav>
            <a href="../index.html">Accueil</a>
            <a href="ressources.php" class="active">Ressources</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Tableau de bord</a>
                <a href="../php/logout.php">Déconnexion</a>
            <?php else: ?>
                <a href="login.php">Connexion</a>
                <a href="register.php">Inscription</a>
            <?php endif; ?>
        </nav>
    </header>

    <section class="hero-ressources">
        <h1>Bibliothèque de ressources pédagogiques</h1>
        <p>Explorez les ressources partagées par nos enseignants</p>
    </section>

    <section class="search-section">
        <input type="text" id="search_input" placeholder="Rechercher une ressource...">
        <button onclick="searchRessources()">🔍 Rechercher</button>
    </section>

    <section class="filters-section">
        <div class="filter-group">
            <label>Matière :</label>
            <select id="filter_matiere" onchange="filterRessources()">
                <option value="">Toutes</option>
                <option value="mathematiques">Mathématiques</option>
                <option value="francais">Français</option>
                <option value="anglais">Anglais</option>
                <option value="histoire">Histoire</option>
                <option value="geographie">Géographie</option>
                <option value="physique">Physique</option>
                <option value="chimie">Chimie</option>
                <option value="biologie">Biologie</option>
                <option value="informatique">Informatique</option>
                <option value="autre">Autre</option>
            </select>
        </div>

        <div class="filter-group">
            <label>Niveau :</label>
            <select id="filter_niveau" onchange="filterRessources()">
                <option value="">Tous</option>
                <option value="primaire">Primaire</option>
                <option value="college">Collège</option>
                <option value="lycee">Lycée</option>
                <option value="superieur">Supérieur</option>
            </select>
        </div>

        <div class="filter-group">
            <label>Type :</label>
            <select id="filter_type" onchange="filterRessources()">
                <option value="">Tous</option>
                <option value="pdf">PDF</option>
                <option value="video">Vidéo</option>
                <option value="audio">Audio</option>
                <option value="lien">Lien</option>
            </select>
        </div>

        <button class="btn-reset" onclick="resetFilters()">Réinitialiser</button>
    </section>

    <section class="resources-section">
        <h2>Toutes les ressources</h2>
        <div class="cards-grid" id="ressources_grid">
            <p>Chargement des ressources...</p>
        </div>
    </section>

    <footer>
        <p>© 2025 EduRessources – Tous droits réservés</p>
    </footer>

    <script src="../js/ressources.js"></script>
</body>
</html>
