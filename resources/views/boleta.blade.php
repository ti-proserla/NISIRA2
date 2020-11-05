<!DOCTYPE html>
<html lang="es">
<head>
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
        .table{
            font-size: 12px;
            border-collapse: collapse;
        }
        .table>tbody>tr>td{
            vertical-align: top
        }
        .table>tbody>tr>td,.table>thead>tr>th{
            padding: 5px;
            border: 1px solid black;
        }
        h1,h2,h3,h4,h5,h6{
            width: 100%;
        }

        /* .content-children table{
            width: 100%
        } */
    </style>
</head>
<body>
    <table>
        <tr>
            <td>Sueldo:</td>
            <td>{{ $sueldo }}</td>
        </tr>
    </table>
    <h5>sueldo</h5>
        <h5 class="center"> {{ $periodo->FECHA_INI }} a {{ $periodo->FECHA_FIN }} </h5>
        <table class="content-table table">
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
        </table>
    {{-- {{ $ingresos }} --}}
</body>
</html>