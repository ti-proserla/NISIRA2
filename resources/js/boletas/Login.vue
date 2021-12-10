<template>
    <div class="row justify-content-md-center">
        <div class="col-lg-5" v-if="pagos.length==0">
            <div class="card">
                <ul class="nav nav-tabs">
                    <li class="nav-item tag">
                        <a @click="login_cuenta.planilla='GEN'" class="nav-link" :class="(login_cuenta.planilla=='GEN')?'active':''">General</a>
                    </li>
                    <li class="nav-item tag">
                        <a @click="login_cuenta.planilla='ADM'" class="nav-link" :class="(login_cuenta.planilla=='ADM')?'active':''">Administrativos</a>
                    </li>
                </ul>
                <div class="card-body" v-if="login_cuenta.planilla=='GEN'">
                    <div class="row justify-content-md-center">
                        <div class="col-12 form-group">
                            <label for="">Empresa:</label>
                            <select v-model="login_cuenta.empresa" class="form-control">
                                <option value="01">PROSERLA</option>
                                <option value="02">JAYANCA FRUITS</option>
                            </select>
                        </div>
                        <div class="col-12 form-group">
                            <label for="">Código Trabajador</label>
                            <input v-model="login_cuenta.codigo" type="text" class="form-control">
                        </div>
                        <div class="col-12 form-group">
                            <label for="">Fecha Nacimiento</label>
                            <input v-model="login_cuenta.fecha_nacimiento" type="date" class="form-control">
                        </div>
                        <div class="col-12">
                            <button @click="consultar()" class="btn btn-primary form-control">Ingresar</button>
                            <!-- <router-link to="/registrar">Registrar</router-link> -->
                        </div>
                    </div>
                </div>
                <div class="card-body" v-if="login_cuenta.planilla=='ADM'">
                    <div class="row justify-content-md-center">
                        <div class="col-12 form-group">
                            <label for="">Empresa:</label>
                            <select v-model="login_cuenta.empresa" class="form-control">
                                <option value="01">PROSERLA</option>
                                <option value="02">JAYANCA FRUITS</option>
                            </select>
                        </div>
                        <div class="col-12 form-group">
                            <label for="">Código Trabajador</label>
                            <input v-model="login_cuenta.codigo" type="text" class="form-control">
                        </div>
                        <div class="col-12 form-group">
                            <label for="">Contraseña</label>
                            <input v-model="login_cuenta.password" type="password" class="form-control">
                        </div>
                        <div class="col-12">
                            <button @click="consultar()" class="btn btn-primary form-control">Ingresar</button>
                            <!-- <router-link to="/registrar">Registrar</router-link> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-7" v-if="pagos.length>0">
            <button class="btn btn-danger" @click="limpiar()">Salir</button>
            <h5 class="text-center mb-3">Historial de Boletas</h5>
            <div v-for="pago in pagos" class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <h6>{{ pago.anio }} - 
                                <label v-if="pago.envio=='S'">SEMANA</label> 
                                <label v-else-if="pago.envio=='Q'">QUINCENA</label> 
                                <label v-else>MES</label> {{ pago.semana }}
                            </h6>
                        </div>
                        <div class="col-sm-3">
                            <h6 class="text-right">S/. {{ pago.monto }}</h6>
                        </div>
                        <div class="col-sm-3">
                            <a class="btn btn-sm btn-info" :href="url_boleta+pago.movimientos+'&empresa='+empresa">Descargar</a>
                        </div>
                    </div>
                    <!-- <button>Enviar al Correo</button> -->
                </div>
            </div>
        </div>
    </div>
</template>
<style >
    .tag{
        width: 50%;
        text-align: center;
    }
</style>
<script>
import { mapState,mapMutations } from 'vuex'
export default {
    name: "Login",
    data() {
        return {
            login_cuenta: {
                codigo: '',
                empresa: '01',
                fecha_nacimiento: null,
                planilla: 'GEN'
            },
            pagos: [],
        }
    },
    computed: {
        ...mapState(['cuenta']),
        url_boleta() {
            return url_base+'/bp/boletas/show?codigo='
        }
    },
    methods: {
        consultar(){
            axios.post(url_base+'/cuenta_trabajador',this.login_cuenta)
            .then(response => {
                var res=response.data;
                switch (res.status) {
                    case 'OK':
                        this.$store.commit('auth_success', res.data);                
                        break;
                    case 'ERROR':
                        swal({
                            title: res.message,
                            icon: "error",
                        });
                        // alert(res.message);
                        break;
                    default:
                        break;
                }
            });
        }
    }
}
</script>