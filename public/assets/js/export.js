const AppExportPDF = () => {
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
    _data = {},
    _index = 0,
    _chartItems = [],
    _htmlItems = [],
    _fontsize = 14,

    _methods = {
        all: function (url, formData = new FormData()) {
            return new Promise((resolve, reject) => {
                fetch(url, {method: "POST", body: formData })
                .then(function(res){ return res.json(); })
                .then(function(rsp) { rsp.success ? resolve(rsp.data) : reject(rsp.message) })
                .catch(function(e) { reject(e); });
            });
        },
        scheme: function (args) {
            return new Promise((resolve, reject)=>{
                try {
                    _data = args;
                    let html = _defaults.html,
                    $panel = $('#' + html.AppExportGeneral);
                    $panel.append($('<div/>', {id: html.AppStructureExport, class: 'col12'}));
                    $panel.append($('<div/>', {id: html.AppPanelExport}));
                    $panel.find('#'+html.AppPanelExport).append($('<div/>', {id: html.AppCoverPage, class: 'bg-white'}));
                    $panel.find('#'+html.AppPanelExport).append($('<div/>', {id: html.AppContentPage, class: 'bg-white', style: 'width: 850px;'}));
                    $panel.find('#'+html.AppStructureExport).html(args.structure);
                    $panel.find('#'+html.AppCoverPage).html($panel.find('#'+html.AppStructureExport).find('#cover-page').html());
                    $panel.find('#'+html.AppStructureExport).find('#cover-page').html('');
                    let body = args.design.body; 
                    _chartItems = [];
                    _htmlItems = [];
                    body = body.replaceAll('$BLOQUE_INICIO$', `<div class="row">`);
                    body = body.replaceAll('$BLOQUE_FIN$', `</div>`);
                    const images = [], texts = [], tables = [], htmls = [];
                    function recursiveCode(contents, body) {
                        for (let i = 0; i < contents.length; i++) {
                            const code = contents[i].code, index = body.indexOf(code);
                            if (index !== -1) {
                                const init = Number(index) + Number(code.length);
                                let attributes = [], attr = '', open = false, codeText = code;
                                contents[i].cid = codeText.replaceAll('$$', '');    
                                for (let ii = init; ii < body.length; ii++) {
                                    const charText = body.charAt(ii);
                                    if (charText == '[') {
                                        codeText += charText;
                                        open = true;
                                        continue;
                                    } else {
                                        if (!open || charText == ']') {
                                            if (charText == ']') {
                                                codeText += charText;
                                            }
                                            attributes.push(attr);
                                            if (body.charAt(ii + 1) !== undefined && body.charAt(ii + 1) == '[') { // exist new styles
                                                attr = '';
                                                continue;
                                            } else {
                                                break;
                                            }
                                        }
                                        codeText += charText;
                                        attr += charText;
                                    }
                                }
                                _index ++;
                                let template = ``;
                                if (contents[i].type == 1) { // table
                                    /*template = _template.table.find(o => { return o.code == code });
                                    body = body.replace(codeText, template ? template.html({code: code, attributes: attributes}) : '');*/
                                    tables.push(contents[i]);
                                    body = body.replace(codeText, `<div class="${contents[i].cid} text-center">Cargando...</div>`);
                                } else if (contents[i].type == 2) { // chart
                                    images.push(contents[i]);
                                    body = body.replace(codeText, `<img src="" class="${contents[i].cid}" title="Cargando...">`);
                                } else if (contents[i].type == 3) { // text
                                    /*template = _template.text.find(o => { return o.code == code });
                                    body = body.replace(codeText, template.html());*/
                                    texts.push(contents[i]);
                                    body = body.replace(codeText, `<span class="${contents[i].cid}"></span>`);
                                } else if (contents[i].type == 4) { // html
                                    /*let htmlItem = _template.html.find(o => { return o.code == code });
                                    htmlItem = htmlItem.html({code: code, attributes: attributes});
                                    body = body.replace(codeText, htmlItem.html);*/
                                    // body = body.replace(codeText, '');
                                    htmls.push(contents[i]);
                                    body = body.replace(codeText, `<div class="${contents[i].cid}"></div>`);
                                } else {
                                    body = body.replace(codeText, ``);
                                }
                                body = body.replace(codeText, ``);
                                return recursiveCode(contents, body);
                            }
                        }
                        return body;
                    }
                    function recursiveContent(body) {
                        const openText = '{=', closeText = '=}'; 
                        const openIndex = body.indexOf(openText);
                        let content = '';
                        if (openIndex !== -1) {
                            const closeIndex = body.indexOf(closeText);
                            if (closeIndex !== -1) {
                                const start = Number(openIndex) + Number(openText.length);
                                const end = Number(closeIndex);
                                for (let i = start; i < end; i++) {
                                    content += body.charAt(i);
                                }
                                const init = end + Number(closeText.length);
                                let attributes = [], attr = '', open = false, codeText = openText + content + closeText;
                                for (let ii = init; ii < body.length; ii++) {
                                    const charText = body.charAt(ii);
                                    if (charText == '[') {
                                        codeText += charText;
                                        open = true;
                                        continue;
                                    } else {
                                        if (!open || charText == ']') {
                                            if (charText == ']') {
                                                codeText += charText;
                                            }
                                            attributes.push(attr);
                                            if (body.charAt(ii + 1) !== undefined && body.charAt(ii + 1) == '[') { // exist new styles
                                                attr = '';
                                                continue;
                                            } else {
                                                break;
                                            }
                                        }
                                        codeText += charText;
                                        attr += charText;
                                    }
                                }
                                _index ++;
                                let template = _template.content.html({content: content, attributes: attributes});
                                body = body.replace(codeText, template);
                                return recursiveContent(body);
                            }
                        }
                        return body;
                    }
                    body = recursiveContent(body);
                    body = recursiveCode(args.contents, body);

                    args.complements = JSON.stringify({images, texts, htmls, tables});
                    args.chartItems = _chartItems;
                    args.htmlItems = _htmlItems;
                    $panel.find('#'+html.AppContentPage).html('<div style="width:700px !important;">'+body+'</div>');
                    $('#panel-report').removeClass('d-none');
                    args.filename = args.design.name;
                    if (args.report.name) {
                        args.filename = args.filename + ' - ' + args.report.name;
                    }
                    if (args.report.entity) {
                        args.filename = args.filename + ' - ' + args.report.entity;
                    }
                    args.filename = args.filename.toUpperCase();
                    resolve(args);
                } catch (err) {
                    console.log(err);
                    reject(err)
                }
            });
        },
        graph: function (args) {
            return new Promise((resolve, reject)=>{

                for (let i = 0; i < args.htmlItems.length; i++) {
                    const elem = args.htmlItems[i];
                    let d = AppChartHtml.oneGraph(elem.htmlId, elem.data);
                }

                let charts = [];
                if (args.chartItems.length > 0) {
                    for (let i = 0; i < args.chartItems.length; i++) {
                        const elem = args.chartItems[i];
                        AppChartSystem[elem.chart](elem.param)
                        .then(chart => {
                            charts.push({chart:chart, graph: elem.chartId, img: elem.imgId});
                            if (i == (args.chartItems.length - 1)) { // last
                                args.charts = charts;
                                resolve(args);
                            }
                        })
                        .catch( err => {
                            reject(err);
                        });
                    }
                } else {
                    args.charts = charts;
                    resolve(args)
                }
            });
        },
        convert: function (args) {
            return new Promise((resolve, reject)=>{
                let charts = [];
                for (let i = 0; i < args.charts.length; i++) {
                    const element = args.charts[i];
                    charts.push(element.chart.exporting.getImage("png"));
                }
                Promise.all(charts)
                .then(imgs => {
                    for (let i = 0; i < args.charts.length; i++) {
                        let d = args.charts[i];
                        $('#' + d.graph).remove();
                        $('#' + d.img).attr('src', imgs[i]).removeClass('d-none');
                    }
                    resolve(args);
                })
                .catch(err => {
                    reject(err);
                });
            });
        },
        exporting: function (args) {
            kendo.pdf.defineFont({
                "DejaVu Sans": "https://kendo.cdn.telerik.com/2014.3.1314/styles/fonts/DejaVu/DejaVuSans.ttf",
                "DejaVu Sans|Bold": "https://kendo.cdn.telerik.com/2014.3.1314/styles/fonts/DejaVu/DejaVuSans-Bold.ttf",
                "DejaVu Sans|Bold|Italic": "https://kendo.cdn.telerik.com/2014.3.1314/styles/fonts/DejaVu/DejaVuSans-Oblique.ttf",
                "DejaVu Sans|Italic": "https://kendo.cdn.telerik.com/2014.3.1314/styles/fonts/DejaVu/DejaVuSans-Oblique.ttf"
            });
            return kendo.drawing.drawDOM('#'+_defaults.html.AppPanelExport, {
                paperSize: "A4",
                // margin: { left: "1.8cm", top: "2.8cm", right: "1.8cm", bottom: "2.8cm" },
                scale: 0.7,
                template: $("#page-template").html()
            }).then(function(group){
                kendo.drawing.pdf.saveAs(group, args.filename + '.pdf');
                return args;
            });
        },
        waitTime: function (args) {
            return new Promise((resolve, reject) => {
                setTimeout(() => {
                    resolve(args);
                }, 5000); 
            });
        },
        complement: function (url, formData = new FormData()) {
            return new Promise((resolve, reject) => {
                fetch(url, {method: "POST", body: formData })
                .then(function(res){ return res.json(); })
                .then(function(rsp) { rsp.success ? resolve(rsp.data) : reject(rsp.message) })
                .catch(function(e) { reject(e); });
            });
        },
    },

    _template = {
        renderChart: function (args) {
            let extra = {};
            if (args.height !== undefined) {
                extra.height = args.height;
            }
            let { chartId, imgId } = args;
            let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 2, extra: extra});
            return  `<div id="${ chartId }" class="ichart ${myclass}" style="${mystyles}"></div>
                     <img id="${ imgId }" src="" class="ichart d-none ${myclass}" style="${mystyles}" />`;
        },
        renderHtml: function (args) {
            let extra = {};
            if (args.height !== undefined) {
                extra.height = args.height;
            }
            let { htmlId } = args;
            let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 2, extra: extra});
            return  `<div id="${ htmlId }" class="ichart ${myclass}" style="${mystyles}"></div>`;
        },
        css: function (args) {
            let { attributes, type, extra } = args;
            let styles = [], classes = [];
            for (let i = 0; i < attributes.length; i++) {
                let elem = attributes[i].trim();
                if (elem) {
                    if (elem.indexOf('.') !== -1) { // class
                        let array = elem.split('.');
                        for (let ii = 0; ii < array.length; ii++) {
                            if (array[ii]) {
                                classes.push(array[ii]);
                            }
                        }
                    } else if (elem.indexOf('*') !== -1) { // styles
                        let array = elem.split('*');
                        for (let ii = 0; ii < array.length; ii++) {
                            if (array[ii]) {
                                styles.push(array[ii]);
                            }
                        }
                    }
                }
            }
            let stylestext = '', classtext = '';
            if (type == 2) {
                if (extra && extra.height !== undefined) {
                    stylestext += extra.height;
                } else {
                    stylestext += `height:350px;`;
                }
            }
            for (let i = 0; i < styles.length; i++) {
                stylestext += styles[i];
            }
            for (let i = 0; i < classes.length; i++) {
                classtext += ' '+classes[i];
            }
            return { mystyles: stylestext, myclass: classtext };
        },
        chart: [
            {
                code: '$$FE2DP$$',
                html: function (args) {
                    let data = _data.system.calculate;
                    let chartId = `cost-economic-chart-${ _index }`;
                    let imgId = `cost-economic-img-${ _index }`;
                    args.chartId = chartId; 
                    args.imgId = imgId;
                    return  {
                        html: _template.renderChart(args),
                        item: {
                            chartId: chartId,
                            imgId: imgId,
                            chart: 'onColumnAll',
                            param: {
                                scope: chartId,
                                data: data.graph, 
                                animate: false,
                                fontSize: _fontsize,
                                cursor: false,
                                tooltipText: false
                            }
                        }
                    };
                }
            },
            {
                code: '$$W6OMQ$$',
                html: function (args) {
                    let data = _data.system.developed;
                    let chartId = `developed-general-chart-${ _index }`;
                    let imgId = `developed-genera-img-${ _index }`;
                    args.chartId = chartId; 
                    args.imgId = imgId;
                    return  {
                        html: _template.renderChart(args),
                        item: {
                            chartId: chartId,
                            imgId: imgId,
                            chart: 'onColumnSM',
                            param: {
                                scope: chartId, 
                                data: data.structure.graph.general, 
                                animate: false,                        
                                fontSize: _fontsize,
                                cursor: false
                            }
                        },
                    };
                }
            },
            {
                code: '$$W3VUC$$',
                html: function (args) {
                    let data = _data.system.curve;
                    let chartId = `curve-chart-${ _index }`;
                    let imgId = `curve-img-${ _index }`;
                    args.chartId = chartId; 
                    args.imgId = imgId;
                    return  {
                        html: _template.renderChart(args),
                        item: {
                            chartId: chartId,
                            imgId: imgId,
                            chart: 'oncurve',
                            param: {
                                scope: chartId, 
                                data: data.graph.performance, 
                                animate: false,                        
                                fontSize: 10,
                                cursor: false
                            }
                        },
                    };
                }
            },
            {
                code: '$$AZS5F$$',
                html: function (args) {
                    let data = _data.system.curve_project;
                    let chartId = `curve-project-chart-${ _index }`;
                    let imgId = `curve-project-img-${ _index }`;
                    args.chartId = chartId; 
                    args.imgId = imgId;
                    return  {
                        html: _template.renderChart(args),
                        item: {
                            chartId: chartId,
                            imgId: imgId,
                            chart: 'oncurve',
                            param: {
                                scope: chartId, 
                                data: data.graph.performance, 
                                animate: false,                        
                                fontSize: 10,
                                cursor: false
                            }
                        },
                    };
                }
            },
            {
                code: '$$AE58D$$',
                html: function (args) {
                    let data = _data.system.developed;
                    let chartId = `developed-structure-chart-${ _index }`;
                    let imgId = `developed-structure-img-${ _index }`;
                    args.chartId = chartId; 
                    args.imgId = imgId;
                    return  {
                        html: _template.renderChart(args),
                        item: {
                            chartId: chartId,
                            imgId: imgId,
                            chart: 'onGroupSM',
                            param: {
                                scope: chartId, 
                                data: data.structure.graph.structure, 
                                animate: false,                        
                                fontSize: _fontsize,
                                cursor: false
                            }
                        },
                    };
                }
            },
            {
                code: '$$W4RLB$$',
                html: function (args) {
                    let data = _data.system.developed;
                    let chartId = `developed-parameter-chart-${ _index }`;
                    let imgId = `developed-parameter-img-${ _index }`;
                    args.chartId = chartId; 
                    args.imgId = imgId;
                    return  {
                        html: _template.renderChart(args),
                        item: {
                            chartId: chartId,
                            imgId: imgId,
                            chart: 'onGroupPM',
                            param: {
                                scope: chartId, 
                                data: data.parameter.graph.general, 
                                animate: false,                        
                                fontSize: _fontsize,
                                cursor: false
                            }
                        },
                    };
                }
            },
            {
                code: '$$3NSTH$$',
                html: function (args) {
                    let data = _data.system.developed;
                    let chartId = `developed-average-chart-${ _index }`;
                    let imgId = `developed-average-img-${ _index }`;
                    args.chartId = chartId; 
                    args.imgId = imgId;
                    return  {
                        html: _template.renderChart(args),
                        item: {
                            chartId: chartId,
                            imgId: imgId,
                            chart: 'onPointAMV2',
                            param: {
                                scope: chartId, 
                                data: data.average.graph.general, 
                                animate: false,                        
                                fontSize: _fontsize,
                                cursor: false
                            }
                        },
                    };
                }
            },
            {
                code: '$$DFR25$$',
                html: function (args) {
                    let data = _data.system.emerging;
                    let chartId = `emerging-riesgo-pais-chart-${ _index }`;
                    let imgId = `emerging-riesgo-pais-img-${ _index }`;
                    args.chartId = chartId; 
                    args.imgId = imgId;
                    return  {
                        html: _template.renderChart(args),
                        item: {
                            chartId: chartId,
                            imgId: imgId,
                            chart: 'onLineAll',
                            param: {
                                scope: chartId, 
                                data: data.structure.graph.riesgo.data,
                                title: data.structure.graph.riesgo.title,
                                animate: false,                        
                                fontSize: _fontsize,
                                cursor: false
                            }
                        },
                    };
                }
            },
            {
                code: '$$Y2T4Y$$',
                html: function (args) {
                    let data = _data.system.emerging;
                    let chartId = `emerging-general-chart-${ _index }`;
                    let imgId = `emerging-general-img-${ _index }`;
                    args.chartId = chartId; 
                    args.imgId = imgId;
                    return  {
                        html: _template.renderChart(args),
                        item: {
                            chartId: chartId,
                            imgId: imgId,
                            chart: 'onGroupSM',
                            param: {
                                scope: chartId, 
                                data: data.structure.graph.general,
                                animate: false,                        
                                fontSize: _fontsize,
                                cursor: false
                            }
                        },
                    };
                }
            },
            {
                code: '$$AJ4RG$$',
                html: function (args) {
                    let data = _data.system.emerging;
                    let chartId = `emerging-parameter-chart-${ _index }`;
                    let imgId = `emerging-parameter-img-${ _index }`;
                    args.chartId = chartId; 
                    args.imgId = imgId;
                    return  {
                        html: _template.renderChart(args),
                        item: {
                            chartId: chartId,
                            imgId: imgId,
                            chart: 'onGroupPM',
                            param: {
                                scope: chartId, 
                                data: data.parameter.graph.general,
                                legend: {
                                    position: 'top'
                                },
                                animate: false,                        
                                fontSize: 14,
                                cursor: false,
                                template: {
                                    width: 75
                                }
                            }
                        },
                    };
                }
            },
            {
                code: '$$0W19R$$',
                html: function (args) {
                    let data = _data.system.emerging;
                    let chartId = `emerging-average-chart-${ _index }`;
                    let imgId = `emerging-average-img-${ _index }`;
                    args.chartId = chartId; 
                    args.imgId = imgId;
                    return  {
                        html: _template.renderChart(args),
                        item: {
                            chartId: chartId,
                            imgId: imgId,
                            chart: 'onPointAMV2',
                            param: {
                                scope: chartId, 
                                data: data.average.graph.general,
                                animate: false,                        
                                fontSize: _fontsize,
                                cursor: false,
                                tooltipText: false
                            }
                        },
                    };
                }
            },
            {
                code: '$$E4SZX$$',
                html: function (args) {
                    let data = _data.system.company;
                    let chartId = `company-structure-chart-${ _index }`;
                    let imgId = `company-structure-img-${ _index }`;
                    args.chartId = chartId; 
                    args.imgId = imgId;
                    return  {
                        html: _template.renderChart(args),
                        item: {
                            chartId: chartId,
                            imgId: imgId,
                            chart: 'onGroupSM',
                            param: {
                                scope: chartId, 
                                data: data.structure.graph.general,
                                animate: false,                        
                                fontSize: _fontsize,
                                cursor: false,
                                tooltipText: false
                            }
                        },
                    };
                }
            },
            {
                code: '$$SDHJ8$$',
                html: function (args) {
                    let data = _data.system.company;
                    let chartId = `company-dolares-chart-${ _index }`;
                    let imgId = `company-dolares-img-${ _index }`;
                    args.chartId = chartId; 
                    args.imgId = imgId;
                    return  {
                        html: _template.renderChart(args),
                        item: {
                            chartId: chartId,
                            imgId: imgId,
                            chart: 'onPointAMV2',
                            param: {
                                scope: chartId, 
                                data: data.dolares.graph.general,
                                animate: false,                        
                                fontSize: _fontsize,
                                cursor: false,
                                tooltipText: false,
                            }
                        },
                    };
                }
            },
            {
                code: '$$BDTSR$$',
                html: function (args) {
                    let data = _data.system.company;
                    let chartId = `company-national-chart-${ _index }`;
                    let imgId = `company-national-img-${ _index }`;
                    args.chartId = chartId; 
                    args.imgId = imgId;
                    return  {
                        html: _template.renderChart(args),
                        item: {
                            chartId: chartId,
                            imgId: imgId,
                            chart: 'onPointAMV2',
                            param: {
                                scope: chartId, 
                                data: data.national.graph.general,
                                animate: false,                        
                                fontSize: _fontsize,
                                cursor: false,
                                tooltipText: false,
                            }
                        },
                    };
                }
            },
            {
                code: '$$Z7RDW$$',
                html: function (args) {
                    let data = _data.system.report;
                    
                    let chartId = `report-company-chart-${ _index }`;
                    let imgId = `report-company-img-${ _index }`;
                    args.chartId = chartId; 
                    args.imgId = imgId;
                    return  {
                        html: _template.renderChart(args),
                        item: {
                            chartId: chartId,
                            imgId: imgId,
                            chart: 'onColumnHztRC',
                            param: {
                                scope: chartId, 
                                data: data.graph.general,
                                animate: false,                        
                                fontSize: 12,
                                cursor: false,
                                tooltipText: false,
                            }
                        },
                    };
                }
            },
            {
                code: '$$NFHEW$$',
                html: function (args) {
                    let data = _data.system.sectorial;
                    let chartId = `report-sectorial-chart-${ _index }`;
                    let imgId = `report-sectorial-img-${ _index }`;
                    args.chartId = chartId; 
                    args.imgId = imgId;
                    return  {
                        html: _template.renderChart(args),
                        item: {
                            chartId: chartId,
                            imgId: imgId,
                            chart: 'onColumnHztRC',
                            param: {
                                scope: chartId, 
                                data: data.graph.general,
                                animate: false,                        
                                fontSize: _fontsize,
                                cursor: false,
                                tooltipText: false,
                            }
                        },
                    };
                }
            },
            {
                code: '$$ASWQF$$',
                html: function (args) {
                    let data = _data.system.comparation;
                    let chartId = `report-comparation-chart-${ _index }`;
                    let imgId = `report-comparation-img-${ _index }`;
                    args.chartId = chartId; 
                    args.imgId = imgId;
                    return  {
                        html: _template.renderChart(args),
                        item: {
                            chartId: chartId,
                            imgId: imgId,
                            chart: 'onColumnHztRC',
                            param: {
                                scope: chartId, 
                                data: data.graph.general,
                                animate: false,                        
                                fontSize: 8,
                                cursor: false,
                                tooltipText: false,
                            }
                        },
                    };
                }
            },
            {
                code: '$$KF45D$$',
                html: function (args) {
                    let mypixel = 20;
                    let items = _data.system.company.structure.graph.deuda;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 2});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                    <div class="d-block text-center">
                                        <label>COSTO DE DEUDA</label>
                                    </div>
                                    <ul class="chartCustom" id="chartCustomId">`;
                                    for (let i = 0; i < items.length; i++) {
                                        const item = items[i];
                            html += `   <li>
                                            <div class="chartTextTop"><span>${ item.value }%</span></div>`;
                                        if (item.value == item.measure) {
                                        } else {
                                            if (item.prima > 0) {
                                        html += `<div class="chartLine" style="height:${ item.prima * mypixel }px;">`;
                                                if (item.text) {
                                        html +=     `<span>${ item.text }%</span>`;   
                                                }
                                        html += `</div>`;   
                                            }
                                        }

                            html +=         `<div class="chartItem" style="height:${ item.value * mypixel }px; border: 2px solid ${ item.color };" title="${ item.label }"></div>
                                        </li>`;
                                    }
                            html += `</ul>
                                </div>`;
                    return { html: html };
                }
            },
        ],
        text: [
            {
                code: '$$NPQ3H$$',
                html: function (args) {
                    let country = _data.system.emerging.structure.country;
                    return country ? country : '';
                }
            }
        ],
        html: [
            {
                code: '$$YFGHJ$$',
                html: function (args) {
                    let data = _data.system.company;
                    args.htmlId = `company-parameter-html-${ _index }`;
                    return {
                        html: _template.renderHtml(args),
                        item: {
                            htmlId: '#' + args.htmlId,
                            data: data.parameter.graph.general
                        }
                    }
                }
            }
        ],
        content: {
            html: function (args) {
                let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                return `<div class="${myclass}" style="${mystyles}">${args.content}</div>`;
            }
        },
        table: [
            {
                code: '$$MK2OI$$',
                html: function (args) {
                    let items = _data.system.report.percentages;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-group">
                                <tbody>
                                    <tr>
                                        <td class="border-0"></td>
                                        <td class="bgtwo">Dólares</td>
                                        <td class="bgtwo">Moneda Nacional</td>
                                    </tr>`;
                                    for (let i = 0; i < items.length; i++) {
                                        const item = items[i]; 
                        html += `       <tr>

                                            <td class="bgtwo">${ item.label }</td>
                                            <td class="font-weight-bold">${ item.dolar }</td>
                                            <td class="font-weight-bold">${ item.national }</td>
                                        </tr>`;
                                        }
                        html += `   </tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$KMZGY$$',
                html: function (args) {
                    let items = _data.system.developed.structure.calculations;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-one">
                                    <tbody>`;
                                    for (let i = 0; i < items.length; i++) {
                                        const item = items[i]; 
                        html += `       <tr>
                                            <td>${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                        }
                        html += `   </tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$T4P7H$$',
                html: function (args) {
                    const item = _data.system.flow;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    return `<div class="${myclass}" style="${mystyles}">
                            <table class="table table-one">
                                <tbody>
                                    <tr>
                                        <td>Horizonte de evaluación (en años)</td>
                                        <td>${ item.horizon }</td>
                                    </tr>
                                    <tr>
                                        <td>Periodicidad de los flujos</td>
                                        <td>${ item.periodicity }</td>
                                    </tr>
                                    <tr>
                                        <td>Periodos de la inversión</td>
                                        <td>${ item.period }</td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>`;
                }
            },
            {
                code: '$$GT3DW$$',
                html: function (args) {
                    let items = _data.system.flow.flows;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-item">
                                    <tbody>
                                        <tr>
                                            <td class="bgone text-center">Periodo</td>
                                            <td class="bgone text-center">Flujo de caja</td>
                                            <td class="bgone text-center">Flujo de inversiones</td>
                                        </tr>`;
                                    for (let i = 0; i < items.length; i++) {
                                        const item = items[i], index = i;
                            html += `   <tr>
                                            <td class="bgone text-center">${ index }</td>
                                            <td class="text-center">${ item.box }</td>
                                            <td class="text-center">${ item.investment }</td>						
                                        </tr>`;
                                        }
                        html += `   </tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$VF5TQ$$',
                html: function (args) {
                    const item = _data.system.curve_project;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    return `<div class="${myclass}" style="${mystyles}">
                            <table class="table table-one">
                                <tbody>
                                    <tr>
                                        <td>Duración</td>
                                        <td>${ item.duration } año(s)</td>
                                    </tr>
                                    <tr>
                                        <td>Instrumento libre de riesgo</td>
                                        <td>Bono del tesoro norteamericano de ${ item.instrument } año(s)</td>
                                    </tr>
                                    <tr>
                                        <td>Rendimiento del instrumento</td>
                                        <td>${ item.performance }</td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>`;
                }
            },
            {
                code: '$$TSKGM$$',
                html: function (args) {
                    let items = _data.system.developed.structure.sectors;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-one">
                                    <tbody>`;
                                    for (let i = 0; i < items.length; i++) {
                                        const item = items[i];
                            html += `   <tr>
                                            <td>${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                        }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$B5JVC$$',
                html: function (args) {
                    let parameters = _data.system.company.parameter.finance.parameters;
                    let results = _data.system.company.parameter.finance.results;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-item">
                                    <tbody>`;
                                    for (let i = 0; i < parameters.length; i++) {
                                        const item = parameters[i];
                            html += `   <tr>
                                            <td class="bgone">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                                    for (let i = 0; i < results.length; i++) {
                                        const item = results[i];
                            html += `   <tr>
                                            <td class="bgtwo">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$7B61E$$',
                html: function (args) {
                    let items = _data.system.developed.parameter.general;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-one">
                                    <tbody>`;
                                    for (let i = 0; i < items.length; i++) {
                                        const item = items[i];
                            html += `   <tr>
                                            <td>${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                        }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$KYZA9$$',
                html: function (args) {
                    let parameters = _data.system.developed.parameter.finance.parameters;
                    let results = _data.system.developed.parameter.finance.results;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-item">
                                    <tbody>`;
                                    for (let i = 0; i < parameters.length; i++) {
                                        const item = parameters[i];
                            html += `   <tr>
                                            <td class="bgone">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                                    for (let i = 0; i < results.length; i++) {
                                        const item = results[i];
                            html += `   <tr>
                                            <td class="bgtwo">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$7TZYU$$',
                html: function (args) {
                    let parameters = _data.system.developed.parameter.economic.parameters;
                    let results = _data.system.developed.parameter.economic.results;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-item">
                                    <tbody>`;
                                    for (let i = 0; i < parameters.length; i++) {
                                        const item = parameters[i];
                            html += `   <tr>
                                            <td class="bgone">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                                    for (let i = 0; i < results.length; i++) {
                                        const item = results[i];
                            html += `   <tr>
                                            <td class="bgtwo">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$8ECMO$$',
                html: function (args) {
                    let parameters = _data.system.developed.parameter.debt.parameters;
                    let results = _data.system.developed.parameter.debt.results;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-item">
                                    <tbody>`;
                                    for (let i = 0; i < parameters.length; i++) {
                                        const item = parameters[i];
                            html += `   <tr>
                                            <td class="bgone">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                                    for (let i = 0; i < results.length; i++) {
                                        const item = results[i];
                            html += `   <tr>
                                            <td class="bgtwo">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$WPBYC$$',
                html: function (args) {
                    let parameters = _data.system.developed.average.general.parameters;
                    let results = _data.system.developed.average.general.results;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-item">
                                    <tbody>`;
                                    for (let i = 0; i < parameters.length; i++) {
                                        const item = parameters[i];
                            html += `   <tr>
                                            <td class="bgone">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                                    for (let i = 0; i < results.length; i++) {
                                        const item = results[i];
                            html += `   <tr>
                                            <td class="bgtwo">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$HG8KV$$',
                html: function (args) {
                    let items = _data.system.emerging.structure.general;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-one">
                                    <tbody>`;
                                    for (let i = 0; i < items.length; i++) {
                                        const item = items[i];
                            html += `   <tr>
                                            <td>${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$GH7RV$$',
                html: function (args) {
                    let item = _data.system.emerging.structure.finance;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                    <table class="table table-one">
                                        <tbody>
                                            <tr>
                                                <td>${item.deuda.label}</td>
                                                <td><strong> ${item.deuda.value}%</strong></td>
                                            </tr>
                                            <tr>
                                                <td>${item.capital.label}</td>
                                                <td><strong> ${item.capital.value}%</strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$AAMKD$$',
                html: function (args) {
                    let items = _data.system.emerging.parameter.general;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-one">
                                    <tbody>`;
                                    for (let i = 0; i < items.length; i++) {
                                        const item = items[i];
                            html += `   <tr>
                                            <td>${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$HTJR8$$',
                html: function (args) {
                    let parameters = _data.system.emerging.parameter.finance.parameters;
                    let results = _data.system.emerging.parameter.finance.results;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-item">
                                    <tbody>`;
                                    for (let i = 0; i < parameters.length; i++) {
                                        const item = parameters[i];
                            html += `   <tr>
                                            <td class="bgone">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                                    for (let i = 0; i < results.length; i++) {
                                        const item = results[i];
                            html += `   <tr>
                                            <td class="bgtwo">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$5AKK4$$',
                html: function (args) {
                    let parameters = _data.system.emerging.parameter.economic.parameters;
                    let results = _data.system.emerging.parameter.economic.results;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-item">
                                    <tbody>`;
                                    for (let i = 0; i < parameters.length; i++) {
                                        const item = parameters[i];
                            html += `   <tr>
                                            <td class="bgone">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                                    for (let i = 0; i < results.length; i++) {
                                        const item = results[i];
                            html += `   <tr>
                                            <td class="bgtwo">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$M2MZB$$',
                html: function (args) {
                    let parameters = _data.system.emerging.parameter.debt.parameters;
                    let results = _data.system.emerging.parameter.debt.results;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-item">
                                    <tbody>`;
                                    for (let i = 0; i < parameters.length; i++) {
                                        const item = parameters[i];
                            html += `   <tr>
                                            <td class="bgone">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                                    for (let i = 0; i < results.length; i++) {
                                        const item = results[i];
                            html += `   <tr>
                                            <td class="bgtwo">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$XI3SQ$$',
                html: function (args) {
                    let parameters = _data.system.emerging.average.general.parameters;
                    let results = _data.system.emerging.average.general.results;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-item">
                                    <tbody>`;
                                    for (let i = 0; i < parameters.length; i++) {
                                        const item = parameters[i];
                            html += `   <tr>
                                            <td class="bgone">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                                    for (let i = 0; i < results.length; i++) {
                                        const item = results[i];
                            html += `   <tr>
                                            <td class="bgtwo">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$IE7QQ$$',
                html: function (args) {
                    let general = _data.system.company.structure.general;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-group">
                                    <tbody>
                                        <tr>
                                            <td class="border-0"></td>
                                            <td class="bgone">${general.dolares.label}</td>
                                            <td class="bgone">${general.derivative.label}</td>
                                            <td class="bgone">${general.national.label}</td>
                                        </tr>
                                        <tr>
                                            <td class="bgone">
                                                <span>${general.label}</span>
                                            </td>
                                            <td style='backgroundColor: ${general.dolares.currency == 1 ? '#f4f6f9' : 'white'}'">
                                                <strong>${general.dolares.value + '%'}</strong>
                                            </td>
                                            <td>
                                                <strong>${general.derivative.value + '%'}</strong>
                                            </td>
                                            <td style='backgroundColor:${general.national.currency == 1 ? '#f4f6f9' : 'white'}'">
                                                <strong>${general.national.value + '%'}</strong>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$V9NJF$$',
                html: function (args) {
                    let item = _data.system.company.structure.prima;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    return `<div class="${myclass}" style="${mystyles}">
                            <table class="table table-one">
                                <tbody>
                                    <tr>
                                        <td>${ item.label }</td>
                                        <td>${ item.value }</td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>`;
                }
            },
            {
                code: '$$E0C1K$$',
                html: function (args) {
                    let item = _data.system.company.structure.percentage;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    return `<div class="${myclass}" style="${mystyles}">
                            <table class="table table-one">
                                <tbody>
                                    <tr>
                                        <td>${ item.debt.label }</td>
                                        <td>${ item.debt.value }%</td>
                                    </tr>
                                    <tr>
                                        <td>${ item.capital.label }</td>
                                        <td>${ item.capital.value }%</td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>`;
                }
            },
            {
                code: '$$HUXP5$$',
                html: function (args) {
                    let items = _data.system.company.parameter.general;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-one">
                                    <tbody>`;
                                    for (let i = 0; i < items.length; i++) {
                                        const item = items[i];
                            html += `   <tr>
                                            <td>${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$MHNTZ$$',
                html: function (args) {
                    let parameters = _data.system.company.parameter.economic.parameters;
                    let results = _data.system.company.parameter.economic.results;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-item">
                                    <tbody>`;
                                    for (let i = 0; i < parameters.length; i++) {
                                        const item = parameters[i];
                            html += `   <tr>
                                            <td class="bgone">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                                    for (let i = 0; i < results.length; i++) {
                                        const item = results[i];
                            html += `   <tr>
                                            <td class="bgtwo">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$YXOFA$$',
                html: function (args) {
                    let parameters = _data.system.company.parameter.debt.parameters;
                    let results = _data.system.company.parameter.debt.results;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-item">
                                    <tbody>`;
                                    for (let i = 0; i < parameters.length; i++) {
                                        const item = parameters[i];
                            html += `   <tr>
                                            <td class="bgone">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                                    for (let i = 0; i < results.length; i++) {
                                        const item = results[i];
                            html += `   <tr>
                                            <td class="bgtwo">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$VP8DY$$',
                html: function (args) {
                    let parameters = _data.system.company.dolares.general.parameters;
                    let results = _data.system.company.dolares.general.results;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-item">
                                    <tbody>`;
                                    for (let i = 0; i < parameters.length; i++) {
                                        const item = parameters[i];
                            html += `   <tr>
                                            <td class="bgone">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                                    for (let i = 0; i < results.length; i++) {
                                        const item = results[i];
                            html += `   <tr>
                                            <td class="bgtwo">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$FLEMN$$',
                html: function (args) {
                    let parameters = _data.system.company.national.general.parameters;
                    let results = _data.system.company.national.general.results;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-item">
                                    <tbody>`;
                                    for (let i = 0; i < parameters.length; i++) {
                                        const item = parameters[i];
                            html += `   <tr>
                                            <td class="bgone">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                                    for (let i = 0; i < results.length; i++) {
                                        const item = results[i];
                            html += `   <tr>
                                            <td class="bgtwo">${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$OTSBT$$',
                html: function (args) {
                    let items = _data.system.report.parameters;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-item">
                                    <tbody>
                                        <tr>
                                            <td colspan="2" class="bgone text-center">Parámetros del reporte3</td>
                                        </tr>`;
                                    for (let i = 0; i < items.length; i++) {
                                        const item = items[i];       
                        html += `       <tr>
                                            <td>${ item.label }</td>
                                            <td>${ item.value }</td>
                                        </tr>`;
                                    }
                        html += `   </tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$AAMKD$$',
                html: function (args) {
                    let items = _data.system.report.percentages;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-group">
                                    <tbody>
                                        <tr>
                                            <td class="border-0"></td>
                                            <td class="bgtwo">Dólares</td>
                                            <td class="bgtwo">Moneda Nacional</td>
                                        </tr>`;
                                    for (let i = 0; i < items.length; i++) {
                                        const item = items[i];
                            html += `   <tr>
                                            <td class="bgtwo">${ item.label }</td>
                                            <td class="font-weight-bold">${ item.dolar }</td>
                                            <td class="font-weight-bold">${ item.national }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$VFTS2$$',
                html: function (args) {
                    let items = _data.system.sectorial.parameters;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-group">
                                    <tbody>
                                        <tr>
                                            <td class="bgone text-center">Parámetros del reporte</td>
                                            <td class="bgone text-center">MD</td>
                                            <td class="bgone text-center">ME</td>
                                        </tr>`;
                                    for (let i = 0; i < items.length; i++) {
                                        const item = items[i];
                            html += `   <tr>
                                            <td>${ item.label }</td>
                                            <td class="font-weight-bold">${ item.value }</td>
                                            <td class="font-weight-bold text-center">${ item.value2 }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$HGFD7$$',
                html: function (args) {
                    let items = _data.system.report.percentages;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-group">
                                    <tbody>
                                        <tr>
                                            <td class="border-0"></td>
                                            <td class="bgtwo with-col-table">M. Desarrollado</td>
                                            <td class="bgtwo with-col-table">M. Emergente</td>
                                        </tr>`;
                                    for (let i = 0; i < items.length; i++) {
                                        const item = items[i];
                            html += `   <tr>
                                            <td class="bgtwo">${ item.label }</td>
                                            <td class="font-weight-bold with-col-table">${ item.dolar }</td>
                                            <td class="font-weight-bold with-col-table">${ item.national }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
            {
                code: '$$MJUYH$$',
                html: function (args) {
                    let items = _data.system.comparation.percentages;
                    let { mystyles, myclass } = _template.css({attributes: args.attributes, type: 1});
                    let html = `<div class="${myclass}" style="${mystyles}">
                                <table class="table table-group">
                                    <tbody>
                                    <tr>
                                        <td class="border-0"></td>
                                        <td class="bgtwo with-col-table">M. Desarrollado</td>
                                        <td class="bgtwo with-col-table">M.Emergente</td>
                                        <td class="bgtwo with-col-table">Empresa - Dólares</td>
                                        <td class="bgtwo with-col-table">Empresa - Moneda Nacional</td>
                                    </tr>`;
                                    for (let i = 0; i < items.length; i++) {
                                        const item = items[i];
                            html += `   <tr>
                                            <td class="bgtwo">${ item.label }</td>
                                            <td class="font-weight-bold with-col-table">${ item.desarrollado }</td>
                                            <td class="font-weight-bold with-col-table">${ item.emergente }</td>
                                            <td class="font-weight-bold with-col-table">${ item.dolar }</td>
                                            <td class="font-weight-bold with-col-table">${ item.nacional }</td>
                                        </tr>`;
                                    }
                            html += `</tbody>
                                </table>
                                </div>`;
                    return html;
                }
            },
        ] 
    };

    const report = function () {
        let { all, scheme, graph, convert, exporting, waitTime } = _methods;
        $(_defaults.html.btnExportPdf).off('click');
        $(_defaults.html.btnExportPdf).on('click', function (e) {
            $('#AppDesignFormAdmin').css('margin-bottom', '100px');
            swal2.loading();
            let formData = new FormData();
            formData.append('id', $(this).attr('data-id'));
            all(formData)
            .then(scheme)
            .then(graph)
            .then(waitTime)
            .then(convert)
            .then(exporting)
            .then(function () {
                $('#'+_defaults.html.AppExportGeneral).html('');
                swal2.loading(false);
                $('#AppDesignFormAdmin').css('margin-bottom', '10px');
            })
            .catch(function (e) {
                $('#'+_defaults.html.AppExportGeneral).html('');
                console.error(e);
                swal2.show({ html: e, icon: 'error'});
            });
        });
    }

    const reportDefaultByCode = function (args) {
        let { all, scheme, graph, convert, exporting, waitTime } = _methods;
        $(_defaults.html.btnExportPdf).off('click');
        $(_defaults.html.btnExportPdf).on('click', function (e) {
            sweet2.loading();
            let formData = new FormData();
            formData.append('code', args.code);
            formData.append('id', $(this).attr('data-id'));
            all(formData)
            .then(scheme)
            .then(graph)
            .then(convert)
            .then(exporting)
            .then(function () {
                $('#'+_defaults.html.AppExportGeneral).html('');
                sweet2.loading(false);
            })
            .catch(function (e) {
                $('#'+_defaults.html.AppExportGeneral).html('');
                console.error(e);
                sweet2.show({ html: e, icon: 'error'});
            });
        });
    }
    // 649c04c1be6cd5joyv
    // 11
    const initBuildReport = function (uid, id, userId, platform) {
        let { all, scheme, graph, convert, exporting, waitTime, complement } = _methods;
        //sweet2.loading();
        all(`/api/${platform}/users/${userId}/templates/${uid}/reports/${id}/content`)
        .then(scheme)
        .then(function (args) {
            //sweet2.loading(false);
            $('#panel-acctions').html($('#template-acctions').html());
            $('#title-export').text(args.design.name.toUpperCase());
            let num = 0;
            let finterval = setInterval(() => {
                $('.progress-bar').css('width', num + '%');
                $('.progress-bar').html(num + '%');
                num = num + 10;
                if (num > 95){
                    clearInterval(finterval);
                }
            }, 800);
            return new Promise((resolve, reject) => {
                const formData = new FormData();
                formData.append('complements', args.complements);
                complement(`/api/${platform}/users/${userId}/templates/${uid}/reports/${id}/complement`, formData)
                .then(({images, texts, tables, date}) => {
                    $('#date-title').text(date);
                    images.forEach(d => {
                        $('.' + d.cid).attr('src', 'data:image/png;base64,'+d.image).removeClass('d-none');
                    });
                    texts.forEach(d => {
                        $('.' + d.cid).html(d.value);
                    });
                    tables.forEach(d => {
                        html = `<table class="table table-bordered" style="height:auto;"><thead>`;
                        for (let i = 0; i < 1; i++) {
                            html += `<tr>`;
                            for (let ii = 0; ii < d.rows[i].length; ii++) {
                                html += `<td style="${ ii > 0 ? 'text-align:center': ''}">${d.rows[i][ii] ? d.rows[i][ii] : ''}</td>`;
                            }
                            html += `</tr>`;
                        }
                        html += `</thead><tbody>`;
                        for (let i = 1; i < d.rows.length; i++) {
                            html += `<tr>`;
                            for (let ii = 0; ii < d.rows[i].length; ii++) {
                                html += `<td style="${ ii > 0 ? 'text-align:center': ''}">${d.rows[i][ii] ? d.rows[i][ii] : ''}</td>`;
                            }
                            html += `</tr>`;
                        }
                        html += `</tbody></table>`;
                        $('.' + d.cid).html(html);
                    });
                    clearInterval(finterval);
                    $('.progress-bar').css('width', '100%');
                    $('.progress-bar').html('Completado');
                    $('#btn-download').attr('disabled', false);
                    setTimeout(() => {
                        $('.panel-progress').remove();
                    }, 2000);
                    $('#btn-download').off('click');
                    $('#btn-download').on('click', function () {
                        sweet2.loading();
                        exporting(args)
                        .then(function () {
                            sweet2.loading(false);
                        });
                    });
                    resolve(args);
                })
                .catch(e => {
                    reject(e);
                    console.log(e);
                });
            });
        })
        .catch(function (e) {
            console.error(e);
            sweet2.show({ html: e, icon: 'error'});
        });
    }

    const index = () => {
        const container = document.getElementById('AppExportKapitalWeb');

        if (container) {
            const uid = container.getAttribute('uid');
            const rid = container.getAttribute('rid');
            const userId = container.getAttribute('userId');
            container.removeAttribute("uid");
            container.removeAttribute("rid");
            container.removeAttribute("userId");
            initBuildReport(uid, rid, userId, 'kapital');
        }
        const container2 = document.getElementById('AppExportValoraWeb');
        if (container2) {
            const uid = container2.getAttribute('uid');
            const rid = container2.getAttribute('rid');
            const userId = container2.getAttribute('userId');
            container2.removeAttribute("uid");
            container2.removeAttribute("rid");
            container2.removeAttribute("userId");
            initBuildReport(uid, rid, userId, 'valora');
        }
    }

    index();
}
// window.AppExportPDF = new AppExportPDF();
document.addEventListener('DOMContentLoaded', AppExportPDF());