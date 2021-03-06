define(function(require) {

    'use strict';

    var List = require('../../data/List');
    var modelUtil = require('../../util/model');
    var completeDimensions = require('../../data/helper/completeDimensions');

    var FunnelSeries = require('../../echarts').extendSeriesModel({

        type: 'series.funnel',

        init: function (option) {
            FunnelSeries.superApply(this, 'init', arguments);

            // Enable legend selection for each data item
            // Use a function instead of direct access because data reference may changed
            this.legendDataProvider = function () {
                return this.getRawData();
            };
            // Extend labelLine emphasis
            this._defaultLabelLine(option);
        },

        getInitialData: function (option, ecModel) {
            var dimensions = completeDimensions(['value'], option.data);
            var list = new List(dimensions, this);
            list.initData(option.data);
            return list;
        },

        _defaultLabelLine: function (option) {
            // Extend labelLine emphasis
            modelUtil.defaultEmphasis(option.labelLine, ['show']);

            var labelLineNormalOpt = option.labelLine.normal;
            var labelLineEmphasisOpt = option.labelLine.emphasis;
            // Not show label line if `label.normal.show = false`
            labelLineNormalOpt.show = labelLineNormalOpt.show
                && option.label.normal.show;
            labelLineEmphasisOpt.show = labelLineEmphasisOpt.show
                && option.label.emphasis.show;
        },

        // Overwrite
        getDataParams: function (dataIndex) {
            var data = this.getData();
            var params = FunnelSeries.superCall(this, 'getDataParams', dataIndex);
            var sum = data.getSum('value');
            // Percent is 0 if sum is 0
            params.percent = !sum ? 0 : +(data.get('value', dataIndex) / sum * 100).toFixed(2);

            params.$vars.push('percent');
            return params;
        },

        defaultOption: {
            zlevel: 0,                  // ????????????
            z: 2,                       // ????????????
            legendHoverLink: true,
            left: 80,
            top: 60,
            right: 80,
            bottom: 60,
            // width: {totalWidth} - left - right,
            // height: {totalHeight} - top - bottom,

            // ??????????????????????????????
            // min: 0,
            // max: 100,
            minSize: '0%',
            maxSize: '100%',
            sort: 'descending', // 'ascending', 'descending'
            gap: 0,
            funnelAlign: 'center',
            label: {
                normal: {
                    show: true,
                    position: 'outer'
                    // formatter: ???????????????????????????Tooltip.formatter????????????????????????
                    // textStyle: null      // ???????????????????????????????????????TEXTSTYLE
                },
                emphasis: {
                    show: true
                }
            },
            labelLine: {
                normal: {
                    show: true,
                    length: 20,
                    lineStyle: {
                        // color: ??????,
                        width: 1,
                        type: 'solid'
                    }
                },
                emphasis: {}
            },
            itemStyle: {
                normal: {
                    // color: ??????,
                    borderColor: '#fff',
                    borderWidth: 1
                },
                emphasis: {
                    // color: ??????,
                }
            }
        }
    });

    return FunnelSeries;
});
