@extends('layouts.frontend_2')

@section('body-content')

    <div class="content-body">
        <div style="margin:3%;">
            <div class="row">
                <!-- content-->
                <div class="col-lg-10 col-lg-offset-1 woocommerce">
                    <h2 class="title-section mb-5">
                        <span>My Cart</span>
                    </h2>
                    <form action="#" method="post">
                        <table class="shop_table cart">
                            <thead>
                            <tr>
                                <th class="product-thumbnail">Package</th>
                                <th class="product-name"> </th>
                                <th class="product-name"> Start Date</th>
                                <th class="product-name"> End Date</th>
                                <th class="product-price">Price</th>
                                <th class="product-remove"> </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($carts as $cart)
                                <tr class="cart_item">
                                    <td class="product-thumbnail">
                                        <a href="#">
                                            <img src="{{ URL::asset('storage/package_image/'.$cart->package->featured_image) }}"
                                                 data-at2x="{{ URL::asset('storage/package_image/'.$cart->package->featured_image) }}"
                                                 alt="" class="attachment-shop_thumbnail wp-post-image">
                                        </a>
                                    </td>
                                    <td class="product-name">
                                        <a href="#">{{$cart->package->name}}</a>
                                    </td>
                                    @php($startDate = \Carbon\Carbon::parse($cart->package->start_date)->format('d F Y'))
                                    @php($endDate = \Carbon\Carbon::parse($cart->package->end_date)->format('d F Y'))
                                    <td class="product-quantity">
                                        {{$startDate}}
                                    </td>
                                    <td class="product-quantity">
                                        {{$endDate}}
                                    </td>
                                    <td class="product-price"><span class="amount">Rp {{$cart->package->price}}</span></td>

                                    <td class="product-remove"><a href="{{route('delete-cart', ['cartId'=>$cart->id])}}" title="Remove this item" class="remove"></a></td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="6" class="actions">
                                    <div class="coupon">
                                        <label for="coupon_code">Voucher:</label>
                                        <input id="coupon_code" type="text" name="coupon_code" value="" placeholder="Voucher code" class="input-text corner-radius-top">
                                        <input type="button" name="apply_coupon" value="Apply" class="cws-button alt">
                                    </div>
                                    <input type="button" name="update_cart" value="Update Cart" class="cws-button">
                                    {{--<input type="button" name="proceed" value="Proceed to Checkout" class="cws-button">--}}
                                    <a href="{{route('transaction-result')}}" class="cws-button">Proceed to Checkout</a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                    <div class="row mt-60">
                        <div class="col-md-12 mb-md-60">
                            <h2 class="mb-30 mt-0">Cart Totals</h2>
                            <table class="total-table">
                                <tbody>
                                <tr class="cart-subtotal">
                                    <th>Cart Subtotal</th>
                                    <td><span class="amount">Rp {{$totalPrice}}</span></td>
                                </tr>
                                <tr class="shipping">
                                    <th>Voucher</th>
                                    <td>-</td>
                                </tr>
                                <tr class="order-total">
                                    <th>Order Total</th>
                                    <td><span class="amount">Rp {{$totalPrice}}</span></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- ! content-->
            </div>
        </div>
    </div>


	@include('frontend.partials._modal-login')
@endsection

@section('styles')
    @parent
@endsection

@section('scripts')
    @parent
@endsection