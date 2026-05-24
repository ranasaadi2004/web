<?php
/**
 * REST API for Educational Resources Management (EduShare)
 * 
 * Endpoints:
 * - POST /api/resources          : Deposit a new resource (requires 'enseignant' role)
 * - GET /api/resources           : Retrieve all resources (filters: matiere, niveau)
 * - GET /api/resources/{id}      : Retrieve details of a specific resource
 * - PUT /api/resources/{id}/version : Update resource and increment its version (author only)
 * - DELETE /api/resources/{id}   : Delete resource (author only)
 */

require_once '../php/config.php';

// Set JSON headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-HTTP-Method-Override');

// Handle OPTIONS requests (for CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

// Support PUT via POST override (highly recommended for file uploads in PHP)
if ($method === 'POST') {
    if (isset($_POST['_method']) && strtoupper($_POST['_method']) === 'PUT') {
        $method = 'PUT';
    } elseif (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) && strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) === 'PUT') {
        $method = 'PUT';
    }
}

// Parse route parameters from $_GET['id'] passed by .htaccess
$id_param = $_GET['id'] ?? '';
$id = null;
$action = null;

if (!empty($id_param)) {
    $parts = explode('/', trim($id_param, '/'));
    if (is_numeric($parts[0])) {
        $id = intval($parts[0]);
    }
    if (isset($parts[1])) {
        $action = strtolower($parts[1]); // e.g. 'version'
    }
}

/**
 * Helper to output JSON response and exit
 */
function sendResponse($status, $data) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

/**
 * Helper to check if current user is logged in
 */
function getLoggedInUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Helper to check role of current user
 */
function getLoggedInUserRole() {
    return $_SESSION['role'] ?? null;
}

// Router
switch ($method) {
    case 'GET':
        if ($id !== null) {
            handleGetDetails($conn, $id);
        } else {
            handleGetAll($conn);
        }
        break;
        
    case 'POST':
        if ($id === null) {
            handleCreate($conn);
        } else {
            sendResponse(400, ['error' => 'Action non supportée pour POST avec ID.']);
        }
        break;
        
    case 'PUT':
        if ($id !== null && $action === 'version') {
            handleUpdate($conn, $id);
        } else {
            sendResponse(400, ['error' => "Action non valide. Utilisez PUT /api/resources/{id}/version pour mettre à jour."]);
        }
        break;
        
    case 'DELETE':
        if ($id !== null) {
            handleDelete($conn, $id);
        } else {
            sendResponse(400, ['error' => "Spécifiez l'identifiant de la ressource à supprimer."]);
        }
        break;
        
    default:
        sendResponse(405, ['error' => 'Méthode non autorisée.']);
        break;
}

/**
 * GET /api/resources
 * Retrieve all resources with filters (matiere and niveau) and visibility controls
 */
function handleGetAll($conn) {
    $matiere = $_GET['matiere'] ?? '';
    $niveau = $_GET['niveau'] ?? '';
    
    $sql = "SELECT r.*, u.nom as auteur_nom, u.prenom as auteur_prenom 
            FROM ressources r 
            JOIN users u ON r.id_enseignant = u.id 
            WHERE 1=1";
            
    $params = [];
    $types = '';
    
    // Visibility filters:
    // - Guests (not logged in): can only see public resources
    // - Logged-in users: can see public and inscrit resources, plus their own privatif resources
    $userId = getLoggedInUserId();
    if ($userId) {
        $userId = intval($userId);
        $sql .= " AND (r.visibilite IN ('public', 'inscrit') OR r.id_enseignant = ?)";
        $params[] = $userId;
        $types .= 'i';
    } else {
        $sql .= " AND r.visibilite = 'public'";
    }
    
    // Filter by matiere
    if (!empty($matiere)) {
        $sql .= " AND r.id_matiere = ?";
        $params[] = $matiere;
        $types .= 's';
    }
    
    // Filter by niveau
    if (!empty($niveau)) {
        $sql .= " AND r.id_niveau = ?";
        $params[] = $niveau;
        $types .= 's';
    }
    
    $sql .= " ORDER BY r.created_at DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        sendResponse(500, ['error' => 'Erreur de base de données.', 'details' => mysqli_error($conn)]);
    }
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $resources = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $resources[] = [
            'id' => intval($row['id']),
            'titre' => $row['titre'],
            'description' => $row['description'],
            'type' => $row['type'],
            'URL_fichier' => $row['URL_fichier'],
            'version' => intval($row['version']),
            'id_matiere' => $row['id_matiere'],
            'id_niveau' => $row['id_niveau'],
            'id_enseignant' => intval($row['id_enseignant']),
            'auteur' => $row['auteur_prenom'] . ' ' . $row['auteur_nom'],
            'visibilite' => $row['visibilite'],
            'created_at' => $row['created_at']
        ];
    }
    
    sendResponse(200, $resources);
}

