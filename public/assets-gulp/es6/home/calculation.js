const AppCalculation = function() {
    let __this = this;
    __this.init = function (args) {
        console.log(args);
        let self = null;
        new Vue({
            el: '#AppCalculation',
            data() {
                return {
                    bonus: 'Bono EE.UU',
                    code: args.code,
                    instrument: args.system.instrument,
                    instruments: args.system.instruments,
                    graph: args.system.graph,
                    redirect: '#',
                    sector: args.system.sector
                }
            },
            created: function() {
                self = this;
            },
            mounted: function () {
                this.onload();
            },
            watch: {
    
            },
            computed: {
    
            },
            methods: {
                onChangeGraph: function (e) {
                    let value = e.target.value;
                    let _this = this;
                    let formData = new FormData();
                    formData.append('order', value);
                    formData.append('code', _this.code);
                    sweet2.loading(false); sweet2.loading();
                    fetch("/system/filterRiskLevel", {method: "POST", body: formData })
                    .then(function(res){ return res.json(); })
                    .then(function(rsp){
                        if (rsp.success) {
                            _this.onGraph(rsp.data.system.graph);
                        }
                        sweet2.loading(false);
                    })
                    .catch(function () {
                        sweet2.show({type:'error', text:'Hubo un error en el sistema.'});
                    });
                },
                onCalculation: function (e) {
                    e.preventDefault();
                    // let formData = new FormData(e.currentTarget);
                    let _this = this;
                    let formData = new FormData(document.getElementById('frmCalculation'));
                    formData.append('code', this.code);
                    let instrument = formData.get('instrument');
                    if (!_this.instruments.includes(instrument)) {
                        sweet2.show({type: 'error', text:'El instrumento es un valor incorrecto'});
                    }
                    sweet2.loading(false); sweet2.loading();
                    fetch("/system/onCalculation", {method: "POST", body: formData })
                    .then(function(res){ return res.json(); })
                    .then(function(rsp){
                        sweet2.loading(false);
                        if (rsp.success) {
                            // _this.onRedirect();
                            _this.onload();
                            if (instrument != 'Bono EE.UU') {
                                $('#modal-info-1').modal('show');
                            }
                            // location.href = rsp.data.redirect;
                        } else {
                            sweet2.show({type: rsp.success ? 'success' : 'error', text:rsp.message});
                        }
                    })
                    .catch(function () {
                        sweet2.show({type:'error', text:'Hubo un error en el sistema.'});
                    });
                },
                onload: function () {
                    this.onGraph(this.graph);
                    this.onRedirect();
                },
                onRedirect: function () {
                    let instruments = document.getElementsByName('instrument');
                    for (let i = 0, length = instruments.length; i < length; i++) {
                        if (instruments[i].checked) {
                            if (i == 0) {
                                this.redirect = '/rendimiento/' + this.code;
                            } else {
                                this.redirect = '/flujos/' + this.code;
                            }
                          break;
                        }
                    }
                },
                onGraph: function (data) {
                    am4core.ready(function() {
                        AppChartSystem.onColumnAll({scope:'myChart', data:data, selected: self.sector});
                    });
                },
            }
        });
    }
}

window.AppCalculation = new AppCalculation();