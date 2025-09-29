$('#sn-titulo').summernote({
  placeholder: 'Título principal',
  tabsize: 2,
  height: 150
});

$('#sn-stitulo').summernote({
  placeholder: 'Subtitulo',
  tabsize: 2,
  height: 150
});

$('#terminos_condiciones').summernote({
  placeholder: 'terminos y condiciones',
  tabsize: 2,
  height: 350
});

$('#sn-equipo').summernote({
  placeholder: 'Descripción',
  tabsize: 2,
  height: 150
});

$("#g_titulo_b").summernote({
  placeholder: "Ingresar Titulo",
  tabsize: 2,
  height: 150
})

$("#g_descripcion").summernote({
    placeholder: "Ingresar Descripcion",
    tabsize: 2,
    height: 150
})

if (document.getElementById("imgBanner")) {
document.getElementById("imgBanner").onchange = function(e) {
	let reader = new FileReader();
  
  reader.onload = function(){
    let preview = document.getElementById('previewBanner'),
        file_url = reader.result,
        tipo_file = file_url.split(";")[0].split("/")[0].split(":")[1],
        multi = "";
    
    if (tipo_file == 'image') {
      multi = document.createElement('img');
    }

    if (tipo_file == 'video') {
      multi = document.createElement('video');
    }
    multi.src = file_url;

    preview.innerHTML = '';
    preview.append(multi);
  };

  reader.readAsDataURL(e.target.files[0]);
}
}

let frmLanding = document.querySelector("#frmLanding");
if (frmLanding) {
    frmLanding.addEventListener("submit", (e) => {
    e.preventDefault();
    let formData = new FormData(e.currentTarget);
    fetch("/admin/home-update", {method: "POST", body: formData})
    .then(function(res){ return res.json(); })
    .then(function(rsp){
      if (rsp.success){
        Toast.fire({
          icon: 'success',
          title: 'Registro exitoso.'
        })
      }
    })
    .catch(function () {
        alert('Hubo un error en el sistema.');
    });
  });
}


var frmGlosario = document.querySelector("#frmGlosario");

frmGlosario && frmGlosario.addEventListener("submit", function(e) {
    e.preventDefault();
    var t = new FormData(e.currentTarget);
    fetch("/admin/glosario-update", {
        method: "POST",
        body: t
    }).then(function(e) {
        return e.json();
    }).then(function(e) {
        e.success && Toast.fire({
            icon: "success",
            title: "Registro exitoso."
        });
    }).catch(function() {
        alert("Hubo un error en el sistema.");
    });
});

let frmServicio = document.querySelector("#frmServicio");
if (frmServicio) {
    frmServicio.addEventListener("submit", (e) => {
    e.preventDefault();
    let formData = new FormData(e.currentTarget);
    let id_servicio = $("#id_servicio").val(),
        ruta = "/admin/servicio-update"; 
    if (!id_servicio) {
      ruta = "/admin/servicio-add";
    }
    fetch(ruta, {method: "POST", body: formData})
    .then(function(res){ return res.json(); })
    .then(function(rsp){
      if (rsp.success){
        let $form = $('#frmServicio');
        $form[0].reset();
        $("#modal_servicio").modal('hide');
        Toast.fire({
          icon: 'success',
          title: 'Registro exitoso.'
        })
        setTimeout(function(){  location.reload(); },1000); 
      }
    })
    .catch(function () {
        alert('Hubo un error en el sistema.');
    });
  });
}

