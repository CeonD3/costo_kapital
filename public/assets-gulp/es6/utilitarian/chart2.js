// https://www.amcharts.com/docs/v4/concepts/formatters/formatting-strings/
const AppChartSystem = {
    validate: function (args = {}) {
        let rsp = {message:'Lo sentimos hubo un error, intentelo más tarde.', data:args, success: false};
        if (args.scope == undefined) {
            rsp.message = 'No sé logró identificar el scope';
            throw rsp;
        }
        if (!document.getElementById(args.scope)) {
            throw {success: false, message:'No sé logró identificar el objecto del scope'};
        }
        if (args.data == undefined) {
            rsp.message = 'No sé logro identicar los datos del gráfico';
            throw rsp;
        }
        if (args.animate == undefined) {
            args.animate = true;
        }
        return args;
    },
    helper:{
        getMaxValue: function (data) {
            let vmax = 0;
            if (data.length > 0) {
                let d = data;
                for (let i = 0; i < d.length; i++) {
                    const elem = Number(d[i].value);
                    if (elem > 0 && elem > vmax) {
                        vmax = elem;
                    }
                }
            }
            vmax = vmax + 1;  
            return vmax;
        }
    },
    onColumnSM: function (args) {
        return new Promise((resolve, reject)=>{
            let chart;
            try {
                args = this.validate(args);
                if (args.animate) {
                    // Themes
                    am4core.useTheme(am4themes_animated);
                }
                // Create chart instance
                chart = am4core.create(args.scope, am4charts.XYChart);
                if (chart.logo) {
                    chart.logo.disabled = true;
                }
                // Export
                // chart.exporting.menu = new am4core.ExportMenu();

                let vmax = this.helper.getMaxValue(args.data);

                /* Title */
                let title = chart.titles.create();
                title.text = "PARÁMETROS GENERALES";
                title.fontSize = 14;
                title.marginBottom = 10;
                if (args.fontSize != undefined) {
                    title.fontSize = args.fontSize;
                }
                /* Create axes */
                var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
                categoryAxis.dataFields.category = "label";
                categoryAxis.renderer.minGridDistance = 1;
                categoryAxis.numberFormatter = new am4core.NumberFormatter();
                categoryAxis.renderer.grid.template.location = 0;
                categoryAxis.renderer.grid.template.disabled = true;
                if (args.fontSize != undefined) {
                    categoryAxis.renderer.labels.template.fontSize = args.fontSize;
                }
                var label = categoryAxis.renderer.labels.template;
                label.truncate = true;
                label.tooltipText = "{category}";

                categoryAxis.events.on("sizechanged", function(ev) {
                    var axis = ev.target;
                    var cellWidth = axis.pixelWidth / (axis.endIndex - axis.startIndex);
                    axis.renderer.labels.template.maxWidth = cellWidth;
                });

                /* Create value axis */
                var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
                valueAxis.numberFormatter = new am4core.NumberFormatter();
                valueAxis.numberFormatter.numberFormat = "#'%'";
                valueAxis.renderer.labels.template.disabled = true;
                valueAxis.renderer.grid.template.disabled = true;
                valueAxis.min = 0;
                valueAxis.max = vmax;
                /* Create series */
                var columnSeries = chart.series.push(new am4charts.ColumnSeries());
                columnSeries.name = "Punto";
                columnSeries.dataFields.valueY = "value";
                columnSeries.dataFields.categoryX = "label";
                // columnSeries.columns.template.tooltipText = "{valueY}%"
                columnSeries.columns.template.propertyFields.fillOpacity = "fillOpacity";
                columnSeries.columns.template.propertyFields.stroke = "color"; // "stroke"
                columnSeries.columns.template.propertyFields.strokeWidth = 4; // "strokeWidth";
                columnSeries.columns.template.propertyFields.strokeDasharray = "columnDash";
                columnSeries.tooltip.label.textAlign = "middle";
                columnSeries.columns.template.showTooltipOn = "always";
                columnSeries.tooltip.pointerOrientation = "down";
                columnSeries.columns.template.tooltipY = 0;

                columnSeries.columns.template.strokeWidth = 2;
                columnSeries.columns.template.fill = am4core.color("#fff");

                columnSeries.columns.template.width = am4core.percent(50);
                if (args.fontSize != undefined) {
                    columnSeries.tooltip.label.fontSize = args.fontSize;
                }

                // Add label
                var labelBullet = columnSeries.bullets.push(new am4charts.LabelBullet());
                labelBullet.label.text = "[bold]{valueY}%";
                /*labelBullet.locationY = 0.5;
                labelBullet.label.hideOversized = true;
                labelBullet.label.fill = am4core.color("#000");*/
                labelBullet.label.dy = -15;
                labelBullet.label.hideOversized = false;
                labelBullet.label.truncate = false;

                if (args.fontSize != undefined) {
                    labelBullet.label.fontSize = args.fontSize;
                }

                /*columnSeries.columns.template.adapter.add("fill", (fill, target) => {
                    console.log(target);
                    return chart.colors.getIndex(target.dataItem.index);
                });*/
                
                if (!(args.cursor != undefined && args.cursor == false)) {
                    // chart.cursor = new am4charts.XYCursor();
                }
                /* Data */
                chart.data = args.data;
                chart.events.on('ready', () => {
                    resolve(chart);
                });
            } catch (err) {
                reject(err)
                console.error(err);
            }
        });
        return chart;
    },
    onGroupSM: function (args) {
        return new Promise((resolve, reject)=>{
            let chart = null;
            try {
                args = this.validate(args);
                if (args.animate) {
                    // Themes
                    am4core.useTheme(am4themes_animated);
                }
                // Create chart instance
                chart = am4core.create(args.scope, am4charts.XYChart);
                if (chart.logo) {
                    chart.logo.disabled = true;
                }

                /* title */
                let title = chart.titles.create();
                title.text = "ESTRUCTURA DE FINANCIAMIENTO";
                title.fontSize = 14;
                title.marginBottom = 20;
                if (args.fontSize != undefined) {
                    title.fontSize = args.fontSize;
                }
                // Add data
                chart.data = args.data.items;
                // Create axes
                var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
                categoryAxis.dataFields.category = "label";
                categoryAxis.renderer.grid.template.location = 0;
                categoryAxis.renderer.grid.template.disabled = true;
                if (args.fontSize != undefined) {
                    categoryAxis.renderer.labels.template.fontSize = args.fontSize;
                }
                // categoryAxis.title.text = 'MERCADO DESARROLLADO';
                
                var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
                valueAxis.numberFormatter = new am4core.NumberFormatter();
                valueAxis.numberFormatter.numberFormat = "#'%'";
                valueAxis.renderer.grid.template.disabled = true;
                // valueAxis.renderer.labels.template.disabled = true;
                valueAxis.min = 0;
                valueAxis.max = 100;

                // Create series
                function createSeries(param) {
                    let { index, label, color } = param;
                    // Set up series
                    var series = chart.series.push(new am4charts.ColumnSeries());
                    series.name = label; // name
                    series.dataFields.valueY = index; // field
                    series.dataFields.categoryX = "label";
                    series.sequencedInterpolation = true;
                    // Make it stacked
                    series.stacked = true;
                    // Configure columns
                    series.columns.template.width = am4core.percent(50);
                    if (!(args.tooltipText != undefined && args.tooltipText == false)) {
                        series.columns.template.tooltipText = "[bold]{name}[/]\n{valueY}%";
                        // series.columns.template.tooltipText = "[bold]{name}[/]\n[font-size:14px]{categoryX}: {valueY}%";
                    }

                    series.tooltip.label.adapter.add("text", function(text, target) {
                        if (target.dataItem && target.dataItem.valueY == 0) {
                            return "";
                        }
                        else {
                            return text;
                        }
                    });
                        
                    // Add label
                    var labelBullet = series.bullets.push(new am4charts.LabelBullet());
                    labelBullet.label.text = "[bold]{valueY}%";
                    labelBullet.locationY = 0.5;
                    // labelBullet.label.hideOversized = true;
                    labelBullet.label.fill = am4core.color("#000");
                    if (args.fontSize != undefined) {
                        labelBullet.label.fontSize = args.fontSize;
                    }

                    // series.columns.template.stroke = am4core.color("#ff0000"); // red outline
                    // series.columns.template.stroke = am4core.color('rgba(54, 162, 235, 0.2)');
                    series.columns.template.stroke = color; // am4core.color("#7DDDFF");
                    series.columns.template.strokeWidth = 2;
                    series.columns.template.fill = am4core.color("#fff"); // green fill

                    return series;
                }

                for (let i = 0; i < args.data.groups.length; i++) {
                    createSeries(args.data.groups[i]);
                }

                chart.events.on('ready', () => {
                    resolve(chart);
                });
            } catch (err) {
                reject(err)
                console.error(err);
            }
        });
        return chart;
    },
    onGroupPM: function (args) {
        return new Promise((resolve, reject)=>{
            let chart = null;
            try {
                args = this.validate(args);
                if (args.animate) {
                    // Themes
                    am4core.useTheme(am4themes_animated);
                }
                // Create chart instance
                chart = am4core.create(args.scope, am4charts.XYChart);
                if (chart.logo) {
                    chart.logo.disabled = true;
                }

                // Add data
                chart.data = args.data.items;

                let ymax = this.helper.getMaxValue(args.data.items);
                /*let ymax = 0;
                if (args.data.items.length > 0) {
                    let d = args.data.items;
                    let all = [];
                    let except = ['label', 'title', 'value', 'measure'];
                    for (let i = 0; i < d.length; i++) {
                        let object = d[i];
                        let suma = 0;
                        for (const property in object) {
                            if (!except.includes(property)) {
                                if (Number(object[property]) > 0) {
                                    suma = Number(suma) + Number(object[property]);
                                }
                            }
                        }
                        all.push(suma);
                    }
                    for (let i = 0; i < all.length; i++) {
                        let elem = Number(all[i]);
                        if (elem > 0 && elem > ymax) {
                            ymax = elem;
                        }                   
                    }
                }*/
                // Create axes
                var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
                categoryAxis.dataFields.category = "label";
                categoryAxis.renderer.grid.template.location = 0;
                categoryAxis.renderer.grid.template.disabled = true;
                if (args.fontSize != undefined) {
                    categoryAxis.renderer.labels.template.fontSize = args.fontSize;
                }
                // categoryAxis.title.text = 'MERCADO DESARROLLADO';
                
                var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
                valueAxis.numberFormatter = new am4core.NumberFormatter();
                valueAxis.numberFormatter.numberFormat = "#'%'";
                valueAxis.renderer.grid.template.disabled = true;
                valueAxis.renderer.labels.template.disabled = true;
                valueAxis.min = 0;
                valueAxis.max = Number(ymax) + 1.5;
                valueAxis.strictMinMax = true;
                //valueAxis.max = 100;

                // Create series
                function createSeries(param, last) {
                    let { index, label, color, measure } = param;
                    // Set up series
                    var series = chart.series.push(new am4charts.ColumnSeries());
                    series.name = label;
                    series.dataFields.valueY = index;
                    series.dataFields.categoryX = "label";
                    series.sequencedInterpolation = true;
                    // Make it stacked
                    series.stacked = true;
                    // Configure columns
                    let widthvalue = 50;
                    if (args.template !== undefined) {
                        if (args.template.width !== undefined) {
                            widthvalue = args.template.width;
                        }
                    }
                    series.columns.template.width = am4core.percent(widthvalue);

                    series.columns.template.stroke = color; // am4core.color("#7DDDFF");
                    series.columns.template.strokeWidth = 2;
                    series.columns.template.fill = am4core.color("#fff");

                    if (!(args.tooltipText != undefined && args.tooltipText == false)) {
                        series.columns.template.tooltipText = "[bold]{name}[/]\n{valueY}%";
                    }
                    
                    // Add label
                    var labelBullet = series.bullets.push(new am4charts.LabelBullet());
                    labelBullet.label.text = "[bold]{name} = {valueY}%[/]";
                    labelBullet.locationY = 0.5;
                    labelBullet.label.hideOversized = true;
                    labelBullet.label.fill = am4core.color("#000");
                    if (args.fontSize != undefined) {
                        labelBullet.label.fontSize = args.fontSize;
                    }

                    if (last) {
                        var sumBullet = series.bullets.push(new am4charts.LabelBullet());
                        sumBullet.label.text = "[bold]{title}[/]";
                        sumBullet.dy = -10;
                    }
                    
                    // var range = valueAxis.createSeriesRange(series);
                    // range.value = "{measure}";
                    // range.endValue ="{measure2}";
                    // range.contents.stroke = am4core.color("#E52320");
                    // range.contents.fill = range.contents.stroke;
                    // // range.label.text = "label";
                    // // range.grid.strokeDasharray = "5,2";
                    // range.label.text = "100";

                    // range.label.fontSize = 14;
                    // range.label.fontWeight =  "bold";

                    // range.label.text =  "Hola Mundo";
                    // //   range.label.inside = true;
                    // // range.label.rotation = 90;
                    // range.label.horizontalCenter = "right";


                    


                    //    range.strokeWidth = "3,3";

                        //range.contents.strokeWidth = "3,3"
                        // range.dy = -10;
                        
                        // var range2 = valueAxis.axisRanges.create();
                        // range2.grid.stroke = am4core.color("#A96478");
                        // range2.grid.strokeWidth = 2;
                        // range2.grid.strokeOpacity = 1;

                        // series.bulletsContainer.parent = chart.seriesContainer;
                    
                    return series;
                }

                for (let i = 0; i < args.data.groups.length; i++) {
                    let last = false;
                    if (args.data.groups[i].prima !== undefined) {
                        last = Number(args.data.groups[i].prima) >= 0 ? false : true;
                    } else {
                        last = i == (args.data.groups.length - 1);
                    }
                    createSeries(args.data.groups[i], last);
                }

                // Legend
                /*chart.legend = new am4charts.Legend();
                chart.legend.position = "left";
                chart.legend.fontSize = 12;
                if (args.legend !== undefined) {
                    if (args.legend.position !== undefined) {
                        chart.legend.position = args.legend.position;
                    }
                }
                if (args.fontSize != undefined) {
                    chart.legend.fontSize = args.fontSize;
                }*/
                /* Create a cursor */
                // chart.cursor = new am4charts.XYCursor();     
                chart.events.on('ready', () => {
                    resolve(chart);
                });
            } catch (err) {
                reject(err)
                console.error(err);
            }
        });
        return chart;
    },
    onPointAM: function (args) {
        return new Promise((resolve, reject)=>{
            let chart = null;
            try {
                args = this.validate(args);
                if (args.animate) {
                    // Themes
                    am4core.useTheme(am4themes_animated);
                }
                let vmax = 0;
                for (let i = 0; i < args.data.length; i++) {
                    let aux = Number(args.data[i].x);
                    vmax = aux >= vmax ? aux : vmax;
                }
                // vmax = Math.ceil(vmax);
                // Themes begin
                am4core.useTheme(am4themes_animated);
                // Create chart instance
                chart = am4core.create(args.scope, am4charts.XYChart);
                if (chart.logo) {
                    chart.logo.disabled = true;
                }
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

                /*let all = [
                    { y:2.46, x:0, label:'KD(1-T)' },
                    { y:5.72, x:0, label:'Koa' },
                    { y:8.22, x:0.78, label:'Ke' },
                    { y:5.70, x:0.78, label:'CPPC' }
                ];*/

                for (let i = 0; i < args.data.length; i++) {
                    f_point(args.data[i]);
                }

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
                    /*var trend = chart.series.push(new am4charts.LineSeries());
                    trend.dataFields.valueY = "vy";
                    trend.dataFields.valueX = "vx";
                    trend.strokeWidth = 2;
                    trend.strokeOpacity = 0.7;
                    trend.data = [
                        { "vx": d.x, "vy": d.y },
                        { "vx": 1, "vy": d.y }
                    ];

                    trend.propertyFields.strokeDasharray = "lineDash";
                    trend.tooltip.label.textAlign = "middle";

                    let labelBullet2 = trend.bullets.push(new am4charts.LabelBullet());
                    labelBullet2.label.text = d.label; // "{vy}%";
                    labelBullet2.label.paddingLeft = 70;
                    labelBullet2.label.dy = 8;*/
                }
                chart.events.on('ready', () => {
                    resolve(chart);
                });
            } catch (err) {
                reject(err)
                console.error(err);
            }
        });
        return chart;
    },
    oncurve: function (args) {
        return new Promise((resolve, reject)=>{
            let chart = null;
            try {
                args = this.validate(args);
                if (args.animate) {
                    // Themes
                    am4core.useTheme(am4themes_animated);
                }

                // Create chart instance
                chart = am4core.create(args.scope, am4charts.XYChart);
                if (chart.logo) {
                    chart.logo.disabled = true;
                }
                // Export
                // chart.exporting.menu = new am4core.ExportMenu();

                /* Create axes */
                var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
                categoryAxis.dataFields.category = "label";
                categoryAxis.numberFormatter = new am4core.NumberFormatter();
                categoryAxis.numberFormatter.numberFormat = "#.##";
                categoryAxis.renderer.grid.template.disabled = true;
                categoryAxis.title.text = 'Instrumento de deuda del Treasury';
                categoryAxis.renderer.grid.template.location = 0;
                categoryAxis.renderer.minGridDistance = 1;
                if (args.fontSize != undefined) {
                    categoryAxis.renderer.labels.template.fontSize = args.fontSize;
                    categoryAxis.title.fontSize = args.fontSize;
                }
                /*categoryAxis.renderer.labels.template.adapter.add("text", function(text,t,r) {
                    console.log(text, t, r);
                    return text + "%";
                });*/

                /* Create value axis */
                var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
                valueAxis.numberFormatter = new am4core.NumberFormatter();
                valueAxis.numberFormatter.numberFormat = "#'%'"
                valueAxis.renderer.grid.template.disabled = true;
                valueAxis.title.text = 'Rendimiento del instrumento';
                if (args.fontSize != undefined) {
                    valueAxis.title.fontSize = args.fontSize;
                }
                var lineSeries = chart.series.push(new am4charts.LineSeries());
                lineSeries.name = "Rendimiento";
                lineSeries.dataFields.valueY = "value";
                lineSeries.dataFields.categoryX = "label";

                lineSeries.stroke = am4core.color("#02abde");
                lineSeries.strokeWidth = 2;
                lineSeries.propertyFields.strokeDasharray = "lineDash";
                lineSeries.tooltip.label.textAlign = "middle";

                var bullet = lineSeries.bullets.push(new am4charts.Bullet());
                bullet.fill = am4core.color("#02abde"); // tooltips grab fill from parent by default
                bullet.tooltipText = "{name} :\n[/]{valueY}%";
                
                var circle = bullet.createChild(am4core.Circle);
                circle.radius = 4;
                circle.fill = am4core.color("#fff");
                circle.strokeWidth = 5;

                /* Create series */
                var columnSeries = chart.series.push(new am4charts.ColumnSeries());
                columnSeries.name = "Instrumento";
                columnSeries.dataFields.valueY = "value";
                columnSeries.dataFields.categoryX = "index";
                columnSeries.dataFields.stroke = am4core.color("#02abde");

                //columnSeries.columns.template.tooltipText = "{name} :\n[/]{categoryX}"
                columnSeries.columns.template.propertyFields.fillOpacity = 5;
                // columnSeries.columns.template.propertyFields.stroke = "color";
                // columnSeries.columns.template.propertyFields.strokeWidth = "strokeWidth";
                columnSeries.columns.template.propertyFields.strokeDasharray = "columnDash";
                columnSeries.tooltip.label.textAlign = "middle";
                columnSeries.columns.template.width = am4core.percent(25);
                // columnSeries.columns.template.strokeWidth = 4;
                // columnSeries.columns.template.fill = am4core.color("#fff");

                if (!(args.cursor != undefined && args.cursor == false)) {
                    /* Create a cursor */
                    chart.cursor = new am4charts.XYCursor();
                }

                chart.data = args.data;

                chart.events.on('ready', () => {
                    resolve(chart);
                });
            } catch (err) {
                reject(err)
                console.error(err);
            }
        });
        return chart;
    },
    onColumnHztRC: function (args) {
        return new Promise((resolve, reject)=>{
            let chart = null;
            try {
                args = this.validate(args);
                if (args.animate) {
                    // Themes
                    am4core.useTheme(am4themes_animated);
                }
                // Create chart instance
                chart = am4core.create(args.scope, am4charts.XYChart);
                if (chart.logo) {
                    chart.logo.disabled = true;
                }
                // Add data
                chart.data = args.data.items;

                let ymax = 0;
                for (let i = 0; i < args.data.items.length; i++) {
                    let elem = args.data.items[i];
                    if (elem.value1 !== undefined) {
                        ymax = Number(elem.value1) > ymax ? elem.value1 : ymax;
                    }
                    if (elem.value2 !== undefined) {
                        ymax = Number(elem.value2) > ymax ? elem.value2 : ymax;
                    }
                    if (elem.value3 !== undefined) {
                        ymax = Number(elem.value3) > ymax ? elem.value3 : ymax;
                    }
                    if (elem.value4 !== undefined) {
                        ymax = Number(elem.value4) > ymax ? elem.value4 : ymax;
                    }
                }

                var xAxis = chart.xAxes.push(new am4charts.CategoryAxis())
                xAxis.dataFields.category = 'label'
                xAxis.renderer.cellStartLocation = 0.1
                xAxis.renderer.cellEndLocation = 0.9
                xAxis.renderer.grid.template.location = 0;
                xAxis.numberFormatter.numberFormat = "#";
                xAxis.renderer.grid.template.location = 0;
                xAxis.renderer.grid.template.disabled = true;
                xAxis.min = 0;

                xAxis.renderer.inside = true;
                xAxis.renderer.labels.template.valign = "top";
                
                var yAxis = chart.yAxes.push(new am4charts.ValueAxis());
                yAxis.numberFormatter = new am4core.NumberFormatter();
                yAxis.numberFormatter.numberFormat = "#'%'";
                yAxis.renderer.grid.template.disabled = true;
                yAxis.min = 0;
                yAxis.max = (Number(ymax) + 2);
                //yAxis.renderer.minGridDistance = 75;
                
                // Create series
                function createSeries(param) {
                    let { index, label, color } = param;
                    var series = chart.series.push(new am4charts.ColumnSeries())
                    series.dataFields.valueY = index;
                    series.dataFields.categoryX = 'label';
                    series.name = label;

                    series.columns.template.tooltipText = "{name}: [bold]{valueY}%[/]";
                    series.columns.template.height = am4core.percent(100);
                    series.columns.template.width = am4core.percent(100);
                    series.columns.template.stroke = color;
                    series.columns.template.strokeWidth = 2;
                    series.columns.template.fill = am4core.color("#fff");
                    series.sequencedInterpolation = true;

                    var categoryLabel = series.bullets.push(new am4charts.LabelBullet());
                    categoryLabel.label.text = "[bold]{name}";
                    //categoryLabel.label.horizontalCenter = "top";
                    categoryLabel.label.dy = -10;
                    categoryLabel.label.fill = am4core.color("#000");
                    categoryLabel.label.hideOversized = false;
                    categoryLabel.label.truncate = false;
                    if (args.fontSize != undefined) {
                        categoryLabel.label.fontSize = args.fontSize;
                    }
                    
                    var valueLabel = series.bullets.push(new am4charts.LabelBullet());
                    valueLabel.label.text = "[bold]{valueY}%";
                    //valueLabel.label.horizontalCenter = "left";
                    valueLabel.label.dy = 25;
                    valueLabel.label.hideOversized = false;
                    valueLabel.label.truncate = false;
                    if (args.fontSize != undefined) {
                        valueLabel.label.fontSize = args.fontSize;
                    }
                }

                for (let i = 0; i < args.data.groups.length; i++) {
                    createSeries(args.data.groups[i]);
                }
                chart.events.on('ready', () => {
                    resolve(chart);
                });
            } catch (err) {
                reject(err)
                console.error(err);
            }
        });
        return chart;
    },
    onColumnHztRC2: function (args) {
        return new Promise((resolve, reject)=>{
            let chart = null;
            try {
                args = this.validate(args);
                if (args.animate) {
                    // Themes
                    am4core.useTheme(am4themes_animated);
                }
                // Create chart instance
                chart = am4core.create(args.scope, am4charts.XYChart);
                if (chart.logo) {
                    chart.logo.disabled = true;
                }   
                // Add data
                chart.data = args.data.items;
                
                // Create axes
                var categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
                categoryAxis.dataFields.category = "label";
                categoryAxis.numberFormatter.numberFormat = "#";
                categoryAxis.renderer.grid.template.location = 0;
                categoryAxis.renderer.cellStartLocation = 0.1;
                categoryAxis.renderer.cellEndLocation = 0.9;
                categoryAxis.renderer.grid.template.disabled = true;
                if (args.rotation != undefined) {
                    categoryAxis.renderer.labels.template.rotation = args.rotation;
                    categoryAxis.renderer.labels.template.verticalCenter = "bottom";
                    categoryAxis.renderer.labels.template.horizontalCenter = "middle";
                }
                if (args.fontSize != undefined) {
                    categoryAxis.renderer.labels.template.fontSize = args.fontSize;
                }
                var  valueAxis = chart.xAxes.push(new am4charts.ValueAxis());
                valueAxis.numberFormatter = new am4core.NumberFormatter();
                valueAxis.numberFormatter.numberFormat = "#'%'";
                valueAxis.renderer.grid.template.disabled = true;
                valueAxis.min = 0;
                // valueAxis.max = 100;
                
                // Create series
                function createSeries(param) {
                    let { index, label, color } = param;
                    var series = chart.series.push(new am4charts.ColumnSeries());
                    series.dataFields.valueX = index;
                    series.dataFields.categoryY = "label";
                    series.name = label;
                    series.columns.template.tooltipText = "{name}: [bold]{valueX}%[/]";
                    series.columns.template.height = am4core.percent(80);
                    series.columns.template.stroke = color;
                    series.columns.template.strokeWidth = 2;
                    series.columns.template.fill = am4core.color("#fff");
                    series.sequencedInterpolation = true;

                    var valueLabel = series.bullets.push(new am4charts.LabelBullet());
                    valueLabel.label.text = "[bold]{valueX}%";
                    valueLabel.label.horizontalCenter = "left";
                    valueLabel.label.dx = 10;
                    valueLabel.label.hideOversized = false;
                    valueLabel.label.truncate = false;
                    if (args.fontSize != undefined) {
                        valueLabel.label.fontSize = args.fontSize;
                    }
                    var categoryLabel = series.bullets.push(new am4charts.LabelBullet());
                    categoryLabel.label.text = "[bold]{name}";
                    categoryLabel.label.horizontalCenter = "right";
                    categoryLabel.label.dx = -10;
                    categoryLabel.label.fill = am4core.color("#000");
                    categoryLabel.label.hideOversized = false;
                    categoryLabel.label.truncate = false;
                    if (args.fontSize != undefined) {
                        valueLabel.label.fontSize = args.fontSize;
                    }
                }

                for (let i = 0; i < args.data.groups.length; i++) {
                    createSeries(args.data.groups[i]);
                }
                
                chart.legend = new am4charts.Legend();
                if (args.fontSize != undefined) {
                    chart.legend.fontSize = args.fontSize;
                }   
                chart.events.on('ready', () => {
                    resolve(chart);
                });
            } catch (err) {
                reject(err)
                console.error(err);
            }
        });
        return chart;
    },
    onPointAMV2: function (args) {
        return new Promise((resolve, reject)=>{
            let chart = null;
            try {
                args = this.validate(args);
                if (args.animate) {
                    // Themes
                    am4core.useTheme(am4themes_animated);
                }

                var vmax = 0, ymax = 0;
                for (let i = 0; i < args.data.length; i++) {
                    let aux = Number(args.data[i].x);
                    vmax = aux >= vmax ? aux : vmax;

                    let yaux = Number(args.data[i].y);
                    ymax = yaux >= ymax ? yaux : ymax;
                }
                //vmax = Math.ceil(vmax);
                ymax = ymax + 1;
                let xmax = vmax + 0.05;
                // Themes begin
                // am4core.useTheme(am4themes_animated);
                // Create chart instance
                chart = am4core.create(args.scope, am4charts.XYChart);
                if (chart.logo) {
                    chart.logo.disabled = true;
                }
                // Add data
                chart.data = [];
                // Create axes
                var valueAxisX = chart.xAxes.push(new am4charts.ValueAxis());
                // valueAxisX.title.text = 'X Axis';
                valueAxisX.renderer.minGridDistance = 100;
                valueAxisX.renderer.grid.template.location = 0;
                valueAxisX.renderer.grid.template.disabled = true;
                valueAxisX.min = 0;
                valueAxisX.max = xmax;
                // Create value axis
                var valueAxisY = chart.yAxes.push(new am4charts.ValueAxis());
                // valueAxisY.title.text = 'Y Axis';
                valueAxisY.renderer.grid.template.location = 0;
                valueAxisY.renderer.grid.template.disabled = true;
                valueAxisY.min = 0;
                valueAxisY.renderer.minGridDistance = 50;
                valueAxisY.numberFormatter = new am4core.NumberFormatter();
                valueAxisY.numberFormatter.numberFormat = "#.#'%'";
                valueAxisY.max = ymax;
                
                let range = valueAxisX.axisRanges.create();
                range.value = vmax;
                range.grid.stroke = am4core.color("#396478");
                range.grid.strokeWidth = 2;
                range.grid.strokeOpacity = 1;
                range.grid.strokeDasharray = "3,3";
                range.label.inside = true;
                range.label.text = "D/C = " + vmax;
                range.label.fill = range.grid.stroke;
                range.label.verticalCenter = "bottom";
                range.label.valign = "top";
                range.label.fontWeight = 600;
                range.label.dy = -25;
                range.label.dx = -35;

                /*let all = [
                    { y:2.46, x:0, label:'KD(1-T)', y1:2.46, y2:2.46, x1:0, x2: 0.78, line:1 },
                    { y:5.72, x:0, label:'Koa', y1:5.72, y2:8.22, x1:0, x2: 0.78,line: 1 },
                    { y:8.22, x:0.78, label:'Ke', y1:5.70, y2:5.70, x1:0, x2: 0.78, line:2 },
                    { y:5.70, x:0.78, label:'CPPC', y1:5.72, y2:5.72, x1:0, x2: 0.78, line:1 }
                ];*/

                for (let i = 0; i < args.data.length; i++) {
                    args.data[i].idx = i;
                    f_point(args.data[i]);
                }

                /*for (let i = 0; i < all.length; i++) {
                    f_point(all[i]);
                }*/

                function f_point(d) {
                    let colors = ['#00e676', '#2196f3','#e91e63', '#673ab7'];
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
                    if (!(args.tooltipText != undefined && args.tooltipText == false)) {
                        circleBullet.tooltipText = "[bold]{y} %[/]";
                    }
                    circleBullet.circle.stroke = am4core.color("#fff");
                    circleBullet.circle.fill = am4core.color("#02abde");
                    
                    if (!(d.label == 'Koa')) {
                        let labelBullet = lineSeries.bullets.push(new am4charts.LabelBullet());
                        labelBullet.label.text = "{y}%";
                        labelBullet.label.dy = -15;
                        labelBullet.label.fontWeight = 600;
                        if (args.fontSize != undefined) {
                            labelBullet.label.fontSize = args.fontSize;
                        }
                        labelBullet.label.dx = 25;
                    }

                    //labelBullet.label.dx = -30;
                    if (d.line == 2) {
                        var valueLabelY = lineSeries.bullets.push(new am4charts.LabelBullet());
                        valueLabelY.label.text = "Koa  "+d.y+"%";
                        valueLabelY.label.dy = 17;
                        valueLabelY.label.dx = 17;
                        valueLabelY.label.fontWeight = 600;
                        valueLabelY.label.fontSize = 15;
                        valueLabelY.label.fill = am4core.color("#02abde");
                        if (args.fontSize != undefined) {
                            valueLabelY.label.fontSize = args.fontSize;
                        }
                        valueLabelY.label.dx = 45;
                    }
                    //if (d.x == 0) {
                    //}
                    // LINE
                    var trend = chart.series.push(new am4charts.LineSeries());
                    trend.dataFields.valueY = "vy";
                    trend.dataFields.valueX = "vx";
                    trend.strokeWidth = 4;
                    trend.strokeOpacity = 0.7;
                    trend.minBulletDistance = 10;
                    trend.stroke = colors[d.idx];
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
                    if (d.line != 2) {
                        labelBullet2.label.text = d.label; // "{vy}%";
                    }
                    labelBullet2.label.paddingRight = 45;
                    labelBullet2.strokeWidth = 2;
                    labelBullet2.label.dy = -10;
                    //labelBullet2.label.dx = -30 * d.idx;
                    labelBullet2.label.dx = -5;
                    labelBullet2.label.textAlign = "start";
                    labelBullet2.label.fill = colors[d.idx];
                    labelBullet2.label.fontWeight = 600;
                    labelBullet2.label.fontSize = 15;
                    if (args.fontSize != undefined) {
                        labelBullet2.label.fontSize = args.fontSize;
                    }
                    // colors[d.idx];
                    if (d.line == 2) {
                        trend.strokeDasharray = "3,4";
                    }
                }
                chart.events.on('ready', () => {
                    resolve(chart);
                });
            } catch (err) {
                reject(err)
                console.error(err);
            }
        });
        return chart;
    },
    onColumnAll: function (args) {
        return new Promise((resolve, reject)=>{
            let chart = null;
            try {
                args = this.validate(args);
                if (args.animate) {
                    // Themes
                    am4core.useTheme(am4themes_animated);
                }
                // Create chart instance
                chart = am4core.create(args.scope, am4charts.XYChart);

                if (chart.logo) {
                    chart.logo.disabled = true;
                }
                // Export
                // chart.exporting.menu = new am4core.ExportMenu();
                // Add data
                chart.data = args.data.industries;
                // Create axes
                var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
                categoryAxis.dataFields.category = "label";
                categoryAxis.renderer.grid.template.location = 0;
                categoryAxis.renderer.minGridDistance = 15;
                categoryAxis.renderer.grid.template.location = 0.5;
                categoryAxis.renderer.grid.template.strokeDasharray = "1,3";
                categoryAxis.renderer.labels.template.rotation = -90;
                categoryAxis.renderer.labels.template.horizontalCenter = "right";
                categoryAxis.renderer.labels.template.location = 0.5;
                categoryAxis.renderer.cellStartLocation = 0.2;
                categoryAxis.renderer.cellEndLocation = 0.8;
                categoryAxis.renderer.labels.template.fontSize = 12;
                categoryAxis.renderer.grid.template.disabled = true;

                var label = categoryAxis.renderer.labels.template;
                //label.truncate = true;
                label.tooltipText = "{category}";
                label.textAlign = "middle";
                label.marginTop = 5;

                label.truncate = true;
                label.maxWidth = 100;

                /*categoryAxis.renderer.labels.template.adapter.add("dx", function(dx, target) {
                    return -target.maxRight / 2;
                })*/

                /*categoryAxis.events.on("sizechanged", function(ev) {
                    var axis = ev.target;
                    var cellWidth = axis.pixelWidth / (axis.endIndex - axis.startIndex);
                    axis.renderer.labels.template.maxWidth = cellWidth;
                });*/

                var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
                valueAxis.numberFormatter = new am4core.NumberFormatter();
                valueAxis.numberFormatter.numberFormat = "#'%'";
                valueAxis.renderer.grid.template.disabled = true;
                // valueAxis.renderer.minGridDistance = 20;

                // Create series
                var series = chart.series.push(new am4charts.ColumnSeries());
                series.dataFields.valueY = "value";
                series.dataFields.categoryX = "label";
                series.name = "Visits";
                if (!(args.tooltipText != undefined && args.tooltipText == false)) {
                    series.columns.template.tooltipText = "{categoryX}: [bold]{valueY} %[/]";
                }
                series.sequencedInterpolation = true;
                series.columns.template.fillOpacity = .9;

                var columnTemplate = series.columns.template;
                columnTemplate.strokeWidth = 1.5;
                columnTemplate.strokeOpacity = 0;
                
                if (!(args.cursor != undefined && args.cursor == false)) {
                    chart.cursor = new am4charts.XYCursor();
                }

                let title = chart.titles.create();
                title.text = "COSTO DE CÁPITAL ECONÓMICO";
                title.fontSize = 16;
                title.fontWeight = 600;
                title.marginBottom = 5;
                title.fill = am4core.color('#02abde');
                if (args.fontSize != undefined) {
                    title.fontSize = args.fontSize;
                }
                series.columns.template.adapter.add("fill", (fill, target) => {
                    if (args.selected !== undefined) {
                        if (target.dataItem.categoryX == args.selected) {
                            return am4core.color('#fdd835');
                        }
                    }
                    return am4core.color('#02abde');
                });
                chart.events.on('ready', () => {
                    resolve(chart);
                });
            } catch (err) {
                reject(err)
                console.error(err);
            }
        });
        return chart;
    },
    onLineAll: function (args) {
        return new Promise((resolve, reject)=>{
            let chart;
            try {
                args = this.validate(args);
                if (args.animate) {
                    // Themes
                    am4core.useTheme(am4themes_animated);
                }
                // Create chart instance
                chart = am4core.create(args.scope, am4charts.XYChart);
                if (chart.logo) {
                    chart.logo.disabled = true;
                }       
                // Create chart instance
                // var chart = am4core.create("chartdiv", am4charts.XYChart);
                // Add data
                chart.data = args.data;

                /* Create axes */
                var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
                categoryAxis.dataFields.category = "label";
                categoryAxis.renderer.minGridDistance = 10;
                categoryAxis.renderer.grid.template.disabled = true;
                categoryAxis.renderer.labels.template.rotation = -90;
                /* Create value axis */
                var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
                valueAxis.renderer.grid.template.disabled = true;
                valueAxis.min = 0;
                /* Create series */

                var lineSeries = chart.series.push(new am4charts.LineSeries());
                lineSeries.name = "label";
                lineSeries.dataFields.valueY = "value";
                lineSeries.dataFields.categoryX = "label";

                lineSeries.stroke = am4core.color("#02abde");
                lineSeries.strokeWidth = 3;
                lineSeries.propertyFields.strokeDasharray = "lineDash";
                lineSeries.tooltip.label.textAlign = "middle";

                let title = chart.titles.create();
                title.text = "RIESGO PAÍS: " + (args.title != undefined ? args.title.toUpperCase() : '');
                title.fontSize = 16;
                title.fontWeight = 500;

                if (args.scroll !== undefined) {
                    if (args.scroll) {
                        // Add scrollbar
                        chart.scrollbarX = new am4core.Scrollbar();
                        chart.cursor = new am4charts.XYCursor();
                    }
                } else {
                    // Add scrollbar
                    chart.scrollbarX = new am4core.Scrollbar();
                    chart.cursor = new am4charts.XYCursor();
                }

                // var bullet = lineSeries.bullets.push(new am4charts.Bullet());
                // bullet.fill = am4core.color("#fdd400"); // tooltips grab fill from parent by default
                // bullet.tooltipText = "[#fff font-size: 15px]{name} in {categoryX}:\n[/][#fff font-size: 20px]{valueY}[/] [#fff]{additional}[/]"
                // var circle = bullet.createChild(am4core.Circle);
                // circle.radius = 4;
                // circle.fill = am4core.color("#fff");
                // circle.strokeWidth = 3;
                chart.events.on('ready', () => {
                    resolve(chart);
                });
            } catch (err) {
                reject(err)
                console.error(err);
            }
        });
        return chart;
    },
    onColumnSingle: function (args) {
        return new Promise((resolve, reject)=>{
            let chart;
            try {
                args = this.validate(args);
                if (args.animate) {
                    // Themes
                    am4core.useTheme(am4themes_animated);
                }
                // Create chart instance
                chart = am4core.create(args.scope, am4charts.XYChart);
                if (chart.logo) {
                    chart.logo.disabled = true;
                }

                let vmax = this.helper.getMaxValue(args.data);

                /* Title */
                let title = chart.titles.create();
                title.text = "COSTO DE DEUDA";
                title.fontSize = 14;
                title.marginBottom = 10;
                if (args.fontSize != undefined) {
                    title.fontSize = args.fontSize;
                }
                /* Create axes */
                var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
                categoryAxis.dataFields.category = "label";
                categoryAxis.renderer.minGridDistance = 1;
                categoryAxis.numberFormatter = new am4core.NumberFormatter();
                categoryAxis.renderer.grid.template.location = 0;
                categoryAxis.renderer.grid.template.disabled = true;
                if (args.fontSize != undefined) {
                    categoryAxis.renderer.labels.template.fontSize = args.fontSize;
                }
                var label = categoryAxis.renderer.labels.template;
                label.truncate = true;
                label.tooltipText = "{category}";

                categoryAxis.events.on("sizechanged", function(ev) {
                    var axis = ev.target;
                    var cellWidth = axis.pixelWidth / (axis.endIndex - axis.startIndex);
                    axis.renderer.labels.template.maxWidth = cellWidth;
                });

                /* Create value axis */
                var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
                valueAxis.numberFormatter = new am4core.NumberFormatter();
                valueAxis.numberFormatter.numberFormat = "#'%'";
                //valueAxis.renderer.labels.template.disabled = true;
                valueAxis.renderer.grid.template.disabled = true;
                valueAxis.renderer.grid.template.location = 0;
                valueAxis.min = 0;
                valueAxis.max = vmax;
                /* Create series */
                var columnSeries = chart.series.push(new am4charts.ColumnSeries());
                columnSeries.name = "Punto";
                columnSeries.dataFields.valueY = "value";
                columnSeries.dataFields.categoryX = "label";
                // columnSeries.columns.template.tooltipText = "{valueY}%"
                columnSeries.columns.template.propertyFields.fillOpacity = "fillOpacity";
                columnSeries.columns.template.propertyFields.stroke = "color"; // "stroke"
                columnSeries.columns.template.propertyFields.strokeWidth = 4; // "strokeWidth";
                columnSeries.columns.template.propertyFields.strokeDasharray = "columnDash";
                columnSeries.tooltip.label.textAlign = "middle";
                columnSeries.columns.template.showTooltipOn = "always";
                columnSeries.tooltip.pointerOrientation = "down";
                columnSeries.columns.template.tooltipY = 0;

                columnSeries.columns.template.strokeWidth = 2;
                columnSeries.columns.template.fill = am4core.color("#fff");
                if (args.fontSize != undefined) {
                    columnSeries.tooltip.label.fontSize = args.fontSize;
                }

                // Add label
                var labelBullet = columnSeries.bullets.push(new am4charts.LabelBullet());
                labelBullet.label.text = "[bold]{valueY}%";
                /*labelBullet.locationY = 0.5;
                labelBullet.label.hideOversized = true;
                labelBullet.label.fill = am4core.color("#000");*/
                labelBullet.label.dy = -15;
                labelBullet.label.hideOversized = false;
                labelBullet.label.truncate = false;

                if (args.fontSize != undefined) {
                    labelBullet.label.fontSize = args.fontSize;
                }

                /*columnSeries.columns.template.adapter.add("fill", (fill, target) => {
                    console.log(target);
                    return chart.colors.getIndex(target.dataItem.index);
                });*/
                
                if (!(args.cursor != undefined && args.cursor == false)) {
                    // chart.cursor = new am4charts.XYCursor();
                }
                /* Data */
                chart.data = args.data;
                chart.events.on('ready', () => {
                    resolve(chart);
                });
            } catch (err) {
                reject(err)
                console.error(err);
            }
        });
        return chart;
    },
};
window.AppChartSystem = AppChartSystem;