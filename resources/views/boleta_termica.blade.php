<!DOCTYPE html>
<html lang="es">
<head><title>BOLETA DE REMUNERACIONES</title><link rel="stylesheet" href="{{ asset('css/boleta-termica.css') }}"></head>
<body>
    <div class="boleta">
        <table>
            <tr>
                <td class="center">
                    <img src="{{ asset('img/'.$empresa['logo']) }}" alt="" height="80px">
                </td>
            </tr>
            <tr>
                <td class="content-children-medium">
                    <h5 class="center"><b>{{ $empresa['nombre_empresa'] }}</b></h5>
                    <h6 class="center"><b>RUC: {{ $empresa['ruc'] }}</b></h6>
                </td>
            </tr>
        </table>
        <h4 class="center"><b>BOLETA DE REMUNERACIONES</b></h4>
        <h5 class="center"><b>AÑO</b> {{ $periodo->anio }} <b>{{ ($periodo->ENVIO == 'S' ? 'SEMANA': ($periodo->ENVIO == 'Q' ? 'QUINCENA': 'MES') ) }}</b> {{ $periodo->semana }} <b>PERIODO</b> {{ $periodo->FECHA_INI }} a {{ $periodo->FECHA_FIN }}</h5>
        <hr>
        <table class="table-10">
            <tr>
                <td><b>Trabajador</b></td>
                <td>{{ $datos->A_PATERNO.' '.$datos->A_MATERNO.', '.$datos->NOMBRES }}</td>
                <td><b>Sueldo</b></td>
                <td>{{ $datos->BASICO }}</td>
            </tr>
            <tr>
                <td><b>D.N.I.</b></td>
                <td>{{ $datos->CODIGO }}</td>
                <td><b>SPP</b></td>
                <td>{{ $datos->SPP }}</td>
            </tr>
            <tr>
                <td><b>Fec.Ingreso</b></td>
                <td>{{ $datos->INICIO_PLANILLA }}</td>
                <td><b>CUSPP</b></td>
                <td>{{ $datos->COD_SPP }}</td>
            </tr>
            <tr>
                <td><b>Banco</b></td>
                <td>{{ $datos->IDBANCO }}</td>
                <td><b>N° Cuenta</b></td>
                <td>{{ $datos->CUENTA_BANCO }}</td>
            </tr>
        </table>
        <table class="table table-10">
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
                        <table class="table-10">
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
        <table class="table table-10">
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
        <table class="table-9">
            <tr>
                <td class="right"><b>NETO A PAGAR {{ $totales["NETO_A_PAGAR"] }}</b></td>
            </tr>
        </table>
        <table class="center table-10">
            <tr>
                <td><img src="{{ asset('img/jpuga.bmp') }}" alt="" height="100px"></td>
            </tr>
        </table>
        @if ($horas_semana!=null&&$periodo->ENVIO == 'S')
            <table class="table table-10">
                <thead>
                    <tr>
                        <th>L</th><th>M</th><th>M</th><th>J</th><th>V</th><th>S</th><th>D</th><th>TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $horas_semana->lunes }}</td><td>{{ $horas_semana->martes }}</td><td>{{ $horas_semana->miercoles }}</td><td>{{ $horas_semana->jueves }}</td><td>{{ $horas_semana->viernes }}</td><td>{{ $horas_semana->sabado }}</td><td>{{ $horas_semana->domingo }}</td><td>{{ $horas_semana->total }}</td></tr>
                </tbody>
            </table>
        @endif
        <h6 class="center">{{ $empresa['direccion'] }}</h6>
    </div>
</body>
</html>
