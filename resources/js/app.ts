import './bootstrap';
import {createApp, h} from 'vue';
import {createPinia} from 'pinia';
import {createInertiaApp} from '@inertiajs/inertia-vue3';
import getI18nMessages, { I18nMessages } from './lib/i18n';
import {createI18n} from 'vue-banana-i18n'
import bubble from './lib/bubble';
import Error from './Pages/Error.vue';
import Layout from './Pages/Layout.vue';

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
            wikilinks: true
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
