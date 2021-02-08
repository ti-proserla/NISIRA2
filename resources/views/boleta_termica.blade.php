<!DOCTYPE html>
<html lang="es">
<head>
    <title>BOLETA DE REMUNERACIONES</title>
    <link rel="stylesheet" href="{{ asset('css/boleta-termica.css') }}">
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
        <table class="table-8">
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
        <table class="table table-8">
            <thead>
                <tr>
                    <th class="content-children-medium">REMUNERACIONES</th>
                    <th class="content-children-medium">RETENCIONES AL TRABAJADOR</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
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
                        <table class="table-8">
                            @foreach ($descuentos as $item)
                            <tr>
                                <td> {{ $item->DESCR_CORTA }} </td>
                                <td class="right"> {{ $item->CALCULO }} </td>
                            </tr>    
                            @endforeach
                        </table>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td class="right"><b>{{ $totales["TOT_INGRESOS"] }}</b></td>
                    <td class="right"><b>{{ $totales["TOT_DESCUENTOS"] }}</b></td>
                </tr>
            </tfoot>
        </table>
        <table class="table table-8">
            <thead>
                <tr>
                    <th class="content-children-medium">CONTRIBUCIONES DEL EMPLEADOR</th>
                    <th class="content-children-medium">TIEMPOS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
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
                    <td class="right"><b>{{ isset($totales["TOT_APORT_GRAL"]) ? $totales["TOT_APORT_GRAL"] : 0.00 }}</b></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        <table class="table-8">
            <tr>
                <td><b>NETO A PAGAR</b></td>
                <td class="right"><b>{{ $totales["NETO_A_PAGAR"] }}</b></td>
            </tr>
        </table>
        <table class="center table-8">
            <tr>
                <td>
                    <img src="{{ asset('img/jpuga.bmp') }}" alt="" height="100px">
                </td>
            </tr>
        </table>
        @if ($horas_semana!=null&&$periodo->ENVIO == 'S')
            <table class="table table-8">
                <thead>
                    <tr>
                        <th>°</th>
                        <th>L</th>
                        <th>M</th>
                        <th>M</th>
                        <th>J</th>
                        <th>V</th>
                        <th>S</th>
                        <th>D</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Horas</td>
                        <td>{{ $horas_semana->lunes }}</td>
                        <td>{{ $horas_semana->martes }}</td>
                        <td>{{ $horas_semana->miercoles }}</td>
                        <td>{{ $horas_semana->jueves }}</td>
                        <td>{{ $horas_semana->viernes }}</td>
                        <td>{{ $horas_semana->sabado }}</td>
                        <td>{{ $horas_semana->domingo }}</td>
                    </tr>
                </tbody>
            </table>
        @endif
    </div>
</body>
</html>