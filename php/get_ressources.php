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
        JOIN users u ON r.user_id = u.id 
        WHERE 1=1";

$params = [];
$types = '';

// Filtre "mes ressources" (uniquement pour enseignants connectés)
if ($mes === 'true' && isset($_SESSION['user_id']) && $_SESSION['role'] === 'enseignant') {
    $sql .= " AND r.user_id = ?";
    $params[] = $_SESSION['user_id'];
    $types .= 'i';
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
    $sql .= " AND r.matiere = ?";
    $params[] = $matiere;
    $types .= 's';
}

// Filtre niveau
if (!empty($niveau)) {
    $sql .= " AND r.niveau = ?";
    $params[] = $niveau;
    $types .= 's';
}

// Filtre type
if (!empty($type)) {
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
        'fichier' => $row['fichier'],
        'matiere' => $row['matiere'],
        'niveau' => $row['niveau'],
        'auteur' => $row['auteur_prenom'] . ' ' . $row['auteur_nom'],
        'date' => date('d/m/Y', strtotime($row['created_at']))
    ];
}

echo json_encode($ressources);
?>
