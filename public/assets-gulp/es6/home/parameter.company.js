const AppParameterCompany = function() {
    let __this = this;
    __this.init = function (args) {
        new Vue({
            el: '#AppParameterCompany',
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
                    //this.onGraphGroup(this.graph.general);
                    AppChartHtml.oneGraph('#chart-html', this.graph.general);
                },
                onGraphGroup: function (data) {
                    am4core.ready(function() {
                        AppChartSystem.onGroupPM({scope:'groupChart', data:data});
                    });
                },
            }
        });
    }
}

window.AppParameterCompany = new AppParameterCompany();