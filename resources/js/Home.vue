<template>
    <div class="row justify-content-md-center">
        <div class="col-sm-5 mb-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-center">Datos Trabajador</h4>
                    <br>
                    <h6>DNI: {{ `${ cuenta.codigo }` }}</h6>
                    <h6>Nombres: {{ `${ cuenta.nombres }` }}</h6>
                    <h6>Apellidos: {{ `${ cuenta.a_paterno } ${ cuenta.a_materno }` }}</h6>
                    <div class="text-right" v-if="cuenta.planilla=='ADM'">
                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#exampleModal">
                            Cambiar contraseña
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <h4 class="text-center">Cambiar contraseña</h4>
                            <br>
                            <div class="form-group">
                                <h6>Contraseña Nueva</h6>
                                <input v-model="data_password.password" type="password" class="form-control">
                            </div>
                            <div class="form-group">
                                <h6>Confirmar contraseña nueva</h6>
                                <input v-model="data_password.confirm_password" type="password" class="form-control">
                            </div>
                            <button @click="confirm_form()" class="btn btn-danger" :disabled="validate_password()">Guardar</button>
                            
                        </div>
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
            data_password: {
                password: '',
                confirm_password: '',
            }
        }
    },
    mounted() {
        // this.data_password.codigo=this.cuenta.codigo
    },
    computed:{
        ...mapState(['cuenta']),
    },
    methods: {
        validate_password(){
            if (
                (this.data_password.password==this.data_password.confirm_password)
                && this.data_password.password.length>3
                ) {
                return false;
            }else{
                return true;
            }
            return false;
        },
        confirm_form(){
            swal({
                title: "Confirmar cambio de contraseña",
                buttons: ["Cancelar", "OK"]
            })
            .then((value) => {
                if (value) {
                    this.change_password();
                }
            });
        },
        change_password(){
            axios.post(url_base+`/cuenta_trabajador/${ this.cuenta.codigo }?_method=PUT`,this.data_password)
            .then(response => {
                swal({
                    title: "Contraseña Actualizada",
                    icon: "success",
                });
                setTimeout(() => {
                    location.reload();
                }, 1000);
            });
        }
    },
}
</script>