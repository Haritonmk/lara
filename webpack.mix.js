const {mix} = require('laravel-mix');
const CleanWebpackPlugin = require('clean-webpack-plugin');

// paths to clean
var pathsToClean = [
    'public/js',
    'public/css',
];

// the clean options to use
var cleanOptions = {};

mix.webpackConfig({
    plugins: [
        new CleanWebpackPlugin(pathsToClean, cleanOptions)
    ]
});

	mix.scripts([
		'node_modules/jquery/dist/jquery.js',
		'node_modules/bootstrap/dist/js/bootstrap.js',
		'node_modules/gentelella/vendors/bootstrap-progressbar/bootstrap-progressbar.min.js',
		'node_modules/gentelella/build/js/custom.js',
	], 'public/js/app.js').version();

	mix.styles([
		'node_modules/font-awesome/css/font-awesome.css',
		'node_modules/bootstrap/dist/css/bootstrap.css',
		'node_modules/gentelella/vendors/animate.css/animate.css',
		'node_modules/gentelella/build/css/custom.css',
	], 'public/css/app.css').version();

	mix.copy([
		'node_modules/font-awesome/fonts/',
		'node_modules/gentelella/vendors/bootstrap/dist/fonts',
	], 'public/fonts');

	mix.scripts([
		'node_modules/select2/dist/js/select2.full.js',
		'node_modules/gentelella/vendors/Flot/jquery.flot.js',
		'node_modules/gentelella/vendors/Flot/jquery.flot.time.js',
		'node_modules/gentelella/vendors/Flot/jquery.flot.pie.js',
		'node_modules/gentelella/vendors/Flot/jquery.flot.stack.js',
		'node_modules/gentelella/vendors/Flot/jquery.flot.resize.js',

		'node_modules/gentelella/vendors/flot.orderbars/js/jquery.flot.orderBars.js',
		'node_modules/gentelella/vendors/DateJS/build/date.js',
		'node_modules/gentelella/vendors/flot.curvedlines/curvedLines.js',
		'node_modules/gentelella/vendors/flot-spline/js/jquery.flot.spline.min.js',

		'node_modules/gentelella/production/js/moment/moment.min.js',
		'node_modules/gentelella/vendors/bootstrap-daterangepicker/daterangepicker.js',

		'node_modules/gentelella/vendors/Chart.js/dist/Chart.js',

		'resources/assets/js/app.js',
	], 'public/js/source.js').version();

	mix.styles([
		'node_modules/select2/dist/css/select2.css',
		'node_modules/gentelella/vendors/bootstrap-daterangepicker/daterangepicker.css',
		'resources/assets/css/app.css',
	], 'public/css/source.css').version();

	mix.scripts([
		'node_modules/vue/dist/vue.js'
	],'public/js/vue.js').version();