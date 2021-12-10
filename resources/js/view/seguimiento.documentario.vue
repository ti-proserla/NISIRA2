<template>
    <v-card>
        <v-card-title>Seguimiento Documentario</v-card-title>              
        <v-card-text>
            <v-row>
                <!-- <v-col cols="12" sm=6 lg="3">
                    <v-text-field
                        label="Desde:"
                        v-model="consulta.idclieprov"
                        type="date">
                    </v-text-field>
                </v-col> -->
                <v-col cols="12" sm=6 lg="3">
                    <v-text-field
                        label="Código:"
                        v-model="consulta.idclieprov"
                        type="text"
                        @change="buscarProveedor"
                        >
                    </v-text-field>
                </v-col>

                <v-col cols="12" sm=6 lg="3">
                    <v-text-field
                        label="Razón Social:"
                        v-model="consulta.razon_social"
                        readonly>
                    </v-text-field>
                </v-col>
                <!-- <v-col cols="12" sm=6 lg="3">
                    <v-text-field
                        label="Hasta:"
                        v-model="consulta.hasta"
                        type="date">
                    </v-text-field>
                </v-col> -->
                <!-- <v-col cols="12" sm=8 lg="4">
                    <v-select
                        @change="buscar"
                            outlined
                            dense
                            v-model="consulta.cliente_id"
                            label="Productor:"
                            :items="clientes"
                            item-text="descripcion"
                            item-value="id"
                            >
                            </v-select>
                </v-col> -->
                <v-col cols="12" sm=4 lg="2">
                    <v-btn color="info" @click="consultar">
                        <i class="fas fa-search"></i> BUSCAR
                    </v-btn>
                    <!-- <v-btn color="success" :href="excel">
                        <i class="fas fa-file-excel"></i>
                    </v-btn> -->
                </v-col>
                <v-col cols="12">
                    <v-data-table
                        class="elevation-1"
                        :headers="header"
                        :items="table"
                        >
                    </v-data-table>
                </v-col>
            </v-row>
        </v-card-text>
    </v-card>

    
</template>
<script>
export default {
    data() {
        return {
            consulta: {
                idclieprov: '',
            },
            table: [],
            header: [
                { text: 'Código', value: 'idclieprov' },
                { text: 'Razón Social', value: 'razon_social' },
                { text: 'Documento', value: 'documento' },
                { text: 'Fecha Documento', value: 'fecha_documento'},
                { text: 'Importe', value: 'importe' },
                { text: 'ID Recepción', value: 'idrecepcion' },
                { text: 'Item', value: 'item' },
                { text: 'Recepción', value: 'fecha_recepcion' },
                { text: 'Provisión', value: 'fecha_provision' },
                { text: 'Tesoreria', value: 'tesoreria' },
                { text: 'Fecha Tesoreria', value: 'fecha_tesoreria' },
                { text: 'Importe CTA', value: 'importe_cta' },
            ],
        }
    },
    methods: {
        buscarProveedor(){
            axios.get(url_base+`/cliente-proveedor/${this.consulta.idclieprov}`)
            .then(response => {
                this.consulta=response.data;
            });
        },
        consultar(){
            axios.get(url_base+'/SeguimientoDocumentario', {
                params: this.consulta
            })
            .then(response => {
                this.table=response.data;
            });
        },
    }
}
</script>