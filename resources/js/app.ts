import './bootstrap';
import Vue from 'vue';
import i18n from 'vue-banana-i18n';
import { createInertiaApp } from '@inertiajs/inertia-vue';
import i18nMessages from './lib/i18n';
import Error from './Pages/Error.vue';
import Layout from './Pages/Layout.vue';

// Retrieve i18n messages and setup the Vue instance to handle them.
async function setupI18n(locale: string): Promise<void>{
    const messages = await i18nMessages(locale);
    Vue.use(i18n, { locale, messages });
}

createInertiaApp({
    resolve: name => {
        const page = require(`./Pages/${name}`).default;
        // Ensure that every page uses the Mismatch Finder layout
        page.layout = page.layout || Layout;

        return page
    },
    setup({ el, app, props }) {
        (async () => {
            try {
                await setupI18n(document.documentElement.lang);

                new Vue({
                    render: h => h(app, props),
                }).$mount(el)
            } catch (e) {
                new Vue({
                    render: h => h(Error, {
                        props: {
                            title: 'Oops!',
                            description: 'Something unexpected happened, but we are working on it... please try to refresh, or come back later.'
                        }
                    }),
                }).$mount(el)
            }
        })()
    },
})
