const AppAverageEmerging = function() {
    let __this = this;
    __this.init = function (args) {
        new Vue({
            el: '#AppAverageEmerging',
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
                    this.onGraphLine(this.graph.general);
                },
                onGraphLine: function (data) {
                    am4core.ready(function() {
                        AppChartSystem.onPointAMV2({scope:'lineChart', data:data});
                    });
                },
            }
        });
    }
}

window.AppAverageEmerging = new AppAverageEmerging();