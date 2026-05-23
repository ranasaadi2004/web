<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validation des champs
    if (empty($email) || empty($password)) {
        header("Location: ../pages/login.html?error=empty_fields");
        exit();
    }

    // Recherche de l'utilisateur
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Vérification du mot de passe
        if (password_verify($password, $user['password'])) {
            // Création de la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirection selon le rôle
            if ($user['role'] === 'enseignant') {
                header("Location: ../pages/dashboard.html");
            } else {
                header("Location: ../pages/dashboard.html");
            }
            exit();
        } else {
            header("Location: ../pages/login.html?error=invalid_credentials");
            exit();
        }
    } else {
        header("Location: ../pages/login.html?error=invalid_credentials");
        exit();
    }
}
?>
