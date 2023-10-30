import './bootstrap';
import Vue, { createApp, h } from 'vue';
import i18n from 'vue-banana-i18n';
import { setupStore } from './store';
import { createInertiaApp } from '@inertiajs/inertia-vue3';

import i18nMessages from './lib/i18n';
import bubble from './lib/bubble';
import Error from './Pages/Error.vue';
import Layout from './Pages/Layout.vue';

Vue.use(bubble);

// Retrieve i18n messages and setup the Vue instance to handle them.
async function setupI18n(locale: string): Promise<void>{
    const messages = await i18nMessages(locale);
    Vue.use(i18n, { locale, messages });
}

// Only bootstrap inertia if setup is successful. Display generic error
// component otherwise
(async () => {
    try {
        await setupI18n(document.documentElement.lang);
        const pinia = setupStore();

        createInertiaApp({
            resolve: name => {
                const page = require(`./Pages/${name}`).default;
                // Ensure that every page uses the Mismatch Finder layout
                page.layout = page.layout || Layout;

                return page
            },
            setup({ el, app, props, plugin }) {
                createApp({ render: () => h(app, props), pinia })
                    .use(plugin)
                    .mount(el);
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

