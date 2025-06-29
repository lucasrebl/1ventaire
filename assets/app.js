import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
import { initDropdowns } from './js/dropdown.js';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

// S'assurer que les dropdowns sont initialisés après le chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Attendre un court instant pour s'assurer que Bootstrap est chargé
    setTimeout(initDropdowns, 200);
});
