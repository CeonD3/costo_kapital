$("#sing-out").click(function() {
  fetch("/sing-out", {method: "POST", body: {}})
    .then(function(res){ return res.json(); })
    .then(function(rsp){
      if (!rsp.success){
          alert(rsp.message); return;
      }
      location.href ="/";
    })
    .catch(function () {
        alert('Hubo un error en el sistema.');
    });
});

function registrar (){
	event.preventDefault();
	$("#iniciar_sesion").css("display","none");
	$("#registrar_cuenta").css("display","block");

}

function init_sesion (){
	event.preventDefault();
	$("#registrar_cuenta").css("display","none");
	$("#iniciar_sesion").css("display","block");

}

let frmLogin = document.querySelector("#frmLogin");
if (frmLogin) {
  frmLogin.addEventListener("submit", (e) => {
    e.preventDefault();
    sweet2.loading();
    let formData = new FormData(e.currentTarget);
    fetch("/postLogin", {method: "POST", body: formData})
    .then(function(res){ return res.json(); })
    .then(function(rsp){        
      if (!rsp.success){
        sweet2.loading(false);
        sweet2.show({type:'error', text:rsp.message});
         return;
      }
      location.href ="/historial";
    })
    .catch(function () {
        sweet2.loading(false);
        sweet2.show({type:'error', text:'Hubo un error en el sistema.'});
    });
  });
}

let frmRegister = document.querySelector("#frmRegister");
if (frmRegister) {
  frmRegister.addEventListener("submit", (e) => {
    e.preventDefault();
    swal2.loading();
    let formData = new FormData(e.currentTarget);
    fetch("/register-user", {method: "POST", body: formData})
    .then(function(res){ return res.json(); })
    .then(function(rsp){
        sweet2.loading(false);
        if (!rsp.success){
            alert(rsp.message); return;
        }
        sweet2.show({
            // text: 'Se registro correctamente, ahora inicie sesión por favor.',
            text: rsp.message,
            type: rsp.success ? 'success' : 'error',
            showCancelButton: false,
            onOk: function () {
                if (rsp.success) {
                    location.href ="/login";
                }
            }
        });
    })
    .catch(function () {
        sweet2.loading(false);
        sweet2.show({type:'error', text:'Hubo un error en el sistema.'});
        // alert('Hubo un error en el sistema.');
    });
  });
}

function copiarAlPortapapeles(id_elemento) {
  // Crea un campo de texto "oculto"
  var aux = $("#" + id_elemento)


  // Añade el campo a la página


  // Selecciona el contenido del campo
  aux.select();

  // Copia el texto seleccionado
  document.execCommand('copy');

  // Elimina el campo de la página

}

let frmForgotPasswordUser = document.querySelector('#frmForgotPasswordUser');
if (frmForgotPasswordUser) {
    frmForgotPasswordUser.addEventListener("submit", (e) => {
        e.preventDefault();
        let formData = new FormData(e.currentTarget);
        try {
            if (!(formData.get('email').length > 0)) {
                throw 'Correo es un campo obligatorio';                
            }
            swal2.loading();
            fetch("/forgotPasswordUser", {method: "POST", body: formData})
            .then(function(res){ return res.json(); })
            .then(function(rsp){
                swal2.show({
                    text: rsp.message,
                    icon: rsp.success ? 'success' : 'error',
                    showCancelButton: false,
                    onOk: function () {
                        if (rsp.success) {
                            frmForgotPasswordUser.reset();
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

let frmRecoverPassword = document.querySelector('#frmRecoverPassword');
if (frmRecoverPassword) {
    frmRecoverPassword.addEventListener("submit", (e) => {
        e.preventDefault();
        let formData = new FormData(e.currentTarget);
        let url = frmRecoverPassword.getAttribute('action');
        try {
            if (!(formData.get('password').length > 0)) {
                throw 'La contraseña es un campo obligatorio';                
            }
            if (formData.get('password').length <= 3) {
                throw 'La contraseña debe de tener como minimo una longitd de 4 carácteres';                
            }
            if (!(formData.get('repeat_password').length > 0)) {
                  throw 'Debe de repetir la contraseña';                
            }
            if (formData.get('password') != formData.get('repeat_password')) {
                throw 'Las contraseñas deben de ser iguales';                
            }
            swal2.loading();
            fetch(url, {method: "POST", body: formData})
            .then(function(res){ return res.json(); })
            .then(function(rsp){
                swal2.show({
                    text: rsp.message,
                    icon: rsp.success ? 'success' : 'error',
                    showCancelButton: false,
                    onOk: function () {
                        if (rsp.success) {
                            window.location.href = '/login';
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
