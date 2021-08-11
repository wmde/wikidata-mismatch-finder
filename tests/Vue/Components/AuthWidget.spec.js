import { mount } from '@vue/test-utils';
import AuthWidget from '@/Components/AuthWidget.vue';

describe('AuthWidget.vue', () => {
    it('displays a the username when provided', () => {
        const username = "TinkyWinky"
        const wrapper = mount(AuthWidget, {
            propsData: {
                user: { name: username }
            }
        });

        expect(wrapper.text()).toContain(username);
    })
})
