import { mount } from '@vue/test-utils';
import MismatchesTable from '@/Components/MismatchesTable.vue';
import MismatchRow from '@/Components/MismatchRow.vue';
import { createI18n } from 'vue-banana-i18n';

const i18n = createI18n({
    messages: {},
    locale: 'en',
    wikilinks: true
});

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
            props: { mismatches },
            global: {
                plugins: [i18n]
            }
        });

        const rows = wrapper.findAllComponents(MismatchRow);

        expect( wrapper.props().mismatches ).toStrictEqual( mismatches );
        expect(rows.length).toBe(mismatches.length);

        rows.forEach(row => {
            expect(mismatches).toContainEqual(row.props().mismatch);
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
            props: { disabled, mismatches },
            global: {
                plugins: [i18n]
            }
        });

        const rows = wrapper.findAllComponents(MismatchRow);

        expect( wrapper.props().disabled ).toBe( disabled );
        rows.forEach(row => {
            expect(row.props().disabled).toBe( disabled )
        });
    });
})
