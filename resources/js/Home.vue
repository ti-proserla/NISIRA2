<template>
    <div class="row justify-content-md-center">
        <div class="col-sm-5">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-center">Datos Trabajador</h4>
                    <br>
                    <h6>DNI: {{ `${ cuenta.codigo }` }}</h6>
                    <h6>Nombres: {{ `${ cuenta.nombres }` }}</h6>
                    <h6>Apellidos: {{ `${ cuenta.a_paterno } ${ cuenta.a_materno }` }}</h6>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { mapState,mapMutations } from 'vuex'
export default {
    computed: {
        ...mapState(['cuenta']),
    },
}
</script>