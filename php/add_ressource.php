<?php
require_once 'config.php';

// Vérifier si l'utilisateur est connecté et est un enseignant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enseignant') {
    header("Location: ../pages/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $type = $_POST['type'];
    $matiere = $_POST['matiere'];
    $niveau = $_POST['niveau'];
    
    // Validation
    if (empty($titre) || empty($description) || empty($type) || empty($matiere) || empty($niveau)) {
        header("Location: ../pages/dashboard.php?error=empty_fields");
        exit();
    }
    
    $fichier = '';
    
    // Gestion de l'upload de fichier
    if ($type !== 'lien' && isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        
        // Créer le dossier uploads s'il n'existe pas
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['fichier']['name']);
        $file_path = $upload_dir . $file_name;
        
        // Vérifier le type de fichier
        $allowed_types = ['application/pdf', 'video/mp4', 'video/avi', 'audio/mpeg', 'audio/mp3'];
        $file_type = $_FILES['fichier']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            header("Location: ../pages/dashboard.php?error=invalid_file_type");
            exit();
        }
        
        // Vérifier la taille du fichier (max 50MB)
        if ($_FILES['fichier']['size'] > 50 * 1024 * 1024) {
            header("Location: ../pages/dashboard.php?error=file_too_large");
            exit();
        }
        
        if (move_uploaded_file($_FILES['fichier']['tmp_name'], $file_path)) {
            $fichier = 'uploads/' . $file_name;
        } else {
            header("Location: ../pages/dashboard.php?error=upload_failed");
            exit();
        }
    } elseif ($type === 'lien') {
        $fichier = trim($_POST['lien_url']);
    }
    
    // Insertion dans la base de données
    $sql = "INSERT INTO ressources (titre, description, type, fichier, matiere, niveau, user_id, created_at) 
            VALUES ('$titre', '$description', '$type', '$fichier', '$matiere', '$niveau', '$user_id', NOW())";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: ../pages/dashboard.php?success=ressource_added");
        exit();
    } else {
        header("Location: ../pages/dashboard.php?error=db_error");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une ressource - EduRessources</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>

    <header>
        <div class="logo">📚 EduRessources</div>
        <nav>
            <a href="../index.html">Accueil</a>
            <a href="../pages/ressources.php">Ressources</a>
            <a href="../pages/dashboard.php">Tableau de bord</a>
            <a href="../php/logout.php">Déconnexion</a>
        </nav>
    </header>

    <div class="auth-container">
        <div class="auth-box" style="max-width: 600px;">
            <div class="auth-logo">
                <h2>➕ Ajouter une ressource</h2>
                <p>Partagez vos ressources pédagogiques</p>
            </div>

            <?php
            if (isset($_GET['error'])) {
                $error_messages = [
                    'empty_fields' => 'Veuillez remplir tous les champs',
                    'invalid_file_type' => 'Type de fichier non autorisé (PDF, MP4, MP3 uniquement)',
                    'file_too_large' => 'Le fichier dépasse 50MB',
                    'upload_failed' => 'Erreur lors de l\'upload du fichier',
                    'db_error' => 'Erreur lors de l\'enregistrement'
                ];
                $error = $_GET['error'];
                if (isset($error_messages[$error])) {
                    echo '<div class="alert alert-error">' . $error_messages[$error] . '</div>';
                }
            }
            ?>

            <form action="" method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Titre</label>
                    <input type="text" name="titre" placeholder="Titre de la ressource" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Description de la ressource" rows="4" required style="width: 100%; padding: 11px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; outline: none; resize: vertical;"></textarea>
                </div>

                <div class="form-group">
                    <label>Type</label>
                    <select name="type" id="type_select" onchange="toggleFileInput()" required>
                        <option value="">Sélectionner...</option>
                        <option value="pdf">PDF</option>
                        <option value="video">Vidéo</option>
                        <option value="audio">Audio</option>
                        <option value="lien">Lien</option>
                    </select>
                </div>

                <div class="form-group" id="file_input_group">
                    <label>Fichier</label>
                    <input type="file" name="fichier" id="file_input" accept=".pdf,.mp4,.avi,.mp3">
                </div>

                <div class="form-group" id="lien_input_group" style="display: none;">
                    <label>URL du lien</label>
                    <input type="url" name="lien_url" id="lien_url" placeholder="https://exemple.com">
                </div>

                <div class="form-group">
                    <label>Matière</label>
                    <select name="matiere" required>
                        <option value="">Sélectionner...</option>
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

                <div class="form-group">
                    <label>Niveau</label>
                    <select name="niveau" required>
                        <option value="">Sélectionner...</option>
                        <option value="primaire">Primaire</option>
                        <option value="college">Collège</option>
                        <option value="lycee">Lycée</option>
                        <option value="superieur">Supérieur</option>
                    </select>
                </div>

                <button type="submit" class="btn-auth">Ajouter la ressource</button>

                <p class="auth-link">
                    <a href="../pages/dashboard.php">Annuler</a>
                </p>

            </form>
        </div>
    </div>

    <script>
        function toggleFileInput() {
            const type = document.getElementById('type_select').value;
            const fileGroup = document.getElementById('file_input_group');
            const lienGroup = document.getElementById('lien_input_group');
            const fileInput = document.getElementById('file_input');
            
            if (type === 'lien') {
                fileGroup.style.display = 'none';
                lienGroup.style.display = 'block';
                fileInput.required = false;
                document.getElementById('lien_url').required = true;
            } else {
                fileGroup.style.display = 'block';
                lienGroup.style.display = 'none';
                fileInput.required = true;
                document.getElementById('lien_url').required = false;
                
                // Mettre à jour l'accept selon le type
                if (type === 'pdf') {
                    fileInput.accept = '.pdf';
                } else if (type === 'video') {
                    fileInput.accept = '.mp4,.avi';
                } else if (type === 'audio') {
                    fileInput.accept = '.mp3';
                }
            }
        }
    </script>

</body>
</html>
