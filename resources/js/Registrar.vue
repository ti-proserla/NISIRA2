<template>
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-sm-5">
                <div class="card">
                    <div class="card-body">
                        <div class="row justify-content-md-center">
                            <div class="col-12 form-group">
                                <label for="">Empresa:</label>
                                <select v-model="empresa" class="form-control">
                                    <option value="01">PROSERLA</option>
                                    <option value="02">JAYANCA FRUITS</option>
                                </select>
                            </div>
                            <div class="col-12 form-group">
                                <label for="">Código Trabajador</label>
                                <input type="text" v-model="codigo" class="form-control">
                            </div>
                            <div class="col-12 form-group">
                                <label for="">Cod. Verificador</label>
                                <input type="text" v-model="verificador" class="form-control">
                            </div>
                            <div class="col-12 form-group">
                                <label for="">Contraseña</label>
                                <input type="text" v-model="password" class="form-control">
                            </div>
                            <div class="col-12">
                                <button @click="consultar()" class="btn btn-primary form-control">Consultar</button>
                                <router-link to="/registrar">Registrar</router-link>
                            </div>
                        </div>
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
            cuenta:{
                codigo: '',
                verificador: '',
                password: '',
                empresa: '01'
            }
        }
    },
    methods: {
        consultar(){
            axios.post(url_base+'/cuenta_trabajador',this.cuenta)
            .then(response => {
                console-log
            });
        },
        limpiar(){
            this.codigo='';
        }
    },
}
</script>