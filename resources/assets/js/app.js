
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.scroll = scroll;
window.Vue = require('vue');

Vue.component('TicketCheckout', require('./components/TicketCheckout.vue'));

const app = window.app = new Vue({
    el: '#app'
});
