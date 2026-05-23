<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - EduRessources</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>

    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-logo">
                <h2>📚 EduRessources</h2>
                <p>Créez votre compte</p>
            </div>

            <?php
            if (isset($_GET['error'])) {
                if ($_GET['error'] == 'email_exists') {
                    echo '<div class="alert alert-error">Cet email est déjà utilisé</div>';
                } elseif ($_GET['error'] == 'failed') {
                    echo '<div class="alert alert-error">Erreur lors de l\'inscription</div>';
                }
            }
            ?>

            <div>
            </div>

            <form action="../php/register.php" method="POST">

                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" placeholder="Votre nom" required>
                </div>

                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" placeholder="Votre prénom" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="votre@email.com" required>
                </div>

                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="form-group">
                    <label>Vous êtes</label>
                    <select name="role">
                        <option value="etudiant">Étudiant</option>
                        <option value="enseignant">Enseignant</option>
                    </select>
                </div>

                <button type="submit" class="btn-auth">S'inscrire</button>

                <p class="auth-link">
                    Déjà un compte ? 
                    <a href="login.php">Se connecter</a>
                </p>

            </form>
        </div>
    </div>

</body>
</html>