function getItemServicio(id_item, id_form, id_modal, id_tipo = 0){
  if(!id_item){

    $('#titulo').summernote('destroy');
    $('#stitulo').summernote('destroy');

    $("#id_servicio").val('');
    $("#titulo").val('');
    $("#stitulo").val('');
    $("#tipo_id").val(id_tipo);
    $("#previewBanner img").attr("src", '');
    
    $('#titulo').summernote({
      placeholder: 'Título',
      tabsize: 2,
      height: 100
    });
    $('#stitulo').summernote({
      placeholder: 'Descripción',
      tabsize: 2,
      height: 100
    });

    $("#" + id_modal).modal('show');

  }else{

    let fdata = new FormData();
    fdata.append('id_servicio', id_item)
    fetch("/admin/get-servicio-item", {method: "POST", body: fdata})
    .then(function(res){ return res.json(); })
    .then(function(rsp){
      if (rsp.success){
        let $data = rsp.data;
        $('#titulo').summernote('destroy');
        $('#stitulo').summernote('destroy');

        $("#id_servicio").val($data.id);
        $("#titulo").val($data.titulo);
        $("#stitulo").val($data.stitulo);
        $("#tipo_id").val($data.tipo);
        $("#previewBanner img").attr("src", $data.icono);
        
        $('#titulo').summernote({
          placeholder: 'Subtitulo',
          tabsize: 2,
          height: 100
        });
        $('#stitulo').summernote({
          placeholder: 'Subtitulo',
          tabsize: 2,
          height: 100
        });

        $("#" + id_modal).modal('show');
      }
    })
    .catch(function () {
        alert('Hubo un error en el sistema.');
    });
  }
}

function deleteItemServicio(id_item, id_modal){
  Confirm.fire({
    title: '¿Estas seguro?',
    text: "¡Se eliminará el registro permanetemente!",
    icon: 'warning',
    showCancelButton: true,
    cancelButtonText: 'No, Cancelar',
    reverseButtons: false
  }).then((result) => {
    if (result.isConfirmed) {
      let fdata = new FormData();
      fdata.append('id_servicio', id_item)
      fetch("/admin/delete-servicio-item", {method: "POST", body: fdata})
      .then(function(res){ return res.json(); })
      .then(function(rsp){
        if (rsp.success){
          Confirm.fire(
            '¡Elimnado!',
            'El registro fue eliminado.',
            'success'
          )
          setTimeout(function(){  location.reload(); },1000); 
        }
      })
      .catch(function () {
          alert('Hubo un error en el sistema.');
      });
    } else{
      Confirm.fire(
        'Cancelado',
        'El registro está a salvo',
        'error'
      )
    }
  })
}

let frmTextTeam = document.querySelector("#frmTextTeam");
if (frmTextTeam) {
    frmTextTeam.addEventListener("submit", (e) => {
    e.preventDefault();
    let formData = new FormData(e.currentTarget);
    fetch("/admin/team-text-update", {method: "POST", body: formData})
    .then(function(res){ return res.json(); })
    .then(function(rsp){
      if (rsp.success){
        Toast.fire({
          icon: 'success',
          title: 'Registro exitoso.'
        })
      }
    })
    .catch(function () {
        alert('Hubo un error en el sistema.');
    });
  });
}

function getItemTeam(id_item, id_form, id_modal, id_tipo = 0){
  if(!id_item){
    console.log(id_modal)

    $("#id_team").val('');
    $("#name").val('');
    $("#order").val('');

    $("#" + id_modal).modal('show');

  }else{
    let fdata = new FormData();
    fdata.append('id_team', id_item)
    fetch("/admin/get-team-item", {method: "POST", body: fdata})
    .then(function(res){ return res.json(); })
    .then(function(rsp){
     
      if (rsp.success){

        let $data = rsp.data;
        $("#name").val('');
        $("#order").val('');

        $("#id_team").val($data.id);
        $("#name").val($data.name);
        $("#order").val($data.order);
        
        $("#" + id_modal).modal('show');
      }
    })
    .catch(function () {
        alert('Hubo un error en el sistema.');
    });
  }
}

