@extends('layouts.frontend_2')

@section('body-content')
    <!-- content-->
    <div class="content-body">
        {{--<div class="container page">--}}
        <div style="margin-top:3%;">
            <div class="row">
                @include('frontend.travelmate.partials._left-side')
                <div class="col-md-7">
                    <div class="row">
                        <div class="col-md-6">
                            <h1>MY PACKAGES</h1>
                        </div>
                        <div class="col-md-6">
                        </div>
                    </div>
                    <div class="row" style="margin-top: 5px !important; margin-bottom: 35px;">
                        <div class="col-md-12">

                            <div class="board">
                                <!-- <h2>Welcome to IGHALO!<sup>™</sup></h2>-->
                                <div class="board-inner">
                                    <ul class="nav nav-tabs" id="myTab">
                                        <div class="liner"></div>
                                        <li class="active">
                                            <a href="#one" data-toggle="tab" title="one">
                                              <span class="round-tabs one">
                                                      1
                                              </span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#two" data-toggle="tab" title="two">
                                                 <span class="round-tabs two">
                                                     2
                                                 </span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#three" data-toggle="tab" title="three">
                                                 <span class="round-tabs five">
                                                      3
                                                 </span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="tab-content">
                                    <div class="tab-pane fade in active" id="one">
                                        <div class="col-lg-12 col-md-12">
                                            <div class="col-lg-12 col-md-12">
                                                <div class="row form-panel">
                                                    <div class="col-lg-6 col-md-6 text-center">
                                                        <select id="province" name="province" class="form-control" onchange="getCity()">
                                                            <option value="-1">- Select Province -</option>
                                                            @foreach($provinces as $province)
                                                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6">
                                                        <select id="city" name="city" class="form-control">
                                                            <option value="-1">- Select City -</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12" style="margin-top: 20px;">
                                            <div class="col-lg-9 col-md-9 col-xs-12">
                                                <div class="row form-panel">
                                                    {!! Form::file('cover', array('id' => 'cover', 'class' => 'file-loading')) !!}
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-md-3 col-xs-12">

                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12" style="margin-top: 20px;">
                                            <div class="col-lg-12 col-md-12 col-xs-12">
                                                <div class="row form-panel">
                                                    <div class="form-group">
                                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="description">
                                                            ABOUT THE TRIP
                                                        </label>
                                                        <div class="col-lg-9 col-md-3 col-xs-12">
                                                            <textarea id="description" name="description" rows="5" class="form-control" style="resize: none; overflow-y: scroll;"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-xs-12" style="margin-top: 20px;">
                                            <div class="col-lg-6 col-md-6 col-xs-12">
                                                <div class="row form-panel">
                                                    <div class="text-center" style="width: 100%;">
                                                        <label for="start_date">START DATE</label>
                                                    </div>
                                                    <div class='input-group date' >
                                                        <input id='start_date' name="start_date" type='text' class="form-control" />
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-calendar"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 col-md-6 col-xs-12">
                                                <div class="row form-panel">
                                                    <div class="text-center" style="width: 100%;">
                                                        <label for="end_date">END DATE</label>
                                                    </div>
                                                    <div class='input-group date' >
                                                        <input id='end_date' name="end_date" type='text' class="form-control" />
                                                        <span class="input-group-addon">
                                                            <span class="glyphicon glyphicon-calendar"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-xs-12" style="margin-top: 20px;">
                                            <div class="col-lg-8 col-md-8 col-xs-12">
                                                <div class="row form-panel">
                                                    <div class="form-group">
                                                        <div class="text-center" style="width: 100%;">
                                                            <label for="meeting_point">MEETING POINT</label>
                                                        </div>
                                                        <textarea id="meeting_point" name="meeting_point" rows="5" class="form-control" style="resize: none; overflow-y: scroll;"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-xs-12">
                                                <div class="row form-panel">
                                                    <div class="form-group">
                                                        <div class="text-center" style="width: 100%;">
                                                            <label for="max_capacity">MAX CAPACITY</label>
                                                            <input id='max_capacity' name="max_capacity" type='number' placeholder="PERSONS" class="form-control" style="width: 100%;"/>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-xs-12" style="margin-top: 20px;">
                                            <div class="col-lg-12 col-md-12">
                                                <div class="row">
                                                    <div style="float: right">
                                                        <button class="btn btn-success" id="next_one" onclick="switchTab(2);">NEXT</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="two">
                                        <div id="trip_list" class="col-lg-12 col-md-12" style="margin-top: 20px;">
                                            <div id="trip_1" class="col-lg-12 col-md-12" style="margin-bottom: 20px;">
                                                <div class="row form-panel">
                                                    <div class="col-lg-12 col-md-12 col-xs-12">
                                                        <h3 class="text-center">DESTINATION 1</h3>
                                                    </div>
                                                    <div class="col-lg-12 col-md-12 col-xs-12">
                                                        <div class="col-lg-6 col-md-6 col-xs-12">
                                                            <div class="row form-panel">
                                                                <div class="text-center" style="width: 100%;">
                                                                    <label for="trip_start_date_1">START DATE</label>
                                                                </div>
                                                                <div class='input-group date' >
                                                                    <input id='trip_start_date_1' name="trip_start_date[]" type='text' class="form-control" />
                                                                    <span class="input-group-addon">
                                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-xs-12">
                                                            <div class="row form-panel">
                                                                <div class="text-center" style="width: 100%;">
                                                                    <label for="trip_end_date_1">END DATE</label>
                                                                </div>
                                                                <div class='input-group date'>
                                                                    <input id='trip_end_date_1' name="trip_end_date[]" type='text' class="form-control" />
                                                                    <span class="input-group-addon">
                                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12 col-md-12 col-xs-12" style="margin-top: 20px;">
                                                        <div class="col-lg-6 col-md-6 col-xs-12">
                                                            <div class="row form-panel">
                                                                {!! Form::file('trip_image_1', array('id' => 'trip_image_1', 'class' => 'file-loading')) !!}
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-xs-12">
                                                            <div class="row form-panel">
                                                                <textarea id="trip_description_1" name="trip_description[]" rows="5" placeholder="TRIP DESCRIPTION" class="form-control" style="resize: none; overflow-y: scroll;"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="trip_2" class="col-lg-12 col-md-12" style="margin-bottom: 20px;">

                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-xs-12 text-center" style="margin-top: 20px;">
                                            <a onclick="addTrip()">
                                                <i class="fa fa-plus fa-5x"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="three">
                                        <div class="text-center">
                                            <i class="img-intro icon-checkmark-circle"></i>
                                        </div>
                                        <h3 class="head text-center">thanks for staying tuned! <span style="color:#f48260;">♥</span> Bootstrap</h3>
                                        <p class="narrow text-center">
                                            Lorem ipsum dolor sit amet, his ea mollis fabellas principes. Quo mazim facilis tincidunt ut, utinam saperet facilisi an vim.
                                        </p>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    @include('frontend.travelmate.partials._right-side')
                </div>
            </div>
        </div>
    </div>
    <!-- ! content-->

    @include('frontend.partials._modal-login')
