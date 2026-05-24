<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nom     = trim($_POST['nom']);
    $prenom  = trim($_POST['prenom']);
    $email   = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role    = $_POST['role'];

    if (!in_array($role, ['enseignant', 'etudiant'], true)) {
        $role = 'etudiant';
    }

   
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    
    if (mysqli_num_rows($check) > 0) {
        header("Location: " . BASE_URL . "pages/register.php?error=email_exists");
        exit();
    }

  
    $sql = "INSERT INTO users (nom, prenom, email, password, role) 
            VALUES ('$nom', '$prenom', '$email', '$password', '$role')";

    if (mysqli_query($conn, $sql)) {
        header("Location: " . BASE_URL . "pages/login.php?success=registered");
        exit();
    } else {
        header("Location: " . BASE_URL . "pages/register.php?error=failed");
        exit();
    }
}
?>