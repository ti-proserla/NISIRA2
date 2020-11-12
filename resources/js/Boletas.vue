<template>
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-sm-7" v-if="pagos.length>0">
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
                                <a class="btn btn-sm btn-info" :href="url_boleta(pago.movimientos,cuenta.empresa)">Descargar</a>
                            </div>
                        </div>
                        <!-- <button>Enviar al Correo</button> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { mapState,mapMutations } from 'vuex'
export default {
    data() {
        return {
            login_cuenta: {
                codigo: '',
                empresa: '01',
                fecha_nacimiento: null
                },
            pagos: [],
        }
    },
    mounted() {
        axios.post(url_base+'/bp/boletas',this.cuenta)
        .then(response => {
            this.pagos=response.data;
        });
    },
    computed: {
        ...mapState(['cuenta']),
    },
    methods: {
        url_boleta(codigo,empresa) {
            return url_base+'/bp/boletas/show?codigo='+codigo+'&empresa='+empresa;
        },
        consultar(){
            axios.post(url_base+'/bp/boletas',this.cuenta)
            .then(response => {
                this.pagos=response.data;
            });
        },
    },
}
</script>