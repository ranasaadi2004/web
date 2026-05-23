<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nom     = trim($_POST['nom']);
    $prenom  = trim($_POST['prenom']);
    $email   = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role    = $_POST['role'];

   
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    
    if (mysqli_num_rows($check) > 0) {
        header("Location: ../pages/register.html?error=email_exists");
        exit();
    }

  
    $sql = "INSERT INTO users (nom, prenom, email, password, role) 
            VALUES ('$nom', '$prenom', '$email', '$password', '$role')";

    if (mysqli_query($conn, $sql)) {
        header("Location: ../pages/login.html?success=registered");
        exit();
    } else {
        header("Location: ../pages/register.html?error=failed");
        exit();
    }
}
?>