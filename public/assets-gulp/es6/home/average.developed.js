const AppAverageDeveloped = function() {
    let __this = this;
    __this.init = function (args) {
        new Vue({
            el: '#AppAverageDeveloped',
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
                onGraphLine2: function (data) { // Probando
                    am4core.ready(function() {
                        var vmax = 0;
                        for (let i = 0; i < data.length; i++) {
                            let aux = Number(data[i].x);
                            vmax = aux >= vmax ? aux : vmax;
                        }
                        //vmax = Math.ceil(vmax);
                        // Themes begin
                        am4core.useTheme(am4themes_animated);
                        // Create chart instance
                        var chart = am4core.create("lineChart", am4charts.XYChart);
                        // Add data
                        chart.data = [];
                        // Create axes
                        var valueAxisX = chart.xAxes.push(new am4charts.ValueAxis());
                        // valueAxisX.title.text = 'X Axis';
                        // valueAxisX.renderer.minGridDistance = 1;
                        valueAxisX.renderer.grid.template.location = 0;
                        valueAxisX.min = 0;
                        valueAxisX.max = vmax;
                        // Create value axis
                        var valueAxisY = chart.yAxes.push(new am4charts.ValueAxis());
                        // valueAxisY.title.text = 'Y Axis';
                        valueAxisY.numberFormatter = new am4core.NumberFormatter();
                        valueAxisY.numberFormatter.numberFormat = "#.#'%'";

                        let all = [
                            { y:2.46, x:0, label:'KD(1-T)', y1:2.46, y2:2.46, x1:0, x2: 0.78, line:1 },
                            { y:5.72, x:0, label:'Koa', y1:5.72, y2:8.22, x1:0, x2: 0.78,line: 1 },
                            { y:8.22, x:0.78, label:'Ke', y1:5.70, y2:5.70, x1:0, x2: 0.78, line:2 },
                            { y:5.70, x:0.78, label:'CPPC', y1:5.72, y2:5.72, x1:0, x2: 0.78, line:1 }
                        ];
                        
                        for (let i = 0; i < data.length; i++) {
                            f_point(data[i]);
                        }

                        /*for (let i = 0; i < all.length; i++) {
                            f_point(all[i]);
                        }*/

                        function f_point(d) {
                            // POINT
                            let lineSeries = chart.series.push(new am4charts.LineSeries());
                            lineSeries.dataFields.valueY = "y";
                            lineSeries.dataFields.valueX = "x";
                            lineSeries.strokeOpacity = 5;
                            lineSeries.data = [{
                                "y": d.y,
                                "x": d.x
                            }];

                            let circleBullet = lineSeries.bullets.push(new am4charts.CircleBullet());
                            circleBullet.tooltipText = "[bold]{y} %[/]";
                            circleBullet.circle.stroke = am4core.color("#fff");
                            circleBullet.circle.fill = am4core.color("#02abde");
                            
                            let labelBullet = lineSeries.bullets.push(new am4charts.LabelBullet());
                            labelBullet.label.text = "{y}%";
                            labelBullet.label.dy = 5;
                            labelBullet.label.dx = -30;
                            if (d.x == 0) {
                                labelBullet.label.dx = 30;
                            }
                            // LINE
                            var trend = chart.series.push(new am4charts.LineSeries());
                            trend.dataFields.valueY = "vy";
                            trend.dataFields.valueX = "vx";
                            trend.strokeWidth = 2;
                            trend.strokeOpacity = 0.7;
                            trend.minBulletDistance = 10;
                            
                            // trend.data = [
                            //     { "vx": d.x, "vy": d.y },
                            //     { "vx": (vmax ? vmax : 1), "vy": d.y }
                            // ];
                            trend.data = [
                                { "vx": d.x1, "vy": d.y1 },
                                { "vx": d.x2, "vy": d.y2 }
                            ];

                            trend.propertyFields.strokeDasharray = "lineDash";
                            trend.tooltip.label.textAlign = "end";
    
                            let labelBullet2 = trend.bullets.push(new am4charts.LabelBullet());
                            labelBullet2.label.text = d.label; // "{vy}%";
                            // labelBullet2.label.paddingTop = getRandomArbitrary(getRandomArbitrary(50,20),70);
                            // labelBullet2.strokeWidth = 2;
                            labelBullet2.label.dy = 5;
                            labelBullet2.label.dx = -150;
                            labelBullet2.label.textAlign = "start";
                          
                            if (d.line == 2) {
                                trend.strokeDasharray = "3,4";
                            }
                        }
                    });
                },
            }
        });
    }
}

window.AppAverageDeveloped = new AppAverageDeveloped();