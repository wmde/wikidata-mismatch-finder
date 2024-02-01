import bubble from '@/lib/bubble.ts';

describe('bubble plugin', () => {
    it('mutates passed constructor to add the `$bubble` property', () => {
        class MockVue {}
        MockVue.config = {globalProperties:{}};
        bubble(MockVue);

        expect(MockVue.config.globalProperties.$bubble).toBeTruthy();
    });

    describe('$bubble()', () => {
        it('calls $emit on instance of passed constructor', () => {
            class MockVue {};
            MockVue.config = {globalProperties:{}};
            MockVue.prototype.$emit = jest.fn();

            bubble(MockVue);
            MockVue.prototype.$bubble = MockVue.config.globalProperties.$bubble;

            const instance = new MockVue();
            const args = ['test', [1,2,3]]

            instance.$bubble(...args);

            expect(instance.$emit).toHaveBeenCalledTimes(1);
            expect(instance.$emit).toHaveBeenCalledWith(...args);
        });

        it('calls $emit on instance of parent of passed constructor', () => {
            class MockVue {
                constructor(emit, parent){
                    this.$parent = parent;
                    this.$emit = emit;
                    this.$bubble = MockVue.config.globalProperties.$bubble;
                }
            };

            MockVue.config = {globalProperties:{}};
            bubble(MockVue);

            const grandparent = new MockVue(jest.fn());
            const parent = new MockVue(jest.fn(), grandparent);
            const instance = new MockVue(jest.fn(), parent);
            const args = ['test', [1,2,3]]

            instance.$bubble(...args);

            expect(parent.$emit).toHaveBeenCalledTimes(1);
            expect(parent.$emit).toHaveBeenCalledWith(...args);

            expect(grandparent.$emit).toHaveBeenCalledTimes(1);
            expect(grandparent.$emit).toHaveBeenCalledWith(...args);
        });
    });
});
