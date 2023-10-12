import Vue from 'vue';
// import Vuex, {Store} from 'vuex';
import { defineStore } from 'pinia';
import RootState from './RootState';
import { Inertia } from '@inertiajs/inertia';
import mutations from './mutations';

// export function getInitialState(): RootState {
//     return {
//         loading: false,
//         lastSearchedIds: ''
//     }
// }

export const useStore = defineStore('storeId', {
    state: () : RootState => {
        return {
            loading: false,
            lastSearchedIds: ''
        }
    }
});
// Store<RootState>{

    // const store = new Store({
    //     state: getInitialState(),
    //     mutations
    // });

    // TODO: migrate this part

//     let timer: ReturnType<typeof setTimeout>;

//     Inertia.on('start', () => {
//         // Only instantiate loading state after 250ms. This is done to
//         // prevent a flash of the loader in case loading is nearly
//         // immediate, which can be visually distracting.
//         timer = setTimeout(() => store.commit('startLoader'), 250);
//     });

//     Inertia.on('finish', (event) => {
//         clearTimeout(timer);
//         const status = event.detail.visit;

//         if (status.completed || status.cancelled) {
//             store.commit('stopLoader');
//         }
//     });

//     return store;
// }