const AppParameterDeveloped = function() {
    let __this = this;
    __this.init = function (args) {
        new Vue({
            el: '#AppParameterDeveloped',
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
                    this.onGraphGroup(this.graph.general);
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

window.AppParameterDeveloped = new AppParameterDeveloped();