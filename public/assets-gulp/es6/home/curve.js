const AppCurvePerformance = function() {
    let __this = this;
    __this.init = function (args) {
        new Vue({
            el: '#AppCurvePerformance',
            data() {
                return {
                    code: args.code,
                    graph: args.system.graph
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
                onPeriod: function (e) {
                    let _this = this;
                    let formData = new FormData();
                    formData.append('code', this.code);
                    formData.append('period', e.target.value);
                    sweet2.loading(false);
                    sweet2.loading();
                    fetch("/system/onCurvePerformance", {method: "POST", body: formData })
                    .then(function(res){ return res.json(); })
                    .then(function(rsp){
                        if (rsp.success) {
                            _this.graph = rsp.data.graph.performance;
                            _this.onGraph(_this.graph);
                            sweet2.loading(false);
                        } else {
                            sweet2.loading(false);
                            sweet2.show({type: rsp.success ? 'success' : 'error', text:rsp.message});
                        }
                    })
                    .catch(function (e) {
                        sweet2.show({type:'error', text:'Hubo un error en el sistema.'});
                    });
                },
                onLoad: function () {
                    this.onGraph(this.graph.performance);
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

window.AppCurvePerformance = new AppCurvePerformance();