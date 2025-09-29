const AppRecordCost = function() {
    let __this = this;
    __this.init = function (args) {
        new Vue({
            el: '#AppRecordCost',
            data() {
                return {
                    reports: args ? args.reports : [],
                    template: Object.create(args ? args.template : {})
                }
            },
            created: function() {
    
            },
            mounted: function () {
                this.dataTable();
            },
            watch: {
    
            },
            computed: {
    
            },
            methods: {
                onFormRecord: function () {
                    $('#modal-record').modal();
                },
                onCreateRecord: function(e) {
                    e.preventDefault();
                    let formData = new FormData(e.currentTarget);
                    sweet2.loading(false);
                    sweet2.question({
                        html:'¿Estás seguro de iniciar un nuevo cálculo?',
                        onOk:function(){
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
                        }
                    });
                },
                onDelete: function (code, index) {
                    let othis = this;
                    let formData = new FormData();
                    formData.append('code', code);
                    sweet2.question({
                        html:'¿Estás seguro de eliminar?',
                        onOk:function(){
                            sweet2.loading(false);sweet2.loading();
                            fetch("/system/deleteReport", {method: "POST", body: formData})
                            .then(function(res){ return res.json(); })
                            .then(function(rsp){
                                if (rsp.success) {
                                    $('#table-report').DataTable().destroy();
                                    othis.reports.splice(index, 1);
                                    setTimeout(() => {
                                        othis.dataTable()
                                        sweet2.show({type: rsp.success ? 'success' : 'error', text:rsp.message});
                                    }, 50);
                                } else {
                                    sweet2.show({type: rsp.success ? 'success' : 'error', text:rsp.message});  
                                }
                            })
                            .catch(function () {
                                sweet2.show({type:'error', text:'Hubo un error en el sistema.'});
                            });
                        }
                    });
                },
                onNewVersion: function (code, index) {
                    let _this = this;
                    let formData = new FormData();
                    formData.append('code', code);
                    sweet2.question({
                        html:'¿Estás seguro de actualizar a la nueva versión?',
                        onOk:function(){
                            sweet2.loading(false);sweet2.loading();
                            fetch("/system/updateNewVersion", {method: "POST", body: formData})
                            .then(function(res){ return res.json(); })
                            .then(function(rsp){
                                if (rsp.success) {
                                    $('#table-report').DataTable().destroy();
                                    Object.assign(_this.reports[index], rsp.data.report);
                                    setTimeout(() => {
                                        _this.dataTable()
                                        sweet2.show({type: rsp.success ? 'success' : 'error', text:rsp.message});
                                    }, 50);
                                } else {
                                    sweet2.show({type: rsp.success ? 'success' : 'error', text:rsp.message});  
                                }
                            })
                            .catch(function () {
                                sweet2.show({type:'error', text:'Hubo un error en el sistema.'});
                            });
                        }
                    });
                },
                dataTable: function () {
                    $('#table-report').DataTable( {
                        "language": {
                            "lengthMenu": "Mostrar _MENU_ registros por página.",
                            "zeroRecords": "No se encontraron registros.",
                            "info": "Se muestra _PAGE_ de _PAGES_",
                            "infoEmpty": "No se encontraron	coincidencias",
                            "infoFiltered": "(Busqueda en _MAX_ del total de registros)",
                            "search": "Buscar:"
                        },
                        ordering:false, 
                    } );
                }
            }
        });
    }
}


window.AppRecordCost = new AppRecordCost();