(function ($) {

    Drupal.behaviors.summary = {
        attach: function (context) {
            var dom = document.getElementById('summary-echart-1')

            if (!dom) {
                return
            }

            var echart_1 = echarts.init(dom);

            var echart_1_data = JSON.parse($(dom).attr('data-init') || []);
            echart_1.setOption({
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { // 坐标轴指示器，坐标轴触发有效
                        type: 'shadow' // 默认为直线，可选为：'line' | 'shadow'
                    }
                },
                color: ['#6699FF', '#52CCA3', '#C4BAFD'],
                legend: {
                    top: '5%',
                    right: '5%',
                    data: ['IP数', '浏览量(PV)', '访问量(UV)'],
                    itemWidth: 8,
                    itemHeight: 8,
                    itemGap: 20,
                    textStyle: {
                        color: '#909090'
                    },
                    icon: 'circle',
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '5%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: echart_1_data.map(function (d) {
                        return d.label
                    }),
                    axisTick: {
                        alignWithLabel: true
                    },
                    "axisLabel": {
                        "color": "#a0a9bc",
                    },
                    "axisLine": {
                        "show": false
                    },
                },
                "yAxis": {
                    "nameTextStyle": {
                        "color": "gray"
                    },
                    "type": "value",
                    "axisLabel": {
                        "color": "#a0a9bc",
                        "margin": 0,
                        "verticalAlign": "bottom"
                    },
                    "splitLine": {
                        "lineStyle": {
                            "type": "dashed"
                        }
                    },
                    "minInterval": 1,
                    "axisLine": {
                        "show": false
                    },
                    "axisTick": {
                        "show": false
                    }
                },
                barMaxWidth: 28,
                dataset: echart_1_data,
                series: [{
                    name: 'IP数',
                    type: 'line',
                    data: echart_1_data.map(function (d) {
                        return d.nb_uniq_visitors || 0
                    })
                },
                    {
                        name: '浏览量(PV)',
                        type: 'line',
                        data: echart_1_data.map(function (d) {
                            return d.nb_actions || 0
                        })
                    },
                    {
                        name: '访问量(UV)',
                        type: 'line',
                        data: echart_1_data.map(function (d) {
                            return d.nb_visits || 0
                        })
                    },
                ]
            });
        }
    }

    $(document).ready(function () {

    });

}(jQuery))
