const AppStructureDeveloped = function() {
    let __this = this;
    __this.init = function (args) {
        new Vue({
            el: '#AppStructureDeveloped',
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
                    this.onGraphGeneral(this.graph.general);
                    this.onGraphGroup(this.graph.structure);
                },
                onGraphGroup: function (data) {
                    am4core.ready(function() {
                        AppChartSystem.onGroupSM({scope:'groupChart', data:data});
                    });
                },
                onGraphGeneral: function (data) {
                    am4core.ready(function() {
                        AppChartSystem.onColumnSM({scope:'generalChart', data:data});
                    });
                }
            }
        });
    }
}

window.AppStructureDeveloped = new AppStructureDeveloped();