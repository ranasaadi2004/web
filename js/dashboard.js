document.addEventListener('DOMContentLoaded', function() {
    const role = document.querySelector('.role-badge').textContent.includes('Enseignant') ? 'enseignant' : 'etudiant';
    
    if (role === 'enseignant') {
        loadTeacherStats();
        loadRecentRessources();
    } else {
        loadStudentStats();
        loadRecommendedRessources();
    }
});

function loadTeacherStats() {
    fetch('../php/dashboard.php?action=stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('ressources-count').textContent = data.ressources ?? 0;
            document.getElementById('vues-count').textContent = data.vues ?? 0;
            document.getElementById('commentaires-count').textContent = data.commentaires ?? 0;
        })
        .catch(() => {
            document.getElementById('ressources-count').textContent = '0';
            document.getElementById('vues-count').textContent = '0';
            document.getElementById('commentaires-count').textContent = '0';
        });
}

function loadStudentStats() {
    fetch('../php/dashboard.php?action=stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('consulted-count').textContent = data.consulted ?? 0;
            document.getElementById('favoris-count').textContent = data.favoris ?? 0;
            document.getElementById('quiz-count').textContent = data.quiz ?? 0;
        })
        .catch(() => {
            document.getElementById('consulted-count').textContent = '0';
            document.getElementById('favoris-count').textContent = '0';
            document.getElementById('quiz-count').textContent = '0';
        });
}

function loadRecentRessources() {
    fetch('../php/dashboard.php?action=recent_ressources')
        .then(response => response.json())
        .then(ressources => {
            const container = document.getElementById('recent-ressources');
            if (!Array.isArray(ressources) || ressources.length === 0) {
                container.innerHTML = '<p>Aucune ressource ajoutée pour le moment.</p>';
                return;
            }

            container.innerHTML = ressources.map(r => `
                <div class="ressource-item">
                    <div class="ressource-icon">${getTypeIcon(r.type)}</div>
                    <div class="ressource-info">
                        <h4>${r.titre}</h4>
                        <p>${r.matiere}</p>
                        <div class="ressource-meta">
                            <span>📅 ${r.date}</span>
                            <span>📄 ${r.type}</span>
                        </div>
                    </div>
                    <div class="ressource-actions">
                        <a href="api-tester.php" class="btn-small btn-edit">Versionner</a>
                        <a href="api-tester.php" class="btn-small btn-delete">Supprimer</a>
                    </div>
                </div>
            `).join('');
        })
        .catch(() => {
            document.getElementById('recent-ressources').innerHTML = '<p>Erreur lors du chargement des ressources.</p>';
        });
}

function loadRecommendedRessources() {
    fetch('../php/dashboard.php?action=recommended_ressources')
        .then(response => response.json())
        .then(ressources => {
            const container = document.getElementById('recommended-ressources');
            if (!Array.isArray(ressources) || ressources.length === 0) {
                container.innerHTML = '<p>Aucune ressource recommandée pour le moment.</p>';
                return;
            }

            container.innerHTML = ressources.map(r => `
                <div class="ressource-item">
                    <div class="ressource-icon">${getTypeIcon(r.type)}</div>
                    <div class="ressource-info">
                        <h4>${r.titre}</h4>
                        <p>${r.matiere} • ${r.auteur}</p>
                        <div class="ressource-meta">
                            <span>📄 ${r.type}</span>
                        </div>
                    </div>
                    <div class="ressource-actions">
                        <a href="ressources.php" class="btn-small btn-edit">Voir</a>
                    </div>
                </div>
            `).join('');
        })
        .catch(() => {
            document.getElementById('recommended-ressources').innerHTML = '<p>Erreur lors du chargement des ressources.</p>';
        });
}

function getTypeIcon(type) {
    const icons = {
        'PDF': '📄',
        'pdf': '📄',
        'Vidéo': '🎥',
        'vidéo': '🎥',
        'video': '🎥',
        'Audio': '🎵',
        'audio': '🎵',
        'Lien': '🔗',
        'lien': '🔗'
    };
    return icons[type] || '📁';
}
