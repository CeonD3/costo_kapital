let AppPaymentAdmin = function() {

    this.init = function () {
        let AppPaymentAdmin = $('#AppPaymentAdmin'),
        btnAddMobile = AppPaymentAdmin.find('#btnAddMobile'),
        panelMobile = AppPaymentAdmin.find('#panelMobile'),
        panelBank = AppPaymentAdmin.find('#panelBank'),
        btnAddBank = AppPaymentAdmin.find('#btnAddBank'),
        templateMobile = AppPaymentAdmin.find('#template-mobile'),
        templateBank = AppPaymentAdmin.find('#template-bank'),
        formTransfer = AppPaymentAdmin.find('.formTransfer');
    
        btnAddMobile.off('click');
        btnAddMobile.on('click', function() {
            console.log(templateMobile.html());
            panelMobile.append(templateMobile.html());
            delete_mobile();
        });

        btnAddBank.off('click');
        btnAddBank.on('click', function() {
            panelBank.append(templateBank.html());
            delete_bank();
        });

        formTransfer.off('submit');
        formTransfer.on('submit', function(e){
            e.preventDefault();
            let formData = new FormData(e.currentTarget);
            swal2.loading(false);
            swal2.show({
                icon: 'question',
                html:'¿Estás seguro de guardar cambios?',
                showCancelButton: true,
                onOk:function(){
                    swal2.loading();
                    fetch("/admin/pagos/save", {method: "POST", body: formData})
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
        
        function delete_mobile(){
            $('.btnDeleteMobile').off('click');
            $('.btnDeleteMobile').on('click', function() {
                $(this).parent().parent().remove();
            });
        }

        function delete_bank(){
            $('.btnDeleteBank').off('click');
            $('.btnDeleteBank').on('click', function() {
                $(this).parent().parent().remove();
            });
        }
        
        delete_mobile();
        delete_bank();
    }
}

window.AppPaymentAdmin = new AppPaymentAdmin();