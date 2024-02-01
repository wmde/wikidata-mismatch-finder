import { mount } from '@vue/test-utils';
import TableComponent from '@/Components/TableComponent.vue';
import {Breakpoint} from '@/types/Breakpoint.ts';

describe('TableComponent.vue', () => {
    it('accepts a linearize property', () => {
        const wrapper = mount(TableComponent, {
            propsData: {
                linearize: Breakpoint.Desktop
            }
        });

        expect( wrapper.props().linearize ).toBe( Breakpoint.Desktop );
		expect( wrapper.find( 'table' ).classes() ).toContain( 'table-component--linear-desktop' );
    });

    it('ignores invalid breakpoint values', () => {
        const wrapper = mount(TableComponent, {
            propsData: { linearize: 'nonsense' }
        });

        expect(wrapper.find('table').classes()).toContain('table-component--linear-tablet');
    });
})