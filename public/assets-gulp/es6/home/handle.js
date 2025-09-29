let frmContactUser = document.querySelector('#frmContactUser');
if (frmContactUser) {
    frmContactUser.addEventListener("submit", (e) => {
        e.preventDefault();
        let formData = new FormData(e.currentTarget);
        try {
            if (!(formData.get('names').length > 0)) {
                throw 'Nombres y Apellidos es un campo obligatorio';
            }
            if (!(formData.get('email').length > 0)) {
                throw 'Correo es un campo obligatorio';                
            }
            if (!(formData.get('description').length > 0)) {
                throw 'Descripción es un campo obligatorio';
            }
            swal2.loading();
            fetch("/sendContact", {method: "POST", body: formData})
            .then(function(res){ return res.json(); })
            .then(function(rsp){
                swal2.show({
                    text: rsp.message,
                    icon: rsp.success ? 'success' : 'error',
                    showCancelButton: false,
                    onOk: function () {
                        if (rsp.success) {
                            frmContactUser.reset();
                        }
                    }
                });
            })
            .catch(function (err) {
                console.error(err);
                swal2.loading(false);
                alert('Hubo un error en el sistema.');
            });
        } catch (error) {
            swal2.show({
                text: error,
                icon: 'error',
                showCancelButton: false,
            }); 
        }
    });
}      


