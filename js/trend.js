(function ($) {

    Drupal.behaviors.trend = {
        attach: function (context) {

            // var echart_pie_1 = echarts.init(document.getElementById('echart-pie-1'));

            // var echart_pie_1_data = [{
            //     value: 335,
            //     name: '直接访问'
            //   },
            //   {
            //     value: 310,
            //     name: '邮件营销'
            //   },
            //   {
            //     value: 234,
            //     name: '联盟广告'
            //   },
            // ];
            // echart_pie_1.setOption({
            //   tooltip: {
            //     trigger: 'item',
            //     formatter: '{b}: {c} ({d}%)'
            //   },
            //   legend: {
            //     orient: 'vertical',
            //     top: 'middle',
            //     right: 10,
            //     itemWidth: 8,
            //     itemHeight: 8,
            //     itemGap: 20,
            //     textStyle: {
            //       color: '#909090'
            //     },
            //     formatter: function (name) {
            //       var item = echart_pie_1_data.find(function (d) {
            //         return d.name === name
            //       });

            //       var value = item && item.value;
            //       return name + ' ' + value;
            //     },
            //     icon: 'circle',
            //     data: echart_pie_1_data
            //   },
            //   series: [{
            //     name: '访问来源',
            //     type: 'pie',
            //     radius: ['30%', '45%'],
            //     center: ['40%', '50%'],
            //     color: ['#6699FF', '#52CCA3', '#C4BAFD'],
            //     label: {
            //       show: false,
            //     },
            //     labelLine: {
            //       show: false
            //     },
            //     itemStyle: {
            //       normal: {
            //         borderColor: '#ffffff',
            //         borderWidth: 2
            //       }
            //     },
            //     data: echart_pie_1_data
            //   }]
            // });

            // var echart_line_1 = echarts.init(document.getElementById('echart-line-1'));

            // echart_line_1.setOption({
            //   "textStyle": {
            //     "fontFamily": "Din-Light"
            //   },
            //   "color": ['#6699FF', '#52CCA3', '#C4BAFD', "#9b52ff", "#fac524", "#46caff", "#a1e867", "#10b2b2", "#ec87f7", "#f4905a", "#00baba", "#facf24", "#e89d67", "#23c6c6", "#fa8699", "#40b7fc", "#006d75", "#595959", "#f4764f", "#a640fc", "#fda23f", "#2d7ae4", "#5092ff", "#9351ed", "#8a89fe", "#df89e8", "#2797ff", "#6ad089", "#7c92e8 "],
            //   "legend": {
            //     bottom: '0',
            //     itemWidth: 8,
            //     itemHeight: 8,
            //     itemGap: 20,
            //     textStyle: {
            //       color: '#909090'
            //     },
            //     icon: 'circle',
            //     data: ['百度', '搜狗', '谷歌']
            //   },
            //   title: {
            //     text: '周统计',
            //     textStyle: {
            //       align: 'center',
            //       color: '#5F5F5F',
            //       fontSize: 12,
            //     },
            //     top: '10',
            //     left: 'center',
            //   },
            //   "tooltip": {
            //     "backgroundColor": "#fff",
            //     "trigger": "axis",
            //     "axisPointer": {
            //       "type": "none"
            //     },
            //     "textStyle": {
            //       "color": "#565656",
            //       "lineHeight": 28
            //     },
            //     "confine": true,
            //     "padding": 12,
            //     "extraCssText": "box-shadow: 0px 2px 8px 0px #cacaca;border-radius: 4px;opacity: 0.9;max-height: 100%;",
            //   },
            //   "grid": {
            //     top: 40,
            //     "bottom": '50',
            //   },
            //   "xAxis": {
            //     "type": "category",
            //     "boundaryGap": true,
            //     "data": ["05-11", "05-12", "05-13", "05-14", "05-15", "05-16", "05-17", "05-18", "05-19", "05-20", "05-21", "05-22", "05-23", "05-24", "05-25", "05-26", "05-27", "05-28", "05-29", "05-30", "05-31", "06-01", "06-02", "06-03", "06-04", "06-05", "06-06", "06-07", "06-08", "06-09", "06-10"],
            //     "axisLabel": {
            //       "color": "#a0a9bc",
            //     },
            //     "axisLine": {
            //       "show": false
            //     },
            //     "axisTick": {
            //       "show": false
            //     }
            //   },
            //   "yAxis": {
            //     "name": "",
            //     "nameTextStyle": {
            //       "color": "gray"
            //     },
            //     "type": "value",
            //     "axisLabel": {
            //       "color": "#a0a9bc",
            //       "margin": 0,
            //       "verticalAlign": "bottom"
            //     },
            //     "splitLine": {
            //       "lineStyle": {
            //         "type": "dashed"
            //       }
            //     },
            //     "minInterval": 1,
            //     "axisLine": {
            //       "show": false
            //     },
            //     "axisTick": {
            //       "show": false
            //     }
            //   },
            //   "series": [{
            //     "name": "百度",
            //     "data": [43, 58, 195, 229, 320, 211, 124, 131, 124, 360, 124, 78, 160, 604, 17, 0, 0, 0, 2, 8, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            //     "type": "line",
            //     "smooth": true,
            //     "smoothMonotone": "x",
            //     "cursor": "pointer",
            //     "showSymbol": false,
            //     "lineStyle": {
            //       "shadowColor": "rgba(18,61,172,0.5)",
            //       "shadowBlur": 10
            //     }
            //   }, {
            //     "name": "搜狗",
            //     "data": [23, 39, 118, 71, 116, 89, 58, 71, 51, 146, 31, 41, 61, 485, 5, 0, 0, 0, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            //     "type": "line",
            //     "smooth": true,
            //     "smoothMonotone": "x",
            //     "cursor": "pointer",
            //     "showSymbol": false,
            //     "lineStyle": {
            //       "shadowColor": "rgba(115,226,226,0.5)",
            //       "shadowBlur": 10
            //     }
            //   }, {
            //     "name": "谷歌",
            //     "data": [20, 37, 91, 72, 68, 67, 54, 42, 42, 115, 41, 33, 64, 312, 4, 0, 0, 0, 3, 4, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            //     "type": "line",
            //     "smooth": true,
            //     "smoothMonotone": "x",
            //     "cursor": "pointer",
            //     "showSymbol": false,
            //     "lineStyle": {
            //       "shadowColor": "rgba(255,126,133,0.5)",
            //       "shadowBlur": 10
            //     }
            //   }]
            // });

            var echart_1 = echarts.init(document.getElementById('trend-echart-1'));

            var echart_1_data = JSON.parse($('#trend-echart-1').attr('data-init') || [])

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
                        return d.date
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
