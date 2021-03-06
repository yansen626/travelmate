@extends('layouts.frontend_2')

@section('body-content')
    <!-- content-->
    <div class="content-body">
        {{--<div class="container page">--}}

        <section class="cws_prlx_section" style="padding-bottom: 10% !important;padding: 250px 0;">
            <img src="{{ URL::asset('storage/package_image/'.$package->featured_image) }}" alt class="cws_prlx_layer">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 mb-md-60">
                        &nbsp;<br>
                        &nbsp;<br>
                        &nbsp;<br>
                        &nbsp;
                    </div>
                </div>
            </div>
        </section>
        <div style="margin:3%;">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    {{--<div class="col-md-12">--}}
                        {{--<img src="{{ URL::asset('storage/package_image/'.$package->featured_image) }}">--}}
                    {{--</div>--}}
                    <div class="col-md-12">
                        <h4>{{$package->name}}</h4>

                    </div>

                    <div class="col-md-12">
                        <hr>
                        <h4>TOUR INFORMATION</h4>
                    </div>
                    <div class="col-md-3 col-sm-3">
                        <p>DESTINATION </p>
                    </div>
                    <div class="col-md-9 col-sm-9">
                        <p>: {{$package->name}}, {{$package->province->name}}</p>
                    </div>

                    <div class="col-md-3 col-sm-3">
                        <p>SCHEDULE</p>
                    </div>
                    <div class="col-md-9 col-sm-9">
                        {{--@php($startDate = \Carbon\Carbon::parse($package->start_date)->format('d F Y'))--}}
                        {{--@php($endDate = \Carbon\Carbon::parse($package->end_date)->format('d F Y'))--}}
                        {{--<p>: {{$startDate}} - {{$endDate}}</p>--}}
                        <p>: {{$package->start_date}} - {{$package->duration}} day(s)</p>
                    </div>
                    {{--<div class="col-md-3 col-sm-3">--}}
                        {{--<p>TRAVEL MATE</p>--}}
                    {{--</div>--}}
                    {{--<div class="col-md-9 col-sm-9">--}}
                        {{--<p style="font-size: 16px;">--}}
                            {{--: <a href="{{ route('travelmate.profile.showid', ['id'=>$package->travelmate_id]) }}">--}}
                                {{--{{$package->travelmate->first_name}} {{$package->travelmate->last_name}}--}}
                            {{--</a>--}}
                        {{--</p>--}}
                    {{--</div>--}}

                    <div class="col-md-3 col-sm-3">
                        <p>MEETING POINT </p>
                    </div>
                    <div class="col-md-9 col-sm-9">
                        <p>: {{$package->meeting_point}}&nbsp;</p>
                    </div>
                    <div class="col-md-3 col-sm-3">
                        <p>MAX CAPACITY </p>
                    </div>
                    <div class="col-md-9 col-sm-9">
                        <p>: {{$package->max_capacity}}&nbsp;Person(s)</p>
                    </div>
                    <div class="col-md-3 col-sm-3">
                        <p>CATEGORY </p>
                    </div>
                    <div class="col-md-9 col-sm-9">
                        @if($package->category_id == null)
                            <p>: -</p>
                        @else
                            @php($categories = preg_split('@;@', $package->category_id, NULL, PREG_SPLIT_NO_EMPTY))
                            @foreach($categories as $category)
                                <img src="{{ URL::asset('frontend_images/categories/'.$category.".png") }}" style="width: 70px;padding-bottom: 10px;">
                            @endforeach
                        @endif
                    </div>
                    <div class="col-md-12">
                        <hr>
                        <h4>PRICING</h4>
                    </div>
                    <div id="price" class="col-md-3 col-sm-3">
                        <span>PRICE : </span>
                        <br>
                        <label class="radio-inline">
                            <input type="radio" value="IDR" {{$currencyType == "IDR" ? 'checked':''}}
                            onchange="selectCurrency(this, '{{ $package->id }}');" name="optradio">IDR
                        </label>
                        <label class="radio-inline">
                            <input type="radio" value="USD" {{$currencyType == "USD" ? 'checked':''}}
                            onchange="selectCurrency(this, '{{$package->id}}');" name="optradio">USD
                        </label>
                        <label class="radio-inline">
                            <input type="radio" value="RMB" {{$currencyType == "RMB" ? 'checked':''}}
                            onchange="selectCurrency(this, '{{$package->id}}');" name="optradio">RMB
                        </label>
                    </div>
                    <div class="col-md-9 col-sm-9">
                        @if($packagePrices->count() > 0)
                            @php($qty = 0)
                            @foreach($packagePrices as $packagePrice)
                                @php($qty = $qty+1)
                                @php($finalPrice = $packagePrice->price / $currencyValue)
                                @php($priceConvert = number_format($finalPrice, 2, ",", "."))
                                <span> ({{$qty}}-{{$packagePrice->quantity}} Person) {{$currencyType}} {{$priceConvert}}</span>
                                <br>
                                @php($qty = $packagePrice->quantity)
                            @endforeach
                        @endif

                    </div>
                    <div class="col-md-12 col-sm-12">
                        <hr>
                        <h4>MAIN PROGRAM</h4>
                    </div>
                    <div class="col-md-12">
                        <span>PROGRAM : </span>
                        <br>

                        <div class="row form-panel">
                            @if($packageTrips->count() > 0)
                                @foreach($packageTrips as $packageTrip)
                                    {{--@php($startDateTrip = \Carbon\Carbon::parse($packageTrip->start_date)->format('d/m/Y G:i'))--}}
                                    {{--@php($endDateTrip = \Carbon\Carbon::parse($packageTrip->end_date)->format('d/m/Y G:i'))--}}
                                    <div class="col-md-offset-4" style="max-width: 200px; max-height: 400px;">
                                        <img src="{{ URL::asset('storage/package_trip_image/'.$packageTrip->featured_image) }}">
                                    </div>
                                    <br>
                                    <span> Desc : {{$packageTrip->description}}</span>
                                    <br>
                                    <br>

                                @endforeach
                            @endif
                        </div>
                        <br>
                        @if(auth()->guard('web')->check())
                            <a href="/package-pdf/{{$package->id}}?currency={{$currencyType}}" class="btn btn-default" style="background-color: #ffc801; color:white;">
                                Download PDF
                            </a>
                        @endif
                        <button onclick="showAddtoCartForm('{{$package->id}}', '{{$package->start_date}}','{{$package->price}}', '{{$package->package_prices}}')"
                                class="btn btn-success" style="color:white;">Add to cart</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ! content-->


	@include('frontend.partials._modal-login')
    @include('frontend.partials._modal-add-cart-form')
    @include('frontend.partials._modal-add-cart')
@endsection


@section('styles')
    @parent
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <style>
        #price input {
            float: left;
            -webkit-appearance: radio !important;
        }

        .cws_divider, hr {
            border-bottom: 2px solid #EB5532;
        }
        .form-panel{
            overflow-y :scroll;
            height:350px;
            border: 2px solid #EB5532;
            border-radius: 15px;
            padding: 10px;
            margin: 0;
        }
    </style>
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function () {
            // $('.daterangepicker.dropdown-menu.ltr.show-calendar.opensleft').show();
            $(".daterangepicker").show();
        });

        function selectCurrency(e, id){
            // Get status filter value
            var status = e.value;

            var url = "/package-detail/"+id+"?currency=" + status;

            window.location = url;
        }
        $(function() {
            $('input[name="daterange"]').daterangepicker({
                autoApply: true,
                alwaysShowCalendars: true,
                opens: 'left',
                locale: {
                    format: 'DD/MM/YYYY'
                }
            }, function(start, end, label) {
                console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));


            });
        });
    </script>
    {{--<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>--}}
    {{--<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>--}}
    {{--<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />--}}
    <script src="{{ URL::asset('js/moment.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js"></script>
    <script src="{{ URL::asset('js/frontend/custom-cart.js') }}"></script>
@endsection