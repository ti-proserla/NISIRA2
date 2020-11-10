// require('./bootstrap');
import Vue from 'vue'
import App from './App.vue'
window.axios = require('axios');

var router = require('./router.js').default;

new Vue({
  el: '#app',
  router,
  render: h => h(App)
})
