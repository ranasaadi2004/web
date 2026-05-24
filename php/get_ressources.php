<?php
require_once 'config.php';

header('Content-Type: application/json');

$search = $_GET['search'] ?? '';
$matiere = $_GET['matiere'] ?? '';
$niveau = $_GET['niveau'] ?? '';
$type = $_GET['type'] ?? '';
$mes = $_GET['mes'] ?? '';
$favoris = $_GET['favoris'] ?? '';

// Construire la requête SQL
$sql = "SELECT r.*, u.nom as auteur_nom, u.prenom as auteur_prenom 
        FROM ressources r 
        JOIN users u ON r.id_enseignant = u.id 
        WHERE 1=1";

$params = [];
$types = '';

// Filtre "mes ressources" (uniquement pour enseignants connectés)
if ($mes === 'true' && isset($_SESSION['user_id']) && $_SESSION['role'] === 'enseignant') {
    $sql .= " AND r.id_enseignant = ?";
    $params[] = $_SESSION['user_id'];
    $types .= 'i';
}

if (isset($_SESSION['user_id'])) {
    $sql .= " AND (r.visibilite IN ('public', 'inscrit') OR r.id_enseignant = ?)";
    $params[] = $_SESSION['user_id'];
    $types .= 'i';
} else {
    $sql .= " AND r.visibilite = 'public'";
}

// Filtre recherche
if (!empty($search)) {
    $sql .= " AND (r.titre LIKE ? OR r.description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'ss';
}

// Filtre matière
if (!empty($matiere)) {
    $sql .= " AND r.id_matiere = ?";
    $params[] = $matiere;
    $types .= 's';
}

// Filtre niveau
if (!empty($niveau)) {
    $sql .= " AND r.id_niveau = ?";
    $params[] = $niveau;
    $types .= 's';
}

// Filtre type
if (!empty($type)) {
    $type_map = [
        'pdf' => 'PDF',
        'video' => 'vidéo',
        'audio' => 'audio',
        'lien' => 'lien',
    ];
    $type = $type_map[$type] ?? $type;
    $sql .= " AND r.type = ?";
    $params[] = $type;
    $types .= 's';
}

$sql .= " ORDER BY r.created_at DESC";

// Exécuter la requête avec prepared statement
if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql);
}

$ressources = [];
while ($row = mysqli_fetch_assoc($result)) {
    $ressources[] = [
        'id' => $row['id'],
        'titre' => $row['titre'],
        'description' => $row['description'],
        'type' => $row['type'],
        'fichier' => $row['URL_fichier'],
        'URL_fichier' => $row['URL_fichier'],
        'version' => $row['version'],
        'matiere' => $row['id_matiere'],
        'niveau' => $row['id_niveau'],
        'id_matiere' => $row['id_matiere'],
        'id_niveau' => $row['id_niveau'],
        'id_enseignant' => $row['id_enseignant'],
        'auteur' => $row['auteur_prenom'] . ' ' . $row['auteur_nom'],
        'visibilite' => $row['visibilite'],
        'date' => date('d/m/Y', strtotime($row['created_at']))
    ];
}

echo json_encode($ressources);
?>
