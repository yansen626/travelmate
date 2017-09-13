@extends('layouts.admin')

@section('dashboard')

    <!-- sidebar -->
    @include('admin.partials._sidebar')
    <!-- sidebar -->

    <!-- top navigation -->
    @include('admin.partials._navigation')
    <!-- /top navigation -->

    <!-- page content -->
    <div class="right_col" role="main">
        <div class="">
            <div class="page-title">
            </div>
            <div class="clearfix"></div>
            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Bank Manual Transfer</h2>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Customer Name</th>
                                    <th>Bank</th>
                                    <th>Sender Name</th>
                                    <th>Transfer Amount</th>
                                    <th>Total Payment</th>
                                    <th>Transfer Date</th>
                                    <th>Confirm Date</th>
                                    <th>Note</th>
                                    <th>Status</th>
                                    <th>Option</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($transactions as $trx)
                                    @php( $trans = $trx->transfer_confirmation()->first())
                                    <tr>
                                        <td>{{ $trx->invoice }}</td>
                                        <td>{{ $trx->user->first_name }}&nbsp;{{ $trx->user->last_name }}</td>
                                        <td>{{ $trans->receiver_bank ?? '-' }}</td>
                                        <td>{{ $trans->sender_name ?? '-'}}</td>
                                        <td>
                                            @if(!empty($trans))
                                                Rp {{ $trans->transfer_amount }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>Rp {{ $trx->total_payment }}</td>
                                        <td>
                                            @if(!empty($trans->transfer_date))
                                                {{ \Carbon\Carbon::parse($trans->transfer_date)->format('j M Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($trans->created_on))
                                                {{ \Carbon\Carbon::parse($trans->created_on)->format('j M Y G:i:s') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($trans->note))
                                                {{ $trans->note }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($trx->status_id == 3)
                                                <span style="color: red;">Pending Payment</span>
                                            @else
                                                <span style="color: orange;">Need Verification</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($trans))
                                                <a onclick="modalPop('{{ $trans->id }}', 'transfer', '/admin/transfer/confirm/')" class="btn btn-success">Confirm</a>
                                            @endif
                                            <a href="/admin/transaction/detail/{{ $trx->id }}" class="btn btn-primary">Detail</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /page content -->

    <!-- small modal -->
    @include('admin.partials._small_modal')
    <!-- small modal -->

@endsection