<template>
  <div class="">  
    <nav class="navbar navbar-expand-md navbar-light bg-light">
      <router-link to="/" class="navbar-brand">
        <img src="/img/logotipo.png" alt="" width="100px">
      </router-link>
      <button class="navbar-toggler" type="button" @click="sidebar_open()">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div v-if="cuenta!=null" class="collapse navbar-collapse" :class="(sidebar==true ? 'show' : '' )" id="navbarSupportedContent">
        <ul class="navbar-nav mr-right">
          <li class="nav-item">
            <router-link to="/" @click.native="cerrar()" class="nav-link">Datos</router-link>
          </li>
          <li class="nav-item">
            <router-link to="/boletas" @click.native="cerrar()" class="nav-link">Boletas de Pago</router-link>
          </li>
        </ul>
        <button class="btn btn-danger btn-sm" @click="cerrar();salir()">Salir</button>
      </div>
    </nav>
    <div class="container py-3">
      <Login v-if="cuenta==null"></Login>
      <router-view v-else></router-view>
    </div>
  </div>
</template>
<style>
  .mr-right{
    margin-left: auto;
  }
  .navbar-collapse.show{
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 100;
    background-color: #ffffff;
    padding: 10px;
  }
</style>
<script>
import { mapState,mapMutations } from 'vuex'
import Login from './Login.vue'
export default {
  // data() {
  //   return {
  //     open: false 
  //   }
  // },
  components:{
    Login
  },
  computed: {
    ...mapState(['cuenta','sidebar']),
  },
  methods: {
    salir(){
      this.$store.commit('auth_close');
    },
    cerrar(){
      this.$store.commit('sidebar_close');
    },
    sidebar_open(){
      this.$store.commit('sidebar_open');
    }
  },  
}
</script>