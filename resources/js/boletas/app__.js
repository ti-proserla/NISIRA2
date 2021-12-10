// require('./bootstrap');
import swal from 'sweetalert'
import Vue from 'vue'
import App from './App.vue'
window.axios = require('axios');

var router = require('./router.js').default;
import Vuex from 'vuex'
Vue.use(Vuex)

window.store=new Vuex.Store({
  state: {
      cuenta: JSON.parse(localStorage.getItem('cuenta_personal'))||null,
      sidebar: false
  },
  mutations: {        
    auth_success(state,cuenta){
      state.cuenta=cuenta;
      localStorage.setItem('cuenta_personal',JSON.stringify(cuenta));
    },
    auth_close(state){
      state.cuenta=null;
      localStorage.removeItem('cuenta_personal');
    },
    sidebar_close(state){
      state.sidebar=false;
      console.log('close');
    },
    sidebar_open(state){
      state.sidebar=true;
    }
  }
});

new Vue({
  el: '#app',
  router,
  store,
  render: h => h(App)
})
