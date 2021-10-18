const mix = require("laravel-mix");
const webpack = require("webpack");

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

mix
  .js("resources/assets/js/app.js", "public/js")
  .sass("resources/assets/sass/app.scss", "public/css");

mix.webpackConfig({
  plugins: [new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/)],
});

if (mix.inProduction()) {
  mix.config.webpackConfig.output = {
    chunkFilename: "js/[name].[chunkhash].bundle.js",
    publicPath: "/",
  };
  mix.version();
}
