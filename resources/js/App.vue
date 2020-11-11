<template>
  <div class="">  
    <nav class="navbar navbar-expand-md navbar-light bg-light">
      <a class="navbar-brand" href="#">
        <img src="/img/logotipo.png" alt="" width="100px">
      </a>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item active">
            <router-link to="/boletas" class="nav-link">Boletas de Pago</router-link>
            <!-- <a class="nav-link" href="#">Boletas de Pago<span class="sr-only">(current)</span></a> -->
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Link</a>
          </li>
        </ul>
      </div>
    </nav>
    <Login v-if="cuenta==null"></Login>
    <router-view v-else></router-view>
  </div>
</template>
<script>
import { mapState,mapMutations } from 'vuex'
import Login from './Login.vue'
export default {
  components:{
    Login
  },
    computed: {
      ...mapState(['cuenta']),
    }  
}
</script>