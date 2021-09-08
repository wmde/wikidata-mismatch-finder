import './bootstrap';
import Vue from 'vue';
import i18n from 'vue-banana-i18n';
import { createInertiaApp } from '@inertiajs/inertia-vue';
import i18nMessages from './lib/i18n';
import Error from './Pages/Error.vue';
import Layout from './Pages/Layout.vue';

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
                const locale = document.documentElement.lang;
                const messages = await i18nMessages(locale);
                Vue.use(i18n, { locale, messages });

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
