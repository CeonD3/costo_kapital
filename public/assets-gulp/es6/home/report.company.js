const AppReportCompany = function() {
    let __this = this;
    __this.init = function (args) {
        new Vue({
            el: '#AppReportCompany',
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
                onLoad: function () {
                    this.onGraphColumnGroup(this.graph.general);
                },
                onGraphColumnGroup: function (data) {
                    am4core.ready(function() {
                        AppChartSystem.onColumnHztRC({scope:'groupChart', data:data});
                    });
                },
            }
        });
    }
}

window.AppReportCompany = new AppReportCompany();