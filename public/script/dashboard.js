/* globals Chart:false, feather:false */

(function () {
  'use strict'

  feather.replace();

  const delete_btns = document.querySelectorAll('[name="delete_task"]');
  for (let i = 0; i < delete_btns.length; i++) {
    delete_btns[i].addEventListener("click", function(e) {
      let confirmation = confirm('Êtes-vous certain de vouloir faire supprimer cela ?');
      if (!confirmation) {
        e.preventDefault();
      }
    });
}
}())
