const mix = require('laravel-mix');

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
    .vue({ version: 2 })
    .sass('resources/sass/app.scss', 'public/css')
    .sourceMaps(false); // False prevents source maps in production

mix.override(webpackConfig => {
    // BUG: vue-loader doesn't handle file-loader's default esModule:true setting properly causing
    // <img src="[object module]" /> to be output from vue templates.
    // WORKAROUND: Override mixs and turn off esModule support on images.
    // FIX: When vue-loader fixes their bug AND laravel-mix updates to the fixed version
    // this can be removed
    webpackConfig.module.rules.forEach(rule => {
        if (rule.test.toString() === '/(\\.(png|jpe?g|gif|webp)$|^((?!font).)*\\.svg$)/') {
        if (Array.isArray(rule.use)) {
            rule.use.forEach(ruleUse => {
            if (ruleUse.loader === 'file-loader') {
                ruleUse.options.esModule = false;
            }
            });
        }
        }
    });
    });