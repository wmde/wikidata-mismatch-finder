import { mount } from '@vue/test-utils';
import TextArea from '@/Components/TextArea.vue';
import ResizeLimit from '@/types/ResizeLimit';
import { toEditorSettings } from 'typescript';

describe('TextArea.vue', () => {
    it('accepts rows property', () => {
        const wrapper = mount(TextArea, {
            propsData: { rows: 42 }
        });

        expect(wrapper.props().rows).toBe(42);
        expect(wrapper.find('.wikit-TextArea').attributes('rows')).toBe('42');
    });

    it('accepts resize property', () => {
        const wrapper = mount(TextArea, {
            propsData: { resize: ResizeLimit.Horizontal }
        });

        expect(wrapper.props().resize).toBe(ResizeLimit.Horizontal);
        expect(wrapper.find('.wikit-TextArea').classes()).toContain('wikit-TextArea--horizontal');
    });

    todo.test('accepts label property');
    todo.test('accepts placeholder property');

    // TODO: This test is waaay too noisy and we need to find a way to shush it
    // it('ignores invalid resize values', () => {
    //     const wrapper = mount(TextArea, {
    //         propsData: { resize: 'nonsense' }
    //     });
    //     const spy = jest.spyOn(console, 'error').mockImplementation(() => {});
    //     expect(spy).toHaveBeenCalled();

    //     expect(wrapper.find('.wikit-TextArea').classes()).not.toContain('wikit-TextArea--nonsense');
    // });
});
