<?php
require_once '../php/config.php';

if (isset($_SESSION['user_id'])) {
    $params = http_build_query([
        'nom' => $_SESSION['nom'],
        'prenom' => $_SESSION['prenom'],
        'role' => $_SESSION['role']
    ]);

    header("Location: " . BASE_URL . "pages/dashboard.php?" . $params);
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - EduShare</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>

    <!-- Animated background bubbles -->
    <div class="bg-bubbles">
        <div class="bubble bubble-1"></div>
        <div class="bubble bubble-2"></div>
        <div class="bubble bubble-3"></div>
    </div>

    <div class="auth-wrapper">
        <div class="auth-container">
            
            <!-- Left Banner Side -->
            <div class="auth-banner">
                <div class="banner-logo">
                    <h1>📚 EduShare</h1>
                </div>
                <div class="banner-content">
                    <h2>Bienvenue à nouveau sur votre plateforme de partage</h2>
                    <p>Connectez-vous pour accéder à votre tableau de bord, gérer vos ressources pédagogiques et interagir avec la communauté.</p>
                </div>
                <div class="banner-footer">
                    <p>© 2026 EduShare – Propulsé par l'apprentissage</p>
                </div>
            </div>

            <!-- Right Form Side -->
            <div class="auth-form-side">
                <div class="auth-box">
                    <div class="auth-logo">
                        <h2>Connexion</h2>
                        <p>Veuillez entrer vos coordonnées pour continuer</p>
                    </div>

                    <?php
                    if (isset($_GET['error'])) {
                        if ($_GET['error'] == 'invalid_credentials') {
                            echo '<div class="alert alert-error">⚠️ Email ou mot de passe incorrect.</div>';
                        } elseif ($_GET['error'] == 'empty_fields') {
                            echo '<div class="alert alert-error">⚠️ Veuillez remplir tous les champs.</div>';
                        }
                    }
                    if (isset($_GET['success'])) {
                        if ($_GET['success'] == 'registered') {
                            echo '<div class="alert alert-success">🎉 Compte créé avec succès ! Connectez-vous.</div>';
                        }
                    }
                    ?>

                    <form action="../php/login.php" method="POST">
                        
                        <div class="form-group">
                            <label for="email">Adresse Email</label>
                            <div class="input-wrapper">
                                <input type="email" id="email" name="email" placeholder="nom@exemple.com" required autocomplete="email">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">Mot de passe</label>
                            <div class="input-wrapper">
                                <input type="password" id="password" name="password" placeholder="Votre mot de passe" required autocomplete="current-password">
                                <button type="button" class="password-toggle" id="togglePassword">👁️</button>
                            </div>
                        </div>

                        <button type="submit" class="btn-auth">Se connecter</button>

                        <p class="auth-link">
                            Pas encore de compte ? 
                            <a href="register.php">S'inscrire</a>
                        </p>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Show/Hide Password
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '🙈';
        });
    </script>
</body>
</html>
