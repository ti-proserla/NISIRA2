<template>
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-sm-5" v-if="pagos.length==0">
                <div class="card">
                    <div class="card-body">
                        <div class="row justify-content-md-center">
                            <div class="col-12 form-group">
                                <label for="">Código Trabajador</label>
                                <input type="text" v-model="codigo" class="form-control">
                            </div>
                            <div class="col-12 form-group">
                                <label for="">Empresa:</label>
                                <select v-model="empresa" class="form-control">
                                    <option value="01">PROSERLA</option>
                                    <option value="02">JAYANCA FRUITS</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <button @click="consultar()" class="btn btn-primary form-control">Consultar</button>
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
    </div>
</template>
<script>
export default {
    data() {
        return {
            codigo: '',
            empresa: '01',
            pagos: [],
        }
    },
    computed: {
        url_boleta() {
            return url_base+'/bp/boletas/show?codigo='
        }
    },
    methods: {
        consultar(){
            axios.post(url_base+'/bp/boletas',{
                codigo: this.codigo,
                empresa: this.empresa
            })
            .then(response => {
                this.pagos=response.data;
            });
        },
        limpiar(){
            this.codigo='';
            this.pagos=[];
        }
    },
}
</script>