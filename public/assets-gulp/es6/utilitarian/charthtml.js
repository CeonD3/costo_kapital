const AppChartHtml = {
    oneGraph: function (scope, args) {
        return new Promise((resolve, reject)=>{
            let items = args.items;
            let groups = args.groups.reverse();
            let mypixel = 15;
            let minheight = 25;
            let isValueMin = false;
            let remaining = 0;
            for (let i = 0; i < items.length; i++) {
                let item = items[i];
                for (let ii = 0; ii < groups.length; ii++) {
                    let group = groups[ii];
                    let result = item[group.index] * mypixel;
                    if (minheight >= result) {
                        if (!isValueMin) {
                            isValueMin = true;
                            remaining = minheight - result;
                        }
                    }
                }
            }
            let html = `<ul class="chartCustom">`;
            for (let i = 0; i < items.length; i++) {
                let item = items[i];
                html += `<li>
                            <div class="chartTextTop">
                                <span>${ item.title }</span>
                            </div>`;
                if (item.value == item.measure) {
                } else {
                    html += `<div class="chartLine" style="height:${ item.measure * mypixel }px;">
                                <span>${ item.title }</span>
                            </div>`;
                }
                for (let ii = 0; ii < groups.length; ii++) {
                    let group = groups[ii];
                    if (item[group.index] > 0) {
                        let myheight = item[group.index] * mypixel;
                        if (remaining > 0) {
                            myheight = Number(myheight) + Number(remaining);
                        }
                        html += `<div class="chartItem" style="height:${ myheight }px; border: 2px solid ${ group.color };" title="${ item.label }">
                                    <span>${ group.label } = ${ item[group.index] }%</span>
                                </div>`;
                    }
                }
                html += `</li>`;                        
            }
            html += `</ul>`;
            $(scope).html(html);
            resolve();
        });
    },
}