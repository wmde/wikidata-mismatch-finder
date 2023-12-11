import './bootstrap';
import {createApp, h} from 'vue';
import {createPinia} from 'pinia';
import {createInertiaApp} from '@inertiajs/inertia-vue3';
import getI18nMessages from './lib/i18n';
import {createI18n} from 'vue-banana-i18n'
import bubble from './lib/bubble';
import Error from './Pages/Error.vue';
import Layout from './Pages/Layout.vue';

// TODO: how to effectively export this function and use it in setup at the same time?
// i18n needs to be imported in the other files where it is to be used.
// The issue here is that this is an async function and there is the following error 
// at compilation time:
// TS2614: Module '"vue"' has no exported member 'withAsyncContext'. 
// Did you mean to use 'import withAsyncContext from "vue"' instead?

// export const i18n = async () => {
//     const locale = document.documentElement.lang;
//     const i18nMessages = await getI18nMessages(locale);
//     const i18nPlugin = createI18n({
//         locale: locale,
//         messages: i18nMessages,
//         wikilinks: true,
//         globalInjection: true,
//         legacy: false
//     });
//     return i18nPlugin;
// };

// const resolvedi18n = await i18n();

// Only bootstrap inertia if setup is successful. Display generic error
// component otherwise
(async () => {
    try {
        const locale = document.documentElement.lang;
        const i18nMessages = await getI18nMessages(locale);
        const pinia = createPinia();
        const i18nPlugin = createI18n({
            locale: locale,
            messages: i18nMessages,
            wikilinks: true,
            globalInjection: true,
            legacy: false
        });
        createInertiaApp({
            resolve: name => {
                const page = require(`./Pages/${name}`).default;
                // Ensure that every page uses the Mismatch Finder layout
                page.layout = page.layout || Layout;

                return page
            },
            setup({ el, app, props, plugin }) {
                createApp({
                    render: () => h(app, props)
                })
                    .use(bubble)
                    .use(i18nPlugin)
                    .use(pinia)
                    .use(plugin)
                    .mount(el)
            }
        });
    } catch (e) {
        createApp({
            render: () => h(Error, {
                title: 'Oops!',
                description: 'Something unexpected happened, but we are working on it... please try to refresh, or come back later.'
            }),
        }).mount('#app');
    }
})();
