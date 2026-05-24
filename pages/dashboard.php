<?php
require_once '../php/config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$nom = $_GET['nom'] ?? $_SESSION['nom'];
$prenom = $_GET['prenom'] ?? $_SESSION['prenom'];
$role = $_SESSION['role'];

if ($nom !== $_SESSION['nom'] || $prenom !== $_SESSION['prenom']) {
    $nom = $_SESSION['nom'];
    $prenom = $_SESSION['prenom'];
}

$dashboard_url = 'dashboard.php?' . http_build_query([
    'nom' => $_SESSION['nom'],
    'prenom' => $_SESSION['prenom'],
    'role' => $_SESSION['role']
]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - EduRessources</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>

    <header>
        <div class="logo">📚 EduRessources</div>
        <nav>
            <a href="<?php echo htmlspecialchars($dashboard_url); ?>" class="active">Tableau de bord</a>
            <a href="ressources.php">Ressources</a>
            <?php if ($role === 'enseignant'): ?>
                <a href="../php/add_ressource.php">Ajouter une ressource</a>
                <a href="api-tester.php">Testeur d'API</a>
            <?php endif; ?>
            <a href="../php/logout.php">Déconnexion</a>
        </nav>
    </header>

    <section class="dashboard-header">
        <div class="welcome-section">
            <h1>Bonjour, <?php echo htmlspecialchars($prenom); ?> <?php echo htmlspecialchars($nom); ?> !</h1>
            <p class="role-badge"><?php echo $role === 'enseignant' ? '👨‍🏫 Enseignant' : '👨‍🎓 Étudiant'; ?></p>
        </div>
    </section>

    <?php if ($role === 'enseignant'): ?>
    
    <!-- DASHBOARD ENSEIGNANT -->
    <section class="dashboard-content">
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📁</div>
                <div class="stat-info">
                    <h3>Mes Ressources</h3>
                    <p class="stat-number" id="ressources-count">-</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👁️</div>
                <div class="stat-info">
                    <h3>Total Vues</h3>
                    <p class="stat-number" id="vues-count">-</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💬</div>
                <div class="stat-info">
                    <h3>Commentaires</h3>
                    <p class="stat-number" id="commentaires-count">-</p>
                </div>
            </div>
        </div>

        <div class="action-section">
            <h2>Actions rapides</h2>
            <div class="action-buttons">
                <a href="../php/add_ressource.php" class="btn-action btn-primary">
                    <span>➕</span> Ajouter une ressource
                </a>
                <a href="ressources.php?mes=true" class="btn-action btn-secondary">
                    <span>📋</span> Gérer mes ressources
                </a>
                <a href="api-tester.php" class="btn-action btn-secondary">
                    <span>🧪</span> Tester les endpoints API
                </a>
            </div>
        </div>

        <div class="api-endpoints-section">
            <div class="api-endpoints-header">
                <div>
                    <h2>Endpoints API des ressources</h2>
                    <p>Ces routes permettent de déposer, consulter, versionner et supprimer les ressources pédagogiques.</p>
                </div>
                <a href="api-tester.php" class="btn-small btn-edit">Ouvrir le testeur</a>
            </div>

            <div class="endpoint-grid">
                <div class="endpoint-card">
                    <span class="method post">POST</span>
                    <code>/api/resources</code>
                    <p>Déposer une nouvelle ressource avec fichier ou lien.</p>
                </div>
                <div class="endpoint-card">
                    <span class="method get">GET</span>
                    <code>/api/resources</code>
                    <p>Récupérer toutes les ressources, avec filtres par matière et niveau.</p>
                </div>
                <div class="endpoint-card">
                    <span class="method get">GET</span>
                    <code>/api/resources/{id}</code>
                    <p>Voir le détail complet d'une ressource.</p>
                </div>
                <div class="endpoint-card">
                    <span class="method put">PUT</span>
                    <code>/api/resources/{id}/version</code>
                    <p>Mettre à jour une ressource et incrémenter sa version.</p>
                </div>
                <div class="endpoint-card">
                    <span class="method delete">DELETE</span>
                    <code>/api/resources/{id}</code>
                    <p>Supprimer une ressource. Réservé à son auteur.</p>
                </div>
            </div>
        </div>

        <div class="recent-section">
            <h2>Mes ressources récentes</h2>
            <div class="ressources-list" id="recent-ressources">
                <p>Chargement...</p>
            </div>
        </div>

    </section>

    <?php else: ?>
    
    <!-- DASHBOARD ÉTUDIANT -->
    <section class="dashboard-content">
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-info">
                    <h3>Ressources consultées</h3>
                    <p class="stat-number" id="consulted-count">-</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-info">
                    <h3>Favoris</h3>
                    <p class="stat-number" id="favoris-count">-</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-info">
                    <h3>Quiz complétés</h3>
                    <p class="stat-number" id="quiz-count">-</p>
                </div>
            </div>
        </div>

        <div class="action-section">
            <h2>Actions rapides</h2>
            <div class="action-buttons">
                <a href="ressources.php" class="btn-action btn-primary">
                    <span>🔍</span> Explorer les ressources
                </a>
                <a href="ressources.php?favoris=true" class="btn-action btn-secondary">
                    <span>⭐</span> Mes favoris
                </a>
            </div>
        </div>

        <div class="recent-section">
            <h2>Ressources recommandées</h2>
            <div class="ressources-list" id="recommended-ressources">
                <p>Chargement...</p>
            </div>
        </div>

    </section>

    <?php endif; ?>

    <footer>
        <p>© 2025 EduRessources – Tous droits réservés</p>
    </footer>

    <script src="../js/dashboard.js"></script>
</body>
</html>
