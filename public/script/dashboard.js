(function () {
  'use strict'

  feather.replace();

  const delete_btns = document.querySelectorAll('[name="delete_item"]');
  for (let i = 0; i < delete_btns.length; i++) {
    delete_btns[i].addEventListener('click', function(e) {
      let confirmation = confirm('Êtes-vous certain de vouloir supprimer cet item ?');
      if (!confirmation) {
        e.preventDefault();
      }
    });
  }
}())
