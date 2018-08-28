'use strict';

/**
 * David Millington
 * Draws a custom background behind the chart with a dropshadow.
 */

const isSupported = (type) => [
	'bar',
	'horizontalBar'
].indexOf(type) !== -1;

const defaultOptions = {
	border_color: '#cecece'
};

const hasData = (data) => {
	return data && data.datasets && data.datasets.length > 0;
};

const plugin = {
	id: 'chartJsPluginBarchartCustomBackground',

	beforeInit: (chart) => {
		if (!isSupported(chart.config.type)) {
			console.warn('The type %s is not supported by this plugin', chart.config.type);
		}
	},

	beforeDraw: (chart, easingValue, options) => {
		if (!hasData(chart.config.data) || !isSupported(chart.config.type)) {
			return;
		}
		const pluginOptions = Object.assign({}, defaultOptions, options);
		const chartWidth = chart.chartArea.right - chart.chartArea.left;
		const chartHeight = chart.chartArea.bottom - chart.chartArea.top;
		const ctx = chart.ctx;

		ctx.save();

		// Draw fake background to serve as shadow caster
		ctx.shadowBlur = 4;
		ctx.shadowColor = "#e5e5e5";
		ctx.shadowOffsetX = 0;
		ctx.shadowOffsetY = 4;
		ctx.fillStyle = "white";
		ctx.fillRect(chart.chartArea.left, chart.chartArea.top, chartWidth, chartHeight);

		ctx.restore();
	},
	afterDraw: (chart, easingValue, options) => {
		if (!hasData(chart.config.data) || !isSupported(chart.config.type)) {
			return;
		}
		const pluginOptions = Object.assign({}, defaultOptions, options);
		const ctx = chart.ctx;

		ctx.save();

		// Draw borders and gridlines on top of fills
		ctx.strokeStyle = pluginOptions.border_color;
		ctx.lineWidth = 1;
		// Border at graph top
		ctx.beginPath();
		ctx.moveTo(chart.chartArea.left, chart.chartArea.top);
		ctx.lineTo(chart.chartArea.right, chart.chartArea.top);
		ctx.stroke();
		// Border along graph right
		ctx.beginPath();
		ctx.moveTo(chart.chartArea.right, chart.chartArea.top);
		ctx.lineTo(chart.chartArea.right, chart.chartArea.bottom);
		ctx.stroke();
		// The other two borders are already drawn by the axis

		ctx.restore();
	}
};

Chart.pluginService.register(plugin);