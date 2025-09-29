$('#formCreateRecord').off('submit');
$('#formCreateRecord').on('submit', function (e) {
    e.preventDefault();
    let formData = new FormData(e.currentTarget);
    sweet2.loading(false);
    /*sweet2.question({
        html:'¿Estás seguro de iniciar un nuevo cálculo?',
        onOk:function(){*/
            sweet2.loading();
            fetch("/system/onCreateRecord", {method: "POST", body: formData})
            .then(function(res){ return res.json(); })
            .then(function(rsp){
                if (rsp.success) {
                    $('#modal-record').modal('hide');
                    location.href = '/calcula/'+rsp.data.report.code;
                } else {
                    sweet2.loading(false);
                    sweet2.show({type: rsp.success ? 'success' : 'error', text:rsp.message});
                }
            })
            .catch(function () {
                sweet2.show({type:'error', text:'Hubo un error en el sistema.'});
            });
        /*}
    });*/ 
});

$('#formCreateSectorial').off('submit');
$('#formCreateSectorial').on('submit', function (e) {
    e.preventDefault();
    let formData = new FormData(e.currentTarget);
    sweet2.loading(false);
    /*sweet2.question({
        html:'¿Estás seguro de iniciar un nuevo cálculo?',
        onOk:function(){*/
            sweet2.loading();
            fetch("/system/onCreateSectorial", {method: "POST", body: formData})
            .then(function(res){ return res.json(); })
            .then(function(rsp){
                if (rsp.success) {
                    $('#modal-record').modal('hide');
                    location.href = '/calcula/'+rsp.data.report.code;
                } else {
                    sweet2.loading(false);
                    sweet2.show({type: rsp.success ? 'success' : 'error', text:rsp.message});
                }
            })
            .catch(function () {
                sweet2.show({type:'error', text:'Hubo un error en el sistema.'});
            });
        /*}
    });*/ 
});

$('#formSaveUser').off('submit');
$('#formSaveUser').on('submit', function (e) {
    e.preventDefault();
    let formData = new FormData(e.currentTarget);
    sweet2.loading(false);
    sweet2.question({
        html:'¿Estás seguro de guardar cambios?',
        onOk:function(){
            sweet2.loading();
            fetch("/system/onSaveUserData", {method: "POST", body: formData})
            .then(function(res){ return res.json(); })
            .then(function(rsp){ 
                sweet2.loading(false);
                sweet2.show({type: rsp.success ? 'success' : 'error', text:rsp.message});
            })
            .catch(function () {
                sweet2.show({type:'error', text:'Hubo un error en el sistema.'});
            });
        }
    });    
});

$('#formSavePassword').off('submit');
$('#formSavePassword').on('submit', function (e) {
    e.preventDefault();
    let formData = new FormData(e.currentTarget);
    sweet2.loading(false);
    sweet2.question({
        html:'¿Estás seguro de cambiar su contraseña?',
        onOk:function(){
            sweet2.loading();
            fetch("/system/onSavePassword", {method: "POST", body: formData})
            .then(function(res){ return res.json(); })
            .then(function(rsp){ 
                if (rsp.success) {
                    document.getElementById("formSavePassword").reset();
                }
                sweet2.loading(false);
                sweet2.show({type: rsp.success ? 'success' : 'error', text:rsp.message});
            })
            .catch(function () {
                sweet2.show({type:'error', text:'Hubo un error en el sistema.'});
            });
        }
    });    
});

$('#formPaymentTransfer').off('submit');
$('#formPaymentTransfer').on('submit', function(e){
    e.preventDefault();
    let formData = new FormData(e.currentTarget);
    swal2.loading(false);
    swal2.show({
        icon: 'question',
        html:'¿Estás seguro de enviar?',
        showCancelButton: true,
        onOk:function(){
            swal2.loading();
            fetch("/pagos/transfer", {method: "POST", body: formData})
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
                swal2.show({icon:'error', text:'Hubo un error en el sistema.'});
            });
        }
    });
});

$('#frmRegisterReport').off('submit');
$('#frmRegisterReport').on('submit', function (e) {
    e.preventDefault();
    let formData = new FormData(e.currentTarget);
    sweet2.loading(false);
    /*sweet2.question({
        html:'¿Estás seguro de cambiar su contraseña?',
        onOk:function(){*/
            sweet2.loading();
            fetch("/system/reporte/register", {method: "POST", body: formData})
            .then(function(res){ return res.json(); })
            .then(function(rsp){ 
                if (rsp.success) {
                    document.getElementById("frmRegisterReport").reset();
                }
                sweet2.loading(false);
                sweet2.show({
                    type: rsp.success ? 'success' : 'error', 
                    text: rsp.message,
                    onOk: function () {
                        sweet2.loading();
                        window.location.reload();
                    }
                });
            })
            .catch(function (e) {
                sweet2.show({type:'error', text:'Hubo un error en el sistema.'});
            });
        /*}
    });*/ 
});

$('.ondocument').off('click');
$('.ondocument').on('click', function (e) {
    let id = $(this).data().id;
    let code = $(this).data().code;
    $('#frmRegisterReport2').find('#formCode').val(code);
    $('#frmRegisterReport2').find('#formDocumentId').val(id);
    $('#modal-register-report-2').modal();
});

$('#frmRegisterReport2').off('submit');
$('#frmRegisterReport2').on('submit', function (e) {
    e.preventDefault();
    let formData = new FormData(e.currentTarget);
    sweet2.loading(false);
    let code = formData.get('code');
    let documentId = formData.get('documentId');
    /*sweet2.question({
        html:'¿Estás seguro de cambiar su contraseña?',
        onOk:function(){*/
            sweet2.loading();
            fetch("/system/reporte/register", {method: "POST", body: formData})
            .then(function(res){ return res.json(); })
            .then(function(rsp){ 
                if (rsp.success) {
                    document.getElementById("frmRegisterReport").reset();
                }
                sweet2.loading(false);
                sweet2.show({
                    type: rsp.success ? 'success' : 'error', 
                    text: rsp.message,
                    onOk: function () {
                        sweet2.loading();
                        window.location.href = '/reporte/' + code + '/documento/' + documentId + '/download';
                    }
                });
            })
            .catch(function (e) {
                sweet2.show({type:'error', text:'Hubo un error en el sistema.'});
            });
        /*}
    });*/    
});
