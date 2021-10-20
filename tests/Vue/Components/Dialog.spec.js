import { mount } from '@vue/test-utils';
import Dialog from '@/Components/Dialog.vue';

describe('Dialog.vue', () => {
    // Props
    it('accepts and renders title prop', () => {
        const title = 'Hello World!';

        const wrapper = mount(Dialog, {
            propsData: {
                title
            }
        });

        const header = wrapper.find('header');

        expect(wrapper.props().title).toBe(title);
        expect(header.text()).toContain(title);
    });

    test.todo('accepts and renders actions prop');
    test.todo('accepts dismissible prop');
    test.todo('accepts open prop');

    // Slots
    test.todo('renders content slot');

    // Events
    test.todo('emits closed event');
    test.todo('emits action event');

    // Methods
    test.todo('exposes show method');

    // Bindings
    test.todo('bind visibility state to v-model');

    // Future iterations
    test.todo('exposes hide method');
    test.todo('exposes toggle method');
    test.todo('accepts loading prop');
    test.todo('accepts responsive breakpoint prop');
});
