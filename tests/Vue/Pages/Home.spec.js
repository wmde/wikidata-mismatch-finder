import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex'
import Home, { MAX_NUM_IDS } from '@/Pages/Home.vue'

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

    it('sanitises input with empty lines', async () => {

        const itemsInput = 'Q1\n\nQ2\n';

        const store = new Vuex.Store({});

        const wrapper = mount(Home, {
            mocks,
            localVue,
            store,
            data() {
                return {
                    form: {
                        itemsInput
                    }
                }
            }
        });

        expect( wrapper.vm.serializeInput() ).toEqual('Q1|Q2');
    });

    it('restores lastSearchIds value from store on page load', async () => {

        const itemsInput = 'Q1\n\nQ2\n';
        const store = new Vuex.Store({state: {lastSearchedIds: itemsInput} });

        const wrapper = mount(Home, {
            mocks,
            localVue,
            store
        });

        expect( wrapper.vm.form.itemsInput ).toEqual(itemsInput);
    });

    it('shows dialog after clicking the more info button', async () => {
        const store = new Vuex.Store();

        const wrapper = mount(Home, { mocks, localVue, store });
        await wrapper.find('#faq-button').trigger('click');

        const dialog = wrapper.find('#faq-dialog .wikit-Dialog');
        expect(dialog.isVisible()).toBe(true);
    });

    it('validates that items in textarea input dont exceed the maximum number of ids', async () => {
        const store = new Vuex.Store();

        const itemsInput = Array(MAX_NUM_IDS + 2).fill('Q21').join('\n');

        const wrapper = mount(Home, {
            mocks,
            localVue,
            store,
            data() {
                return {
                    form: {
                        itemsInput
                    }
                }
            }
        });

        wrapper.vm.validate();
        expect(wrapper.vm.validationError.message).toBe('item-form-error-message-max');
    });

})
