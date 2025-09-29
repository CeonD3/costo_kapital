const AppStructureCompany = function() {
    let __this = this;
    __this.init = function (args) {
        new Vue({
            el: '#AppStructureCompany',
            data() {
                return {
                    code: args.code,
                    graph: args.system.graph,
                    general: args.system.general,
                    prima: args.system.prima,
                    opercentage: args.system.percentage,
                    currency: '',
                    percentage: 0,
                    deuda: args.system.percentage.debt.value,
                    capital: args.system.percentage.capital.value
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
                myCapital: function () {
                    return Number(this.capital).toFixed(2) + '%';
                }
            },
            methods: {
                onLoad: function () {
                    // this.onGraphGroup(this.graph.deuda);
                    this.onRenderGraph(this.graph.deuda);
                    $('#formPercentageInvestment').on('submit', function (e) {
                        e.preventDefault();
                    });
                    console.log(1111);
                },
                onCurrency: function (percentage, currency) {
                    this.currency = currency;
                    this.percentage = percentage;
                    $('#modal-currency').modal();
                },
                onCalculation: function () {
                    this.capital = 100 - Number(this.deuda);
                },
                onPercentageInvestment: function (e) {
                    e.preventDefault();
                    let form = document.getElementById('formPercentageInvestment');
                    let _this = this;
                    let formData = new FormData(form);
                    formData.append('code', this.code);
                    sweet2.loading(false); sweet2.loading();
                    fetch("/system/onPercentageInvestment", {method: "POST", body: formData })
                    .then(function(res){ return res.json(); })
                    .then(function(rsp){
                        if (rsp.success) {
                            window.location.href = '/documentos/'+_this.code;
                            // let data = rsp.data.system;
                            // _this.graph = data.graph;
                            // _this.general = data.general;
                            // _this.prima = data.prima;
                            // _this.opercentage = data.percentage;
                            // _this.onGraphGroup(_this.graph.deuda);
                        } else {
                            sweet2.show({type: rsp.success ? 'success' : 'error', text:rsp.message});
                        }
                    })
                    .catch(function () {
                        sweet2.show({type:'error', text:'Hubo un error en el sistema.'});
                    });
                },
                onFormPercentage: function (e) {
                    e.preventDefault();
                    let _this = this;
                    let formData = new FormData(e.currentTarget);
                    formData.append('code', this.code);
                    sweet2.loading(false); sweet2.loading();
                    fetch("/system/onPercentageCurrencyCompany", {method: "POST", body: formData })
                    .then(function(res){ return res.json(); })
                    .then(function(rsp){
                        sweet2.loading(false);
                        if (rsp.success) {
                            $('#modal-currency').modal('hide');
                            let data = rsp.data.system;
                            _this.graph = data.graph;
                            _this.general = data.general;
                            _this.prima = data.prima;
                            _this.opercentage = data.percentage;
                            // _this.onGraphGroup(_this.graph.deuda);
                            _this.onRenderGraph(_this.graph.deuda);
                        } else {
                            sweet2.show({type: rsp.success ? 'success' : 'error', text:rsp.message});
                        }
                    })
                    .catch(function () {
                        sweet2.show({type:'error', text:'Hubo un error en el sistema.'});
                    });
                },
                onGraphGroup: function (data) {
                    am4core.ready(function() {
                        AppChartSystem.onColumnSingle({scope:'groupChart', data:data});
                        // AppChartSystem.onLineAll({scope:'groupChart', data:data});
                    });
                },
                onRenderGraph: function(data) {
                    let items = data;
                    let html = ``;
                    let mypixel = 20;
                    for (let i = 0; i < items.length; i++) {
                        const item = items[i];
                        html += `<li>
                                  <div class="chartTextTop"><span>${ item.value }%</span></div>`;
                            if (item.prima > 0) {
                                html += `<div class="chartLine" style="height:${ Number(item.prima) * mypixel }px; ${ i == 1 ? 'left:38px;' : ( i == 2 ? 'left: -78px;' : '' ) }">`;
                                if (item.text) {
                                    html += `<span>${ item.text }%</span>`;                                    
                                }
                                html += `</div>`;
                            }
                        html += `<div class="chartItem" style="height:${ Number(item.value) * mypixel }px; border: 2px solid ${ item.color };" title="${ item.label }">
                                </div>
                            </li>`;

                    }
                    document.getElementById('chartCustomId').innerHTML = html;
                }
            }
        });
    }
}

window.AppStructureCompany = new AppStructureCompany();