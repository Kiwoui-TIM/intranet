/* globals Chart:false, feather:false */

(function () {
  'use strict'

  feather.replace();

  const task_delete_btns = document.querySelectorAll('[name="delete_task"]');
  for (let i = 0; i < task_delete_btns.length; i++) {
    task_delete_btns[i].addEventListener('click', function(e) {
      let confirmation = confirm('Êtes-vous certain de vouloir supprimer cette tâche ?');
      if (!confirmation) {
        e.preventDefault();
      }
    });
}
  const milestone_delete_btns = document.querySelectorAll('[name="delete_milestone"]');
  for (let i = 0; i < milestone_delete_btns.length; i++) {
    milestone_delete_btns[i].addEventListener('click', function(e) {
      let confirmation = confirm('Êtes-vous certain de vouloir supprimer ce jalon ?');
      if (!confirmation) {
        e.preventDefault();
      }
    });
}
  const project_delete_btns = document.querySelectorAll('[name="delete_project"]');
  for (let i = 0; i < project_delete_btns.length; i++) {
    project_delete_btns[i].addEventListener('click', function(e) {
      let confirmation = confirm('Êtes-vous certain de vouloir supprimer ce projet ?');
      if (!confirmation) {
        e.preventDefault();
      }
    });
}
}())
