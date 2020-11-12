<template>
  <div class="">  
    <nav class="navbar navbar-expand-md navbar-light bg-light">
      <router-link to="/" class="navbar-brand">
        <img src="/img/logotipo.png" alt="" width="100px">
      </router-link>
      <div v-if="cuenta!=null" class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-right">
          <li class="nav-item">
            <router-link to="/" class="nav-link">Datos</router-link>
          </li>
          <li class="nav-item">
            <router-link to="/boletas" class="nav-link">Boletas de Pago</router-link>
          </li>
        </ul>
        <button class="btn btn-danger btn-sm" @click="salir()">Salir</button>
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
</style>
<script>
import { mapState,mapMutations } from 'vuex'
import Login from './Login.vue'
export default {
  components:{
    Login
  },
  computed: {
    ...mapState(['cuenta']),
  },
  methods: {
    salir(){
      this.$store.commit('auth_close');
    }
  },  
}
</script>