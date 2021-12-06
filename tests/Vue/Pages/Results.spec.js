import { mount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import Results from '@/Pages/Results.vue';
import MismatchesTable from '@/Components/MismatchesTable.vue';

import { ReviewDecision } from '@/types/Mismatch.ts';
import axios from 'axios';

// Stub the inertia vue components module entirely so that we don't run into
// issues with the Head component.
jest.mock('@inertiajs/inertia-vue', () => ({}));

jest.mock("axios", () => ({
    put: jest.fn()
}));

describe('Results.vue', () => {
    function mountWithMocks({
        props = {},
        data = {},
        state = {},
        mocks = {}
    } = {}){
        const globalMocks = {
            $i18n: key => key,
            $page: {
                props: { flash: {} }
            },
        };
        const localVue = createLocalVue();

        localVue.use(Vuex);

        return mount(Results, {
            propsData: props,
            data(){
                return data;
            },
            mocks: {
                ...globalMocks,
                mocks
            },
            localVue,
            store: new Vuex.Store({ state })
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

        const dialog = wrapper.find('#instructions-dialog .wikit-Dialog');
        expect(dialog.isVisible()).toBe(true);
    });

    it('accepts and renders item ids', () => {
        const item_ids =  ['Q1', 'Q2']
        const wrapper = mountWithMocks({
            props: { item_ids }
        });

        expect(wrapper.props().item_ids).toBe(item_ids);
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

        expect(wrapper.props().results).toBe(results);

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

        expect(wrapper.props().labels).toBe(labels);

        Object.values(labels).forEach(label => expect(wrapper.text()).toContain(label));
    });

    it('accepts a user prop', () => {
        const user = {
            name: 'test',
            id: '123'
        };

        const wrapper = mountWithMocks({
            props: { user }
        });

        expect(wrapper.props().user).toBe(user);
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

    it('Does not record a reverted decision', () => {
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
        // revert decision
        wrapper.vm.recordDecision( {id:123, item_id: 'Q321', review_status: ReviewDecision.Pending}, true );

        expect( wrapper.vm.decisions['Q321'] ).toEqual({});
    });

    it('Does record and send a reverted decision, when sent in between', async () => {
        // clear mock object
        axios.put = jest.fn();

        const wrapper = mountWithMocks({
            props: {
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
         },
        });

        // select a review decision and send it
        wrapper.vm.recordDecision( {id:123, item_id: 'Q321', review_status: ReviewDecision.Wikidata} );
        await wrapper.vm.send( 'Q321' );
        expect( wrapper.vm.decisions['Q321'] ).toBeFalsy();

        // revert the same review decision back to its previous value
        const revertDecision = { id:123, item_id: 'Q321', review_status:ReviewDecision.Pending };
        wrapper.vm.recordDecision( revertDecision, true );
        expect( wrapper.vm.decisions ).toEqual( { 'Q321': { '123': revertDecision } } );

        // send again
        await wrapper.vm.send( 'Q321' );
        expect( axios.put ).toHaveBeenCalledTimes( 2 );
        expect( wrapper.vm.decisions['Q321'] ).toBeFalsy();
    });

    it('Sends an axios put request with the selected decisions on click of "Apply changes" button', async () => {

        const item_id = 'Q321';
        const decisions = { [item_id]:{1:{id:1, item_id ,review_status: ReviewDecision.Wikidata}}};
        const wrapper = mountWithMocks({
            data: { decisions }
        });

        const decisionsBeforeDelete = decisions[item_id];
        await wrapper.vm.send( item_id );
        expect( axios.put ).toHaveBeenCalledWith( '/mismatch-review' , decisionsBeforeDelete );

        //the decisions object will be empty after sending the put request on one item
        expect(wrapper.vm.decisions).toEqual({});

    });

    it('Handles errors on axios PUT requests gracefully', async () => {
        // mock axios error response
        axios.put = jest.fn().mockRejectedValue('Error');

        const item_id = 'Q321';
        const decisions = { [item_id]:{1:{id:1, item_id ,review_status: ReviewDecision.Wikidata}}};
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

        const errorMessage = wrapper.find('#error-section .wikit-Message--error.wikit');
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

        const errorMessage = wrapper.find('#error-section .wikit-Message--error.wikit');
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

    it('Doesn\'t show confirmation dialog after failed put requests', async () => {
        // Ensure a failed response (axios throws on any failed response from 400 up)
        axios.put.mockImplementationOnce(() => { throw new Error() });

        const item_id = 'Q321';
        const decisions = { [item_id]:{1:{id:1, item_id ,review_status: ReviewDecision.Wikidata}}};
        const wrapper = mountWithMocks({
            data: { decisions }
        });
        const dialog = wrapper.find('.confirmation-dialog .wikit-Dialog');

        await wrapper.vm.send(item_id);

        expect(dialog.isVisible()).toBe(false);
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
        expect(wrapper.find('#item-mismatches-Q321 .wikit-Message--success').isVisible()).toBe(true);
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

        expect(wrapper.find('#item-mismatches-Q321 .wikit-Message--success').isVisible()).toBe(true);

        await wrapper.vm.send( item_id );

        expect(wrapper.vm.lastSubmitted).toEqual('Q987');
        expect(wrapper.find('#item-mismatches-Q321 .wikit-Message--success').exists()).toBe(false);
        expect(wrapper.find('#item-mismatches-Q987 .wikit-Message--success').isVisible()).toBe(true);
    });

})
