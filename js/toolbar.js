(function (_, $, Drupal) {
  function matomoPost(data, callback) {
    var api = drupalSettings.dyniva_matomo.api;
    $.post(api, data, function (result) {
      if (_.isString(data.params.idSite)) {
        if (data.params.idSite.indexOf(',') != -1 || data.params.idSite == 'all') {
          result = takeoutTopLevel(result);
        }
      }
      callback(result);
    });
  }

  function takeoutTopLevel(data) {
    if (!_.isObject(data) && !_.isArray(data)) return data;
    var temp = null;
    _.each(data, function (item) {
      if (_.isObject(item)) {
        if (!temp) {
          temp = _.clone(item);
          return;
        }
        _.each(item, function (_item, _key) {

          if (_.isArray(_item)) {
            if (!_.has(temp, _key)) temp[_key] = [];
            temp[_key] = _.union(temp[_key], _item);
          }else if (_.isObject(_item)) {
            if (!_.has(temp, _key)) temp[_key] = {};
            _.extend(temp[_key], _item);
          }

        });
      }
      if (_.isArray(item)) {
        if (!temp) temp = [];
        _.each(item, function (_item) {
          temp.push(_item);
        });
      }
    });
    if (temp) return temp;
    return data;
  }

  function get_velolution_cell(num) {
    var reg = /^-\d+\.?\d*%$/;
    var replace = new RegExp(",", "g");
    var cell = '<td>' + num + '</td>';
    if (reg.test(num.replace(replace,''))) {
      cell = '<td class="evolution-falling">&#8595; ' + num + '</td>';
    }
    var reg = /^\d+\.?\d*%$/;
    if (reg.test(num.replace(replace, ''))) {
      cell = '<td class="evolution-rising">&#8593; ' + num + '</td>';
    }
    if (num == '0%'){
      cell = '<td>' + num + '</td>';
    }
    return cell;
  }

  function loading(id, start) {
    var selector = '#' + id;
    if ($(selector).length == 0){
      return;
    }
    if (start) {
      $(selector).addClass('loading-widget');
      if ($(selector + ' .loading').length == 0) {
        var top = $(selector).offset().top;
        if ($(selector + ' table').length > 0 && $(selector + ' table').offset().top > top){
          top = $(selector + ' table').offset().top;
        }
        if ($(selector + ' .chart-wrapper').length > 0 && $(selector + ' .chart-wrapper').offset().top > top) {
          top = $(selector + ' .chart-wrapper').offset().top;
        }
        top = top - $(selector).parent().offset().top + 60;
        var loading = $('<div class="loading"><span class="balls"></span><span class="loading-text">' + Drupal.t('Loading data') + '</span></div>');
        loading.css('top', top+'px');
        if ($(selector).height() < (top + 50)){
          $(selector).css('min-height', (top + 50) + 'px');
        }
        $(selector).append(loading);
      } else {
        $(selector + ' .loading').show();
      }
    } else {
      $(selector).removeClass('loading-widget');
      $(selector).css('min-height', '');
      $(selector + ' .loading').remove();
    }
  }

  function removeSiteName(name) {
    var names = name.split('|');
    if (names.length > 1){
      names.splice(names.length-1,1);
    }
    return names.join();
  }

  /**
   * Open visits logs.
   * @param {*} obj
   * @param {*} pager
   */
  function openVisitsLog(obj,pager) {
    var data = {};
    data.api_method = 'Live.getLastVisitsDetails';
    var params = {};
    params.date = obj.data('date');
    params.segment = obj.data('segment');
    params.idSite = obj.data('idsite');
    params.filter_limit = 50;
    params.filter_offset = pager * params.filter_limit;
    params.period = 'day';
    data.params = params;

    $('.ui-dialog').remove();
    var tmp = _.template($('#pages-visit-log-template').html());
    var params2 = $.extend(true, {}, params);
    params2.title = obj.data('title');
    params2.pager = pager;

    $('body').append(tmp(params2));
    var openSeletor = $('.ui-dialog.ui-widget.ui-widget-content');
    openSeletor.css('top', ((($(window).height() - 200) > 0 ? ($(window).height() - 200) / 2 : 0) + $(window).scrollTop()) + 'px');
    openSeletor.css('width', (($(window).width() < 1024) ? $(window).width() : 1024) + 'px');
    openSeletor.css('left', (($(window).width() > 1024) ? ($(window).width() - 1024) / 2 : 0) + 'px');

    matomoPost(data, function (result) {
      openSeletor.addClass("data-rows-" + result.length);
      result.forEach(function (item) {
        var tmp = _.template($('#pages-visit-detail-template').html());
        $('.ui-widget-content .data-widget').append(tmp(item));
      });
      if (result.length < $('.ui-widget-content .pager-widget').data('limit')){
        $('.ui-widget-content .pager-widget .next-page').remove();
      }
      openSeletor.css('top', ((($(window).height() - openSeletor.height()) > 0 ? ($(window).height() - openSeletor.height()) / 2 : 0) + $(window).scrollTop()) + 'px');
      $('body').css({ 'overflow': 'hidden' });
    });
    $('.ui-widget-closed').click(function(){
      $('.ui-dialog').remove();
      $('body').css({ 'overflow': 'auto' });
    });
    $('.ui-widget-content .pager').click(function () {
      openVisitsLog($(this), $(this).data('pager'));
    });
  }

  /**
   * Open trend chart.
   * @param {*} $obj
   */
  function openTrend(obj){
    var data = {};
    data.api_method = obj.data('method');
    var params = {};
    params.date = obj.data('date');
    params.segment = obj.data('segment');
    params.idSite = obj.data('idsite');
    params.period = 'day';
    data.params = params;

    $('.ui-dialog').remove();
    var tmp = _.template($('#pages-trend-template').html());
    var params2 = $.extend(true, {}, params);
    params2.title = obj.data('title');
    $('body').append(tmp(params2));
    var openSeletor = $('.ui-dialog.ui-widget.ui-widget-content');
    openSeletor.css('top', ((($(window).height() - 200) > 0 ? ($(window).height() - 200) / 2 : 0) + $(window).scrollTop()) + 'px');
    openSeletor.css('width', (($(window).width() < 1024) ? $(window).width() : 1024) + 'px');
    openSeletor.css('left', (($(window).width() > 1024) ? ($(window).width() - 1024) / 2 : 0) + 'px');

    matomoPost(data, function (result) {
      openSeletor.addClass("data-rows-" + result.length);
      var tmp = _.template($('#pages-trend-chart-template').html());
      $('.ui-widget-content .data-widget').append(tmp({}));
      var source = [];
      _.each(result, function (day,date) {
        var nb_hits = 0;
        var nb_visits = 0;
        _.each(day, function (event) {
          nb_hits += event.nb_hits;
          nb_visits += event.nb_visits;
        });
        source.push({ label: date, nb_hits: nb_hits, nb_visits: nb_visits});
      });
      var chart = echarts.init($('.chart-wrapper', openSeletor).get(0), 'dy-chart');
      var options = {
        grid: {
          bottom: '16%'
        },
        legend: {
          bottom: '2%'
        },
        toolbox: {
          feature: {
            saveAsImage: {}
          }
        },
        tooltip: {
          trigger: 'axis',
        },
        dataset: {
          dimensions: [
            'label',
            {
              name: 'nb_hits',
              type: 'int',
              displayName: Drupal.t('Pageviews')
            },
            {
              name: 'nb_visits',
              type: 'int',
              displayName: Drupal.t('Unique Pageviews')
            }
          ],
          source: source
        },
        xAxis: {
          type: 'category',
          boundaryGap: false
        },
        yAxis: {
          minInterval: 1
        },
        series: [{
          type: 'line',
          // smooth: true,
        },
          {
            type: 'line',
            // smooth: true,
          }
        ]
      };
      chart.setOption(options);
      openSeletor.css('top', ((($(window).height() - openSeletor.height()) > 0 ? ($(window).height() - openSeletor.height()) / 2 : 0) + $(window).scrollTop()) + 'px');
      $('body').css({ 'overflow': 'hidden' });
    });
    $('.ui-widget-closed').click(function () {
      $('.ui-dialog').remove();
      $('body').css({ 'overflow': 'auto' });
    });
  }

  function formatSeconds(value) {
    var theTime = parseInt(value);
    var theTime1 = 0;
    var theTime2 = 0;
    if (theTime > 60) {
      theTime1 = parseInt(theTime / 60);
      theTime = parseInt(theTime % 60);
      if (theTime1 > 60) {
        theTime2 = parseInt(theTime1 / 60);
        theTime1 = parseInt(theTime1 % 60);
      }
    }

    var result = "" + parseInt(theTime);
    if (10 > theTime > 0) {
      result = "0" + parseInt(theTime);
    } else {
      result = "" + parseInt(theTime);
    }

    if (10 > theTime1 > 0) {
      result = "0" + parseInt(theTime1) + ":" + result;
    } else {
      result = "" + parseInt(theTime1) + ":" + result;
    }
    if (theTime2 > 0) {
      result = "" + parseInt(theTime2) + ":" + result;
    }
    else {
      result = "00" + ":" + result;
    }
    return result;
  }

  Drupal.behaviors.dyniva_matomo_toolbar = {
    widgetRun: function (id, form_id, context) {
      if (id && drupalSettings.dyniva_matomo.widgets[id]) {
        var auto_refresh = drupalSettings.dyniva_matomo.widgets[id].auto_refresh;

        if (drupalSettings.dyniva_matomo.widgets[id].timer) {
          clearTimeout(drupalSettings.dyniva_matomo.widgets[id].timer);
        }

        function refreshData(id, form_id) {
          var params = _.clone(drupalSettings.dyniva_matomo.params['dyniva-matomo-analytics-toolbar']);
          if (_.isObject(drupalSettings.dyniva_matomo.params[id])) {
            _.extend(params, drupalSettings.dyniva_matomo.params[id]);
          }
          if (_.isObject(drupalSettings.dyniva_matomo.widgets[id].params)) {
            _.extend(params, drupalSettings.dyniva_matomo.widgets[id].params);
          }
          var api_method = drupalSettings.dyniva_matomo.widgets[id].api_method;
          var api_callback = drupalSettings.dyniva_matomo.widgets[id].api_callback;

          if (typeof params['date'] != 'undefined' && params['date'].length == 7) {
            var lastDay = new Date(Date.parse(params['date']));
            lastDay = new Date(lastDay.getFullYear(), lastDay.getMonth() + 1, 0);
            var date1 = params['date'] + "-01";
            var date2 = params['date'] + "-" + lastDay.getDate();
            params['date'] = date1 + ',' + date2;
          }

          var data = {
            'api_method': api_method,
            'params': params
          };
          loading(id,true);
          matomoPost(data, function (result) {
            loading(id, false);
            var callback_function = Drupal.behaviors.dyniva_matomo_toolbar.callback[api_callback]
            var settings = _.clone(drupalSettings.dyniva_matomo.widgets[id]);
            if (_.isObject(settings.params)) {
              settings.params = params;
            }
            callback_function(id, result, context, settings);
          });
        }
        refreshData(id, form_id);
        if (auto_refresh) {
          var refresh_interval = drupalSettings.dyniva_matomo.widgets[id].refresh_interval * 1000;
          drupalSettings.dyniva_matomo.widgets[id].timer = setInterval(refreshData, refresh_interval, id, form_id);
        }
      }
    },
    attach: function (context, settings) {
      var self = this;
      $(document).bind('matomo_params_change', function (event, form_id) {
        if (form_id == 'dyniva-matomo-analytics-toolbar') {
          $('.matomo-widget', context).each(function () {
            var id = $(this).attr('id');

            self.widgetRun(id, form_id, context);
          });
        } else {
          self.widgetRun(form_id, form_id, context);
        }
      });

      // 不依赖toolbar block
      if (_.isArray(drupalSettings.dyniva_matomo.run)) {
        _.each(drupalSettings.dyniva_matomo.run, function (id) {
          self.widgetRun(id, id, context);
        })
      }

      $form = $('.dyniva-matomo-analytics-toolbar-form', context);
      $form.each(function () {
        var form_id = $(this).data('id');

        $('.form-item input, .form-item select', this).each(function () {
          $(this).change(function () {
            var $form = $($(this).prop('form'));
            var form_id = $form.data('id');
            if ($(this).attr('name') == 'date1' || $(this).attr('name') == 'date2') {
              var date1 = $('input[name="date1"]', $form).val();
              var date2 = $('input[name="date2"]', $form).val();
              if (date1 && date2) {
                drupalSettings.dyniva_matomo.params[form_id]['date'] = date1 + ',' + date2;
              }
              $(document).trigger('matomo_params_change', form_id);
              return;
            }
            drupalSettings.dyniva_matomo.params[form_id][$(this).attr('name')] = $(this).val();
            $(document).trigger('matomo_params_change', form_id);
          });
        });

        $(document).trigger('matomo_params_change', form_id);
      });

      // city options
      if ($('select[data-action="city"]').length > 0) {
        var params = _.clone(drupalSettings.dyniva_matomo.params['dyniva-matomo-analytics-toolbar']);
        params['period'] = 'year';
        params['segment'] = 'eventAction==city.content.create';
        var data = {
          'api_method': 'Events.getName',
          'params': params
        };
        matomoPost(data, function (result) {
          $('select[data-action="city"]').each(function () {
            var self = this;
            //$(this).empty();
            _.each(result, function (item) {
              var option = document.createElement("option");
              option.text = item.label;
              self.add(option);
            });
          });
        });
      }
    },
    callback: {
      dyniva_matomo_widget_real_time_visitor_api_callback: function (id, data, context, settings) {
        var widget = $('#' + id, context);
        if (widget && data.length > 0) {
          data = data[0];
          $('.simple-realtime-visitor-counter div', widget).text(data.visitors);
          $('.simple-realtime-elaboration .minutes span', widget).text(settings.params.lastMinutes);
          $('.simple-realtime-elaboration .visits span', widget).text(data.visits);
          $('.simple-realtime-elaboration .actions span', widget).text(data.actions);
        }
      },
      dyniva_matomo_visit_info_per_local_time_api_callback: function (id, data, context, settings) {
        var widget = $('#' + id, context);
        var chart = null;
        if (drupalSettings.dyniva_matomo.widgets[id].chart) {
          chart = drupalSettings.dyniva_matomo.widgets[id].chart;
        } else {
          chart = echarts.init($('.chart-wrapper', widget).get(0), 'dy-chart');
        }
        var options = {
          legend: {},
          toolbox: {
            feature: {
              saveAsImage: {}
            }
          },
          tooltip: {
            trigger: 'axis',
            axisPointer: { // 坐标轴指示器，坐标轴触发有效
              type: 'shadow' // 默认为直线，可选为：'line' | 'shadow'
            }
          },
          dataset: {
            dimensions: [
              'label',
              {
                name: 'nb_visits',
                type: 'int',
                displayName: 'UV'
              },
              {
                name: 'nb_actions',
                type: 'int',
                displayName: 'PV'
              }
            ],
            source: data
          },
          xAxis: {
            type: 'category'
          },
          yAxis: {},
          series: [{
            type: 'line'
          },
            {
              type: 'line'
            }
          ]
        };
        chart.setOption(options);
      },
      dyniva_matomo_visit_over_time_api_callback: function (id, data, context, settings) {
        var widget = $('#' + id, context);
        var chart = null;
        if (drupalSettings.dyniva_matomo.widgets[id].chart) {
          chart = drupalSettings.dyniva_matomo.widgets[id].chart;
        } else {
          chart = echarts.init($('.chart-wrapper', widget).get(0), 'dy-chart');
        }
        var options = {
          legend: {},
          toolbox: {
            feature: {
              saveAsImage: {}
            }
          },
          tooltip: {
            trigger: 'axis',
            axisPointer: {
              type: 'cross',
              label: {
                backgroundColor: '#6a7985'
              }
            }
          },
          dataset: {
            dimensions: [
              'label',
              {
                name: 'nb_visits',
                type: 'int',
                displayName: 'UV'
              },
              {
                name: 'nb_actions',
                type: 'int',
                displayName: 'PV'
              }
            ],
            source: data
          },
          xAxis: {
            type: 'category'
          },
          yAxis: {},
          series: [{
            type: 'line'
          },
            {
              type: 'line'
            }
          ]
        };
        chart.setOption(options);
      },
      dyniva_matomo_visit_real_time_of_day_api_callback: function (id, data, context, settings) {
        var widget = $('#' + id, context);
        var chart = null;
        if (drupalSettings.dyniva_matomo.widgets[id].chart) {
          chart = drupalSettings.dyniva_matomo.widgets[id].chart;
        } else {
          chart = echarts.init($('.chart-wrapper', widget).get(0), 'dy-chart');
        }
        var options = {
          grid: {
            bottom: '16%'
          },
          legend: {
            bottom: '2%'
          },
          toolbox: {
            feature: {
              saveAsImage: {}
            }
          },
          tooltip: {
            trigger: 'axis',
            axisPointer: {
              type: 'cross',
              label: {
                backgroundColor: '#6a7985'
              }
            }
          },
          dataset: {
            dimensions: [
              'label',
              {
                name: 'nb_visits',
                type: 'int',
                displayName: 'UV'
              },
              {
                name: 'nb_actions',
                type: 'int',
                displayName: 'PV'
              }
            ],
            source: data
          },
          xAxis: {
            type: 'category',
            boundaryGap: false
          },
          yAxis: {
            minInterval: 1
          },
          series: [{
            type: 'line',
            smooth: true,
          },
            {
              type: 'line',
              smooth: true,
            }
          ]
        };
        chart.setOption(options);
      },
      dyniva_matomo_events_over_time_api_callback: function (id, data, context, settings) {
        if (!data || data.length == 0) return;
        var widget = $('#' + id, context);
        var chart = null;
        if (drupalSettings.dyniva_matomo.widgets[id].chart) {
          chart = drupalSettings.dyniva_matomo.widgets[id].chart;
        } else {
          chart = echarts.init($('.chart-wrapper', widget).get(0), 'dy-chart');
        }
        var dimensions = ['label'];
        var series = [];
        Object.keys(data.category).forEach(function (key) {
          series.push({
            type: 'bar'
          });
          dimensions.push({
            name: key,
            type: 'int',
            displayName: data.category[key]
          });
        });
        var options = {
          legend: {},
          tooltip: {},
          toolbox: {
            feature: {
              saveAsImage: {}
            }
          },
          tooltip: {
            trigger: 'axis',
            axisPointer: {
              type: 'cross',
              label: {
                backgroundColor: '#6a7985'
              }
            }
          },
          dataset: {
            dimensions: dimensions,
            source: data.data
          },
          xAxis: {
            type: 'category'
          },
          yAxis: {},
          series: series
        };
        chart.setOption(options);
      },
      dyniva_matomo_browsers_api_callback: function (id, data, context, settings) {
        var widget = $('#' + id, context);
        var chart = null;
        if (drupalSettings.dyniva_matomo.widgets[id].chart) {
          chart = drupalSettings.dyniva_matomo.widgets[id].chart;
        } else {
          chart = echarts.init($('.chart-wrapper', widget).get(0), 'dy-chart');
        }
        var options = {
          legend: {
            orient: 'vertical',
            right: 'right'
            //          data: legend
          },
          tooltip: {
            trigger: 'item',
            formatter: "{a} <br/>{b} : {d}%"
          },
          dataset: {
            dimensions: [
              'label',
              {
                name: 'nb_visits',
                type: 'int',
                displayName: '访客数'
              }
            ],
            source: data
          },
          series: [{
            name: '浏览器',
            type: 'pie',
            radius: '70%',
            center: ['50%', '60%']
          }]
        };
        chart.setOption(options);
      },
      dyniva_matomo_device_type_api_callback: function (id, data, context, settings) {
        var widget = $('#' + id, context);
        var chart = null;
        if (drupalSettings.dyniva_matomo.widgets[id].chart) {
          chart = drupalSettings.dyniva_matomo.widgets[id].chart;
        } else {
          chart = echarts.init($('.chart-wrapper', widget).get(0), 'dy-chart');
        }
        //    var legend = [];
        var source = [];
        data.forEach(function (item) {
          if (item.nb_visits > 0) {
            source.push(item);
            //      legend.push({'name': item.label,'icon': 'image://'+ item.icon});
          }
        });
        var options = {
          legend: {
            orient: 'vertical',
            right: 'right'
            //          data: legend
          },
          tooltip: {
            trigger: 'item',
            formatter: "{a} <br/>{b} : {d}%"
          },
          dataset: {
            dimensions: [
              'label',
              {
                name: 'nb_visits',
                type: 'int',
                displayName: '访客数'
              }
            ],
            source: source
          },
          series: [{
            name: '终端类型',
            type: 'pie',
            radius: '70%',
            center: ['50%', '60%']
          }]
        };
        chart.setOption(options);
      },
      dyniva_matomo_pages_api_callback: function (id, data, context, settings) {

        var widget = $('#' + id, context);
        var table = '<table>';
        table += '<thead><tr><th>URL</th><th>UV</th><th>PV</th></tr></thead><tbody>';
        data.forEach(function (item) {
          table += '<tr><td><a href="' + item.url + '" target="_blank">' + item.label + '</a></td><td>' + item.nb_visits + '</td><td>' + item.nb_hits + '</td></tr>';
        });
        table += '</tbody></table>';
        widget.html(table);
      },
      dyniva_matomo_site_keywords_api_callback: function (id, data, context, settings) {
        var widget = $('#' + id, context);
        var table = '<table>';
        table += '<thead><tr><th>排名</th><th>搜索关键词</th><th>搜索数</th></tr></thead>';
        table += '<tbody>';
        data.forEach(function (item, index) {
          table += '<tr><td>' + (index+1) + '</td><td></td>' + item.label + '<td>' + item.nb_actions + '</td></tr>';
        });
        table += '</tbody>';
        table += '</table>';
        widget.html(table);
      },
      dyniva_matomo_site_referrers_api_callback: function (id, data, context, settings) {
        var widget = $('#' + id, context);
        var table = '<table>';
        table += '<thead><tr><th>排名</th><th>来源</th><th>浏览量(PV)</th></tr></thead>';
        table += '<tbody>';
        data.forEach(function (item, index) {
          table += '<tr><td>' + (index+1) + '</td><td></td>' + item.label + '<td>' + item.nb_actions + '</td></tr>';
        });
        table += '</tbody>';
        table += '</table>';
        widget.html(table);
      },
      dyniva_matomo_page_visits_api_callback: function (id, data, context, settings) {
        var widget = $('#' + id, context);
        var table = '<table>';
        table += '<thead><tr><th>排名</th><th>标题</th><th>浏览量(PV)</th></tr></thead>';
        table += '<tbody>';
        data.forEach(function (item, index) {
          table += '<tr><td>' + (index+1) + '</td><td>' + item.label + '</td><td>' + item.nb_hits + '</td></tr>';
        });
        table += '</tbody>';
        table += '</table>';
        widget.html(table);
      },
      dyniva_matomo_entry_pages_api_callback: function (id, data, context, settings) {
        var widget = $('#' + id, context);
        var table = '<table>';
        table += '<thead><tr><th>URL</th><th>访客数</th></tr></thead><tbody>';
        data.forEach(function (item) {
          if (item.entry_nb_visits) {
            table += '<tr><td><a href="' + item.url + '" target="_blank">' + item.label + '</a></td><td>' + (item.entry_nb_visits ? item.entry_nb_visits : 0) + '</td></tr>';
          }
        });
        table += '</tbody></table>';
        widget.html(table);
      },
      dyniva_matomo_screen_resolution_api_callback: function (id, data, context, settings) {
        var widget = $('#' + id, context);
        var table = '<table>';
        table += '<thead><tr><th>分辨率</th><th>访客数</th></tr></thead><tbody>';
        data.forEach(function (item) {
          table += '<tr><td>' + item.label + '</a></td><td>' + item.nb_visits + '</td></tr>';
        });
        table += '</tbody></table>';
        widget.html(table);
      },
      dyniva_matomo_events_list_api_callback: function (id, data, context, settings) {
        var rows = [],
            groups = {};
        if (settings.params.date.indexOf(',') != -1) {
          rows = takeoutTopLevel(data);
        } else {
          rows = data;
        }
        groups = _.groupBy(rows, function (item) {
          return item.label;
        });
        rows = [];
        _.each(groups, function (group, label) {
          var count = _.reduce(group, function (memo, item) {
            return memo + item.nb_visits;
          }, 0);
          rows.push({
            label: label,
            count: count
          });
        });
        rows = _.sortBy(rows, function (item) {
          return -1 * item.count;
        });

        var widget = $('#' + id, context);
        var table = '<table>';
        if (typeof settings.table_headers != 'undefined') {
          var titles = settings.table_headers.split(',');
          table += '<thead><tr><th>' + titles[0] + '</th><th>' + titles[1] + '</th></tr></thead><tbody>';
        } else {
          table += '<thead><tr><th>关键词</th><th>热度</th></tr></thead><tbody>';
        }

        if (rows.length > 0) {
          rows.forEach(function (item) {
            table += '<tr><td>' + item.label + '</a></td><td>' + item.count + '</td></tr>';
          });
        } else {
          table += '<tr><td>暂未有数据</a></td><td></td></tr>';
        }
        table += '</tbody></table>';
        widget.html(table);
      },
      dyniva_matomo_visits_summary_api_callback: function (id, data, context, settings) {
        if (data && settings.api_method == 'Live.getCounters') {
          data = data[0];
        }
        $('[data-action]').each(function () {
          var s = $(this).data('action').split(':');
          if (s && _.has(data, s[0]) && $(this).data('action') == (s[0] + ':' + settings.params.id)) {
            if (s[0] == 'avg_time_on_site') {
              $(this).text(formatSeconds(data[s[0]]));
            }
            else {
              $(this).text(data[s[0]]);
            }
          }
        });
      },
      // 市县访问量排行榜Top8
      dyniva_matomo_city_report_api_callback: function (id, data, context, settings) {
        var rows = [],
            names = [],
            counts = [];

        _.each(data, function (item) {
          names.push(item.label);
          var replace = new RegExp(",", "g");
          counts.push(item.nb_pageviews.replace(replace, ''));
        });

        var widget = $('#' + id, context);
        var chart = null;
        if (drupalSettings.dyniva_matomo.widgets[id].chart) {
          chart = drupalSettings.dyniva_matomo.widgets[id].chart;
        } else {
          chart = echarts.init($('.chart-wrapper', widget).get(0), 'dy-chart');
        }
        var options = {
          grid: {
            bottom: '16%'
          },
          tooltip: {},
          legend: {
            show: false,
            data: ['访问量'],
            bottom: '2%'
          },
          xAxis: {
            data: names
          },
          yAxis: {
            name: '访问量'
          },
          series: [{
            name: '访问量',
            type: 'bar',
            barMaxWidth: 30,
            data: counts,
            label: {
              show: true,
              position: 'top',
            },
          }]
        };
        chart.setOption(options);
      },
      // 年度发文统计
      dyniva_matomo_events_category_api_callback: function (id, data, context, settings) {
        // TODO: 过滤市
        if (!data || data.length == 0) return;
        var widget = $('#' + id, context);
        var chart = null;
        if (drupalSettings.dyniva_matomo.widgets[id].chart) {
          chart = drupalSettings.dyniva_matomo.widgets[id].chart;
        } else {
          $('.chart-wrapper', widget).removeAttr("_echarts_instance_").empty();
          chart = echarts.init($('.chart-wrapper', widget).get(0), 'dy-chart');
        }
        var dimensions = ['label'],
            categories = [],
            series = [],
            report = [];
        _.each(data, function (item, date) {
          var row = {
            label: date
          };
          _.each(item, function (category) {
            row[category.label] = category.nb_events;
            if (_.where(categories, {
              name: category.label
            }).length == 0) {
              series.push({
                type: 'bar',
                barMaxWidth: 30,
                barGap: '2%',
                label: {
                  show: true,
                  position: 'top'
                },
              });
              categories.push({
                name: category.label,
                type: 'int',
                displayName: category.label
              });
            }
          });
          report.push(row);
        });
        _.each(categories, function (category) {
          dimensions.push(category);
        });
        Object.keys(report).forEach(function (key) {
          _.each(categories, function (category) {
            if (!_.has(report[key], category.name)) {
              report[key][category.name] = 0;
            }
          });
        });
        var options = {
          grid: {
            bottom: '16%'
          },
          legend: {
            bottom: '2%'
          },
          toolbox: {
            feature: {
              saveAsImage: {}
            }
          },
          tooltip: {
            trigger: 'axis',
            axisPointer: {
              type: 'cross',
              label: {
                backgroundColor: '#6a7985'
              }
            }
          },
          dataset: {
            dimensions: dimensions,
            source: report
          },
          xAxis: {
            type: 'category'
          },
          yAxis: {
            name: '发文数'
          },
          series: series
        };
        chart.setOption(options);
      },
      dyniva_matomo_widget_publish_summary_api_callback: function (id, data, context, settings) {
        var result = {};
        var city = $('[data-id="'+id+'"] [data-action="city"]', context).val();
        _.each(data, function (item) {
          _.each(item, function (content_type) {
            if (!_.has(result, content_type.Events_EventCategory)) {
              result[content_type.Events_EventCategory] = 0;
            }
            if (city != '') {
              if (content_type.Events_EventName == city) {
                result[content_type.Events_EventCategory] += content_type.nb_events;
              }
            } else {
              result[content_type.Events_EventCategory] += content_type.nb_events;
            }
          });
        });

        $('[data-action]').each(function () {
          var self = this;
          if ($(this).data('action') == 'total-counter') {
            $(this).text(0);
            if (!_.isEmpty(result)) {
              $(this).text(_.reduce(result, function (memo, item) {
                return memo + item;
              }), 0);
            }
          }
          if ($(this).data('action') == 'category-counter') {
            var html = $(this).data('prefix');
            var lines = [];
            $(this).text('');
            if (_.isEmpty(result)) return;
            _.each(result, function (item, content_type) {
              var content = $(self).data('template').replace('{0}', content_type).replace('{1}', item);
              lines.push(content);
            });
            html += lines.join($(this).data('separator'));
            $(this).text(html);
          }
        });
      },
      // 各市县月度发文统计总览
      dyniva_matomo_widget_publish_month_summary_api_callback: function (id, data, context, settings) {
        var widget = $('#' + id, context);
        var chart = null;
        if (drupalSettings.dyniva_matomo.widgets[id].chart) {
          chart = drupalSettings.dyniva_matomo.widgets[id].chart;
        } else {
          chart = echarts.init($('.chart-wrapper', widget).get(0), 'dy-chart');
        }
        var months = [];
        var report = [];
        var city = $('[data-id="' + id + '"] [data-action="city"]', context).val();
        _.each(data, function (item, date) {
          if (drupalSettings.dyniva_matomo.params[id].period=='day'){
            var date = new Date(Date.parse(date));
            months.push((date.getMonth()+1) +'/'+ date.getDate());
          }else{
            months.push(date);
          }
          var r = _.reduce(item, function (memo, _item) {
            if (typeof city != 'undefined' && city != '') {
              if (_item.label == city) {
                return memo + _item.nb_events;
              } else {
                return memo;
              }
            }
            return memo + _item.nb_events;
          }, 0);
          if (r) {
            report.push(r);
          } else
            report.push(0);
        });
        var option = {
          tooltip: {
            trigger: 'axis',
            formatter: '{c}'
          },
          xAxis: {
            type: 'category',
            boundaryGap: false,
            data: months
          },
          yAxis: {
            type: 'value',
            name: '发文数'
          },
          series: [{
            data: report,
            type: 'line',
            areaStyle: {},
            label: {
              show: true,
              position: 'top'
            },
            // smooth: true,
          }],
          dataZoom: [{
            "xAxisIndex": [0],
            handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
          }]
        };
        chart.setOption(option);
      },
      dyniva_matomo_users_summary_api_callback: function (id, data, context, settings) {
        var total = 0,
            roles = {};
        _.each(data, function (day) {
          _.each(day, function (event) {
            total += event.nb_events;
            if (!_.has(roles, event.label)) {
              roles[event.label] = 0;
            }
            roles[event.label] += event.nb_events;
          });
        });
        $('[data-action]').each(function () {
          var self = this;
          if ($(this).data('action') == 'total-counter') {
            $(this).text(total);
          }
          if ($(this).data('action') == 'role-counter') {
            var html = $(this).data('prefix');
            var lines = [];
            $(this).text('');
            if (_.isEmpty(roles)) return;
            _.each(roles, function (item, content_type) {
              var content = $(self).data('template').replace('{0}', content_type).replace('{1}', item);
              lines.push(content);
            });
            html += lines.join($(this).data('separator'));
            $(this).text(html);
          }
        });
      },
      dyniva_matomo_users_summary2_api_callback: function (id, data, context, settings) {

        var total = _.reduce(data, function(memo, item) {
          return memo + _.reduce(item, function (memo2, item2) {
            return memo2 + item2.nb_events;
          }, 0);
        }, 0);
        $('[data-action]', context).each(function() {
          if($(this).data('action') == 'total-content-create') {
            $(this).text(total);
          }
        });
      },
      // 市县年度发文排行榜Top8
      dyniva_matomo_widget_city_content_publish_api_callback: function (id, data, context, settings) {

        var widget = $('#' + id, context);
        var chart = null;
        if (drupalSettings.dyniva_matomo.widgets[id].chart) {
          chart = drupalSettings.dyniva_matomo.widgets[id].chart;
        } else {
          chart = echarts.init($('.chart-wrapper', widget).get(0), 'dy-chart');
        }

        var rows = [];
        var dimensions = ['label'],
            series = [],
            report = [];
        _.each(data, function (date, site) {
          _.each(date, function (day) {
            _.each(day, function (event) {
              rows.push({
                content_type: event.Events_EventCategory,
                city: site,
                count: event.nb_events
              });
            });
          });
        });

        // 文章类型
        var content_types = _.chain(rows).map(function (row) {
          return row.content_type;
        }).uniq().value();
        _.each(content_types, function (content_type) {
          dimensions.push({
            name: content_type,
            type: 'int',
            displayName: content_type
          });
          series.push({
            type: 'bar',
            barMaxWidth: 30,
            barGap: '2%'
          });
        });
        rows = _.groupBy(rows, function (row) {
          return row.city;
        });
        _.each(rows, function (row, city) {
          var _row = [city];
          var numsByType = _.chain(row).groupBy(function (item) {
            return item.content_type;
          }).mapObject(function (items) {
            return _.reduce(items, function (memo, item) {
              return memo + item.count;
            }, 0);
          }).value();
          _.each(content_types, function (content_type) {
            _row.push(_.has(numsByType, content_type) ? numsByType[content_type] : 0);
          });
          report.push(_row);
        });
        report = _.sortBy(report, function (row) {
          return -1 * _.reduce(row, function (memo, item) {
            return memo + (_.isNumber(item) ? item : 0);
          }, 0);
        });

        var options = {
          grid: {
            bottom: '16%'
          },
          legend: {
            bottom: '2%'
          },
          toolbox: {
            feature: {
              saveAsImage: {}
            }
          },
          tooltip: {
            trigger: 'axis',
            axisPointer: {
              type: 'cross',
              label: {
                backgroundColor: '#6a7985'
              }
            }
          },
          dataset: {
            dimensions: dimensions,
            source: report
          },
          xAxis: {
            type: 'category'
          },
          yAxis: {
            name: '发文数',
            minInterval: 1
          },
          series: series
        };
        chart.setOption(options);
      },
      //站点访问概览
      dyniva_matomo_sites_visits_summary_api_callback: function (id, data, context, settings) {
        $("#" + id + " tbody").html('');
        var nb_pageviews = 0, nb_visits=0;
        data = _.sortBy(data, function (item) {
          return -1 * item.nb_pageviews;
        });
        $.each(data, function (index, item) {
          var row = $('<tr></tr>');
          row.append('<td><a target="_blank" href="' + item.main_url + '">' + item.label + '</a></td>')
          row.append('<td>' + item.nb_pageviews + '</td>');
          row.append(get_velolution_cell(item.pageviews_evolution));
          row.append('<td>' + item.nb_visits + '</td>');
          row.append(get_velolution_cell(item.visits_evolution));
          row.append('<td><a target="_blank" href="' + item.analytics + '">' + Drupal.t('View') + '</a></td>')
          $("#" + id + " tbody").append(row);
          nb_pageviews += item.nb_pageviews;
          nb_visits += item.nb_visits;
        });
        $('#' + id + ' .sites-total .visits').text(nb_visits);
        $('#' + id + ' .sites-total .pageviews').text(nb_pageviews);
      },
      //站点访问
      dyniva_matomo_site_page_views_api_callback: function (id, data, context, settings){
        var widget = $('#' + id, context);
        var chart = null;
        var source = [];
        _.each(data, function (item, key) {
          item['label'] = key;
          if (settings.params.period=='day'){
            var date = new Date(Date.parse(item.label));
            item.label = (date.getMonth() + 1) + '/' + date.getDate();
          }
          source.push(item);
        });
        if (drupalSettings.dyniva_matomo.widgets[id].chart) {
          chart = drupalSettings.dyniva_matomo.widgets[id].chart;
        } else {
          chart = echarts.init($('.chart-wrapper', widget).get(0), 'dy-chart');
        }
        var options = {
          grid: {
            bottom: '16%'
          },
          legend: {
            // bottom: '2%',
          },
          toolbox: {
            feature: {
              saveAsImage: {}
            }
          },
          tooltip: {
            trigger: 'axis'
          },
          dataset: {
            dimensions: [
              'label',
              {
                name: 'nb_pageviews',
                type: 'int',
                displayName: Drupal.t('Views(PV)')
              },
              {
                name: 'nb_visits',
                type: 'int',
                displayName: Drupal.t('Visits(UV)')
              }
            ],
            source: source
          },
          xAxis: {
            type: 'category',
            boundaryGap: false
          },
          yAxis: {
            minInterval: 1
          },
          series: [{
            type: 'line',
            // smooth: true,
          },
            {
              type: 'line',
              // smooth: true,
            }
          ],
          dataZoom: [{
            "xAxisIndex": [0],
            handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
          }]
        };
        chart.setOption(options);

        $("#" + id + " tbody").html('');
        var nb_pageviews = 0, nb_visits = 0;
        source = source.sort(function (a,b) {
          if (a.label > b.label) return -1;
          else return 1;
        });
        $.each(source, function (index, item) {
          var row = $('<tr></tr>');
          row.append('<td>' + item.label + '</td>')
          row.append('<td>' + item.nb_pageviews + '</td>');
          row.append(get_velolution_cell(item.pageviews_evolution));
          row.append('<td>' + item.nb_visits + '</td>');
          row.append(get_velolution_cell(item.visits_evolution));
          $("#" + id + " tbody").append(row);
          nb_pageviews += item.nb_pageviews;
          nb_visits += item.nb_visits;
        });
        $("#" + id + " table tfoot").remove();
        $("#" + id + " table").append('<tfoot><tr><td>' + Drupal.t('Totals') + '</td><td>' + nb_pageviews + '</td><td></td><td>' + nb_visits + '</td><td></td></tr></tfoot>');
        $('#' + id + ' .sites-total .visits').text(nb_visits);
        $('#' + id + ' .sites-total .pageviews').text(nb_pageviews);
      },
      dyniva_matomo_site_pages_api_callback: function (id, data, context, settings) {
        var widget = $('#' + id, context);
        widget.find('tbody').html('');
        if (data.length == 0 || !_.isArray(data)) {
          widget.find('.no-data').removeClass('hidden');
          widget.find('table').addClass('hidden');
          return;
        }
        var nb_hits = 0, nb_visits = 0;
        widget.find('.no-data').addClass('hidden');
        widget.find('table').removeClass('hidden');
        data.forEach(function (item) {
          var tmp = _.template($('#pages-rows-template').html());
          item.date = settings.params.date;
          item.idSite = settings.params.idSite;
          item.label = removeSiteName(item.label);
          if (typeof item.url == 'undefined'){
            item.url = '';
          }
          widget.find('tbody').append(tmp(item));
          nb_hits += item.nb_hits;
          nb_visits += item.nb_visits;
        });
        widget.find('.pageviews').text(nb_hits);
        widget.find('.visits').text(nb_visits);
        $('#' + id + ' .open-visit-log-dialog', context).click(function () {
          openVisitsLog($(this), 0);
        });
        $('#' + id + ' .open-trend-dialog', context).click(function () {
          openTrend($(this));
        });
        $('[data-toggle="tooltip"]', context).tooltip()
      },
      // 发文Top8
      dyniva_matomo_user_content_create_api_callback: function (id, data, context, settings) {
        var names = [], counts = [], series = [];
        var widget = $('#' + id, context);
        var chart = null;
        if (drupalSettings.dyniva_matomo.widgets[id].chart) {
          chart = drupalSettings.dyniva_matomo.widgets[id].chart;
        } else {
          $('.chart-wrapper', widget).removeAttr("_echarts_instance_").empty();
          chart = echarts.init($('.chart-wrapper', widget).get(0), 'dy-chart');
        }

        if (settings.params.idSite=='all'){
          var sites = {}, total = [];
          _.each(data, function (row, k1) {
            total.push({
              'name': k1,
              'total': _.reduce(row, function (memo, item) {
                return memo + _.reduce(item, function (memo1, item1) {
                  return memo1 + (_.isNumber(item1.nb_events) ? item1.nb_events : 0);
                }, 0);
              }, 0)
            });
          });

          total = _.sortBy(total, function (row) {
            return -1 * row.total;
          });
          var report = _.sortBy(data, function (row) {
            return -1 * _.reduce(row, function (memo, item) {
              return memo + _.reduce(item, function (memo1, item1) {
                return memo1 + (_.isNumber(item1.nb_events) ? item1.nb_events : 0);
              }, 0);
            }, 0);
          });

          _.each(total, function (item) {
            names.push(item.name);
          });

          _.each(report, function (item,k1) {
            _.each(item, function (list, k2) {
              _.each(list, function (val, k3) {
                if (!_.has(sites, val.Events_EventName)) sites[val.Events_EventName] = {};
                sites[val.Events_EventName][names[k1]] = val.nb_events;
              });
            });
          });
          _.each(sites, function (siteData, user) {
            var yData=[];
            _.each(names, function (name) {
              if (_.has(siteData, name)){
                yData.push(siteData[name]);
              }else{
                yData.push(0);
              }
            });
            series.push({
              name: user,
              type: 'bar',
              barMaxWidth: 30,
              data: yData,
              stack: '发文数',
            })
          });
        }else{
          _.each(data, function (list, k2) {
            _.each(list, function (val, k3) {
              names.push(val.Events_EventName);
              counts.push(val.nb_events);
            });
          });
          series.push({
            name: '发文数',
            type: 'bar',
            barMaxWidth: 30,
            data: counts,
            label: {
              show: true,
              position: 'top',
            },
          });
        }

        var options = {
          grid: {
            bottom: '16%'
          },
          tooltip: {
            trigger: 'axis',
            axisPointer: { // 坐标轴指示器，坐标轴触发有效
              type: 'shadow' // 默认为直线，可选为：'line' | 'shadow'
            },
            formatter: function (params) {//自动提示工具
              var text = params[0].axisValue ;
              var total = 0;
              _.each(params, function (param) {
                if (param.value>0){
                  total += param.value;
                  text += '<br />' + param.marker + param.seriesName + ': ' + param.value;
                }
              });
              text += '<br />' + Drupal.t('Totals') + ': ' + total;
              return text;
            }
          },
          legend: {
            show: false,
            data: ['站点'],
            bottom: '2%'
          },
          xAxis: {
            data: names
          },
          yAxis: {
            name: '站点'
          },
          series: series,
          dataZoom: [{
            "xAxisIndex": [0],
            handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
          }]
        };
        chart.setOption(options);
      },
    }
  };

})(_, jQuery, Drupal);
