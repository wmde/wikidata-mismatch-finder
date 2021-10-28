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

        actions.forEach(({label, namespace}, i) => {
            const variants = (i === 0) ? ['primary', 'progressive'] : ['normal', 'neutral'];
            const button = footer.find(`.${namespace}`);

            variants.map(variant => `wikit-Button--${variant}`)
                .forEach(expect(button.classes()).toContain);

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

    it('accepts visible prop and shows dialog', async () => {
        const wrapper = mount(Dialog, {
            propsData: { visible: true }
        });

        const content = wrapper.find('.wikit-Dialog');

        expect(wrapper.props().visible).toBe(true);
        expect(content.isVisible()).toBe(true);

        await wrapper.setProps({ visible: false });
        expect(content.isVisible()).toBe(false);
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

    // Methods
    it('opens when calling the show method', async () => {
        const wrapper = mount(Dialog, {
            visible: false
        });

        const content = wrapper.find('.wikit-Dialog');

        wrapper.vm.show();
        await wrapper.vm.$nextTick();

        expect(content.isVisible()).toBe(true);
    });

    it('closes when calling the hide method', async () => {
        const wrapper = mount(Dialog, {
            visible: true
        });

        const content = wrapper.find('.wikit-Dialog');

        wrapper.vm.hide();
        await wrapper.vm.$nextTick();

        expect(content.isVisible()).toBe(false);
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
            expect(emitted[i]).toEqual([namespace, wrapper.vm]);
        });
    });

    // Behaviour
    it('doesn\'t show dialog by default', () => {
        const wrapper = mount(Dialog);
        const content = wrapper.find('.wikit-Dialog');

        expect(wrapper.props().visible).toBe(false);
        expect(content.isVisible()).toBe(false);
    });

    it('closes when pressing the close button', async () => {
        const wrapper = mount(Dialog, {
            propsData: {
                visible: true,
                dismissible: true
            }
        });
        const content = wrapper.find('.wikit-Dialog');

        const button = wrapper.find(`.wikit-Dialog__close`);
        // Trigger click event
        button.trigger('click');

        await wrapper.vm.$nextTick();
        expect(content.isVisible()).toBe(false);
    });

    it('closes when pressing the overlay', async () => {
        const wrapper = mount(Dialog, {
            propsData: { visible: true }
        });
        const content = wrapper.find('.wikit-Dialog');

        const overlay = content.find(`.wikit-Dialog__overlay`);
        // Trigger click event
        overlay.trigger('click');

        await wrapper.vm.$nextTick();
        expect(content.isVisible()).toBe(false);
    });

    it('closes when pressing the esc key', async () => {
        const wrapper = mount(Dialog, {
            propsData: { visible: true },
            attachTo: document.body
        });
        const content = wrapper.find('.wikit-Dialog');

        wrapper.trigger('keydown', {
            key: 'Escape'
        });

        await wrapper.vm.$nextTick();
        expect(content.isVisible()).toBe(false);
    });

    /**
     * Additional Behaviour Browser tests to add to WiKit:
     * - Traps page focus, so that only visually focused elements are tab-able
     * - Prevents underlying page from scrolling when opened and on initial render
     * - Reset dialog scroll bars to top when closed and reopened
     */

     // Future iterations
    test.todo('exposes toggle method');
    test.todo('accepts loading prop');
    test.todo('accepts responsive breakpoint prop');
});