/**
 * GET /api/resources/{id}
 * Retrieve details of a specific resource
 */
function handleGetDetails($conn, $id) {
    $sql = "SELECT r.*, u.nom as auteur_nom, u.prenom as auteur_prenom 
            FROM ressources r 
            JOIN users u ON r.id_enseignant = u.id 
            WHERE r.id = ?";
            
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        sendResponse(500, ['error' => 'Erreur de base de données.']);
    }
    
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $resource = mysqli_fetch_assoc($result);
    
    if (!$resource) {
        sendResponse(404, ['error' => 'Ressource non trouvée.']);
    }
    
    // Visibility check
    $userId = getLoggedInUserId();
    if ($userId) {
        $userId = intval($userId);
    }
    $visibilite = $resource['visibilite'];
    $authorId = intval($resource['id_enseignant']);
    
    if ($visibilite === 'privatif') {
        if (!$userId || $userId !== $authorId) {
            sendResponse(403, ['error' => 'Accès interdit. Cette ressource est privée.']);
        }
    } elseif ($visibilite === 'inscrit') {
        if (!$userId) {
            sendResponse(401, ['error' => 'Non authentifié. Connectez-vous pour voir cette ressource.']);
        }
    }
    
    sendResponse(200, [
        'id' => intval($resource['id']),
        'titre' => $resource['titre'],
        'description' => $resource['description'],
        'type' => $resource['type'],
        'URL_fichier' => $resource['URL_fichier'],
        'version' => intval($resource['version']),
        'id_matiere' => $resource['id_matiere'],
        'id_niveau' => $resource['id_niveau'],
        'id_enseignant' => $authorId,
        'auteur' => $resource['auteur_prenom'] . ' ' . $resource['auteur_nom'],
        'visibilite' => $visibilite,
        'created_at' => $resource['created_at']
    ]);
}

/**
 * POST /api/resources
 * Deposit a new resource (supports file upload)
 */
function handleCreate($conn) {
    // Auth Check
    $userId = getLoggedInUserId();
    $userRole = getLoggedInUserRole();
    if (!$userId) {
        sendResponse(401, ['error' => 'Non authentifié. Veuillez vous connecter.']);
    }
    $userId = intval($userId);
    if ($userRole !== 'enseignant') {
        sendResponse(403, ['error' => 'Accès refusé. Seuls les enseignants peuvent déposer des ressources.']);
    }
    
    // Read parameters
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type_raw = trim($_POST['type'] ?? '');
    $id_matiere = trim($_POST['id_matiere'] ?? '');
    $id_niveau = trim($_POST['id_niveau'] ?? '');
    $visibilite = trim($_POST['visibilite'] ?? 'public');
    
    // Map raw type to enum values in DB ('PDF', 'vidéo', 'audio', 'lien')
    $type_map = [
        'pdf' => 'PDF',
        'video' => 'vidéo',
        'audio' => 'audio',
        'lien' => 'lien',
        'PDF' => 'PDF',
        'vidéo' => 'vidéo',
        'video' => 'vidéo',
        'audio' => 'audio',
        'lien' => 'lien'
    ];
    
    $type = $type_map[$type_raw] ?? '';
    
    // Validation
    if (empty($titre) || empty($description) || empty($type) || empty($id_matiere) || empty($id_niveau)) {
        sendResponse(400, ['error' => 'Champs obligatoires manquants : titre, description, type, id_matiere, id_niveau.']);
    }
    
    if (!in_array($visibilite, ['public', 'inscrit', 'privatif'])) {
        sendResponse(400, ['error' => "Visibilité invalide. Valeurs acceptées : public, inscrit, privatif."]);
    }
    
    $URL_fichier = '';
    
    // Handle File Upload or Link
    if ($type !== 'lien') {
        if (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
            sendResponse(400, ['error' => 'Fichier requis pour ce type de ressource.']);
        }
        
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['fichier']['name']);
        $file_path = $upload_dir . $file_name;
        
        // Extension checks
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = [];
        if ($type === 'PDF') {
            $allowed_exts = ['pdf'];
        } elseif ($type === 'vidéo') {
            $allowed_exts = ['mp4', 'avi', 'mpeg', 'mov'];
        } elseif ($type === 'audio') {
            $allowed_exts = ['mp3', 'wav', 'ogg', 'mpeg'];
        }
        
        if (!in_array($file_ext, $allowed_exts)) {
            sendResponse(400, ['error' => "Extension de fichier non autorisée pour le type {$type}."]);
        }
        
        // Size checks (max 50MB)
        if ($_FILES['fichier']['size'] > 50 * 1024 * 1024) {
            sendResponse(400, ['error' => 'Le fichier dépasse la taille maximale autorisée de 50 Mo.']);
        }
        
        if (move_uploaded_file($_FILES['fichier']['tmp_name'], $file_path)) {
            $URL_fichier = 'uploads/' . $file_name;
        } else {
            sendResponse(500, ['error' => "Échec de l'enregistrement du fichier."]);
        }
    } else {
        $URL_fichier = trim($_POST['lien_url'] ?? '');
        if (empty($URL_fichier) || !filter_var($URL_fichier, FILTER_VALIDATE_URL)) {
            sendResponse(400, ['error' => 'URL du lien valide obligatoire pour le type lien.']);
        }
    }
    
    // Insert into DB
    $sql = "INSERT INTO ressources (titre, description, type, URL_fichier, version, id_matiere, id_niveau, id_enseignant, visibilite) 
            VALUES (?, ?, ?, ?, 1, ?, ?, ?, ?)";
            
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        sendResponse(500, ['error' => 'Erreur de préparation SQL.', 'details' => mysqli_error($conn)]);
    }
    
    mysqli_stmt_bind_param($stmt, 'ssssssis', $titre, $description, $type, $URL_fichier, $id_matiere, $id_niveau, $userId, $visibilite);
    
    if (mysqli_stmt_execute($stmt)) {
        $new_id = mysqli_insert_id($conn);
        sendResponse(201, [
            'success' => true,
            'message' => 'Ressource ajoutée avec succès.',
            'resource' => [
                'id' => $new_id,
                'titre' => $titre,
                'description' => $description,
                'type' => $type,
                'URL_fichier' => $URL_fichier,
                'version' => 1,
                'id_matiere' => $id_matiere,
                'id_niveau' => $id_niveau,
                'id_enseignant' => $userId,
                'visibilite' => $visibilite
            ]
        ]);
    } else {
        sendResponse(500, ['error' => "Erreur lors de l'insertion en base de données.", 'details' => mysqli_stmt_error($stmt)]);
    }
}

