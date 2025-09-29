const AppExportPDF = function() {
    let 
    __this = this,
    _defaults = {
        html: {
            AppExportGeneral: 'AppExportGeneral',
            AppStructureExport: 'AppStructureExport',
            AppPanelExport: 'AppPanelExport',
            AppCoverPage: 'AppCoverPage',
            AppContentPage: 'AppContentPage',
            btnExportPdf:'.btnExportPdf',
            btnExportAllPdf:'#btnExportAllPdf'
        }
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
                let html = _defaults.html,
                $panel = $('#' + html.AppExportGeneral);
                $panel.append($('<div/>', {id: html.AppStructureExport, class: 'col12', style:"margin-top: 900px;"}));
                $panel.append($('<div/>', {id: html.AppPanelExport}));
                $panel.find('#'+html.AppPanelExport).append($('<div/>', {id: html.AppCoverPage, class: 'col12'}));
                $panel.find('#'+html.AppPanelExport).append($('<div/>', {id: html.AppContentPage, class: 'col12'}));
                $panel.find('#'+html.AppStructureExport).html(args.structure);
                $panel.find('#'+html.AppCoverPage).html($panel.find('#'+html.AppStructureExport).find('#cover-page').html());
                $panel.find('#'+html.AppStructureExport).find('#cover-page').html('');
                let body = args.design.body;
                for (let i = 0; i < args.contents.length; i++) {
                    const 
                    item = args.contents[i].code,
                    div = $panel.find('#'+html.AppStructureExport).find(`[data-code='${item}']`);
                    body = body.replace(item, div.html());
                }
                $panel.find('#'+html.AppStructureExport).find('#content-page').html('');
                $panel.find('#'+html.AppContentPage).html('<div style="width:700px !important">'+body+'</div>');                
                args.filename = args.design.name;
                if (args.report.name) {
                    args.filename = args.filename + ' - ' + args.report.name;
                }
                if (args.report.entity) {
                    args.filename = args.filename + ' - ' + args.report.entity;
                }
                args.filename = args.filename.toUpperCase();
                resolve(args);
            });
        },
        graph: function (args) {
            return new Promise((resolve, reject)=>{
                let charts = [];
                let fontsize = 15; 
                let cost_economic = args.system.calculate;
                if (document.getElementById('cost-economic-chart')) {
                    let cost_chart = AppChartSystem.onColumnAll({
                        scope: 'cost-economic-chart', 
                        data: cost_economic.graph, 
                        animate: false,
                        fontSize: fontsize
                    });    
                    charts.push({
                        graph: 'cost-economic-chart',
                        img: 'cost-economic-img',
                        chart: cost_chart
                    });
                }

                let curve = args.system.curve;     
                if (document.getElementById('curve-chart')) {
                    let curve_chart = AppChartSystem.oncurve({
                        scope: 'curve-chart', 
                        data: curve.graph.performance, 
                        animate: false,
                        fontSize: fontsize
                    });
                    charts.push({
                        graph: 'curve-chart',
                        img: 'curve-img',
                        chart: curve_chart
                    });
                }

                let curve_project = args.system.curve_project;
                if (document.getElementById('curve-project-chart')) {
                    let curve_project_chart = AppChartSystem.oncurve({
                        scope: 'curve-project-chart', 
                        data: curve_project.graph.performance, 
                        animate: false,
                        fontSize: fontsize
                    });    
                    charts.push({
                        graph: 'curve-project-chart',
                        img: 'curve-project-img',
                        chart: curve_project_chart
                    });
                }

                let developed = args.system.developed;
                if (document.getElementById('developed-general-chart')) {
                    let developed_general_chart = AppChartSystem.onColumnSM({
                        scope: 'developed-general-chart', 
                        data: developed.structure.graph.general, 
                        animate: false,                        
                        fontSize: fontsize
                    });    
                    charts.push({
                        graph: 'developed-general-chart',
                        img: 'developed-general-img',
                        chart: developed_general_chart
                    });
                }

                if (document.getElementById('developed-structure-chart')) {
                    let developed_structure_chart = AppChartSystem.onGroupSM({
                        scope: 'developed-structure-chart', 
                        data: developed.structure.graph.structure, 
                        animate: false,
                        fontSize: fontsize
                    });    
                    charts.push({
                        graph: 'developed-structure-chart',
                        img: 'developed-structure-img',
                        chart: developed_structure_chart
                    });
                }

                if (document.getElementById('developed-parameter-chart')) {
                    let developed_parameter_chart = AppChartSystem.onGroupPM({
                        scope: 'developed-parameter-chart', 
                        data: developed.parameter.graph.general, 
                        animate: false,
                        fontSize: fontsize,
                        legend: {
                            position: 'top'
                        }
                    });    
                    charts.push({
                        graph: 'developed-parameter-chart',
                        img: 'developed-parameter-img',
                        chart: developed_parameter_chart
                    });
                }

                if (document.getElementById('developed-average-chart')) {
                    let developed_average_chart = AppChartSystem.onPointAMV2({
                        scope: 'developed-average-chart', 
                        data: developed.average.graph.general, 
                        animate: false,
                        fontSize: fontsize
                    });    
                    charts.push({
                        graph: 'developed-average-chart',
                        img: 'developed-average-img',
                        chart: developed_average_chart
                    });
                }

                let emerging = args.system.emerging;
                if (document.getElementById('emerging-general-chart')) {
                    let emerging_general_chart = AppChartSystem.onGroupSM({
                        scope: 'emerging-general-chart', 
                        data: emerging.structure.graph.general, 
                        animate: false,
                        fontSize: fontsize
                    });    
                    charts.push({
                        graph: 'emerging-general-chart',
                        img: 'emerging-general-img',
                        chart: emerging_general_chart
                    });
                }

                if (document.getElementById('emerging-parameter-chart')) {
                    let emerging_parameter_chart = AppChartSystem.onGroupPM({
                        scope: 'emerging-parameter-chart', 
                        data: emerging.parameter.graph.general, 
                        animate: false,
                        fontSize: fontsize,
                        legend: {
                            position: 'top'
                        }
                    });    
                    charts.push({
                        graph: 'emerging-parameter-chart',
                        img: 'emerging-parameter-img',
                        chart: emerging_parameter_chart
                    });
                }

                if (document.getElementById('emerging-average-chart')) {
                    let emerging_average_chart = AppChartSystem.onPointAMV2({
                        scope: 'emerging-average-chart', 
                        data: emerging.average.graph.general, 
                        animate: false,
                        fontSize: fontsize
                    });    
                    charts.push({
                        graph: 'emerging-average-chart',
                        img: 'emerging-average-img',
                        chart: emerging_average_chart
                    });
                }

                let company = args.system.company;
                if (document.getElementById('company-structure-chart')) {
                    let company_general_chart = AppChartSystem.onGroupSM({
                        scope: 'company-structure-chart', 
                        data: company.structure.graph.general, 
                        animate: false,
                        fontSize: fontsize
                    });    
                    charts.push({
                        graph: 'company-structure-chart',
                        img: 'company-structure-img',
                        chart: company_general_chart
                    });
                }

                if (document.getElementById('company-parameter-chart')) {
                    let company_parameter_chart = AppChartSystem.onGroupPM({
                        scope: 'company-parameter-chart', 
                        data: company.parameter.graph.general, 
                        animate: false,
                        fontSize: fontsize,
                        legend: {
                            position: 'top'
                        }
                    });    
                    charts.push({
                        graph: 'company-parameter-chart',
                        img: 'company-parameter-img',
                        chart: company_parameter_chart
                    });
                }

                if (document.getElementById('company-dolares-chart')) {
                    let company_dolares_chart = AppChartSystem.onPointAMV2({
                        scope: 'company-dolares-chart', 
                        data: company.dolares.graph.general, 
                        animate: false,
                        fontSize: fontsize
                    });    
                    charts.push({
                        graph: 'company-dolares-chart',
                        img: 'company-dolares-img',
                        chart: company_dolares_chart
                    });
                }

                if (document.getElementById('company-national-chart')) {
                    let company_national_chart = AppChartSystem.onPointAMV2({
                        scope: 'company-national-chart', 
                        data: company.national.graph.general, 
                        animate: false,
                        fontSize: fontsize
                    });    
                    charts.push({
                        graph: 'company-national-chart',
                        img: 'company-national-img',
                        chart: company_national_chart
                    });
                }


                let report = args.system.report;
                if (document.getElementById('report-company-chart')) {
                    let report_general_chart = AppChartSystem.onColumnHztRC({
                        scope: 'report-company-chart', 
                        data: report.graph.general, 
                        animate: false,
                        fontSize: fontsize,
                        rotation: 90
                    });    
                    charts.push({
                        graph: 'report-company-chart',
                        img: 'report-company-img',
                        chart: report_general_chart
                    });
                }

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
                kendo.pdf.defineFont({
                    "DejaVu Sans": "http://kendo.cdn.telerik.com/2014.3.1314/styles/fonts/DejaVu/DejaVuSans.ttf",
                    "DejaVu Sans|Bold": "http://kendo.cdn.telerik.com/2014.3.1314/styles/fonts/DejaVu/DejaVuSans-Bold.ttf",
                    "DejaVu Sans|Bold|Italic": "http://kendo.cdn.telerik.com/2014.3.1314/styles/fonts/DejaVu/DejaVuSans-Oblique.ttf",
                    "DejaVu Sans|Italic": "http://kendo.cdn.telerik.com/2014.3.1314/styles/fonts/DejaVu/DejaVuSans-Oblique.ttf"
                });
                kendo.drawing.drawDOM('#'+_defaults.html.AppPanelExport, {
                    paperSize: "A4",
                    // margin: { left: "1.8cm", top: "2.8cm", right: "1.8cm", bottom: "2.8cm" },
                    scale: 0.7,
                    template: $("#page-template").html()
                }).then(function(group){
                    kendo.drawing.pdf.saveAs(group, args.filename + '.pdf');
                    $('#'+_defaults.html.AppExportGeneral).html('');
                });
            });
        },
        waitTime: function (args) {
            return new Promise((resolve, reject) => {
                setTimeout(() => {
                    resolve(args);
                }, 4500); 
            });
        },
    };

    __this.report = function () {
        let { all, scheme, graph, exportPDF, waitTime } = _methods;
        $(_defaults.html.btnExportPdf).off('click');
        $(_defaults.html.btnExportPdf).on('click', function (e) {
            swal2.loading();
            let formData = new FormData();
            formData.append('id', $(this).attr('data-id'));
            all(formData)
            .then(scheme)
            .then(graph)
            .then(waitTime)
            .then(exportPDF)
            .then(function () {
                swal2.loading(false);
            })
            .catch(function (e) {
                $('#'+_defaults.html.AppExportGeneral).html('');
                console.error(e);
                swal2.show({ html: e, icon: 'error'});
            });
        });
    }

    __this.reportDefaultByCode = function (args) {
        let { all, scheme, graph, exportPDF, waitTime } = _methods;
        $(_defaults.html.btnExportPdf).off('click');
        $(_defaults.html.btnExportPdf).on('click', function (e) {
            sweet2.loading();
            let formData = new FormData();
            formData.append('code', args.code);
            formData.append('id', $(this).attr('data-id'));
            // formData.append('type', 2);
            all(formData)
            .then(scheme)
            .then(graph)
            .then(waitTime)
            .then(exportPDF)
            .then(function () {
                sweet2.loading(false);
            })
            .catch(function (e) {
                $('#'+_defaults.html.AppExportGeneral).html('');
                console.error(e);
                sweet2.show({ html: e, icon: 'error'});
            });
        });
    }

    __this.initBuildReport = function (code, id) {
        let { all, scheme, graph, exportPDF, waitTime } = _methods;
        sweet2.loading();
        let formData = new FormData();
        formData.append('code', code);
        formData.append('id', id);
        all(formData)
        .then(scheme)
        .then(graph)
        .then(waitTime)
        .then(exportPDF)
        .then(function () {
            setTimeout(() => {
                window.location.href = '/';
            }, 2000);
        })
        .catch(function (e) {
            $('#'+_defaults.html.AppExportGeneral).html('');
            console.error(e);
            sweet2.show({ html: e, icon: 'error'});
        });
    }
}
window.AppExportPDF = new AppExportPDF();