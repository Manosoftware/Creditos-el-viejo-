@extends('layouts.app')

@section('content')
    <!-- APP MAIN ==========-->
    <main id="app-main" class="app-main">
        <div class="wrap">
            <section class="app-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="widget p-lg">
                            <h4 class="m-b-lg">Pagos</h4>
                            <form action="{{url('admin/graph')}}" method="POST">
                                {{ csrf_field() }}
                                <div class="row align-items-end">
                                    <div class="col-sm-4">
                                        <label for="nit_number"> Fecha Inicial:</label>
                                        <input type="text" name="date_start"  class="form-control datepicker-trigger" id="date_start" required>
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="nit_number"> Fecha Final:</label>
                                        <input type="text" name="date_end"  class="form-control datepicker-trigger" id="date_end" required>
                                    </div>
                                    <div class="col-sm-4">
                                        <button class="btn btn-info hidden" type="submit">Buscar</button>
                                        <a href="{{url('admin/graph?type=default')}}" class="btn btn-dark">Regresar</a>
                                    </div>
                                </div>
                                <input type="hidden" name="type" id="type" value="payment">
                            </form>
                            <br class="clearfix">
{{--                            {{json_encode($data)}}--}}
                            <div class="container">
                                @if(count($data)>0)
                                    <input type="hidden" name="dataGraph" id="dataGraph" value="{{json_encode($data)}}">

{{--                                    grafica por dias entre rango de fechas--}}
                                    <div class="pt-4 container d-flex justify-content-center">
                                        <div class="col-8">
                                            <canvas id="dataDays" width="200" height="100"></canvas>
                                        </div>
                                    </div>

{{--                                    graficas por rango de fecha--}}
                                    <div class="row pt-5" id="graphs">
                                        <div class="col-sm-6 px-5">
                                            <canvas id="dataItems" width="200" height="100"></canvas>
                                        </div>
                                        <div class="col-sm-6 px-5">
                                            <canvas id="dataAmount" width="200" height="100"></canvas>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div><!-- .widget -->
                    </div>
                </div><!-- .row -->
            </section>
        </div>
    </main>
    <script>
        function load() {
            const dataGraph = JSON.parse(document.getElementById('dataGraph').value);

            graphicsDays(
                dataGraph.dataDays.data,
                dataGraph.dataDays.labels,
                'Pagos por día',
                'dataDays'
            );

            graphics(
                [dataGraph.dataItems.thisWeekend, dataGraph.dataItems.lastWeekend],
                [dataGraph.thisWeekend, dataGraph.lastWeekend],
                'Cantidad de pagos',
                'dataItems'
            );

            graphics(
                [dataGraph.dataAmount.thisWeekend, dataGraph.dataAmount.lastWeekend],
                [dataGraph.thisWeekend, dataGraph.lastWeekend],
                'Pagos por rango',
                'dataAmount'
            );
        }
        setTimeout(function () {
            window.onload = load()
        }, 2000)
    </script>
@endsection