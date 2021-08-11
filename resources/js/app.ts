// import './bootstrap';
import Vue from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue';

const app = document.getElementById('app');

createInertiaApp({
  resolve: name => require(`./Pages/${name}`),
  setup({ el, app, props }) {
    new Vue({
      render: h => h(app, props),
    }).$mount(el)
  },
})