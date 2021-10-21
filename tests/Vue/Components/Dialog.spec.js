import { mount } from '@vue/test-utils';
import Dialog from '@/Components/Dialog.vue';

describe('Dialog.vue', () => {
    it('doesn\'t show dialog by default', () => {
        const wrapper = mount(Dialog);

        const container = wrapper.find('.wikit-Dialog');

        expect(wrapper.props().visible).toBe(false);
        expect(container.isVisible()).toBe(false);
    });

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

    it('accepts and renders actions prop', () => {
        const actions = [
            {
                label: 'Primary Test!',
                namespace: 'primary-test'
            },
            {
                label: 'Secondary Test!',
                namespace: 'secondary-test'
            }
        ];

        const wrapper = mount(Dialog, {
            propsData: { actions }
        });

        const footer = wrapper.find('footer');

        expect(wrapper.props().actions).toBe(actions);

        actions.forEach(({label, namespace}) => {
            const button = footer.find(`.${namespace}`);

            expect(button.text()).toBe(label);
        });
    });

    it('accepts dismissible prop', () => {
        const wrapper = mount(Dialog, {
            propsData: { dismissible: true }
        });

        const closeButton = wrapper.find('.wikit-Dialog__close');

        expect(wrapper.props().dismissible).toBe(true);
        expect(closeButton.exists()).toBe(true);
    });

    it('accepts visible prop and shows dialog', () => {
        const wrapper = mount(Dialog, {
            propsData: { visible: true }
        });

        const container = wrapper.find('.wikit-Dialog');

        expect(wrapper.props().visible).toBe(true);
        expect(container.isVisible()).toBe(true);
    });

    // Slots
    it('renders content slot', () => {
        const content = '<p>Hello World</p>';
        const wrapper = mount(Dialog, {
            slots: {
                default: content
            }
        });

        const slot = wrapper.find('.wikit-Dialog__content');

        expect(slot.element.innerHTML).toBe(content);
    });

    // Events
    it('emits update:visible event when hiding dialog', () => {
        const wrapper = mount(Dialog);

        wrapper.vm.hide();

        expect(wrapper.emitted()['update:visible']).toBeTruthy();
        expect(wrapper.emitted()['update:visible'][0]).toEqual([false]);
    });

    it('emits update:visible event when showing dialog', () => {
        const wrapper = mount(Dialog);

        wrapper.vm.show();

        expect(wrapper.emitted()['update:visible']).toBeTruthy();
        expect(wrapper.emitted()['update:visible'][0]).toEqual([true]);
    });

    it('emits action event when pressing an action button', () => {
        const actions = [
            {
                label: 'CLICK ME!!!',
                namespace: 'clickety-clack'
            },
            {
                label: 'ME CLACK!!!',
                namespace: 'clackety-click'
            }
        ];

        const wrapper = mount(Dialog, {
            propsData: { actions }
        });

        actions.forEach( ({_, namespace}, i) => {
            const button = wrapper.find(`.${namespace}`);

            // Trigger click event
            button.trigger('click');

            const emitted = wrapper.emitted().action;

            expect(emitted).toBeTruthy();
            expect(emitted[i]).toEqual([namespace]);
        });
    });

    // Behaviour
    test.todo('closes when pressing the close button');
    test.todo('closes when pressing the overlay');
    test.todo('closes when pressing an action');

     // Future iterations
    test.todo('exposes toggle method');
    test.todo('accepts loading prop');
    test.todo('accepts responsive breakpoint prop');
});
