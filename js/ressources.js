document.addEventListener('DOMContentLoaded', function() {
    loadRessources();
});

function loadRessources() {
    const search = document.getElementById('search_input').value;
    const matiere = document.getElementById('filter_matiere').value;
    const niveau = document.getElementById('filter_niveau').value;
    const type = document.getElementById('filter_type').value;
    
    // Récupérer les paramètres de l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const mes = urlParams.get('mes') || '';
    const favoris = urlParams.get('favoris') || '';
    
    let url = `../php/get_ressources.php?search=${encodeURIComponent(search)}&matiere=${matiere}&niveau=${niveau}&type=${type}`;
    
    if (mes) {
        url += `&mes=${mes}`;
    }
    
    if (favoris) {
        url += `&favoris=${favoris}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            displayRessources(data);
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('ressources_grid').innerHTML = '<p class="no-results">Erreur lors du chargement des ressources</p>';
        });
}

function displayRessources(ressources) {
    const container = document.getElementById('ressources_grid');
    
    if (ressources.length === 0) {
        container.innerHTML = '<p class="no-results">Aucune ressource trouvée</p>';
        return;
    }
    
    container.innerHTML = ressources.map(r => `
        <div class="ressource-card">
            <div class="ressource-header">
                <div class="ressource-type-icon">${getTypeIcon(r.type)}</div>
                <div class="ressource-title">
                    <h3>${r.titre}</h3>
                    <div class="ressource-meta">
                        <span>👤 ${r.auteur}</span>
                        <span>📅 ${r.date}</span>
                    </div>
                </div>
            </div>
            <p class="ressource-description">${r.description}</p>
            <div class="ressource-footer">
                <div class="ressource-tags">
                    <span class="tag">${capitalizeFirst(r.matiere)}</span>
                    <span class="tag">${capitalizeFirst(r.niveau)}</span>
                </div>
                <a href="${getRessourceUrl(r)}" class="btn-view" target="_blank">Voir</a>
            </div>
        </div>
    `).join('');
}

function getTypeIcon(type) {
    const icons = {
        'pdf': '📄',
        'PDF': '📄',
        'video': '🎥',
        'vidéo': '🎥',
        'audio': '🎵',
        'lien': '🔗'
    };
    return icons[type] || '📁';
}

function getRessourceUrl(ressource) {
    if (ressource.type === 'lien') {
        return ressource.fichier;
    }
    return `../${ressource.fichier}`;
}

function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function searchRessources() {
    loadRessources();
}

function filterRessources() {
    loadRessources();
}

function resetFilters() {
    document.getElementById('search_input').value = '';
    document.getElementById('filter_matiere').value = '';
    document.getElementById('filter_niveau').value = '';
    document.getElementById('filter_type').value = '';
    loadRessources();
}

// Recherche en temps réel avec délai
let searchTimeout;
document.getElementById('search_input').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadRessources();
    }, 300);
});
