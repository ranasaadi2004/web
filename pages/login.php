<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - EduRessources</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>

    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-logo">
                <h2>📚 EduRessources</h2>
                <p>Connectez-vous à votre compte</p>
            </div>

            <?php
            if (isset($_GET['error'])) {
                if ($_GET['error'] == 'invalid_credentials') {
                    echo '<div class="alert alert-error">Email ou mot de passe incorrect</div>';
                } elseif ($_GET['error'] == 'empty_fields') {
                    echo '<div class="alert alert-error">Veuillez remplir tous les champs</div>';
                }
            }
            if (isset($_GET['success'])) {
                if ($_GET['success'] == 'registered') {
                    echo '<div class="alert alert-success">Compte créé avec succès ! Connectez-vous</div>';
                }
            }
            ?>

            <form action="../php/login.php" method="POST">

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="votre@email.com" required>
                </div>

                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-auth">Se connecter</button>

                <p class="auth-link">
                    Pas encore de compte ? 
                    <a href="register.html">S'inscrire</a>
                </p>

            </form>
        </div>
    </div>

</body>
</html>
