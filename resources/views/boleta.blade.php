<!DOCTYPE html>
<html lang="es">
<head>
    <title>BOLETA DE REMUNERACIONES</title>
    <style>
        table{
            width: 100%;
            /* display: flex; */
            /* grid-gap: 0; */
            /* grid-template-columns: repeat(4, 1fr); */
        }
        .content-children{
            width: 25%;
        }
        .center{
            text-align: center
        }
        .right{
            text-align: right;
        }
        table{
            /* font-size: 12px; */
            border-collapse: collapse;
        }
        .table-12{
            font-size: 12px;
        }
        .table-14{
            font-size: 14px;
        }
        .table>tbody>tr>td{
            vertical-align: top
        }
        .table>tbody>tr>td,.table>thead>tr>th,.table>tfoot>tr>td{
            padding: 5px;
            border: 1px solid black;
        }
        h1,h2,h3,h4,h5,h6{
            width: 100%;
            margin: 0;
            font-weight: 400;
        }
        .content-children-medium{
            width: 50%;
        }

        /* .content-children table{
            width: 100%
        } */
    </style>
</head>
<body>
    <table>
        <tr>
            <td class="content-children">
                <img src="{{ public_path('img/logotipo.png') }}" alt="" width="110px">
            </td>
            <td class="content-children-medium">
                <h5 class="center">{{ $empresa['nombre_empresa'] }}</h5>
                <h6 class="center">{{ $empresa['direccion'] }}</h6>
                <h5 class="center">RUC: {{ $empresa['ruc'] }}</h5>
            </td>
            <td class="content-children">

            </td>
        </tr>
    </table>
    <br>
    <br>
    <h3 class="center">BOLETA DE REMUNERACIONES</h3>
    <h5 class="center"> {{ ($periodo->ENVIO == 'S' ? 'SEMANA': ($periodo->ENVIO == 'Q' ? 'QUINCENA': 'MES') ) }} {{ $periodo->semana }} </h5>
    <h5 class="center"> {{ $periodo->FECHA_INI }} a {{ $periodo->FECHA_FIN }} </h5>
    <br>
    <table class="table-14">
        <tr>
            <td>Trabajador:</td>
            <td>{{ $datos->A_PATERNO.' '.$datos->A_MATERNO.', '.$datos->NOMBRES }}</td>
            <td>Sueldo:</td>
            <td>{{ $datos->BASICO }}</td>
        </tr>
        <tr>
            <td>Fec.Ingreso:</td>
            <td>{{ $datos->INICIO_PLANILLA }}</td>
            <td>SPP:</td>
            <td>{{ $datos->SPP }}</td>
        </tr>
        <tr>
            <td>D.N.I.:</td>
            <td>{{ $datos->CODIGO }}</td>
            <td>CUSPP:</td>
            <td>{{ $datos->COD_SPP }}</td>
        </tr>
    </table>
    <br>
        <table class="content-table table table-12">
            <thead>
                <tr>
                    <th class="content-children center">
                        REMUNERACIONES
                    </th>
                    <th class="content-children center">
                        RETENCIONES AL TRABAJADOR
                    </th>
                    <th class="content-children center">
                        CONTRIBUCIONES DEL EMPLEADOR
                    </th>
                    <th class="content-children center">
                        TIEMPOS
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td height="300px">
                        <table>
                            @foreach ($ingresos as $item)
                                <tr>
                                    <td> {{ $item->DESCR_CORTA }} </td>
                                    <td class="right"> {{ $item->CALCULO }} </td>
                                </tr>    
                            @endforeach
                        </table>
                    </td>
                    <td>
                        <table>
                            @foreach ($descuentos as $item)
                                <tr>
                                    <td> {{ $item->DESCR_CORTA }} </td>
                                    <td class="right"> {{ $item->CALCULO }} </td>
                                </tr>    
                            @endforeach
                        </table>
                    </td>
                    <td>
                        <table>
                            @foreach ($seguro as $item)
                                <tr>
                                    <td> {{ $item->DESCR_CORTA }} </td>
                                    <td class="right"> {{ $item->CALCULO }} </td>
                                </tr>    
                            @endforeach
                        </table>
                    </td>
                    <td>
                        <table>
                            @foreach ($tiempos as $item)
                                <tr>
                                    <td> {{ $item->DESCR_CORTA }} </td>
                                    <td class="right">{{ $item->CALCULO }}</td>
                                </tr>    
                            @endforeach
                        </table>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td>
                        <h4><b>TOT.INGRESOS</b></h4>
                        <h4 class="right"><b>{{ $totales["TOT_INGRESOS"] }}</b></h4>
                    </td>
                    <td>
                        <h4><b>TOT.RETENCIONES</b></h4>
                        <h4 class="right"><b>{{ $totales["TOT_DESCUENTOS"] }}</b></h4>
                    </td>
                    <td>
                        <h4><b>TOT.APORTACION</b></h4>
                        <h4 class="right"><b>{{ isset($totales["TOT_APORT_GRAL"]) ? $totales["TOT_APORT_GRAL"] : 0.00 }}</b></h4>
                    </td>
                    <td>
                        <h3><b>NETO A PAGAR</b></h3>
                        <h3 class="right"><b>{{ $totales["NETO_A_PAGAR"] }}</b></h3>
                    </td>
                </tr>
            </tfoot>
        </table>

        <br>
        <br>
        <br>
        <table class="center table-14">
            <tr>
                <td class="content-children-medium">
                    <img src="{{ public_path('img/jpuga.bmp') }}" alt="" height="150px">
                </td>
                <td class="content-children-medium">
                </td>
            </tr>
            <tr>
                <td>
                    <h4>_____________________________</h4>
                    <h4>FIRMA DEL EMPLEADOR</h4>
                </td>
                <td>
                    <h4>_____________________________</h4>
                    <h4>FIRMA DEL EMPLEADO</h4>
                </td>
            </tr>
        </table>
    {{-- {{ $ingresos }} --}}
</body>
</html>