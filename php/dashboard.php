<?php
require_once 'config.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Non connecté']);
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$action = $_GET['action'] ?? '';

if ($action === 'stats') {
    
    if ($role === 'enseignant') {
        // Statistiques enseignant
        $ressources_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM ressources WHERE user_id='$user_id'");
        $ressources_count = mysqli_fetch_assoc($ressources_query)['count'];
        
        // Simuler les vues et commentaires (à implémenter avec des tables appropriées)
        $vues_count = 0;
        $commentaires_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM commentaires c JOIN ressources r ON c.ressource_id = r.id WHERE r.user_id='$user_id'");
        $commentaires_count = mysqli_fetch_assoc($commentaires_query)['count'];
        
        echo json_encode([
            'ressources' => $ressources_count,
            'vues' => $vues_count,
            'commentaires' => $commentaires_count
        ]);
        
    } else {
        // Statistiques étudiant
        // À implémenter avec des tables de favoris et historique
        echo json_encode([
            'consulted' => 0,
            'favoris' => 0,
            'quiz' => 0
        ]);
    }
    
} elseif ($action === 'recent_ressources') {
    
    if ($role === 'enseignant') {
        $query = "SELECT id, titre, matiere, type, created_at FROM ressources WHERE user_id='$user_id' ORDER BY created_at DESC LIMIT 5";
        $result = mysqli_query($conn, $query);
        
        $ressources = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $ressources[] = [
                'id' => $row['id'],
                'titre' => $row['titre'],
                'matiere' => $row['matiere'],
                'type' => $row['type'],
                'date' => date('Y-m-d', strtotime($row['created_at']))
            ];
        }
        
        echo json_encode($ressources);
    }
    
} elseif ($action === 'recommended_ressources') {
    
    if ($role === 'etudiant') {
        // Ressources récentes de tous les enseignants
        $query = "SELECT r.id, r.titre, r.matiere, r.type, u.nom as auteur FROM ressources r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC LIMIT 5";
        $result = mysqli_query($conn, $query);
        
        $ressources = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $ressources[] = [
                'id' => $row['id'],
                'titre' => $row['titre'],
                'matiere' => $row['matiere'],
                'type' => $row['type'],
                'auteur' => 'Prof. ' . $row['auteur']
            ];
        }
        
        echo json_encode($ressources);
    }
    
} else {
    echo json_encode(['error' => 'Action non reconnue']);
}
?>
