$(document).ready(function() {

    $('.custom-file-input').off('change');
    $('.custom-file-input').on('change', function(e) {
        let filename = 'Seleccionar';
        if (e.target.files && e.target.files.length > 0) {
            filename = e.target.files[0].name;				
        }
        $(this).next().text(filename);
    });

});
