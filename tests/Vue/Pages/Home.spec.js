import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex'
import Home from '@/Pages/Home.vue';

// Stub the inertia vue components module entirely so that we don't run into
// issues with the Head component.
jest.mock('@inertiajs/inertia-vue', () => ({}));

describe('Home.vue', () => {

    const mocks = {
        $i18n: key => key,
        $page: {
            props: { flash: {} }
        },
    }

    const localVue = createLocalVue();
    localVue.use(Vuex);

    const store = new Vuex.Store();

    it('sanitises input with empty lines', async () => {
        const formData = { itemsInput: 'Q1\n\nQ2\n' };

        const wrapper = mount(Home, { 
            mocks,
            localVue,
            store,
            data() {
                return { form: formData };
            },
        });

        expect( wrapper.vm.serializeInput() ).toEqual('Q1|Q2');
    });

})