@endsection

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ URL::asset('css/frontend/bootstrap-datetimepicker.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/kartik-bootstrap-file-input/fileinput.min.css') }}">
    <style>
        @import url(http://fonts.googleapis.com/css?family=Roboto+Condensed:400,700);

        .board ul li{
            padding-left: 0 !important;;
            margin-left: 0 !important;
        }

        /* written by riliwan balogun http://www.facebook.com/riliwan.rabo*/
        .board{
            width: 80%;
            margin: 60px auto;
            height: 500px;
            background: #fff;
            /*box-shadow: 10px 10px #ccc,-10px 20px #ddd;*/
        }
        .board .nav-tabs {
            position: relative;
            /* border-bottom: 0; */
            /* width: 80%; */
            margin: 40px auto;
            margin-bottom: 0;
            box-sizing: border-box;

        }

        .board > div.board-inner{
            /*background: #fafafa url(http://subtlepatterns.com/patterns/geometry2.png);*/
            /*background-size: 30%;*/
        }

        p.narrow{
            width: 60%;
            margin: 10px auto;
        }

        .liner{
            height: 4px;
            background: #EB5532;
            position: absolute;
            width: 72%;
            margin: 0 auto;
            left: 0;
            right: 0;
            top: 50%;
            z-index: 1;
        }

        .nav-tabs > li.active > a, .nav-tabs > li.active > a:hover, .nav-tabs > li.active > a:focus {
            color: #555555;
            cursor: default;
            /* background-color: #ffffff; */
            border: 0;
            border-bottom-color: transparent;
        }

        span.round-tabs{
            width: 70px;
            height: 70px;
            line-height: 70px;
            display: inline-block;
            border-radius: 100px;
            z-index: 2;
            position: absolute;
            left: 0;
            text-align: center;
            font-size: 25px;
            background-color: #EB5532 !important;
            color: #ffffff !important;
            border: none !important;
        }

        span.round-tabs.one{
            color: rgb(34, 194, 34);border: 2px solid rgb(34, 194, 34);
        }

        li.active span.round-tabs.one{
            border: 2px solid #ddd;
            color: rgb(34, 194, 34);
        }

        span.round-tabs.two{
            color: #febe29;border: 2px solid #febe29;
        }

        li.active span.round-tabs.two{
            border: 2px solid #ddd;
            color: #febe29;
        }

        span.round-tabs.three{
            color: #3e5e9a;border: 2px solid #3e5e9a;
        }

        li.active span.round-tabs.three{
            border: 2px solid #ddd;
            color: #3e5e9a;
        }

        span.round-tabs.four{
            color: #f1685e;border: 2px solid #f1685e;
        }

        li.active span.round-tabs.four{
            background: #fff !important;
            border: 2px solid #ddd;
            color: #f1685e;
        }

        span.round-tabs.five{
            color: #999;border: 2px solid #999;
        }

        /*li.active span.round-tabs.five{*/
            /*background: #fff !important;*/
            /*border: 2px solid #ddd;*/
            /*color: #999;*/
        /*}*/

        .nav-tabs > li.active > a span.round-tabs{
            background: #fafafa;
        }
        .nav-tabs > li {
            width: 33%;
        }
        /*li.active:before {
            content: " ";
            position: absolute;
            left: 45%;
            opacity:0;
            margin: 0 auto;
            bottom: -2px;
            border: 10px solid transparent;
            border-bottom-color: #fff;
            z-index: 1;
            transition:0.2s ease-in-out;
        }*/
        li:after {
            content: " ";
            position: absolute;
            left: 45%;
            opacity:0;
            margin: 0 auto;
            bottom: 0px;
            border: 5px solid transparent;
            border-bottom-color: #ddd;
            transition:0.1s ease-in-out;

        }
        li.active:after {
            content: " ";
            position: absolute;
            left: 45%;
            opacity:1;
            margin: 0 auto;
            bottom: 0px;
            border: 10px solid transparent;
            border-bottom-color: #ddd;

        }
        .nav-tabs > li a{
            width: 70px;
            height: 70px;
            margin: 20px auto;
            border-radius: 100%;
            padding: 0;
        }

        .nav-tabs > li a:hover{
            background: transparent;
        }

        .tab-content{
        }
        .tab-pane{
            position: relative;
            padding-top: 50px;
        }
        .tab-content .head{
            font-family: 'Roboto Condensed', sans-serif;
            font-size: 25px;
            text-transform: uppercase;
            padding-bottom: 10px;
        }
        .btn-outline-rounded{
            padding: 10px 40px;
            margin: 20px 0;
            border: 2px solid transparent;
            border-radius: 25px;
        }

        .btn.green{
            background-color:#5cb85c;
            /*border: 2px solid #5cb85c;*/
            color: #ffffff;
        }

        @media( max-width : 585px ){

            .board {
                width: 90%;
                height:auto !important;
            }
            span.round-tabs {
                font-size:16px;
                width: 50px;
                height: 50px;
                line-height: 50px;
            }
            .tab-content .head{
                font-size:20px;
            }
            .nav-tabs > li a {
                width: 50px;
                height: 50px;
                line-height:50px;
            }

            li.active:after {
                content: " ";
                position: absolute;
                left: 35%;
            }

            .btn-outline-rounded {
                padding:12px 20px;
            }
        }

        .form-panel{
            border: 2px solid #EB5532;
            border-radius: 20px;
            padding: 10px;
            margin: 0;
        }
    </style>
@endsection

@section('scripts')
    @parent
    <script src="{{ URL::asset('js/moment.js') }}"></script>
    <script src="{{ URL::asset('js/frontend/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ URL::asset('js/kartik-bootstrap-file-input/fileinput.min.js') }}"></script>
    <script src="{{ URL::asset('js/stringbuilder.js') }}"></script>
    <script>
        $(function(){
            $('a[title]').tooltip();
        });

        function getCity(){
            var provId = $("#province option:selected").val();

            if(provId !== '-1'){
                $.get('/travelmate/packages/city?province=' + provId, function (data) {
                    if(data.success == true) {
                        $('#city').html(data.html);
                    }
                });
            }
            else{
                $('#city').html("<option value='-1'>- Select City -</option>");
            }
        }

        // FILEINPUT
        $("#cover").fileinput({
            allowedFileExtensions: ["jpg", "jpeg", "png"],
            browseClass: "btn btn-primary btn-block",
            showUpload: false,
            showRemove: false,
            dropZoneEnabled: true,
            browseOnZoneClick: true,
            dropZoneTitle: "UPLOAD COVER IMAGE HERE!",
            uploadExtraData:{'_token':'{{ csrf_token() }}'}
        });

        $("#trip_image_1").fileinput({
            allowedFileExtensions: ["jpg", "jpeg", "png"],
            browseClass: "btn btn-primary btn-block",
            showUpload: false,
            showRemove: false,
            dropZoneEnabled: true,
            browseOnZoneClick: true,
            dropZoneTitle: "UPLOAD TRIP IMAGE HERE!",
            uploadExtraData:{'_token':'{{ csrf_token() }}'}
        });

        // DATE PICKER
        $('#start_date').datetimepicker({
            format: "DD MMM Y"
        });

        $('#end_date').datetimepicker({
            format: "DD MMM Y"
        });

        // DATETIMEPICKER
        $('#trip_start_date_1').datetimepicker({
            format: "DD MMM Y HH:mm"
        });

        $('#trip_end_date_1').datetimepicker({
            format: "DD MMM Y HH:mm"
        });

        function switchTab(tabNumber){
            if(tabNumber === 1){
                $('.nav-tabs a[href="#one"]').tab('show')
            }
            else if(tabNumber === 2){
                $('.nav-tabs a[href="#two"]').tab('show')
            }
            else if(tabNumber === 3){
                $('.nav-tabs a[href="#three"]').tab('show')
            }
        }

        var i = 2;
        function addTrip(){
            var sbAdd = new stringbuilder();
            sbAdd.append("<div class='row form-panel'>");
            sbAdd.append("<div class='col-lg-12 col-md-12 col-xs-12'>");
            sbAdd.append("<h3 class='text-center'>DESTINATION " + i + "</h3>");
            sbAdd.append("</div>");
            sbAdd.append("<div class='col-lg-12 col-md-12 col-xs-12'>");
            sbAdd.append("<div class='col-lg-6 col-md-6 col-xs-12'>");
            sbAdd.append("<div class='row form-panel'>");
            sbAdd.append("<div class='text-center' style='width: 100%;'>");
            sbAdd.append("<label for='trip_start_date_1'>START DATE</label>");
            sbAdd.append("</div>");
            sbAdd.append("<div class='input-group date' >");
            sbAdd.append("<input id='trip_start_date_" + i + "' name='trip_start_date[]' type='text' class='form-control' />");
            sbAdd.append("<span class='input-group-addon'>");
            sbAdd.append("<span class='glyphicon glyphicon-calendar'></span>");
            sbAdd.append("</span>");
            sbAdd.append("</div>");
            sbAdd.append("</div>");
            sbAdd.append("</div>");
            sbAdd.append("<div class='col-lg-6 col-md-6 col-xs-12'>");
            sbAdd.append("<div class='row form-panel'>");
            sbAdd.append("<div class='text-center' style='width: 100%;'>");
            sbAdd.append("<label for='trip_end_date_" + i + "'>END DATE</label>");
            sbAdd.append("</div>");
            sbAdd.append("<div class='input-group date'>");
            sbAdd.append("<input id='trip_end_date_" + i + "' name='trip_end_date[]' type='text' class='form-control' />");
            sbAdd.append("<span class='input-group-addon'>");
            sbAdd.append("<span class='glyphicon glyphicon-calendar'></span>");
            sbAdd.append( "</span>");
            sbAdd.append("</div>");
            sbAdd.append("</div>");
            sbAdd.append("</div>");
            sbAdd.append("</div>");
            sbAdd.append("<div class='col-lg-12 col-md-12 col-xs-12' style='margin-top: 20px;'>");
            sbAdd.append("<div class='col-lg-6 col-md-6 col-xs-12'>");
            sbAdd.append("<div class='row form-panel'>");
            sbAdd.append("<input id='trip_image_" + i + "' class='file-loading' name='trip_image_" + i + "' type='file'>");
            sbAdd.append("</div>");
            sbAdd.append("</div>");
            sbAdd.append("<div class='col-lg-6 col-md-6 col-xs-12'>");
            sbAdd.append("<div class='row form-panel'>");
            sbAdd.append("<textarea id='trip_description_" + i + "' name='trip_description[]' rows='5' placeholder='TRIP DESCRIPTION' class='form-control' style='resize: none; overflow-y: scroll;'></textarea>");
            sbAdd.append("</div>");
            sbAdd.append("</div>");
            sbAdd.append("</div>");
            sbAdd.append("</div>");

            $('#trip_' + i ).html(sbAdd.toString());

            $('#trip_list').append("<div id='trip_" + (i+1) + "' class='col-lg-12 col-md-12' style='margin-bottom: 20px;'></div>");

            // DYNAMIC FILEINPUT
            $('#trip_image_' + i).fileinput({
                allowedFileExtensions: ["jpg", "jpeg", "png"],
                browseClass: "btn btn-primary btn-block",
                showUpload: false,
                showRemove: false,
                dropZoneEnabled: true,
                browseOnZoneClick: true,
                dropZoneTitle: "UPLOAD TRIP IMAGE HERE!",
                uploadExtraData:{'_token':'{{ csrf_token() }}'}
            });

            // DYNAMIC DATETIMEPICKER
            $('#trip_start_date_' + i).datetimepicker({
                format: "DD MMM Y HH:mm"
            });

            $('#trip_end_date_' + i).datetimepicker({
                format: "DD MMM Y HH:mm"
            });

            i++;
        }

    </script>
@endsection