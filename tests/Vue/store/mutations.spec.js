import mutations from '@/store/mutations.ts';


const { startLoader, stopLoader, saveSearchedIds } = mutations;

describe('mutations', () => {

    it('startLoader', () => {

      const state = { loading: false };

      startLoader(state);

      expect(state.loading).toEqual(true);
    });

    it('stopLoader', () => {

        const state = { loading: true };
  
        stopLoader(state);
  
        expect(state.loading).toEqual(false);
      });

      it('saveSearchedIds', () => {

        const state = { lastSearchedIds: '' };
  
        saveSearchedIds(state, 'Q1/nQ666');
  
        expect(state.lastSearchedIds).toEqual('Q1/nQ666');
      });
});