/**
 * PUT /api/resources/{id}/version
 * Update an existing resource and increment its version (author only)
 */
function handleUpdate($conn, $id) {
    // Auth Check
    $userId = getLoggedInUserId();
    if (!$userId) {
        sendResponse(401, ['error' => 'Non authentifié. Veuillez vous connecter.']);
    }
    $userId = intval($userId);
    
    // Fetch resource to verify ownership
    $check_sql = "SELECT * FROM ressources WHERE id = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $resource = mysqli_fetch_assoc($result);
    
    if (!$resource) {
        sendResponse(404, ['error' => 'Ressource non trouvée.']);
    }
    
    if (intval($resource['id_enseignant']) !== $userId) {
        sendResponse(403, ['error' => 'Accès interdit. Seul l\'auteur peut modifier cette ressource.']);
    }
    
    // Parse PUT input data (since PUT form-data is not natively parsed by PHP, we also support raw PUT urlencoded or JSON)
    $putData = [];
    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        $rawInput = file_get_contents('php://input');
        $putData = json_decode($rawInput, true) ?? [];
    } else {
        $rawInput = file_get_contents('php://input');
        parse_str($rawInput, $putData);
    }
    
    // Merge inputs (prefer $_POST for POST-overridden PUT requests)
    $titre = isset($_POST['titre']) ? trim($_POST['titre']) : ($putData['titre'] ?? $resource['titre']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : ($putData['description'] ?? $resource['description']);
    $type_raw = isset($_POST['type']) ? trim($_POST['type']) : ($putData['type'] ?? $resource['type']);
    $id_matiere = isset($_POST['id_matiere']) ? trim($_POST['id_matiere']) : ($putData['id_matiere'] ?? $resource['id_matiere']);
    $id_niveau = isset($_POST['id_niveau']) ? trim($_POST['id_niveau']) : ($putData['id_niveau'] ?? $resource['id_niveau']);
    $visibilite = isset($_POST['visibilite']) ? trim($_POST['visibilite']) : ($putData['visibilite'] ?? $resource['visibilite']);
    
    $type_map = [
        'pdf' => 'PDF',
        'video' => 'vidéo',
        'audio' => 'audio',
        'lien' => 'lien',
        'PDF' => 'PDF',
        'vidéo' => 'vidéo',
        'video' => 'vidéo',
        'audio' => 'audio',
        'lien' => 'lien'
    ];
    $type = $type_map[$type_raw] ?? $resource['type'];
    
    // Validation
    if (empty($titre) || empty($description) || empty($type) || empty($id_matiere) || empty($id_niveau)) {
        sendResponse(400, ['error' => 'Champs obligatoires vides.']);
    }
    
    if (!in_array($visibilite, ['public', 'inscrit', 'privatif'])) {
        sendResponse(400, ['error' => "Visibilité invalide."]);
    }
    
    $URL_fichier = $resource['URL_fichier'];
    $file_deleted = false;
    
    // Handle File Update if a new file is uploaded
    if ($type !== 'lien') {
        if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = time() . '_' . basename($_FILES['fichier']['name']);
            $file_path = $upload_dir . $file_name;
            
            // Extension checks
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = [];
            if ($type === 'PDF') {
                $allowed_exts = ['pdf'];
            } elseif ($type === 'vidéo') {
                $allowed_exts = ['mp4', 'avi', 'mpeg', 'mov'];
            } elseif ($type === 'audio') {
                $allowed_exts = ['mp3', 'wav', 'ogg', 'mpeg'];
            }
            
            if (!in_array($file_ext, $allowed_exts)) {
                sendResponse(400, ['error' => "Extension de fichier non autorisée pour le type {$type}."]);
            }
            
            // Size checks (max 50MB)
            if ($_FILES['fichier']['size'] > 50 * 1024 * 1024) {
                sendResponse(400, ['error' => 'Le fichier dépasse la taille maximale autorisée de 50 Mo.']);
            }
            
            if (move_uploaded_file($_FILES['fichier']['tmp_name'], $file_path)) {
                // Delete previous file if local
                if (!empty($resource['URL_fichier']) && strpos($resource['URL_fichier'], 'uploads/') === 0) {
                    $old_file = '../' . $resource['URL_fichier'];
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                $URL_fichier = 'uploads/' . $file_name;
            } else {
                sendResponse(500, ['error' => "Échec de l'enregistrement du nouveau fichier."]);
            }
        }
    } else {
        // Link type update
        $new_lien = isset($_POST['lien_url']) ? trim($_POST['lien_url']) : ($putData['lien_url'] ?? '');
        if (!empty($new_lien)) {
            if (!filter_var($new_lien, FILTER_VALIDATE_URL)) {
                sendResponse(400, ['error' => 'URL du lien invalide.']);
            }
            // Delete previous local file if it was a file type before
            if (!empty($resource['URL_fichier']) && strpos($resource['URL_fichier'], 'uploads/') === 0) {
                $old_file = '../' . $resource['URL_fichier'];
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }
            $URL_fichier = $new_lien;
        }
    }
    
    // Update resource details and increment version
    $update_sql = "UPDATE ressources 
                   SET titre = ?, description = ?, type = ?, URL_fichier = ?, version = version + 1, id_matiere = ?, id_niveau = ?, visibilite = ? 
                   WHERE id = ?";
                   
    $stmt = mysqli_prepare($conn, $update_sql);
    if (!$stmt) {
        sendResponse(500, ['error' => 'Erreur de préparation SQL de mise à jour.', 'details' => mysqli_error($conn)]);
    }
    
    mysqli_stmt_bind_param($stmt, 'sssssssi', $titre, $description, $type, $URL_fichier, $id_matiere, $id_niveau, $visibilite, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Retrieve updated version
        $updated_version = intval($resource['version']) + 1;
        sendResponse(200, [
            'success' => true,
            'message' => 'Ressource mise à jour avec succès (nouvelle version).',
            'resource' => [
                'id' => $id,
                'titre' => $titre,
                'description' => $description,
                'type' => $type,
                'URL_fichier' => $URL_fichier,
                'version' => $updated_version,
                'id_matiere' => $id_matiere,
                'id_niveau' => $id_niveau,
                'id_enseignant' => $userId,
                'visibilite' => $visibilite
            ]
        ]);
    } else {
        sendResponse(500, ['error' => 'Erreur lors de la mise à jour de la ressource.']);
    }
}

