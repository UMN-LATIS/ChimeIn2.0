const mix = require("laravel-mix");

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
  .vue()
  .sass("resources/assets/sass/app.scss", "public/css")
  .sourceMaps(false, "source-map");

if (mix.inProduction()) {
  mix
    .version()
    // added to version chunks with hash in filename
    // rather than just appending a hash via query string
    .webpackConfig({
      output: {
        chunkFilename: "js/[name].[chunkhash].js",
        publicPath: "/",
      },
    });
}