let frmTeam = document.querySelector("#frmTeam");
if (frmTeam) {
    frmTeam.addEventListener("submit", (e) => {
    e.preventDefault();
    let formData = new FormData(e.currentTarget);
    let id_team = $("#id_team").val(),
        ruta = "/admin/team-update"; 
    if (!id_team) {
      ruta = "/admin/team-add";
    }
    fetch(ruta, {method: "POST", body: formData})
    .then(function(res){ return res.json(); })
    .then(function(rsp){
      if (rsp.success){
        console.log(rsp)
        let $form = $('#frmTeam');
        $form[0].reset();
        $("#modal_team").modal('hide');
        Toast.fire({
          icon: 'success',
          title: 'Registro exitoso.'
        })
        setTimeout(function(){  location.reload(); },1000); 
      }
    })
    .catch(function () {
        alert('Hubo un error en el sistema.');
    });
  });
}

function deleteItemTeam(id_item, id_modal){
  Confirm.fire({
    title: '¿Estas seguro?',
    text: "¡Se eliminará el registro permanetemente!",
    icon: 'warning',
    showCancelButton: true,
    cancelButtonText: 'No, Cancelar',
    reverseButtons: false
  }).then((result) => {
    if (result.isConfirmed) {
      

      let fdata = new FormData();
      fdata.append('id_team', id_item)
      fetch("/admin/team-delete", {method: "POST", body: fdata})
      .then(function(res){ return res.json(); })
      .then(function(rsp){
        if (rsp.success){
          Confirm.fire(
            '¡Elimnado!',
            'El registro fue eliminado.',
            'success'
          )
          setTimeout(function(){  location.reload(); },1000); 
        }
      })
      .catch(function () {
          alert('Hubo un error en el sistema.');
      });
    } else{
      Confirm.fire(
        'Cancelado',
        'El registro está a salvo',
        'error'
      )
    }
  })
}
//COSTO KAPITAL
let frmServicioCapital = document.querySelector("#frmServicioCapital");
if (frmServicioCapital) {
    frmServicioCapital.addEventListener("submit", (e) => {
    e.preventDefault();
    let formData = new FormData(e.currentTarget);
    let id_servicio = $("#id_servicio").val(),
        ruta = "/admin/servicio-costo-update"; 
    if (!id_servicio) {
      ruta = "/admin/servicio-costo-add";
    }
    fetch(ruta, {method: "POST", body: formData})
    .then(function(res){ return res.json(); })
    .then(function(rsp){
      console.log(rsp); return;
      if (rsp.success){
        let $form = $('#frmServicioCapital');
        $form[0].reset();
        $("#modal_servicio").modal('hide');
        Toast.fire({
          icon: 'success',
          title: 'Registro exitoso.'
        })
        setTimeout(function(){  location.reload(); },1000); 
      }
    })
    .catch(function () {
        alert('Hubo un error en el sistema.');
    });
  });
}

//CONTACTO
let frmContact = document.querySelector("#frmContact");
if (frmContact) {
    frmContact.addEventListener("submit", (e) => {
    e.preventDefault();
    let formData = new FormData(e.currentTarget);
    fetch("/admin/contacto-update", {method: "POST", body: formData})
    .then(function(res){ return res.json(); })
    .then(function(rsp){
      if (rsp.success){
        Toast.fire({
          icon: 'success',
          title: 'Registro exitoso.'
        })
      }
    })
    .catch(function () {
        alert('Hubo un error en el sistema.');
    });
  });
}

let frmInformationAdmin = document.querySelector("#frmInformationAdmin");
if (frmInformationAdmin) {
  frmInformationAdmin.addEventListener("submit", (e) => {
    e.preventDefault();
    let formData = new FormData(e.currentTarget);
    swal2.show({
      text: '¿Estás seguro de guardar cambios?',
      icon: 'question',
      showCancelButton: true,
      onOk: function () {
        fetch("/admin/information/save", {method: "POST", body: formData})
        .then(function(res){ return res.json(); })
        .then(function(rsp){
          swal2.show({
            icon: rsp.success ? 'success' : 'error', 
            html: rsp.message,
            onOk: function () {
              if (rsp.success) {
                swal2.loading();
                location.reload();
              }
            }
          });
        })
        .catch(function () {
            alert('Hubo un error en el sistema.');
        });
      }
    });
  });
}

//CREDITO
