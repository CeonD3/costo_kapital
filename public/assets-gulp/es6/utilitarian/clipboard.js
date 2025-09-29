function copyClipboardReport(id_elemento) {
    // Crea un campo de texto "oculto"
    var aux = $("#" + id_elemento);
    // Añade el campo a la página
    // Selecciona el contenido del campo
    aux.select();
    // Copia el texto seleccionado
    document.execCommand('copy');
    // Elimina el campo de la página
    alertify.message('Acaba de copiar el valor ' + aux.val());
}