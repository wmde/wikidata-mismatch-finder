import './bootstrap';
import {createApp, h} from 'vue';
import {createPinia} from 'pinia';
import {createInertiaApp} from '@inertiajs/inertia-vue3';
import i18nMessages, { I18nMessages } from './lib/i18n';
import i18n from 'vue-banana-i18n';
import bubble from './lib/bubble';
import Error from './Pages/Error.vue';
import Layout from './Pages/Layout.vue';

// Retrieve i18n messages and setup the Vue instance to handle them.
async function setupI18n(locale: string): Promise<I18nMessages>{
    return await i18nMessages(locale);
}

// Only bootstrap inertia if setup is successful. Display generic error
// component otherwise
(async () => {
    try {
        const locale = document.documentElement.lang;
        const i18nMessages = await setupI18n(locale);
        const pinia = createPinia();

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
                    .use(i18n, {locale, messages: i18nMessages})
                    .use(pinia)
                    .use(plugin)
                    .mount(el)
            }
        });
    } catch (e) {
        createApp({
            render: () => h(Error, {
                props: {
                    title: 'Oops!',
                    description: 'Something unexpected happened, but we are working on it... please try to refresh, or come back later.'
                }
            }),
        }).mount('#app');
    }
})();
