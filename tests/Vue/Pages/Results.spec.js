import { mount } from '@vue/test-utils';
import { createTestingPinia } from '@pinia/testing';
import Results from '@/Pages/Results.vue';
import MismatchesTable from '@/Components/MismatchesTable.vue';

import { ReviewDecision } from '@/types/Mismatch.ts';
import axios from 'axios';

// Stub the inertia vue components module entirely so that we don't run into
// issues with the Head component.
jest.mock('@inertiajs/inertia-vue3', () => ({}));

jest.mock("axios", () => ({
    put: jest.fn()
}));

describe('Results.vue', () => {
    function mountWithMocks({
        props = {},
        data = {},
        initialState = {},
        mocks = {}
    } = {}){
        const globalMocks = {
            $i18n: key => key,
            $page: {
                props: { flash: {} }
            },
        };

        return mount(Results, {
            props,
            data(){
                return data;
            },
            global: {
                mocks: {
                    ...globalMocks,
                    mocks
                },
                plugins: [createTestingPinia({ initialState })],
                stubs: {
                    teleport: true,
                    transition: true
                }
            }
        })
    }

    beforeEach(async () => {
        axios.put = jest.fn();
    });

    it('displays intro text and instructions button', () => {
        const wrapper = mountWithMocks();

        const intro = wrapper.find('#about-description');
        expect(intro.isVisible()).toBe(true);

        const instructionsButton = wrapper.find('#instructions-button');
        expect(instructionsButton.isVisible()).toBe(true);

    });

    it('shows dialog after clicking the instructions button', async () => {
        const wrapper = mountWithMocks();
        await wrapper.find('#instructions-button').trigger('click');

        const dialog = wrapper.find('#instructions-dialog.cdx-dialog');
        expect(dialog.isVisible()).toBe(true);
    });

    it('accepts and renders item ids', () => {
        const item_ids =  ['Q1', 'Q2']
        const wrapper = mountWithMocks({
            props: { item_ids }
        });

        expect(wrapper.props().item_ids).toStrictEqual(item_ids);
        item_ids.forEach(id => expect(wrapper.text()).toContain(id));
    });

    it('accepts and renders results', () => {
        const results = {
            'Q321': [{
                id: 123,
                item_id: 'Q321',
                property_id: 'P123',
                property_label: 'some property',
                wikidata_value: 'Some value',
                value_label: null,
                external_value: 'Another Value',
                import_meta: {
                    user: {
                        username: 'some_user_name'
                    },
                    created_at: '2021-09-23'
                },
            }],
            'Q987': [{
                id: 654,
                item_id: 'Q987',
                property_id: 'p789',
                property_label: 'some property',
                wikidata_value: 'Some value',
                value_label: null,
                external_value: 'Another Value',
                import_meta: {
                    user: {
                        username: 'some_user_name'
                    },
                    created_at: '2021-09-23'
                },
            }]
        };

        const wrapper = mountWithMocks({
            props: { results }
        });

        const tables = wrapper.findAllComponents(MismatchesTable);

        expect(wrapper.props().results).toStrictEqual(results);

        Object.keys(results).forEach((itemId, i) => {
            const section = wrapper.find(`#item-mismatches-${itemId}`);
            const table = tables.at(i);

            expect(section.text()).toContain(itemId);
            expect(table.props().mismatches).toEqual(results[itemId]);
        });
    });

    it('accepts and renders labels', () => {
        const results = {
            'Q321': [{
                id: 123,
                item_id: 'Q321',
                property_id: 'P123',
                wikidata_value: 'Q1986',
                external_value: 'Another Value',
                import_meta: {
                    user: {
                        username: 'some_user_name'
                    },
                    created_at: '2021-09-23'
                },
            }]
        };

        const labels = {
            'Q321': 'Some Item',
            'P123': 'Some Property',
            'Q1986': 'Some Value'
        }

        const wrapper = mountWithMocks({
            props: { results, labels }
        });

        expect(wrapper.props().labels).toStrictEqual(labels);

        Object.values(labels).forEach(label => expect(wrapper.text()).toContain(label));
    });

    it('accepts and renders formatted values', () => {
        const results = {
            'Q321': [{
                id: 123,
                item_id: 'Q321',
                property_id: 'P123',
                wikidata_value: '21. century',
                meta_wikidata_value: '',
                external_value: 'Another Value',
                import_meta: {
                    user: {
                        username: 'some_user_name'
                    },
                    created_at: '2021-09-23'
                },
            }]
        };

        const formatted_values = {
            'P123': {
                '|21. century': '21. Jahrhundert', // pretend uselang=de
            },
        };

        const wrapper = mountWithMocks({
            props: { results, formatted_values }
        });

        expect(wrapper.props().formatted_values).toStrictEqual(formatted_values);
        expect(wrapper.text()).toContain('21. Jahrhundert');
    });

    it('renders formatted values when a calendar model item id is specified', () => {
        const results = {
            'Q321': [{
                id: 123,
                item_id: 'Q321',
                property_id: 'P123',
                wikidata_value: '21. century',
                meta_wikidata_value: 'Q565787',
                external_value: 'Another Value',
                import_meta: {
                    user: {
                        username: 'some_user_name'
                    },
                    created_at: '2021-09-23'
                },
            }]
        };
        // meta_wikidata_value is prepended to the wikidata_value with a pipe separator
        const formatted_values = {
            'P123': {
                'Q565787|21. century': '21. Jahrhundert', // pretend uselang=de
            },
        };

        const wrapper = mountWithMocks({
            props: { results, formatted_values }
        });

        expect(wrapper.props().formatted_values).toStrictEqual(formatted_values);
        expect(wrapper.text()).toContain('21. Jahrhundert');
    });

    it('accepts a user prop', () => {
        const user = {
            name: 'test',
            id: '123'
        };

        const wrapper = mountWithMocks({
            props: { user }
        });

        expect(wrapper.props().user).toStrictEqual(user);
    })

    it('Updates decisions mismatches on emitted decision events', () => {
        const results = {
            'Q321': [{
                id: 123,
                item_id: 'Q321',
                property_id: 'P123',
                wikidata_value: 'Q1986',
                external_value: 'Another Value',
                review_status: 'pending',
                import_meta: {
                    user: {
                        username: 'some_user_name'
                    },
                    created_at: '2021-09-23'
                },
            }]
        };

        const wrapper = mountWithMocks({
            props: { results }
        });

        const tables = wrapper.findAllComponents(MismatchesTable);
        const emitted = {
            id: 123,
            item_id: 'Q321',
            review_status: ReviewDecision.Wikidata
        };

        tables.at(0).vm.$emit('decision', emitted);

        expect(wrapper.vm.decisions['Q321'][123]).toEqual(emitted)
    });

    it('Keeps track of decisions with hasChanged()', () => {
        const results = {
            'Q321': [{
                id: 123,
                item_id: 'Q321',
                property_id: 'P123',
                wikidata_value: 'Q1986',
                external_value: 'Another Value',
                review_status: 'pending',
                import_meta: {
                    user: {
                        username: 'some_user_name'
                    },
                    created_at: '2021-09-23'
                },
            }]
        };

        const wrapper = mountWithMocks({
            props: { results }
        });

        // first decision
        wrapper.vm.recordDecision( {id:123, item_id: 'Q321', review_status: ReviewDecision.Wikidata} );
        expect( wrapper.vm.hasChanged('Q321') ).toBe(true);

        // revert decision
        wrapper.vm.recordDecision( {id:123, item_id: 'Q321', review_status: ReviewDecision.Pending} );
        expect( wrapper.vm.hasChanged('Q321') ).toBe(false);
    });

    it('Sends an axios put request with the selected decisions on click of "Save reviews" button', async () => {
        const item_id = 'Q321';
        const decisions = { [item_id]:{1:{id:1, item_id, review_status: ReviewDecision.Wikidata}} };
        const wrapper = mountWithMocks({
            data: { decisions }
        });

        const decisionsBeforeDelete = decisions[item_id];
        await wrapper.vm.send( item_id );
        expect( axios.put ).toHaveBeenCalledWith( '/mismatch-review' , decisionsBeforeDelete );

        //the decisions object will store the review status as previous_status
        expect(wrapper.vm.decisions).toEqual({
            [item_id]:
            {1:{id:1, item_id, review_status: ReviewDecision.Wikidata, previous_status: ReviewDecision.Wikidata}}
        });

    });

    it('Handles errors on axios PUT requests gracefully', async () => {
        // mock axios error response
        axios.put = jest.fn().mockRejectedValue('Error');

        const item_id = 'Q321';
        const decisions = {
            [item_id]: {
                1: {
                    id:1,
                    item_id,
                    review_status: ReviewDecision.Wikidata,
                    previous_status: ReviewDecision.Pending
                }
            }
        };
        const wrapper = mountWithMocks({
            data: { decisions }
        });

        await wrapper.vm.send( item_id );

        //the decisions object will remain untouched after the failed put request
        expect(Object.keys(wrapper.vm.decisions)).toContain(item_id);
    });

    it('Shows error message on failed axios PUT request', async () => {
        const wrapper = mountWithMocks({
            data: { 'requestError' : true }
        });

        const errorMessage = wrapper.find('#error-section .cdx-message--error');
        expect(errorMessage.isVisible()).toBe(true);
    });

    it('Clears error message on successful axios PUT request', async () => {

        const item_id = 'Q321';
        const decisions = { [item_id]:{1:{id:1, item_id ,review_status: ReviewDecision.Wikidata}}};
        const wrapper = mountWithMocks({
            data: {
                decisions,
                'requestError' : true
            }
        });

        await wrapper.vm.send( item_id );

        const errorMessage = wrapper.find('#error-section .cdx-message--error');
        expect(errorMessage.exists()).toBe(false);
    });

    it('Does not send a put request without any decisions', () => {
        // clear mock object
        axios.put = jest.fn();

        const item_id = 'Q321';
        const decisions = { [item_id]:{1:{id:1, item_id ,review_status: ReviewDecision.Wikidata}}};
        const wrapper = mountWithMocks({
            data: { decisions }
        });
        wrapper.vm.send( 'Q42' );
        expect( axios.put ).not.toHaveBeenCalled();

        //the decisions object will be untouched
        expect(wrapper.vm.decisions).toEqual({ [item_id]:{1:{id:1, item_id ,review_status: ReviewDecision.Wikidata}}});
    });

    it('Does not send a reverted decision', () => {
        // clear mock object
        axios.put = jest.fn();

        const item_id = 'Q321';
        const decisions = {
            [item_id]: {
                1: {
                    id:1,
                    item_id,
                    review_status: ReviewDecision.Pending,
                    previous_status: ReviewDecision.Pending
                }
            }
        };
        const wrapper = mountWithMocks({
            data: { decisions }
        });
        wrapper.vm.send( 'Q42' );
        expect( axios.put ).not.toHaveBeenCalled();
    });

    it('Doesn\'t show confirmation dialog after failed put requests', async () => {
        // Ensure a failed response (axios throws on any failed response from 400 up)
        axios.put.mockImplementationOnce(() => { throw new Error() });

        const item_id = 'Q321';
        const decisions = { [item_id]:{1:{id:1, item_id ,review_status: ReviewDecision.Wikidata}}};
        const wrapper = mountWithMocks({
            data: { decisions }
        });

        await wrapper.vm.send(item_id);
        const dialog = wrapper.find('.confirmation-dialog .cdx-dialog');

        expect(dialog.exists()).toBe(false);
    });

    it('Displays a confirmation message after submitting a review decision', async () => {
        const results = {
            'Q321': [{
                id: 123,
                item_id: 'Q321',
                property_id: 'P123',
                wikidata_value: 'Q1986',
                external_value: 'Another Value',
                import_meta: {
                    user: {
                        username: 'some_user_name'
                    },
                    created_at: '2021-09-23'
                },
            }]
        };

        const item_id = 'Q321';
        const decisions = { [item_id]:{1:{id:1, item_id ,review_status: ReviewDecision.Wikidata}}};
        const wrapper = mountWithMocks({
            props: { results },
            data: { decisions }
        });

        await wrapper.vm.send( item_id );

        expect(wrapper.vm.lastSubmitted).toEqual('Q321');
        expect(wrapper.find('#item-mismatches-Q321 .cdx-message--success').isVisible()).toBe(true);
    });

    it('Removes first confirmation message before submitting a second review decision', async () => {
        const results = {
            'Q321': [{
                id: 123,
                item_id: 'Q321',
                property_id: 'P123',
                wikidata_value: 'Q1986',
                external_value: 'Another Value',
                import_meta: {
                    user: {
                        username: 'some_user_name'
                    },
                    created_at: '2021-09-23'
                },
            }],
            'Q987': [{
                id: 654,
                item_id: 'Q987',
                property_id: 'p789',
                property_label: 'some property',
                wikidata_value: 'Some value',
                value_label: null,
                external_value: 'Another Value',
                import_meta: {
                    user: {
                        username: 'some_user_name'
                    },
                    created_at: '2021-09-23'
                },
            }]
        };

        const lastSubmitted = 'Q321';
        const item_id = 'Q987';
        const decisions = { [item_id]:{1:{id:1, item_id ,review_status: ReviewDecision.Wikidata}}};
        const wrapper = mountWithMocks({
            props: { results },
            data: { decisions, lastSubmitted}
        });

        expect(wrapper.find('#item-mismatches-Q321 .cdx-message--success').isVisible()).toBe(true);

        await wrapper.vm.send( item_id );

        expect(wrapper.vm.lastSubmitted).toEqual('Q987');
        expect(wrapper.find('#item-mismatches-Q321 .cdx-message--success').exists()).toBe(false);
        expect(wrapper.find('#item-mismatches-Q987 .cdx-message--success').isVisible()).toBe(true);
    });

})
