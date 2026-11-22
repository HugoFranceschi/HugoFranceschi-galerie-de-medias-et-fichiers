// Confirmation avant les actions destructrices (suppression de média,
// suppression de catégorie). Le formulaire reste utilisable sans JavaScript.
document.addEventListener('DOMContentLoaded', function () {
  var fileInput = document.getElementById('media-files');

  if (fileInput) {
    fileInput.addEventListener('change', function () {
      if (fileInput.files.length > 100) {
        window.alert('100 fichiers maximum par envoi. S\u00e9lectionne moins de fichiers.');
        fileInput.value = '';
      }
    });
  }

  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      var message = form.getAttribute('data-confirm');

      if (message && !window.confirm(message)) {
        event.preventDefault();
      }
    });
  });
});
