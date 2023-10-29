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
    .vue({
        version: 3,
        options: {
            compilerOptions: {
                compatConfig: {
                    MODE: 2,
                },
            },
        },
    })
    .sass('resources/sass/app.scss', 'public/css')
    .sass('resources/sass/noscript.scss', 'public/css')
    .copyDirectory('resources/img', 'public/images')
    .sourceMaps(false)
    .webpackConfig(() => {
        return {
            resolve: {
                alias: {
                    vue: "@vue/compat",
                    "@vue/composition-api": "@vue/compat",
                }
            },
        }
    }); // False prevents source maps in production
