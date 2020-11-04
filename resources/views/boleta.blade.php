<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        .content-table{
            display: grid;
            grid-gap: 0;
            grid-template-columns: repeat(4, 1fr);
        }
        .content-children{
            border: 1px solid #000;
            height: 100%;
            padding: 0 5px;
            display: flex
        }
        .center{
            text-align: center
        }
        .right{
            text-align: right;
        }
        .content-children table{
            width: 100%
        }
    </style>
</head>
<body>
    <h4> {{ $periodo->FECHA_INI }} a {{ $periodo->FECHA_FIN }} </h4>
    <div class="content-table">
        <div class="content-children center">
            REMUNERACIONES
        </div>
        <div class="content-children center">
            RETENCIONES AL TRABAJADOR
        </div>
        <div class="content-children center">
            CONTRIBUCIONES DEL EMPLEADOR
        </div>
        <div class="content-children center">
            TIEMPOS
        </div>
        <div class="content-children">
            <table>
                @foreach ($ingresos as $item)
                    <tr>
                        <td> {{ $item->DESCR_CORTA }} </td>
                        <td class="right"> {{ $item->CALCULO }} </td>
                    </tr>    
                @endforeach
            </table>
        </div>
        <div class="content-children">
            <table>
                @foreach ($descuentos as $item)
                    <tr>
                        <td> {{ $item->DESCR_CORTA }} </td>
                        <td class="right"> {{ $item->CALCULO }} </td>
                    </tr>    
                @endforeach
            </table>
        </div>
        <div class="content-children">
            <table>
                @foreach ($seguro as $item)
                    <tr>
                        <td> {{ $item->DESCR_CORTA }} </td>
                        <td class="right"> {{ $item->CALCULO }} </td>
                    </tr>    
                @endforeach
            </table>
        </div>
        <div class="content-children">
            <table>
                @foreach ($tiempos as $item)
                    <tr>
                        <td> {{ $item->DESCR_CORTA }} </td>
                        <td class="right"> {{ $item->CALCULO }} </td>
                    </tr>    
                @endforeach
            </table>
        </div>
    </div>
    {{-- {{ $ingresos }} --}}
</body>
</html>