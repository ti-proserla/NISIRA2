<template>
    <v-card>
        <v-card-title>Seguimiento Documentario</v-card-title>              
        <v-card-text>
            <v-row>
                <!-- <v-col v-if="consulta!=null" cols="12" sm=6 lg="1">
                    <v-text-field
                        label="Serie:"
                        v-model="serie"
                        type="text"
                        >
                    </v-text-field>
                </v-col> -->
                <v-col v-if="consulta!=null" cols="12" sm=6 lg="2">
                    <v-text-field
                        label="Número Recepcion:"
                        v-model="numero"
                        type="text"
                        >
                    </v-text-field>
                </v-col>
                <v-col cols="12" sm=4 lg="8">
                    <v-btn color="info" @click="consultar">
                        <i class="fas fa-search"></i> BUSCAR
                    </v-btn>
                </v-col>
                <v-col cols="12" sm=4 lg="2" class="text-right">
                    <v-btn color="error" @click="save" v-if="cuenta.usuario=='GSEMINARIO'||cuenta.usuario=='ADMINISTRADOR'">
                        <i class="far fa-save"></i> Asignar
                    </v-btn>
                </v-col>
                <v-col cols="12">
                    <h4 v-if="recepcion!=null">{{ `${recepcion.serie}-${recepcion.numero}` }}</h4>
                    <v-data-table
                        v-if="recepcion!=null"
                        item-key="item"
                        disable-pagination
                        hide-default-footer
                        show-select
                        dense
                        v-model="select"
                        color="red lighten-2"
                        class="elevation-1"
                        :headers="header"
                        :items="recepcion.detalles"
                        @toggle-select-all="selectAllToggle"
                        >
                        <template 
                            v-slot:item.data-table-select="{ item, isSelected, select }">
                            <v-simple-checkbox
                                v-if="cuenta.usuario=='GSEMINARIO'||cuenta.usuario=='ADMINISTRADOR'"
                                :value="isSelected"
                                :disabled="item.con_ccosto=='Si'"
                                :readonly="item.con_ccosto=='Si'"
                                @input="select($event)"
                            ></v-simple-checkbox>
                        </template>
                    </v-data-table>
                </v-col>              
            </v-row>
        </v-card-text>
    </v-card>

    
</template>
<script>
import { mapState,mapMutations } from 'vuex'

export default {
    data() {
        return {
            select: [],
            serie:'',
            numero: '',
            consulta: {
                idclieprov: '',
            },
            recepcion: null,
            header: [
                { text: 'item', value: 'item'},
                { text: 'idclieprov', value: 'idclieprov' },
                { text: 'iddocumento', value: 'iddocumento' },
                { text: 'serie', value: 'serie' },
                { text: 'numero', value: 'numero' },
                { text: 'razon_social', value: 'razon_social' },
                { text: 'Moneda', value: 'moneda'},

            ],
            costo_asignado:{
                item: '',
                idrecepcion: '' 
            },



            descriptionLimit: 60,
            entries: [],
            isLoading: false,
            model: null,
            search: null,
        }
    },
    computed: {
        ...mapState(['cuenta','rutas'])
    },
    methods: {
        selectAllToggle(event){
            console.log(event);
            if (event.value) {
                this.select=[];
                for (let i = 0; i < this.recepcion.detalles.length; i++) {
                    const detalle = this.recepcion.detalles[i];
                    console.log(detalle);
                    if (detalle.con_ccosto=='No') {
                        this.select.push(detalle);
                    }
                }
            } else {
                this.select=[];
                // alert('deselected all')
            }
        },
        consultar(){
            axios.get(url_base+`/SeguimientoDocumentario/Recepcion?empresa=${this.cuenta.empresa}&serie=${this.serie}&numero=${this.numero}`)
            .then(response => {
                var data=response.data;
                this.recepcion=data;
            });
            this.select=[];
        },
        save(idrecepcion,item){
            var t=this;
            swal({ title: "¿Desea Asignar C. Costo?", buttons: ['Cancelar',"Si"]})
            .then((res) => {
                if (res) {
                    axios.post(url_base+`/CostoAsignado/recepcion`,{
                        empresa: t.cuenta.empresa,
                        items: t.select

                    }).then(response => {
                        var res=response.data;
                        switch (res.status) {
                            case 'OK':
                                swal(res.message, { 
                                    icon: "success", 
                                    timer: 2000, 
                                    buttons: false
                                });
                                this.select=[];
                                t.consultar();
                                break;
                        
                            default:
                                break;
                        }
                    });
                }
            });
        }
    }
}
</script>