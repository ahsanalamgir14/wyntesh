<template>
  <div :class="className" :style="{height:height,width:width}" />
</template>

<script>
import echarts from 'echarts';
require('echarts/theme/macarons'); // echarts theme
import { debounce } from '@/utils';

const animationDuration = 6000;

export default {
  props: {
    className: {
      type: String,
      default: 'chart',
    },
    width: {
      type: String,
      default: '100%',
    },
    height: {
      type: String,
      default: '300px',
    },
    chartData: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      chart: null,
    };
  },
  watch: {
    chartData: {
      deep: true,
      handler(val) {        
        this.setOptions(val);
      },
    },
  },
  mounted() {   
    this.initChart();
    this.__resizeHandler = debounce(() => {
      if (this.chart) {
        this.chart.resize();
      }
    }, 100);
    window.addEventListener('resize', this.__resizeHandler);
  },
  beforeDestroy() {
    if (!this.chart) {
      return;
    }
    window.removeEventListener('resize', this.__resizeHandler);
    this.chart.dispose();
    this.chart = null;
  },
  methods: {
    setOptions({ labels, data, title, color } = {}) {
      this.chart.setOption({
        tooltip: {
          trigger: 'axis',
          axisPointer: { // Axis indicator, axis trigger is valid
            type: 'shadow', // The default is a straight line, which can be selected as: 'line' | 'shadow'
          },
        },
        grid: {
          left: '4%',
          right: '4%',
          bottom: '4%',
          containLabel: true,
        },
        xAxis: [{
          type: 'category',
          data: labels,
          axisTick: {
            alignWithLabel: true,
          },
        }],
        yAxis: [{
          type: 'value',
          axisTick: {
            show: false,
          },
        }],
        legend: {
          left: 'center',
          top: '0',
          data: [title],
        },
        color: color,
        series: [{
          name: title,
          type: 'line',
          data: data,
          itemStyle: {
              normal: {
                color: color,
                lineStyle: {
                  color: color,
                  width: 2,
                },
              },
          },
          animationDuration: 2800,
        }, ],
      });
    },

    initChart() {

      this.chart = echarts.init(this.$el, 'macarons');
      this.setOptions(this.chartData);
      
    },
  },
};
</script>
