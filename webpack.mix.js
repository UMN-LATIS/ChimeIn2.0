let mix = require('laravel-mix');

// const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

// mix.webpackConfig({
//   plugins: [
//     new BundleAnalyzerPlugin(),
//   ],
// });
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

 mix.js('resources/assets/js/app.js', 'public/js')
 .sass('resources/assets/sass/app.scss', 'public/css');


const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
// const BundleAnalyzerPlugin = require('babel-plugin-syntax-dynamic-import').BundleAnalyzerPlugin;


const webpack = require('webpack');

 mix.webpackConfig({
 	plugins: [
 	new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),
 	// new BundleAnalyzerPlugin()
 	]
 })


// breaks hot updating?

if (mix.inProduction()) {
 	mix.config.webpackConfig.output = {
    	chunkFilename: 'js/[name].bundle.js',
    	publicPath: '/',
	};
	mix.version();
}