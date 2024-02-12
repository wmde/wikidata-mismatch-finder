const mix = require('laravel-mix');
const path = require('path');
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.ts('resources/js/app.ts', 'public/js')
    .webpackConfig({})
    .vue({ version: 3})
    .sass('resources/sass/app.scss', 'public/css')
    .sass('resources/sass/noscript.scss', 'public/css')
    .copyDirectory('resources/img', 'public/images')
    .sourceMaps(false); // False prevents source maps in production
