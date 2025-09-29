const AppDetailFlows = function() {
    let __this = this;
    __this.init = function (args) {
        new Vue({
            el: '#AppDetailFlows',
            data() {
                return {
                    code: args.code,
                    horizon: args.system.horizon,
                    period: args.system.period,
                    periodicity: args.system.periodicity,
                    flows: args.system.flows,
                    horizons: args.system.horizons,
                    periods: args.system.periods,
                    periodicities: args.system.periodicities,
                    instrument: 0,
                    performance: 0,
                    duration: 0
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
    
            },
            methods: {
                onCalculation: function () {
                    let _this = this;
                    let formData = new FormData();
                    formData.append('code', this.code);
                    formData.append('horizon', this.horizon);
                    formData.append('periodicity', this.periodicity);
                    sweet2.loading(false); sweet2.loading();
                    fetch("/system/onCalculationFlow", {method: "POST", body: formData })
                    .then(function(res){ return res.json(); })
                    .then(function(rsp){
                        if (rsp.success) {
                            _this.period = rsp.data.system.period;
                            let aux = [];
                            for (let i = 0; i <= (_this.period); i++) {
                                if (_this.flows[i] === undefined) {
                                    aux.push(Object.assign({box:0, investment:0}));
                                } else {
                                    aux.push(Object.assign({box:_this.flows[i].box, investment:_this.flows[i].investment}));
                                }
                            }
                            _this.flows = aux;
                            sweet2.loading(false);
                        } else {
                            sweet2.show({type: rsp.success ? 'success' : 'error', text:rsp.message});
                        }
                    })
                    .catch(function () {
                        sweet2.show({type:'error', text:'Hubo un error en el sistema.'});
                    });
                },
                onCalculationDetail: function (e) {
                    let _this = this;
                    e.preventDefault();
                    try {
                        if (!(_this.flows.length > 0)) {
                            throw 'La cantidad de flujos debe ser mayor a 0';
                        }
                        let flows = [];
                        for (let i = 0; i < _this.flows.length; i++) {
                            const item = _this.flows[i];
                            if (!(item.box >= 0)) {
                                throw `El campo de flujo de caja de la fila ${i} no tiene un formato correcto`;
                            }
                            if (!(item.investment >= 0)) {
                                throw `El campo de flujo de inversiones de la fila ${i} no tiene un formato correcto`;
                            }
                            flows.push({box: item.box, investment: item.investment});
                        }
                        sweet2.loading(false);sweet2.loading();
                        let formData = new FormData(e.currentTarget);
                        formData.append('code', _this.code);
                        formData.append('flows', JSON.stringify(flows));
                        fetch("/system/onCalculationDetailFlow", {method: "POST", body: formData })
                        .then(function(res){ return res.json(); })
                        .then(function(rsp){
                            sweet2.loading(false);
                            if (rsp.success) {
                                _this.instrument = rsp.data.system.instrument;
                                _this.performance = rsp.data.system.performance;
                                _this.duration = rsp.data.system.duration;
                                _this.onGraph(rsp.data.system.graph.performance); 
                                $('#modal-risk-free').modal({backdrop : "static", keyboard: false});
                            } else {
                                sweet2.show({type: rsp.success ? 'success' : 'error', text:rsp.message});
                            }
                        })
                        .catch(function () {
                            sweet2.show({type:'error', text:'Hubo un error en el sistema.'});
                        });                            
                    } catch (error) {
                        sweet2.show({type:'error', text:error});                        
                    }                    
                },
                onLoad: function () {
                      
                },
                onGraph: function (data) {
                    am4core.ready(function() {
                        AppChartSystem.oncurve({scope:'myChart', data:data});
                    });
                }
            }
        });
    }
}

window.AppDetailFlows = new AppDetailFlows();