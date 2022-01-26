import Vue from 'vue';
import Vuex, {Store} from 'vuex';
import RootState from './RootState';
import { Inertia } from '@inertiajs/inertia';
import mutations from './mutations';

export function getInitialState(): RootState {
    return {
        loading: false,
        lastSearchedIds: ''
    }
}

export function createStore(): Store<RootState>{
    Vue.use(Vuex);

    const store = new Store({
        state: getInitialState(),
        mutations
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