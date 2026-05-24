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
        $ressources_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM ressources WHERE id_enseignant='$user_id'");
        $ressources_count = mysqli_fetch_assoc($ressources_query)['count'];
        
        // Simuler les vues et commentaires (tables dédiées non créées dans le schéma actuel)
        $vues_count = 0;
        $commentaires_count = 0;
        
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
        $query = "SELECT id, titre, id_matiere, type, created_at FROM ressources WHERE id_enseignant='$user_id' ORDER BY created_at DESC LIMIT 5";
        $result = mysqli_query($conn, $query);
        
        $ressources = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $ressources[] = [
                'id' => $row['id'],
                'titre' => $row['titre'],
                'matiere' => $row['id_matiere'],
                'type' => $row['type'],
                'date' => date('Y-m-d', strtotime($row['created_at']))
            ];
        }
        
        echo json_encode($ressources);
    }
    
} elseif ($action === 'recommended_ressources') {
    
    if ($role === 'etudiant') {
        // Ressources récentes de tous les enseignants
        $query = "SELECT r.id, r.titre, r.id_matiere, r.type, u.nom as auteur FROM ressources r JOIN users u ON r.id_enseignant = u.id WHERE r.visibilite IN ('public', 'inscrit') ORDER BY r.created_at DESC LIMIT 5";
        $result = mysqli_query($conn, $query);
        
        $ressources = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $ressources[] = [
                'id' => $row['id'],
                'titre' => $row['titre'],
                'matiere' => $row['id_matiere'],
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
