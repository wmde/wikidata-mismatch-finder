import './bootstrap';
import Vue from 'vue';
import Vuex, {Store} from 'vuex';
import i18n from 'vue-banana-i18n';
import { Inertia } from '@inertiajs/inertia';
import { createInertiaApp } from '@inertiajs/inertia-vue';

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

// A simple store to manage global client side state. In case this needs to
// scale up, it is recommended to implement a more robust state management
// architecture. See https://vuex.vuejs.org/guide/structure.html
function createStore(): Store<{loading: boolean}>{
    Vue.use(Vuex);

    const store = new Store({
        state: {
            loading: false
        },
        mutations: {
            startLoader (state) {
                state.loading = true;
            },
            stopLoader (state) {
                state.loading = false;
            }
        }
    });

    let timer: ReturnType<typeof setTimeout>;

    Inertia.on('start', () => {
        // Only instantiate loading state after 250ms. This is done to
        // prevent a flash of the loader in case loading is nearly
        // immediate, which can be visually distracting.
        timer = setTimeout(() => store.commit('startLoader'), 250);
    });

    Inertia.on('finish', (event) => {
        clearTimeout(timer);
        const status = event.detail.visit;

        if (status.completed || status.cancelled) {
            store.commit('stopLoader');
        }
    });

    return store;
}



// Only bootstrap inertia if setup is successful. Display generic error
// component otherwise
(async () => {
    try {
        await setupI18n(document.documentElement.lang);
        const store = createStore();

        createInertiaApp({
            resolve: name => {
                const page = require(`./Pages/${name}`).default;
                // Ensure that every page uses the Mismatch Finder layout
                page.layout = page.layout || Layout;

                return page
            },
            setup({ el, app, props }) {
                new Vue({
                    render: h => h(app, props),
                    store
                }).$mount(el);
            }
        });
    } catch (e) {
        new Vue({
            render: h => h(Error, {
                props: {
                    title: 'Oops!',
                    description: 'Something unexpected happened, but we are working on it... please try to refresh, or come back later.'
                }
            }),
        }).$mount('#app');
    }
})();

