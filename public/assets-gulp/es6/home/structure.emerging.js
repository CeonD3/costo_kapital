const AppStructureEmerging = function() {
    let __this = this;
    __this.init = function (args) {
        new Vue({
            el: '#AppStructureEmerging',
            data() {
                return {
                    code: args.code,
                    graph: args.system.graph,
                    general: args.system.general,
                    finance: args.system.finance,
                    countries: args.system.countries,
                    country: args.system.country,
                    deuda: args.system.finance.deuda.value,
                    capital: args.system.finance.capital.value,
                    devaluacion: 0
                }
            },
            created: function() {
    
            },
            mounted: function () {
                this.onLoad();
            },
            watch: {
    
            },
            computed: {
                myCapital: function() {
                    return this.capital + ' %';
                }
            },
            methods: {
                onLoad: function () {
                    for (let i = 0; i < this.general.length; i++) {
                        if (this.general[i].input == 1) {
                            this.devaluacion = this.general[i].value;
                        }
                        
                    }
                    // this.onGraphGroup(this.graph.general);
                    this.onGraphGroup(this.graph.riesgo);
                },
                onCalculation: function (e) {
                    this.capital = 100 - this.deuda;
                    this.capital.toFixed(2);
                },
                onDevaluation: function (e) {
                    e.preventDefault();
                    let form = document.getElementById('formDevaluation');
                    let _this = this;
                    let formData = new FormData(form);
                    formData.append('code', this.code);
                    /*sweet2.question({
                        html:'¿Estás seguro de guardar cambios?',
                        onOk:function(){*/
                            sweet2.loading(false); 
                            sweet2.loading();
                            fetch("/system/onDevaluationEmerging", {method: "POST", body: formData })
                            .then(function(res){ return res.json(); })
                            .then(function(rsp){
                                //sweet2.loading(false);
                                if (rsp.success) {
                                    let report = rsp.data.report;
                                    if (report.type_id == 1) { // empresa
                                        window.location.href = '/estructura-empresa/'+_this.code;
                                    } else {
                                        window.location.href = '/documentos/'+_this.code;
                                    }
                                    /*let system = rsp.data.system; 
                                    _this.graph = system.graph;
                                    _this.general = system.general;
                                    _this.finance = system.finance;
                                    _this.country = system.country;*/
                                    // _this.onGraphGroup(system.graph.general);
                                    // _this.onGraphGroup(system.graph.riesgo);
        
                                } else {
                                    sweet2.show({type: rsp.success ? 'success' : 'error', text:rsp.message});
                                }
                            })
                            .catch(function (e) {
                                console.error(e);
                                sweet2.loading(false); 
                                sweet2.show({type:'error', text:'Hubo un error en el sistema.'});
                            });
                        /*}
                    });*/
                },
                onCountry: function (e) {
                    let _this = this;
                    let country = e.target.value;
                    let isValid = false;
                    for (let i = 0; i < _this.countries.length; i++) {
                        if(country.trim() == _this.countries[i].trim()) {
                            isValid = true;
                        }
                    }
                    if (!isValid){
                        sweet2.show({type: 'error', text: 'Seleccione un país existente.'});
                        return;
                    }
                    let formData = new FormData();
                    formData.append('code', this.code);
                    formData.append('country', country);
                    sweet2.loading(false); sweet2.loading();
                    fetch("/system/onCountryEmerging", {method: "POST", body: formData })
                    .then(function(res){ return res.json(); })
                    .then(function(rsp){
                        sweet2.loading(false);
                        if (rsp.success) {
                            let system = rsp.data.system; 
                            _this.graph = system.graph;
                            _this.general = system.general;
                            _this.finance = system.finance;
                            _this.country = country;
                            _this.deuda = system.finance.deuda.value;
                            _this.capital = system.finance.capital.value;
                            // _this.onGraphGroup(system.graph.general);
                            _this.onGraphGroup(system.graph.riesgo);

                        } else {
                            sweet2.show({type: rsp.success ? 'success' : 'error', text:rsp.message});
                        }
                    })
                    .catch(function () {
                        sweet2.show({type:'error', text:'Hubo un error en el sistema.'});
                    });
                },
                onGraphGroup: function (data) {
                    am4core.ready(function() {
                        // AppChartSystem.onGroupSM({scope:'groupChart', data:data});
                        AppChartSystem.onLineAll({scope:'groupChart', data:data.data, title: data.title});
                    });
                }
            }
        });
    }
}

window.AppStructureEmerging = new AppStructureEmerging();