@extends('layouts.app')

@section('content')
    <!-- APP MAIN ==========-->
    <main id="app-main" class="app-main">
        <div class="wrap">
            <section class="app-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="widget p-lg">
                            <h4 class="m-b-lg">Cobradores</h4>
                            <div class="d-none d-lg-block d-xl-block d-md-block overflow-auto">
                                <table class="table supervisor-close-table">
                                    <tbody>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Cobro</th>
                                            <th>Fecha</th>
                                            <th>Ciudad</th>
                                            <th>Accion</th>
                                        </tr>
                                    
                                    @foreach($clients as $client)
                                        @if($client->show)
                                            <tr>
                                                <td><span class="value">{{$client->name}} {{$client->last_name}}</span></td>
                                                <td><span class="value">{{$client->wallet_name}}</span></td>
                                                <td><span class="value">{{$today}}</span></td>
                                                <td><span class="value">{{$client->address}}</span></td>
                                                <td>
                                                    <a href="{{url('supervisor/close')}}/{{$client->id_user_agent}}" class="btn btn-danger btn-xs">Cerrar</a>
                                                </td>
                                            </tr>
                                        @endif

                                    @endforeach

                                    </tbody>
                                </table>
                            </div>


                            <!-- FOR MOBILE -->
                            <div class=" d-lg-none d-xl-none d-md-none">
                                <table class="table supervisor-close-table">
                                    <tbody>
                                        <!-- <tr>
                                            <th>Nombre</th>
                                            <th>Cartera</th>
                                            <th>Fecha</th>
                                            <th>Ciudad</th>
                                            <th>Accion</th>
                                        </tr> -->
                                    
                                    @foreach($clients as $client)
                                        @if($client->show)
                                            <tr>
                                                <td><span class="value">{{$client->name}} {{$client->last_name}}</span></td>
                                                <td><span class="value">{{$client->wallet_name}}</span></td>
                                                <td><span class="value">{{$today}}</span></td>
                                                <td><span class="value">{{$client->address}}</span></td>
                                                <td>
                                                    <a href="{{url('supervisor/close')}}/{{$client->id_user_agent}}" class="btn btn-danger btn-xs">Cerrar</a>
                                                </td>
                                            </tr>
                                        @endif

                                    @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div><!-- .widget -->
                    </div>
                </div><!-- .row -->
            </section>
        </div>
    </main>
@endsection
