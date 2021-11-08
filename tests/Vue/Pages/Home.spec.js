import { shallowMount, mount, createLocalVue } from '@vue/test-utils';
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

    // let windowSpy;

    // beforeEach(() => {
    //   windowSpy = jest.spyOn(window, "window", "get");
    // });
    
    // afterEach(() => {
    //   windowSpy.mockRestore();
    // });

    it('goes to Results page when Check items button is clicked', async () => {

        const inertiaGet = jest.fn();

        const store = new Vuex.Store();
        const wrapper = mount(Home, { 
            mocks,
            localVue,
            store,
            $inertia: { get: inertiaGet },
            data() {
                return {
                    form: {
                        itemsInput: 'Q1\nQ2'
                    }
                }
            },
        });

        // windowSpy.mockImplementation(() => ({
        //     location: {
        //       origin: "/results?ids=Q1|Q2"
        //     }
        //   }));

        // const itemsInput = wrapper.find('textarea');
        // await itemsInput.setValue('Q5\nQ6');

        // window.location = {
        //     ...window.location,
        //     reload: jest.fn()
        // }

        //window.location.assign = jest.fn();
        await wrapper.find('.wikit-Button[type="submit"]').trigger('click');
        //expect(window.location.origin).toHaveBeenCalledWith('/results?ids=Q1');
        //expect(wrapper.vm.serializeInput)
        expect( inertiaGet ).toHaveBeenCalledWith('/results?ids=Q1|Q2');

    });

})
