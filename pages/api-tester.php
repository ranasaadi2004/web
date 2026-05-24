<?php
require_once '../php/config.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
if (!$is_logged_in) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit();
}

if ($_SESSION['role'] !== 'enseignant') {
    $params = http_build_query([
        'nom' => $_SESSION['nom'],
        'prenom' => $_SESSION['prenom'],
        'role' => $_SESSION['role']
    ]);

    header("Location: " . BASE_URL . "pages/dashboard.php?" . $params);
    exit();
}

$user_name = $is_logged_in ? $_SESSION['prenom'] . ' ' . $_SESSION['nom'] : 'Invité';
$user_role = $is_logged_in ? $_SESSION['role'] : 'Aucun';
$dashboard_url = 'dashboard.php?' . http_build_query([
    'nom' => $_SESSION['nom'],
    'prenom' => $_SESSION['prenom'],
    'role' => $_SESSION['role']
]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testeur d'API REST - EduShare</title>
    <link rel="stylesheet" href="../css/style.css">
    <!-- Google Fonts for premium developer aesthetic -->
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --bg-input: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent-blue: #38bdf8;
            --accent-green: #34d399;
            --accent-orange: #fb923c;
            --accent-red: #f87171;
            --accent-purple: #c084fc;
            --border-color: #475569;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0b0f19;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background-color: #111827;
            border-bottom: 1px solid var(--border-color);
        }

        /* Hero Header */
        .api-hero {
            background: linear-gradient(135deg, #1e1b4b, #0f172a);
            padding: 40px 20px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .api-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 60%);
            animation: pulse 10s infinite alternate;
            pointer-events: none;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }

        .api-hero h1 {
            font-size: 32px;
            font-weight: 700;
            background: linear-gradient(to right, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        .api-hero p {
            color: var(--text-muted);
            font-size: 16px;
        }

        /* Container & Grid layout */
        .tester-container {
            max-width: 1400px;
            width: 100%;
            margin: 30px auto;
            padding: 0 20px;
            flex: 1;
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
        }

        /* Sidebar endpoints list */
        .sidebar {
            background-color: var(--bg-card);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            height: fit-content;
        }

        .session-status {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 15px;
        }

        .session-status h3 {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .session-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 15px;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-dot.active {
            background-color: var(--accent-green);
            box-shadow: 0 0 10px var(--accent-green);
        }

        .status-dot.inactive {
            background-color: var(--accent-red);
            box-shadow: 0 0 10px var(--accent-red);
        }

        .endpoint-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .endpoint-item {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 12px 15px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .endpoint-item:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        .endpoint-item.active {
            background: rgba(99, 102, 241, 0.15);
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: inset 0 0 10px rgba(99, 102, 241, 0.1);
        }

        .method-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
            text-transform: uppercase;
            min-width: 65px;
            text-align: center;
        }

        .method-badge.get {
            background-color: rgba(56, 189, 248, 0.15);
            color: var(--accent-blue);
            border: 1px solid rgba(56, 189, 248, 0.3);
        }

        .method-badge.post {
            background-color: rgba(52, 211, 153, 0.15);
            color: var(--accent-green);
            border: 1px solid rgba(52, 211, 153, 0.3);
        }

        .method-badge.put {
            background-color: rgba(251, 146, 60, 0.15);
            color: var(--accent-orange);
            border: 1px solid rgba(251, 146, 60, 0.3);
        }

        .method-badge.delete {
            background-color: rgba(248, 113, 113, 0.15);
            color: var(--accent-red);
            border: 1px solid rgba(248, 113, 113, 0.3);
        }

        .endpoint-path {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            font-weight: 500;
        }

        /* Workspace (Forms + Results) */
        .workspace {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .work-card {
            background-color: var(--bg-card);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            padding: 25px;
        }

        .work-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .endpoint-details {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .endpoint-details h2 {
            font-size: 22px;
            font-weight: 600;
        }

        .endpoint-description {
            color: var(--text-muted);
            font-size: 14px;
        }

        /* Form styling */
        .params-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-section-title {
            font-size: 14px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            border-left: 3px solid var(--accent-blue);
            padding-left: 8px;
        }

        .input-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .full-width {
            grid-column: span 2;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .input-group label {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-main);
        }

        .input-group input[type="text"],
        .input-group input[type="url"],
        .input-group input[type="number"],
        .input-group select,
        .input-group textarea {
            background-color: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-main);
            padding: 10px 14px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s;
        }

        .input-group input:focus,
        .input-group select:focus,
        .input-group textarea:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 8px rgba(56, 189, 248, 0.2);
        }

        /* File input upload visual */
        .file-dropzone {
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            background: rgba(255, 255, 255, 0.01);
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-dropzone:hover {
            border-color: var(--accent-blue);
            background: rgba(56, 189, 248, 0.02);
        }

        .file-dropzone input[type="file"] {
            display: none;
        }

        .file-dropzone-icon {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .file-dropzone-text {
            font-size: 13px;
            color: var(--text-muted);
        }

        /* Action buttons */
        .btn-send {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            align-self: flex-start;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        }

        .btn-send:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.5);
            transform: translateY(-2px);
        }

        .btn-send:active {
            transform: translateY(0);
        }

        /* Result Panel / Response Terminal */
        .terminal-card {
            background-color: #090d16;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 350px;
        }

        .terminal-header {
            background-color: #0f172a;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .terminal-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .terminal-stats {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 13px;
        }

        .stat-badge {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 4px 10px;
            border-radius: 6px;
        }

        .stat-badge.status-2xx {
            background-color: rgba(52, 211, 153, 0.1);
            color: var(--accent-green);
            border-color: rgba(52, 211, 153, 0.2);
        }

        .stat-badge.status-4xx, .stat-badge.status-5xx {
            background-color: rgba(248, 113, 113, 0.1);
            color: var(--accent-red);
            border-color: rgba(248, 113, 113, 0.2);
        }

        .terminal-body {
            padding: 20px;
            flex: 1;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            overflow: auto;
            position: relative;
            background-color: #0b0e14;
        }

        .code-output {
            white-space: pre-wrap;
            word-wrap: break-word;
            margin: 0;
            color: #e2e8f0;
        }

        /* Loading spinner */
        .spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .spinner-ring {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 255, 255, 0.05);
            border-top-color: var(--accent-blue);
            border-radius: 50%;
            animation: spin 1s infinite linear;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Footer styling overrides */
        footer {
            background-color: #111827;
            border-top: 1px solid var(--border-color);
            margin-top: 50px;
        }

        /* Utility styles */
        .method-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>

    <header>
        <div class="logo">📚 EduRessources</div>
        <nav>
            <a href="ressources.php">Ressources</a>
            <a href="<?php echo htmlspecialchars($dashboard_url); ?>">Tableau de bord</a>
            <a href="../php/add_ressource.php">Ajouter une ressource</a>
            <a href="api-tester.php" class="active">Testeur d'API</a>
            <a href="../php/logout.php">Déconnexion</a>
        </nav>
    </header>

    <section class="api-hero">
        <h1>Console de Test d'API REST</h1>
        <p>Interface interactive pour tester les endpoints de gestion des ressources pédagogiques</p>
    </section>

    <div class="tester-container">
        
        <!-- Sidebar Panel -->
        <div class="sidebar">
            <div class="session-status">
                <h3>Session Actuelle</h3>
                <div class="session-badge">
                    <span class="status-dot <?php echo $is_logged_in ? 'active' : 'inactive'; ?>"></span>
                    <div>
                        <div style="font-weight:600;"><?php echo htmlspecialchars($user_name); ?></div>
                        <div style="font-size:12px; color:var(--text-muted);">Rôle : <?php echo htmlspecialchars($user_role); ?></div>
                    </div>
                </div>
                <?php if (!$is_logged_in): ?>
                    <div style="margin-top:12px; font-size:12px; color:var(--accent-orange);">
                        ⚠️ Connectez-vous en tant qu'enseignant pour pouvoir ajouter, modifier ou supprimer des ressources.
                    </div>
                <?php endif; ?>
            </div>

            <div class="endpoint-list">
                <h3>Endpoints</h3>
                
                <div class="endpoint-item active" onclick="selectEndpoint('get_all', this)">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/resources</span>
                </div>

                <div class="endpoint-item" onclick="selectEndpoint('get_details', this)">
                    <span class="method-badge get">GET</span>
                    <span class="endpoint-path">/api/resources/{id}</span>
                </div>

                <div class="endpoint-item" onclick="selectEndpoint('create', this)">
                    <span class="method-badge post">POST</span>
                    <span class="endpoint-path">/api/resources</span>
                </div>

                <div class="endpoint-item" onclick="selectEndpoint('update', this)">
                    <span class="method-badge put">PUT</span>
                    <span class="endpoint-path">/api/resources/{id}/version</span>
                </div>

                <div class="endpoint-item" onclick="selectEndpoint('delete', this)">
                    <span class="method-badge delete">DELETE</span>
                    <span class="endpoint-path">/api/resources/{id}</span>
                </div>
            </div>
        </div>

        <!-- Main Workspace Panel -->
        <div class="workspace">
            
            <div class="work-card">
                <div class="work-card-header">
                    <div class="endpoint-details">
                        <div class="method-info">
                            <span id="active-method" class="method-badge get">GET</span>
                            <h2 id="active-path">/api/resources</h2>
                        </div>
                        <p id="active-description" class="endpoint-description">Récupérer toutes les ressources pédagogiques (avec filtres optionnels par matière et niveau).</p>
                    </div>
                </div>

                <form id="api-form" class="params-form" onsubmit="sendRequest(event)">
                    <div id="form-inputs" class="input-grid">
                        <!-- Inputs dynamically generated by JavaScript based on selected endpoint -->
                    </div>

                    <button type="submit" class="btn-send">
                        <span>⚡</span> Envoyer la requête
                    </button>
                </form>
            </div>

            <!-- Terminal Output -->
            <div class="terminal-card">
                <div class="terminal-header">
                    <div class="terminal-title">
                        <span>💻</span> Console de Réponse
                    </div>
                    <div class="terminal-stats">
                        <div class="stat-badge">Statut: <span id="res-status">-</span></div>
                        <div class="stat-badge">Temps: <span id="res-time">-</span></div>
                    </div>
                </div>
                <div class="terminal-body">
                    <div id="loading" class="spinner">
                        <div class="spinner-ring"></div>
                        <span style="color:var(--text-muted); font-size:14px;">Exécution de la requête...</span>
                    </div>
                    <pre class="code-output"><code id="response-content">{
  "info": "Sélectionnez un endpoint dans le panneau de gauche, remplissez les paramètres et cliquez sur 'Envoyer la requête'."
}</code></pre>
                </div>
            </div>

        </div>

    </div>

    <footer>
        <p>© 2026 EduShare – Tous droits réservés</p>
    </footer>

    <script>
        const API_BASE = '../api/resources';
        let currentEndpoint = 'get_all';

        const endpoints = {
            get_all: {
                method: 'GET',
                path: '/api/resources',
                description: 'Récupérer toutes les ressources pédagogiques (avec filtres optionnels). La visibilité est filtrée selon que vous êtes connecté ou non.',
                inputs: [
                    { name: 'matiere', type: 'select', label: 'Filtrer par Matière', options: ['', 'mathematiques', 'francais', 'anglais', 'histoire', 'geographie', 'physique', 'chimie', 'biologie', 'informatique', 'autre'] },
                    { name: 'niveau', type: 'select', label: 'Filtrer par Niveau', options: ['', 'primaire', 'college', 'lycee', 'superieur'] }
                ]
            },
            get_details: {
                method: 'GET',
                path: '/api/resources/{id}',
                description: 'Récupérer les détails complets d\'une ressource spécifique en passant son ID.',
                inputs: [
                    { name: 'id', type: 'number', label: 'ID de la Ressource (Requis)', required: true }
                ]
            },
            create: {
                method: 'POST',
                path: '/api/resources',
                description: 'Déposer une nouvelle ressource (Réservé aux enseignants connectés). Insère une nouvelle ressource en version 1.',
                inputs: [
                    { name: 'titre', type: 'text', label: 'Titre de la ressource', required: true, width: 'full' },
                    { name: 'description', type: 'textarea', label: 'Description', required: true, width: 'full' },
                    { name: 'type', type: 'select', label: 'Type de ressource', required: true, options: ['PDF', 'vidéo', 'audio', 'lien'], onchange: 'toggleCreateFields()' },
                    { name: 'id_matiere', type: 'select', label: 'Matière', required: true, options: ['mathematiques', 'francais', 'anglais', 'histoire', 'geographie', 'physique', 'chimie', 'biologie', 'informatique', 'autre'] },
                    { name: 'id_niveau', type: 'select', label: 'Niveau', required: true, options: ['primaire', 'college', 'lycee', 'superieur'] },
                    { name: 'visibilite', type: 'select', label: 'Visibilité', required: true, options: ['public', 'inscrit', 'privatif'] },
                    { name: 'fichier', type: 'file', label: 'Fichier (PDF, Vidéo, Audio)', required: true },
                    { name: 'lien_url', type: 'url', label: 'URL du Lien', required: false, placeholder: 'https://exemple.com', hidden: true }
                ]
            },
            update: {
                method: 'PUT',
                path: '/api/resources/{id}/version',
                description: 'Mettre à jour les informations ou le fichier d\'une ressource et incrémenter sa version (Auteur uniquement).',
                inputs: [
                    { name: 'id', type: 'number', label: 'ID de la ressource à modifier', required: true },
                    { name: 'visibilite', type: 'select', label: 'Visibilité', required: true, options: ['public', 'inscrit', 'privatif'] },
                    { name: 'titre', type: 'text', label: 'Titre (Laissez vide pour conserver l\'actuel)', required: false },
                    { name: 'description', type: 'textarea', label: 'Description (Laissez vide pour conserver l\'actuelle)', required: false, width: 'full' },
                    { name: 'type', type: 'select', label: 'Type de ressource (Optionnel)', required: false, options: ['', 'PDF', 'vidéo', 'audio', 'lien'], onchange: 'toggleUpdateFields()' },
                    { name: 'id_matiere', type: 'select', label: 'Matière (Optionnelle)', required: false, options: ['', 'mathematiques', 'francais', 'anglais', 'histoire', 'geographie', 'physique', 'chimie', 'biologie', 'informatique', 'autre'] },
                    { name: 'id_niveau', type: 'select', label: 'Niveau (Optionnel)', required: false, options: ['', 'primaire', 'college', 'lycee', 'superieur'] },
                    { name: 'fichier', type: 'file', label: 'Nouveau Fichier (Optionnel)', required: false },
                    { name: 'lien_url', type: 'url', label: 'Nouvelle URL du Lien (Optionnelle)', required: false, placeholder: 'https://exemple.com', hidden: true }
                ]
            },
            delete: {
                method: 'DELETE',
                path: '/api/resources/{id}',
                description: 'Supprimer une ressource pédagogique existante de la base de données ainsi que son fichier stocké (Auteur uniquement).',
                inputs: [
                    { name: 'id', type: 'number', label: 'ID de la Ressource à supprimer (Requis)', required: true }
                ]
            }
        };

        function selectEndpoint(key, selectedItem = null) {
            currentEndpoint = key;
            
            // Highlight list item
            document.querySelectorAll('.endpoint-item').forEach(el => el.classList.remove('active'));
            const item = selectedItem || document.querySelector(`.endpoint-item[onclick*="${key}"]`);
            if (item) {
                item.classList.add('active');
            }

            const ep = endpoints[key];
            
            // Update Details
            const methodBadge = document.getElementById('active-method');
            methodBadge.textContent = ep.method;
            methodBadge.className = `method-badge ${ep.method.toLowerCase()}`;
            
            document.getElementById('active-path').textContent = ep.path;
            document.getElementById('active-description').textContent = ep.description;

            // Generate inputs
            generateInputs(ep.inputs);
        }

        function generateInputs(inputs) {
            const container = document.getElementById('form-inputs');
            container.innerHTML = '';

            inputs.forEach(input => {
                const group = document.createElement('div');
                group.className = `input-group ${input.width === 'full' ? 'full-width' : ''}`;
                group.id = `group-${input.name}`;
                
                if (input.hidden) {
                    group.style.display = 'none';
                }

                const label = document.createElement('label');
                label.textContent = input.label + (input.required ? ' *' : '');
                group.appendChild(label);

                if (input.type === 'select') {
                    const select = document.createElement('select');
                    select.name = input.name;
                    select.id = `input-${input.name}`;
                    if (input.required) select.required = true;
                    if (input.onchange) {
                        select.setAttribute('onchange', input.onchange);
                    }

                    input.options.forEach(opt => {
                        const option = document.createElement('option');
                        option.value = opt;
                        option.textContent = opt === '' ? '-- Sélectionner --' : opt.charAt(0).toUpperCase() + opt.slice(1);
                        select.appendChild(option);
                    });
                    group.appendChild(select);
                } else if (input.type === 'textarea') {
                    const textarea = document.createElement('textarea');
                    textarea.name = input.name;
                    textarea.id = `input-${input.name}`;
                    textarea.rows = 3;
                    if (input.required) textarea.required = true;
                    group.appendChild(textarea);
                } else if (input.type === 'file') {
                    const dropzone = document.createElement('label');
                    dropzone.className = 'file-dropzone';
                    dropzone.htmlFor = `input-${input.name}`;
                    
                    const fileInput = document.createElement('input');
                    fileInput.type = 'file';
                    fileInput.name = input.name;
                    fileInput.id = `input-${input.name}`;
                    if (input.required) fileInput.required = true;
                    
                    fileInput.addEventListener('change', function() {
                        const text = this.files[0] ? `Fichier sélectionné : ${this.files[0].name}` : 'Glisser-déposer votre fichier ou cliquer pour parcourir';
                        dropzone.querySelector('.file-dropzone-text').textContent = text;
                    });

                    dropzone.innerHTML = `
                        <div class="file-dropzone-icon">📁</div>
                        <div class="file-dropzone-text">Cliquer pour choisir un fichier</div>
                    `;
                    dropzone.appendChild(fileInput);
                    group.appendChild(dropzone);
                } else {
                    const inp = document.createElement('input');
                    inp.type = input.type;
                    inp.name = input.name;
                    inp.id = `input-${input.name}`;
                    if (input.required) inp.required = true;
                    if (input.placeholder) inp.placeholder = input.placeholder;
                    group.appendChild(inp);
                }

                container.appendChild(group);
            });
        }

        // Toggle Fields for Create Endpoint
        function toggleCreateFields() {
            const type = document.getElementById('input-type').value;
            const fileGroup = document.getElementById('group-fichier');
            const urlGroup = document.getElementById('group-lien_url');
            
            const fileInput = document.getElementById('input-fichier');
            const urlInput = document.getElementById('input-lien_url');

            if (type === 'lien') {
                fileGroup.style.display = 'none';
                fileInput.required = false;
                
                urlGroup.style.display = 'flex';
                urlInput.required = true;
            } else {
                fileGroup.style.display = 'flex';
                fileInput.required = true;
                
                urlGroup.style.display = 'none';
                urlInput.required = false;

                // Adjust file acceptance
                if (type === 'PDF') {
                    fileInput.accept = '.pdf';
                } else if (type === 'vidéo') {
                    fileInput.accept = '.mp4,.avi,.mov,.mpeg';
                } else if (type === 'audio') {
                    fileInput.accept = '.mp3,.wav,.ogg';
                }
            }
        }

        // Toggle Fields for Update Endpoint
        function toggleUpdateFields() {
            const type = document.getElementById('input-type').value;
            const fileGroup = document.getElementById('group-fichier');
            const urlGroup = document.getElementById('group-lien_url');
            
            const fileInput = document.getElementById('input-fichier');
            const urlInput = document.getElementById('input-lien_url');

            if (type === 'lien') {
                fileGroup.style.display = 'none';
                urlGroup.style.display = 'flex';
            } else if (type === '') {
                fileGroup.style.display = 'flex';
                urlGroup.style.display = 'none';
            } else {
                fileGroup.style.display = 'flex';
                urlGroup.style.display = 'none';
                
                if (type === 'PDF') {
                    fileInput.accept = '.pdf';
                } else if (type === 'vidéo') {
                    fileInput.accept = '.mp4,.avi,.mov,.mpeg';
                } else if (type === 'audio') {
                    fileInput.accept = '.mp3,.wav,.ogg';
                }
            }
        }

        // Send AJAX Request
        function sendRequest(e) {
            e.preventDefault();

            const form = document.getElementById('api-form');
            const loading = document.getElementById('loading');
            const responseCode = document.getElementById('response-content');
            const resStatus = document.getElementById('res-status');
            const resTime = document.getElementById('res-time');

            // Reset UI
            responseCode.textContent = '';
            loading.style.display = 'flex';
            resStatus.textContent = '-';
            resStatus.className = 'stat-badge';
            resTime.textContent = '-';

            const ep = endpoints[currentEndpoint];
            const startTime = performance.now();

            // Construct URL & Headers
            let url = API_BASE;
            let options = {
                method: ep.method
            };

            const formData = new FormData();

            if (currentEndpoint === 'get_all') {
                const matiere = document.getElementById('input-matiere').value;
                const niveau = document.getElementById('input-niveau').value;
                const params = [];
                if (matiere) params.push(`matiere=${encodeURIComponent(matiere)}`);
                if (niveau) params.push(`niveau=${encodeURIComponent(niveau)}`);
                if (params.length > 0) {
                    url += '?' + params.join('&');
                }
            } else if (currentEndpoint === 'get_details') {
                const id = document.getElementById('input-id').value;
                url += `/${id}`;
            } else if (currentEndpoint === 'create') {
                formData.append('titre', document.getElementById('input-titre').value);
                formData.append('description', document.getElementById('input-description').value);
                formData.append('type', document.getElementById('input-type').value);
                formData.append('id_matiere', document.getElementById('input-id_matiere').value);
                formData.append('id_niveau', document.getElementById('input-id_niveau').value);
                formData.append('visibilite', document.getElementById('input-visibilite').value);
                
                const type = document.getElementById('input-type').value;
                if (type === 'lien') {
                    formData.append('lien_url', document.getElementById('input-lien_url').value);
                } else {
                    const fileInput = document.getElementById('input-fichier');
                    if (fileInput.files[0]) {
                        formData.append('fichier', fileInput.files[0]);
                    }
                }
                options.body = formData;
            } else if (currentEndpoint === 'update') {
                const id = document.getElementById('input-id').value;
                url += `/${id}/version`;

                // For updates (PUT) we override POST so files work in PHP
                options.method = 'POST';
                formData.append('_method', 'PUT');

                formData.append('visibilite', document.getElementById('input-visibilite').value);
                
                const titre = document.getElementById('input-titre').value;
                if (titre) formData.append('titre', titre);

                const desc = document.getElementById('input-description').value;
                if (desc) formData.append('description', desc);

                const type = document.getElementById('input-type').value;
                if (type) formData.append('type', type);

                const mat = document.getElementById('input-id_matiere').value;
                if (mat) formData.append('id_matiere', mat);

                const niv = document.getElementById('input-id_niveau').value;
                if (niv) formData.append('id_niveau', niv);

                if (type === 'lien') {
                    const lien = document.getElementById('input-lien_url').value;
                    if (lien) formData.append('lien_url', lien);
                } else {
                    const fileInput = document.getElementById('input-fichier');
                    if (fileInput.files[0]) {
                        formData.append('fichier', fileInput.files[0]);
                    }
                }
                options.body = formData;
            } else if (currentEndpoint === 'delete') {
                const id = document.getElementById('input-id').value;
                url += `/${id}`;
            }

            // Execute fetch
            fetch(url, options)
                .then(async response => {
                    const endTime = performance.now();
                    const duration = Math.round(endTime - startTime);
                    resTime.textContent = `${duration} ms`;

                    resStatus.textContent = `${response.status} ${response.statusText}`;
                    if (response.status >= 200 && response.status < 300) {
                        resStatus.className = 'stat-badge status-2xx';
                    } else {
                        resStatus.className = 'stat-badge status-4xx';
                    }

                    loading.style.display = 'none';

                    const text = await response.text();
                    try {
                        const json = JSON.parse(text);
                        responseCode.textContent = JSON.stringify(json, null, 2);
                    } catch (e) {
                        responseCode.textContent = text;
                    }
                })
                .catch(error => {
                    loading.style.display = 'none';
                    resStatus.textContent = 'Erreur';
                    resStatus.className = 'stat-badge status-5xx';
                    responseCode.textContent = JSON.stringify({ error: error.message }, null, 2);
                });
        }

        // Initialize default endpoint
        document.addEventListener('DOMContentLoaded', () => {
            selectEndpoint('get_all');
        });
    </script>
</body>
</html>
