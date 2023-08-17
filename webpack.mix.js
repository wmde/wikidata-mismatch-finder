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
            // Vue 3 compat config: https://gist.github.com/thecrypticace/2e405ec70d3944a068e21caec59c7122
            compilerOptions: {
                compatConfig: {
                    MODE: 2,
                }
            }
        },
    })
    .sass('resources/sass/app.scss', 'public/css')
    .copyDirectory('resources/img', 'public/images')
    .sourceMaps(false); // False prevents source maps in production

mix.webpackConfig(() => {
    return {
        resolve: {
            alias: {
                vue: "@vue/compat"
            }
        }
    }
});
