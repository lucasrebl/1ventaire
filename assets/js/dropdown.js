// Fonction pour initialiser tous les dropdowns Bootstrap
function initDropdowns() {
    // Vérifier si Bootstrap est chargé
    if (typeof bootstrap !== 'undefined') {
        // Attacher les événements aux dropdowns
        document.querySelectorAll('.dropdown-toggle').forEach(function(dropdown) {
            dropdown.addEventListener('click', function(e) {
                e.preventDefault();
                if (bootstrap.Dropdown) {
                    var dropdownInstance = new bootstrap.Dropdown(dropdown);
                    dropdownInstance.toggle();
                }
            });
        });
        
        console.log('Dropdowns initialisés avec succès');
    } else {
        console.error('Bootstrap n\'est pas chargé. Les dropdowns ne fonctionneront pas correctement.');
    }
}

// Exécuter l'initialisation quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    // Attendre un court instant pour s'assurer que Bootstrap est chargé
    setTimeout(initDropdowns, 100);
});

// Exporter la fonction pour pouvoir l'utiliser ailleurs si nécessaire
export { initDropdowns };
