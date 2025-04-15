document.querySelectorAll('.needs-validation').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            
            // Forcer l'affichage des erreurs pour les selects
            form.querySelectorAll('select:invalid').forEach(select => {
                select.classList.add('is-invalid');
            });
        }
        form.classList.add('was-validated');
    }, false);
});