/**
 * DELETE /api/resources/{id}
 * Delete a resource (author only)
 */
function handleDelete($conn, $id) {
    // Auth Check
    $userId = getLoggedInUserId();
    if (!$userId) {
        sendResponse(401, ['error' => 'Non authentifié. Veuillez vous connecter.']);
    }
    $userId = intval($userId);
    
    // Fetch resource to verify ownership
    $check_sql = "SELECT * FROM ressources WHERE id = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $resource = mysqli_fetch_assoc($result);
    
    if (!$resource) {
        sendResponse(404, ['error' => 'Ressource non trouvée.']);
    }
    
    if (intval($resource['id_enseignant']) !== $userId) {
        sendResponse(403, ['error' => 'Accès interdit. Seul l\'auteur peut supprimer cette ressource.']);
    }
    
    // Delete local file if it exists
    if (!empty($resource['URL_fichier']) && strpos($resource['URL_fichier'], 'uploads/') === 0) {
        $file_path = '../' . $resource['URL_fichier'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Delete database record
    $delete_sql = "DELETE FROM ressources WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    
    if (mysqli_stmt_execute($stmt)) {
        sendResponse(200, [
            'success' => true,
            'message' => 'Ressource supprimée avec succès.'
        ]);
    } else {
        sendResponse(500, ['error' => 'Erreur lors de la suppression de la ressource.']);
    }
}
