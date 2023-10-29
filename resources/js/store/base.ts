import { defineStore } from 'pinia';

import RootState from './RootState';

export const useBaseStore = defineStore('base', {
    state: (): RootState => ({
        loading: false,
        lastSearchedIds: ''
    }),
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
    }
});
