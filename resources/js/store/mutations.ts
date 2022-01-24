import RootState from './StoreTypes';

export default {
    startLoader (state: RootState) {
        state.loading = true;
    },
    stopLoader (state: RootState) {
        state.loading = false;
    },
    saveSearchedIds (state: RootState, searchedIds: string) {
        state.lastSearchedIds = searchedIds;
    }
}