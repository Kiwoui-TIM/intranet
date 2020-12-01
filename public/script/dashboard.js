// Fonction qui s'exécute automatiquement, permet d'éviter les variables
// globales. Voir : https://stackoverflow.com/questions/592396/what-is-the-purpose-of-a-self-executing-function-in-javascript
(function () {
  // Voir : https://www.w3schools.com/js/js_strict.asp
  // Exécute le script en mode strict, qui force à programmer
  // de manière plus "clean"
  'use strict';

  // Tous les éléments utilisant feather pour leurs icônes
  // (attribut data-feather) voient leur contenu remplacé par un svg de l'icône.
  feather.replace();

  const deleteButtons = document.querySelectorAll('[name="delete_item"]');
  deleteButtons.forEach(button => {
    button.addEventListener('click', confirmDeletion);
  });

  function confirmDeletion(event) {
    let confirmation = confirm('Êtes-vous certain de vouloir supprimer cet item ?');
    if (!confirmation) {
      event.preventDefault();
    }
  }
}())
