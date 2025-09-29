const AppExportPDF = function() {
    let 
    __this = this,
    _defaults = {
        btnExporPdf:'#btnExportPdf',
        AppPanelExport: '#AppPanelExport',
        AppStructureExport: '#AppStructureExport'
    },
    _methods = {
        all: function (formData) {
            return new Promise((resolve, reject) => {
                fetch("/system/getAllReport", {method: "POST", body: formData })
                .then(function(res){ return res.json(); })
                .then(function(rsp) { rsp.success ? resolve(rsp.data) : reject(rsp.message) })
                .catch(function(e) { reject(e); });
            });
        },
        scheme: function (args) {
            return new Promise((resolve, reject)=>{
                $(_defaults.AppStructureExport).html(args.structure);
                $(_defaults.AppPanelExport).html(args.design.body);
                resolve(args);
            });
        },
        graph: function (args) {
            return new Promise((resolve, reject)=>{
                let charts = [];
                let curve = args.report.curve;
                let curve_chart = AppChartSystem.oncurve({
                    scope: 'curve-chart', 
                    data: curve.graph.performance, 
                    animate: false
                });
                
                charts.push({
                    graph: 'curve-chart',
                    img: 'curve-img',
                    chart: curve_chart
                });

                let developed = args.report.developed;
                let developed_general_chart = AppChartSystem.onColumnSM({
                    scope: 'developed-general-chart', 
                    data: developed.structure.graph.general, 
                    animate: false
                });

                charts.push({
                    graph: 'developed-general-chart',
                    img: 'developed-general-img',
                    chart: developed_general_chart
                });

                let developed_structure_chart = AppChartSystem.onGroupSM({
                    scope: 'developed-structure-chart', 
                    data: developed.structure.graph.structure, 
                    animate: false
                });

                charts.push({
                    graph: 'developed-structure-chart',
                    img: 'developed-structure-img',
                    chart: developed_structure_chart
                });

                let developed_parameter_chart = AppChartSystem.onGroupPM({
                    scope: 'developed-parameter-chart', 
                    data: developed.parameter.graph.general, 
                    animate: false
                });

                charts.push({
                    graph: 'developed-parameter-chart',
                    img: 'developed-parameter-img',
                    chart: developed_parameter_chart
                });

                let developed_average_chart = AppChartSystem.onPointAMV2({
                    scope: 'developed-average-chart', 
                    data: developed.average.graph.general, 
                    animate: false
                });

                charts.push({
                    graph: 'developed-average-chart',
                    img: 'developed-average-img',
                    chart: developed_average_chart
                });

                let emerging = args.report.emerging;
                let emerging_general_chart = AppChartSystem.onGroupSM({
                    scope: 'emerging-general-chart', 
                    data: emerging.structure.graph.general, 
                    animate: false
                });

                charts.push({
                    graph: 'emerging-general-chart',
                    img: 'emerging-general-img',
                    chart: emerging_general_chart
                });

                let emerging_parameter_chart = AppChartSystem.onGroupPM({
                    scope: 'emerging-parameter-chart', 
                    data: emerging.parameter.graph.general, 
                    animate: false
                });

                charts.push({
                    graph: 'emerging-parameter-chart',
                    img: 'emerging-parameter-img',
                    chart: emerging_parameter_chart
                });

                let emerging_average_chart = AppChartSystem.onPointAMV2({
                    scope: 'emerging-average-chart', 
                    data: emerging.average.graph.general, 
                    animate: false
                });

                charts.push({
                    graph: 'emerging-average-chart',
                    img: 'emerging-average-img',
                    chart: emerging_average_chart
                });

                let company = args.report.company;
                let company_general_chart = AppChartSystem.onGroupSM({
                    scope: 'company-structure-chart', 
                    data: company.structure.graph.general, 
                    animate: false
                });

                charts.push({
                    graph: 'company-structure-chart',
                    img: 'company-structure-img',
                    chart: company_general_chart
                });

                let company_parameter_chart = AppChartSystem.onGroupPM({
                    scope: 'company-parameter-chart', 
                    data: company.parameter.graph.general, 
                    animate: false
                });

                charts.push({
                    graph: 'company-parameter-chart',
                    img: 'company-parameter-img',
                    chart: company_parameter_chart
                });

                let company_dolares_chart = AppChartSystem.onPointAMV2({
                    scope: 'company-dolares-chart', 
                    data: company.dolares.graph.general, 
                    animate: false
                });

                charts.push({
                    graph: 'company-dolares-chart',
                    img: 'company-dolares-img',
                    chart: company_dolares_chart
                });

                let company_national_chart = AppChartSystem.onPointAMV2({
                    scope: 'company-national-chart', 
                    data: company.national.graph.general, 
                    animate: false
                });

                charts.push({
                    graph: 'company-national-chart',
                    img: 'company-national-img',
                    chart: company_national_chart
                });


                let report = args.report.report;
                let report_general_chart = AppChartSystem.onColumnHztRC({
                    scope: 'report-company-chart', 
                    data: report.graph.general, 
                    animate: false
                });

                charts.push({
                    graph: 'report-company-chart',
                    img: 'report-company-img',
                    chart: report_general_chart
                });

                args.charts = charts;
                resolve(args);
            });
        },
        exportPDF: function (args) {
            let charts = [];
            for (let i = 0; i < args.charts.length; i++) {
                const element = args.charts[i];
                charts.push(element.chart.exporting.getImage("png"));
            }
            return Promise.all(charts)
            .then(imgs => {
                for (let i = 0; i < args.charts.length; i++) {
                    let d = args.charts[i];
                    $('#' + d.graph).remove();
                    $('#' + d.img).attr('src', imgs[i]);
                }
            })			
            .then(rs => {
                kendo.drawing.drawDOM(_defaults.AppStructureExport, {
                    paperSize: "A4",
                    margin: { left: "0.5cm", top: "1.5cm", right: "0.5cm", bottom: "1.5cm" },
                    scale: 0.5,
                    template: $("#page-template").html()
                }).then(function(group){
                    kendo.drawing.pdf.saveAs(group, "Reporte de Inversión.pdf");
                    $(_defaults.AppStructureExport).html('');
                });                    
            });
        }
    };
    __this.report = function () {
        let { all, scheme, graph, exportPDF } = _methods;
        $(_defaults.btnExporPdf).off('click');
        $(_defaults.btnExporPdf).on('click', function (e) {
            swal2.loading();
            all(new FormData())
            .then(scheme)
            .then(graph)
            .then(exportPDF)
            .then(function () {
                swal2.loading(false);
            })
            .catch(function (e) {
                console.error(e);
                swal2.show({ html: e, icon: 'error'});
            });
        });
        return;
/*
        let charts = [];
        let curve = args.system.curve;
        let curve_chart = AppChartSystem.oncurve({
            scope: 'curve-chart', 
            data: curve.graph.performance, 
            animate: false
        });
        
        charts.push({
            chart: 'curve-chart',
            img: 'curve-img'
        });

        let developed = args.system.developed;
        let developed_general_chart = AppChartSystem.onColumnSM({
            scope: 'developed-general-chart', 
            data: developed.structure.graph.general, 
            animate: false
        });

        charts.push({
            chart: 'developed-general-chart',
            img: 'developed-general-img'
        });

        let developed_structure_chart = AppChartSystem.onGroupSM({
            scope: 'developed-structure-chart', 
            data: developed.structure.graph.structure, 
            animate: false
        });

        charts.push({
            chart: 'developed-structure-chart',
            img: 'developed-structure-img'
        });

        let developed_paremeter_chart = AppChartSystem.onGroupPM({
            scope: 'developed-paremeter-chart', 
            data: developed.paremeter.graph.general, 
            animate: false
        });

        charts.push({
            chart: 'developed-paremeter-chart',
            img: 'developed-paremeter-img'
        });

        return;
        Promise.all([
            curve_chart.exporting.getImage("png"), 
            developed_general_chart.exporting.getImage("png"), 
            developed_structure_chart.exporting.getImage("png"),
            developed_paremeter_chart.exporting.getImage("png")
        ])
        .then(imgs => {
            for (let i = 0; i < charts.length; i++) {
                let d = charts[i];
                $('#' + d.chart).remove();
                $('#' + d.img).attr('src', imgs[i]);
            }
        })			
        .then(rs => {
            kendo.drawing.drawDOM("#AppPanelExport", {
                paperSize: "A4",
                margin: "1.5cm",
                scale: 0.5,
                //height: 500,
                template: $("#page-template").html()
            }).then(function(group){
                kendo.drawing.pdf.saveAs(group, "filename.pdf");
            });
        });
        */
    }

    __this.init = function (args) {
        let charts = [];
        let curve = args.system.curve;
        let curve_chart = AppChartSystem.oncurve({
            scope: 'curve-chart', 
            data: curve.graph.performance, 
            animate: false
        });
        
        charts.push({
            chart: 'curve-chart',
            img: 'curve-img'
        });

        let developed = args.system.developed;
        let developed_general_chart = AppChartSystem.onColumnSM({
            scope: 'developed-general-chart', 
            data: developed.structure.graph.general, 
            animate: false
        });

        charts.push({
            chart: 'developed-general-chart',
            img: 'developed-general-img'
        });

        let developed_structure_chart = AppChartSystem.onGroupSM({
            scope: 'developed-structure-chart', 
            data: developed.structure.graph.structure, 
            animate: false
        });

        charts.push({
            chart: 'developed-structure-chart',
            img: 'developed-structure-img'
        });

        let developed_parameter_chart = AppChartSystem.onGroupPM({
            scope: 'developed-parameter-chart', 
            data: developed.parameter.graph.general, 
            animate: false
        });

        charts.push({
            chart: 'developed-parameter-chart',
            img: 'developed-parameter-img'
        });

        return;
        Promise.all([
            curve_chart.exporting.getImage("png"), 
            developed_general_chart.exporting.getImage("png"), 
            developed_structure_chart.exporting.getImage("png"),
            developed_paremeter_chart.exporting.getImage("png")
        ])
        .then(imgs => {
            for (let i = 0; i < charts.length; i++) {
                let d = charts[i];
                $('#' + d.chart).remove();
                $('#' + d.img).attr('src', imgs[i]);
            }
        })			
        .then(rs => {
            kendo.drawing.drawDOM("#AppPanelExport", {
                paperSize: "A4",
                margin: "1.5cm",
                scale: 0.5,
                //height: 500,
                template: $("#page-template").html()
            }).then(function(group){
                kendo.drawing.pdf.saveAs(group, "filename.pdf");
            });
            /*kendo.drawing.drawDOM($('#AppPanelExport'), 
            {
                paperSize: "A4", // "auto",
                //landscape:true,
                scale: 0.5,
                height: 500,
                template: $("#page-template").html()
            })          
            .then(function(group){
                return kendo.drawing.exportPDF(group, {
                    margin: { left: "0.5cm", top: "0.5cm", right: "0.5cm", bottom: "0.5cm" }
                });
            })
            .done(function(data) {
                kendo.saveAs({
                    dataURI: data,
                    fileName: "reporte-final.pdf"
                });
            });*/
        });



















        $('.btn-export-pdf').off('click');
        $('.btn-export-pdf').on('click', function (e) {
            kendo.drawing.drawDOM($('#AppPanelExport'), {
                // "auto",
                //landscape:true,
                scale: 1})          
                .then(function(group){
                return kendo.drawing.exportPDF(group, {
                margin: { left: "1cm", top: "1cm", right: "1cm", bottom: "1cm" }
                });
            })
                .done(function(data) {
                kendo.saveAs({
                dataURI: data,
                fileName: "reporte-final.pdf"
                });
            });
            return;
            var ids = ["groupChart"];
            var charts = {},
                charts_remaining = ids.length;
            for (var i = 0; i < ids.length; i++) {
                for (var x = 0; x < AmCharts.charts.length; x++) {

                    if (AmCharts.charts[x].div != undefined) {
                        if (AmCharts.charts[x].div.id == ids[i])
                            charts[ids[i]] = AmCharts.charts[x];
                    }

                }
            }


            return;
            // Trigger export of each chart
            var vm = this;
            for (var x in charts) {
                if (charts.hasOwnProperty(x)) {
                    var chart = charts[x];
                    chart["export"].capture({}, function () {
                        this.toJPG({}, function (data) {

                            // Save chart data into chart object itself
                            this.setup.chart.exportedImage = data;

                            // Reduce the remaining counter
                            charts_remaining--;

                            // Check if we got all of the charts
                            if (charts_remaining == 0) {
                                // Yup, we got all of them
                                // Let's proceed to putting PDF together
                                generatePDF();
                            }

                        });
                    });
                }
            }

            function generatePDF() {

                vm.ImagenChartMonto = charts["chartdivMonto"].exportedImage;
                vm.ImagenChartBarras1 = charts["chartdiv"].exportedImage;
                vm.ImagenChartBarras2 = charts["chartdivContrato"].exportedImage;
                
                vm.desaparecerIdBarraMonto = false;
            }

                kendo.drawing.drawDOM("#AppParameterEmerging", {
                    // paperSize: "A4",
                    paperSize: "auto",
                    margin: { top: "1cm", bottom: "1cm" },
                    scale: 0.8,
                    height: 500
                }).then(function(group){
                    kendo.drawing.pdf.saveAs(group, "Reporte.pdf");
                    //$("#pdf_notas").remove();
                });
        
    
                    /*kendo.drawing.drawDOM($('#AppParameterEmerging'), {
                        paperSize: "A4",
                        landscape:true,
                        scale: 0.7})          
                        .then(function(group){
                        return kendo.drawing.exportImage(group, {
                        margin: { left: "1cm", top: "1cm", right: "1cm", bottom: "1cm" }
                        });
                    })
                        .done(function(data) {
                        kendo.saveAs({
                        dataURI: data,
                        fileName: "Invoice.pdf"
                        });
                    });*/
                    
            
                        /* kendo.drawing.drawDOM("#pdf_dom",
                        { 
                            paperSize: "A4",
                            margin: { top: "1cm", bottom: "1cm",left:"1cm", right:"1cm" },
                            scale: 0.8,
                            height: 450, 
                            // template: $("#pnl-reviewed-eval").html(), 
                            keepTogether: ".prevent-split"
                        })
                        .then(function (group) {
                            kendo.drawing.pdf.saveAs(group, "Reporte1.pdf");
                        });*/
            /* kendo.drawing.drawDOM($("#pdf_dom"))
                                .then(function(group) {
                                    // Render the result as a PDF file
                                    return kendo.drawing.exportPDF(group, {
                                        paperSize: "auto",
                                        margin: { left: "1cm", top: "1cm", right: "1cm", bottom: "1cm" }
                                    });
                                })
                                .done(function(data) {
                                    // Save the PDF file
                                    kendo.saveAs({
                                        dataURI: data,
                                        fileName: "Linea_De_Tiempo.pdf",
                                    });
                                });
            */
            /*kendo.drawing.drawDOM($("#pdf_dom")).then(function(group){
                    kendo.drawing.pdf.saveAs(group, "Invoice.pdf");
                    });*/
            
        });
    }
}
window.AppExportPDF = new AppExportPDF();