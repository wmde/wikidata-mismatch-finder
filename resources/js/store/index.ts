import { defineStore } from 'pinia';
import RootState from './RootState';
import { Inertia } from '@inertiajs/inertia';

export const useStore = defineStore('storeId', {
    state: () : RootState => {
        return {
            loading: false,
            lastSearchedIds: ''
        }
    },
    actions: {
        startLoader () {
            this.loading = true;
        },
        stopLoader () {
            this.loading = false;
        },
        saveSearchedIds (searchedIds: string) {
            this.lastSearchedIds = searchedIds;
        }
    },

});

let timer: ReturnType<typeof setTimeout>;

Inertia.on('start', () => {
    // Only instantiate loading state after 250ms. This is done to
    // prevent a flash of the loader in case loading is nearly
    // immediate, which can be visually distracting.
    const store = useStore();
    timer = setTimeout(() => store.startLoader(), 250);
});

Inertia.on('finish', (event) => {
    clearTimeout(timer);
    const status = event.detail.visit;

    if (status.completed || status.cancelled) {
        const store = useStore();
        store.stopLoader();
    }
});