const AppDesignAdmin = function () {
    let __this = this;
    /*__this.index = function (args) {
        new Vue({
            el: '#AppDesignAdmin',
            data() {
                return {
                    products: args.products,
                    product: Object.create({}),
                    index: -1,
                    company_id: args.company_id,
                }
            },
            created: function() {
            },
            mounted: function () {
                this.onLoad();
            },
            watch: {
            },
            methods: {
                onLoad:function () {
                    this.onDataTable();
                },
                onDataTable: function () {
                    $('#kt_datatable').DataTable({
                        language: ovars.dataTable.language
                    });                    
                },
                onDelete: function (index) {
                    let _this = this,
                    product = this.products[index],
                    formData = new FormData();
                    formData.append('id', product.id);
                    _this.index = index;
                    swal2.show({
                        text: '¿Estás seguro de eliminar?',
                        icon: 'question',
                        showCancelButton: true,
                        onOk: function () {
                            swal2.loading();
                            fetch("/admin/product/remove", {method: "POST", body: formData})
                            .then(function(res){ return res.json(); })
                            .then(function(rsp){
                                if (rsp.success) {
                                    $('#kt_datatable').DataTable().destroy();
                                    _this.products.splice(_this.index, 1);
                                    setTimeout(() => {
                                        _this.onDataTable();
                                        swal2.show({
                                            text: rsp.message,
                                            icon: rsp.success ? 'success' : 'error',
                                            showCancelButton: false
                                        });
                                    }, 10);    
                                } else {
                                    swal2.show({
                                        text: rsp.message,
                                        icon: rsp.success ? 'success' : 'error',
                                        showCancelButton: false
                                    });
                                }
                            })
                            .catch(function (err) {
                                console.error(err);
                                swal2.loading(false);
                                alert('Hubo un error en el sistema.');
                            });
                        }
                    });
                }
            }
        });
    }*/
    __this.form = function (args) {
        new Vue({
            el: '#AppDesignFormAdmin',
            data() {
                return {
                    design: Object.create(args.design),
                    structure: args.structure 
                }
            },
            created: function() {
            },
            mounted: function () {
                this.onLoad();
            },
            watch: {
            },
            methods: {
                onFormData: function (e) {  
                    e.preventDefault();
                    let _this = this;
                    let formData = new FormData(e.currentTarget);
                    swal2.show({
                        text: '¿Estás seguro de guardar cambios?',
                        icon: 'question',
                        showCancelButton: true,
                        onOk: function () {
                            swal2.loading();
                            fetch("/admin/report/save", {method: "POST", body: formData})
                            .then(function(res){ return res.json(); })
                            .then(function(rsp){
                                swal2.show({
                                    text: rsp.message,
                                    icon: rsp.success ? 'success' : 'error',
                                    showCancelButton: false,
                                    onOk: function () {
                                        if (rsp.data.design.id != undefined && _this.design.id == undefined) {
                                            $("#img-file").val('');
                                            swal2.loading();
                                            // location.href = '/admin/reportes/editar/'+rsp.data.design.id;
                                            location.href = rsp.data.reload;
                                        }
                                    }
                                });
                            })
                            .catch(function (err) {
                                console.error(err);
                                swal2.loading(false);
                                alert('Hubo un error en el sistema.');
                            });
                        }
                    });
                },
                onLoad:function () {
                    this.onSummernote();
                    this.onEvent();
                },
                onEvent: function(params) {
                    $('#img-file').on('change', function (e) {
                    });
                },
                onSummernote: function (scope) {
                    let oSummernote = {
                        fontSizes: ['8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '20','24', '26', '28', '32'],
                        height: 80,
                        popover: {
                            image: [],
                            link: [],
                            air: []
                        },
                        tabsize: 2,
                        toolbar: [
                            ['style', ['style']],
                            ['font-style', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
                            ['font', ['fontname']],
                            ['font-size',['fontsize']],
                            ['font-color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture', 'hr']],
                            ['misc', ['fullscreen', 'codeview', 'help']]
                        ]
                    };

                    $('#header_summernote').summernote(oSummernote);
                    $('#footer_summernote').summernote(oSummernote);

                    oSummernote.height = 600;
                    oSummernote.callbacks = {
                        onPaste: function (e) {
                            var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                            e.preventDefault();
                            // setTimeout firefox
                            setTimeout(function(){
                                document.execCommand( 'insertText', false, bufferText );
                            }, 10);
                        }
                    }
                    $('#body_summernote').summernote(oSummernote);
                }
            }
        });
    };
    __this.list = function () {
        $('#table-design').DataTable({
            language: {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "No hay elementos por mostrar",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix": "",
                "sSearch": "Buscar:",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            },
            ordering: false,
            reponsive: true
        });

        $('.btn-delete').off('click');
        $('.btn-delete').on('click', function(){
            let id = $(this).attr('data-id');
            console.log(id);
            let formData = new FormData();
            formData.append('id', id);
            swal2.loading(false);
            swal2.show({
                icon: 'question',
                html:'¿Estás seguro de eliminar este elemento?',
                showCancelButton: true,
                onOk:function(){
                    swal2.loading();
                    fetch("/admin/report/remove", {method: "POST", body: formData})
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
    }
}

window.AppDesignAdmin = new AppDesignAdmin();