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
    // Simuler les statistiques (à remplacer par un appel AJAX réel)
    setTimeout(() => {
        document.getElementById('ressources-count').textContent = '12';
        document.getElementById('vues-count').textContent = '348';
        document.getElementById('commentaires-count').textContent = '45';
    }, 500);
}

function loadStudentStats() {
    // Simuler les statistiques (à remplacer par un appel AJAX réel)
    setTimeout(() => {
        document.getElementById('consulted-count').textContent = '28';
        document.getElementById('favoris-count').textContent = '8';
        document.getElementById('quiz-count').textContent = '15';
    }, 500);
}

function loadRecentRessources() {
    // Simuler le chargement des ressources récentes (à remplacer par un appel AJAX réel)
    const ressources = [
        { titre: 'Introduction aux équations différentielles', matiere: 'Mathématiques', type: 'PDF', date: '2025-05-20' },
        { titre: 'La Révolution française', matiere: 'Histoire', type: 'Vidéo', date: '2025-05-18' },
        { titre: 'Exercices de grammaire', matiere: 'Français', type: 'PDF', date: '2025-05-15' }
    ];
    
    const container = document.getElementById('recent-ressources');
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
                <a href="#" class="btn-small btn-edit">Modifier</a>
                <a href="#" class="btn-small btn-delete">Supprimer</a>
            </div>
        </div>
    `).join('');
}

function loadRecommendedRessources() {
    // Simuler le chargement des ressources recommandées (à remplacer par un appel AJAX réel)
    const ressources = [
        { titre: 'Les bases de la chimie organique', matiere: 'Chimie', type: 'PDF', auteur: 'Prof. Martin' },
        { titre: 'Introduction à la physique quantique', matiere: 'Physique', type: 'Vidéo', auteur: 'Prof. Dupont' },
        { titre: 'Grammaire anglaise avancée', matiere: 'Anglais', type: 'PDF', auteur: 'Prof. Smith' }
    ];
    
    const container = document.getElementById('recommended-ressources');
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
                <a href="#" class="btn-small btn-edit">Voir</a>
                <a href="#" class="btn-small btn-delete">⭐</a>
            </div>
        </div>
    `).join('');
}

function getTypeIcon(type) {
    const icons = {
        'PDF': '📄',
        'Vidéo': '🎥',
        'Audio': '🎵',
        'Lien': '🔗'
    };
    return icons[type] || '📁';
}
