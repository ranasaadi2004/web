<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validation des champs
    if (empty($email) || empty($password)) {
        header("Location: " . BASE_URL . "pages/login.php?error=empty_fields");
        exit();
    }

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

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

            $params = http_build_query([
                'nom' => $user['nom'],
                'prenom' => $user['prenom'],
                'role' => $user['role']
            ]);

            header("Location: " . BASE_URL . "pages/dashboard.php?" . $params);
            exit();
        } else {
            header("Location: " . BASE_URL . "pages/login.php?error=invalid_credentials");
            exit();
        }
    } else {
        header("Location: " . BASE_URL . "pages/login.php?error=invalid_credentials");
        exit();
    }
}
?>
