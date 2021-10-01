import { VueConstructor } from "vue";

// Bubble custom events to their parent components, to ease data flow from
// nested child components upwards. See: https://stackoverflow.com/a/54940012
export default function (Vue: VueConstructor<Vue>): void {
    Vue.prototype.$bubble = function $bubble(eventName: string, ...args: any) {
        // Emit the event on all parent components
        let component = this;
        do {
            component.$emit(eventName, ...args);
            component = component.$parent;
        } while (component);
    };
}
