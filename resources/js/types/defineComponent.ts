import Vue from 'vue';

// This trivial function enables us to properly annotate components without
// having to use the redundant Vue.extend to satisfy typescript requirements.
// Mostly used as a workaround for not being able to use `Vue.extend` with
// inertia default layouts.
// See: https://github.com/inertiajs/inertia/issues/335#issuecomment-819389617
const defineComponent: typeof Vue.extend = (x: any) => x

export default defineComponent;
