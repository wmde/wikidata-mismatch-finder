import Vue from 'vue';
import { createPinia, Pinia, PiniaVuePlugin } from 'pinia'
import { Inertia } from '@inertiajs/inertia';
import { useBaseStore } from './base';

export function setupStore(): Pinia {
    Vue.use(PiniaVuePlugin);

    const pinia: Pinia = createPinia();
    const store = useBaseStore();

    let timer: ReturnType<typeof setTimeout>;

    Inertia.on('start', () => {
        // Only instantiate loading state after 250ms. This is done to
        // prevent a flash of the loader in case loading is nearly
        // immediate, which can be visually distracting.
        timer = setTimeout(() => store.startLoader(), 250);
    });

    Inertia.on('finish', (event) => {
        clearTimeout(timer);
        const status = event.detail.visit;

        if (status.completed || status.cancelled) {
            store.stopLoader();
        }
    });

    return pinia;
}
