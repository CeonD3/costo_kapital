var sweet2 = {
    str_loading : "Cargando...",
    intance : (typeof Sweetalert2 != "undefined") ? Sweetalert2 : {},
    global:{
        loading:function(args){
            var args = typeof args === "object" ? args : {},
                timer = typeof args.timer === "number" ? args.timer : null,
                id    = typeof args.id === "string" ? id : "loader",
                title = typeof args.title === "string" ? args.title : sweet2.str_loading;
            sweet2.loading({timer:timer,animation:false,title:title,text:"",_callback:function(){
                $(".swal2-container").attr("id",id);
                $(".swal2-container").css("background-color","#fff");
                $(".swal2-actions.swal2-loading").css("margin","0em");
            }});
        }
    },
    error: function(args){
        args.type = "error";
        args.timer = 2000;
        args.showConfirmButton = false;
        this.show(args)
    },
    success: function(args){
        var args = typeof args !== "object" ? {} : args;
        args.type = "success";
        args.timer = 2000;
        args.showConfirmButton = false;
        this.show(args)
    },
    question: function(args){
        var args = typeof args !== "object" ? {} : args;
        args.type = "question";
        args.showCancelButton = true;
        this.show(args)
    },
    loading: (function(){
        var state = true;
        return function(args){
            if(args === false){
                sweet2.hide();
                state = true;
            } else {
                if (state===true) {
                    state = false;
                    var args = typeof args !== "object" ? {} : args;
                    args.text = typeof args.text === "string" ? args.text : sweet2.str_loading;
                    args.loading = true;
                    args.showConfirmButton = false;
                    sweet2.show(args);
                }
            }
        };
      })(),
    /* loading: function(args){
        if(args === false){
            sweet2.hide();
        } else {
            var args = typeof args !== "object" ? {} : args;
            args.text = typeof args.text === "string" ? args.text : sweet2.str_loading;
            args.loading = true;
            args.showConfirmButton = false;
            sweet2.show(args);
        }
    }, */
    /**
     * obj Object obj {
     *  type:String,
     *  title:String,
     *  html:String,
     *  text:String,
     *  timer:Integer,
     *  showConfirmButton:Boolean,
     *  showCancelButton:Boolean,
     *  confirmButtonText:Boolean,
     *  cancelButtonText:Boolean,
     *  confirmButtonClass:Boolean,
     *  allowOutsideClick:Boolean,
     *  loading:Boolean,
     *  onClose:Function,
     *  onOk:Function
     *  onCancel:Function
     * }
     */
    show:function(obj){
        var _this = this;
        if(typeof obj === "object"){
            $.when()
            .then(function(){
                var sw = _this.intance({
                    type:obj.type,
                    title: obj.title,
                    html: obj.html,
                    text:obj.text,
                    timer: obj.timer,
                    showConfirmButton: (typeof obj.showConfirmButton === "boolean") ? obj.showConfirmButton : true,
                    showCancelButton: (typeof obj.showCancelButton === "boolean") ? obj.showCancelButton : false,
                    confirmButtonText:(obj.confirmButtonText)? obj.confirmButtonText : 'Aceptar',
                    cancelButtonText: (obj.cancelButtonText)? obj.cancelButtonText : 'Cancelar',
                    confirmButtonClass: (typeof obj.confirmButtonClass === "boolean")? obj.confirmButtonClass : false,
                    showCloseButton: (typeof obj.showCloseButton === "boolean")? obj.showCloseButton : false,
                    animation: (typeof obj.animation === "boolean") ? obj.animation : true,
                    allowOutsideClick : false,
                    onOpen: function(toast) {
                        if(obj.loading){
                            _this.intance.showLoading()
                        }
                        if ( typeof obj.onOpen === "function") {
                            obj.onOpen.call(null, toast);
                        }
                    },
                    onClose: function() {
                        if(typeof obj.onClose == "function" ){
                            obj.onClose.call();
                        }
                    }
                    }).then(function(result) {
                        if (result.value) {
                            if(typeof obj.onOk == "function" ){
                                obj.onOk.call();
                            }
                        } if(typeof obj.onCancel == "function" && result.dismiss=="cancel"){
                            obj.onCancel.call();
                        }
                    });
            })
            .then(function(){
                if(typeof obj._callback === "function"){
                    obj._callback.call();
                }
            })
            
        }
    },
    /**
     * args {
     *  position : 'bottom-end' //top,top-start,top-end,center,center-start,center-end,bottom,bottom-start,bottom-end
     * showConfirmButton: false,
     * timer:3000,fix
     * type:'success', //success,error,warning,info,question
     * title:'Proeducative !!'
     * onOpen: function(){}
     * }
     */
    toast:function(args){
        var args = typeof args != "undefined" ? args : {};
         SweetAlert.mixin({
            toast: true,
            position: (typeof args.position != "undefined") ? args.position : "bottom-end",
            showConfirmButton: (typeof args.showConfirmButton !== "undefined") ? args.showConfirmButton : false,
            timer: (typeof args.timer != "undefined") ? args.timer : 3000,
            onOpen: (toast) => {
                if (typeof args.onOpen === "function") {
                    args.onOpen.call(null, toast);
                }
            }
          })({
                type: (typeof args.type != "undefined") ? args.type : "success",
                title: (typeof args.title != "undefined") ? args.title : "Proeducative !!",
        });
    },
    hide:function(){
        this.intance.hideLoading();
        this.intance.close();
    }
}