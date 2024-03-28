import { mount } from '@vue/test-utils';
import MismatchesTable from '@/Components/MismatchesTable.vue';
import MismatchRow from '@/Components/MismatchRow.vue';

describe('MismatchesTable.vue', () => {
    it('accepts a mismatches property', () => {
        const mismatches = [
            {
                id: 123,
                property_id: 'P123',
                wikidata_value: 'Some value',
                external_value: 'Another Value',
                import_meta: {
                    user: {
                        username: 'some_user_name'
                    },
                    created_at: '2021-09-23'
                },
            }
        ];

        const wrapper = mount(MismatchesTable, {
            propsData: { mismatches },
            mocks: {
                // Mock the banana-i18n plugin dependency
                $i18n: key => key
            }
        });

        const rows = wrapper.findAllComponents(MismatchRow);

        expect( wrapper.props().mismatches ).toBe( mismatches );
        expect(rows.length).toBe(mismatches.length);

        rows.wrappers.forEach(row => {
            expect(mismatches).toContain(row.props().mismatch);
        });
    });

    it('accepts a disabled property', () => {
        const disabled = true;
        const mismatches = [
            {
                id: 123,
                property_id: 'P123',
                wikidata_value: 'Some value',
                external_value: 'Another Value',
                import_meta: {
                    user: {
                        username: 'some_user_name'
                    },
                    created_at: '2021-09-23'
                },
            }
        ];

        const wrapper = mount(MismatchesTable, {
            propsData: { disabled, mismatches },
            mocks: {
                // Mock the banana-i18n plugin dependency
                $i18n: key => key
            }
        });

        const rows = wrapper.findAllComponents(MismatchRow);

        expect( wrapper.props().disabled ).toBe( disabled );
        rows.wrappers.forEach(row => {
            expect(row.props().disabled).toBe( disabled )
        });
    });
})
