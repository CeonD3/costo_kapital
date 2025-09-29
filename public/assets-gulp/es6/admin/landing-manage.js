// ADMIN TEMPLATE
const AppAdminTemplate = function () {
    let __this = this;
    __this.init = function (args) {
      new Vue({
        el: '#AppAdminTemplate',
        data() {
            return {
              swal2: swal2,
              templates: args.templates,
              template: Object.create({}),
              index: -1
            }
        },
        created: function() {
        },
        mounted: function () {
        },
        watch: {
        },
        computed: {
        },
        methods: {
          onSave: function (e) {
            e.preventDefault();
            let _this = this,
            formData = new FormData(e.currentTarget),
            status = Number(formData.get('status'));
            _this.swal2.show({
            text: '¿Estás seguro de guardar cambios?',
            icon: 'question',
            showCancelButton: true,
            onOk:function() {
                _this.swal2.loading();
                fetch("/admin/manageTemplate", {method: "POST", body: formData })
                .then(function(res){ return res.json(); })
                .then(function(rsp){
                if (rsp.success){
                    document.getElementById("frmSave").reset();
                    /*_this.template = Object.create({});
                    if (status == 1) {
                        for (let i = 0; i < _this.templates.length; i++) {
                            _this.templates[i].status = 0;
                        }  
                    }
                    if (_this.index >= 0) {
                        Object.assign(_this.templates[_this.index], rsp.data.template);
                    } else {
                        _this.templates.push(Object.create(rsp.data.template));
                    }*/
                    $('#mdlForm').modal('hide');  
                }
                _this.swal2.show({
                  icon: rsp.success ? 'success' : 'error', 
                  text:rsp.message, 
                  onOk: function () {
                    if (rsp.success) {
                      _this.swal2.loading();
                      window.location.reload();
                    }
                  }});
                })
                .catch(function (err) {
                    console.error(err);
                    _this.swal2.show({icon:'error', text:'Hubo un error en el sistema.'});
                });
            }
            });
          },
          onForm:function (index) {
            document.getElementById("frmSave").reset();          
            this.index = index;
            this.template = index === -1 ? Object.create({}) : Object.create(this.templates[index]);
            document.getElementById('lblFile').innerText = this.template.file ? this.template.file : 'Seleccionar Archivo';  
            $('#mdlForm').modal({backdrop : "static", keyboard: false});          
          },
          onDelete: function (index) {
            let _this = this,
            formData = new FormData(),
            id = this.templates[index].id;
            formData.append('id', id);
            _this.swal2.show({
                text: '¿Estás seguro de eliminar esta plantilla?',
                icon: 'question',
                showCancelButton: true,
                onOk:function() {
                    _this.swal2.loading();
                    fetch("/admin/removeTemplate", {method: "POST", body: formData })
                    .then(function(res){ return res.json(); })
                    .then(function(rsp){
                        if (rsp.success) {
                            _this.templates.splice(index,1);
                        }
                        _this.swal2.show({icon: rsp.success ? 'success' : 'error', text:rsp.message});
                    })
                    .catch(function (err) {
                        console.error(err);
                        _this.swal2.show({icon:'error', text:'Hubo un error en el sistema.'});
                    });
                }
            });
          },
          onReadFile: function (e) {
            if (e.target.files.length > 0) {
                document.getElementById('lblFile').innerText = e.target.files[0].name;
            }
          }
        }
      });
    };
  }
  window.AppAdminTemplate = new AppAdminTemplate();