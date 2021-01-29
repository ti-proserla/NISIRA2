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
            margin-bottom: 4px
        }
        .content-children-medium{
            width: 75%;
        }

        /* .content-children table{
            width: 100%
        } */
        .boleta{
            width: 332px
        }
        .pl-3{
            padding-left: 1rem;
        }
    </style>
</head>
<body>
    <div class="boleta">
        <table>
            <tr>
                <td class="center">
                    {{-- <img src="{{ asset('img/logotipo.png') }}" alt="" width="110px"> --}}
                </td>
            </tr>
            <tr>
                <td class="content-children-medium">
                    <h5 class="center"><b>{{ $empresa['nombre_empresa'] }}</b></h5>
                    <h6 class="center">RUC: {{ $empresa['ruc'] }}</h6>
                    <h6 class="center">{{ $empresa['direccion'] }}</h6>
                </td>
            </tr>
        </table>
        
        <hr>
        <h5 class="center"><b>BOLETA DE REMUNERACIONES</b></h5>
        <h5 class="center"> {{ ($periodo->ENVIO == 'S' ? 'SEMANA': ($periodo->ENVIO == 'Q' ? 'QUINCENA': 'MES') ) }} {{ $periodo->semana }}  ( {{ $periodo->FECHA_INI }} a {{ $periodo->FECHA_FIN }} ) </h5>
        <br>
        <table class="table-12">
            <tr>
                <td>Trabajador:</td>
                <td>{{ $datos->A_PATERNO.' '.$datos->A_MATERNO.', '.$datos->NOMBRES }}</td>
            </tr>
            <tr>
                <td>Sueldo:</td>
                <td>{{ $datos->BASICO }}</td>
            </tr>
            <tr>
                <td>Fec.Ingreso:</td>
                <td>{{ $datos->INICIO_PLANILLA }}</td>
            </tr>
            <tr>
                <td>SPP:</td>
                <td>{{ $datos->SPP }}</td>
            </tr>
            <tr>
                <td>D.N.I.:</td>
                <td>{{ $datos->CODIGO }}</td>
            </tr>
            <tr>        
                <td>CUSPP:</td>
                <td>{{ $datos->COD_SPP }}</td>
            </tr>
        </table>
        <hr>
        <table class="table-12">
            <tr>
                <td>REMUNERACIONES</td>
                <td class="right"><b>{{ $totales["TOT_INGRESOS"] }}</b></td>
            </tr>
        </table>
        <hr>
        <table class="table-12">
            @foreach ($ingresos as $item)
                <tr>
                    <td> {{ $item->DESCR_CORTA }} </td>
                    <td class="right"> {{ $item->CALCULO }} </td>
                </tr>    
            @endforeach
        </table>

        <hr>
        <table class="table-12">
            <tr>
                <td>RETENCIONES AL TRABAJADOR</td>
                <td class="right"><b>{{ $totales["TOT_DESCUENTOS"] }}</b></td>
            </tr>
        </table>
        <hr>
        <table class="table-12">
            @foreach ($descuentos as $item)
                <tr>
                    <td> {{ $item->DESCR_CORTA }} </td>
                    <td class="right"> {{ $item->CALCULO }} </td>
                </tr>    
            @endforeach
        </table>

        <hr>
        <table class="table-12">
            <tr>
                <td>CONTRIBUCIONES DEL EMPLEADOR</td>
                <td class="right"><b>{{ isset($totales["TOT_APORT_GRAL"]) ? $totales["TOT_APORT_GRAL"] : 0.00 }}</b></td>
            </tr>
        </table>
        <hr>
        <table class="table-12">
            @foreach ($seguro as $item)
                <tr>
                    <td> {{ $item->DESCR_CORTA }} </td>
                    <td class="right"> {{ $item->CALCULO }} </td>
                </tr>    
            @endforeach
        </table>

        <hr>
        <table class="table-12">
            <tr>
                <td>TIEMPOS</td>
                {{-- <td class="right"><b>{{ $totales["TOT_INGRESOS"] }}</b></td> --}}
            </tr>
        </table>
        <hr>
        <table class="table-12">
            @foreach ($tiempos as $item)
                <tr>
                    <td> {{ $item->DESCR_CORTA }} </td>
                    <td class="right">{{ $item->CALCULO }}</td>
                </tr>    
            @endforeach
        </table>
        <hr>
        <br>
        <table class="table-12">
            <tr>
                <td><b>NETO A PAGAR</b></td>
                <td class="right"><b>{{ $totales["NETO_A_PAGAR"] }}</b></td>
            </tr>
        </table>
        <br>
        <br>
        <table class="center table-12">
            <tr>
                <td class="content-children-medium">
                    <img src="{{ asset('img/jpuga.bmp') }}" alt="" height="120px">
                </td>
            </tr>
            <tr>
                <td>
                    <h4>FIRMA DEL EMPLEADOR</h4>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>