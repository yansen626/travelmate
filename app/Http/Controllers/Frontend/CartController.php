<?php
/**
 * Created by PhpStorm.
 * User: yanse
 * Date: 9/5/2017
 * Time: 1:59 PM
 */

namespace App\Http\Controllers\Frontend;



use App\libs\Utilities;
use App\Models\Cart;
use App\Models\General;
use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\Product;
use App\Models\ProductProperty;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\In;

class CartController
{
    //
    public function CartShowAll(){
        if (Auth::check())
        {
            $userId = Auth::user()->id;

            $carts = Cart::where('user_id', $userId)->get();

            $packageId = array();
            foreach ($carts as $cart){
                array_push($packageId, $cart->package_id);
            }

            $currencyType = "IDR";
            $currencyValue = 1;

            if(!empty(request()->currency)){
                $currencyType = request()->currency;
                $generalDB = General::find(1);

                if($currencyType == "USD"){
                    $currencyValue = $generalDB->idrusd;
                }
                else if ($currencyType == "RMB"){
                    $currencyValue = $generalDB->idrrmb;
                }
            };

//            $totalPriceTem = Package::wherein('id', $packageId)->sum('price');
            $totalPriceTem = $carts->sum('total_price');
            $totalPrice = $totalPriceTem/$currencyValue;
            $totalPrice = number_format($totalPrice, 2, ",", ".");

            $totalPriceVoucherTem = $totalPriceTem;

            $voucherAmount = 0;
            $voucherFinal = 0;
            $voucherDescription = "";
            $voucher = request()->voucher;
            if(!empty($voucher)){
                $voucherDB = Voucher::where('name', $voucher)->first();
                if(empty($voucherDB)){
                    $voucherDescription = "voucher not found / not valid";
                }
                else{
                    if(!empty($voucherDB->amount)){
                        $voucherAmount = $voucherDB->amount;
                        $voucherFinal1 = $voucherAmount/$currencyValue;
                        $voucherFinal = number_format($voucherFinal1, 2, ",", ".");

                        $totalPriceVoucherTem = $totalPriceVoucherTem - $voucherDB->amount;

                    }
                    else{
                        $voucherAmount = (($voucherDB->amount_percentage * $totalPriceTem) / 100);
                        $voucherFinal1 = $voucherAmount/$currencyValue;
                        $voucherFinal = number_format($voucherFinal1, 2, ",", ".");

                        $totalPriceVoucherTem = $totalPriceVoucherTem - (($voucherDB->amount_percentage * $totalPriceTem) / 100);
    //                    dd($totalPriceFinal);
                    }

                    //change cart DB
                    foreach ($carts as $cart){
                        $cart->voucher_code = $voucher;
                        $cart->save();
                    }
                }

            };

            $totalPriceFinal = $totalPriceVoucherTem/$currencyValue;
            $totalPriceFinal = number_format($totalPriceFinal, 2, ",", ".");

            $data = [
                'carts'          => $carts,
                'currencyType'          => $currencyType,
                'currencyValue'          => $currencyValue,
                'totalPrice'          => $totalPrice,
                'totalPriceFinal'          => $totalPriceFinal,
                'voucher'          => $voucher,
                'voucherFinal'          => $voucherFinal,
                'voucherDescription'          => $voucherDescription
            ];

            return view('frontend.transactions.carts')->with($data);
        }
        else
        {
            return redirect()->route('login');
        }
    }
    public function AddToCart(Request $request){
        try{
            if (!Auth::check()){
                return response()->json(['errors' => 'login']);
            }

            $userId = Auth::user()->id;
            $packageId   = $request['id'];
            $participant   = $request['participant'];
            $notes  = $request['notes'];
            $startDate  = $request['start_date'];
//            error_log($userId." ".$packageId." ".$participant." ".$notes." ".$startDate);

            $packageDB = Package::find($packageId);
            $cartDB = Cart::where('package_id',$packageId)->where('user_id', $userId)->first();
            if($cartDB == null){

                $selectedPrice = $packageDB->price;
                $packagePriceList = PackagePrice::where('package_id', $packageId)->orderBy('quantity');
                foreach ($packagePriceList as $packagePrice){
                    if($participant > $packagePrice->quantity){
                        $selectedPrice = $packagePrice->price;
                    }
                }
                $cartCreate = Cart::Create([
                    'package_id'    => $packageId,
                    'user_id'       => $userId,
                    'admin_fee'      => 0,
                    'qty'      => $participant,
                    'price'      => $selectedPrice,
                    'total_price'      => $selectedPrice * $participant,
                    'special_request'      => $notes,
                    'selected_date'      => $startDate,
                    'payment_method'    => 0
                ]);
            }
            else{
                $qty = $cartDB->qty;
                $qtyNew = $qty + $participant;
                $cartDB->qty = $qtyNew;

                $selectedPrice = $packageDB->price;
                $packagePriceList = PackagePrice::where('package_id', $packageId)->orderBy('quantity');
                foreach ($packagePriceList as $packagePrice){
                    if($qtyNew > $packagePrice->quantity){
                        $selectedPrice = $packagePrice->price;
                    }
                }
                $cartDB->price = $selectedPrice;
                $cartDB->total_price = $selectedPrice * $qtyNew;

                $cartDB->save();
            }


            return response()->json(['success' => true]);
        }
        catch (\Exception $ex){
            error_log($ex);
            return response()->json(['errors' => 'INVALID']);
        }
    }

    public function EditQuantityCart(){
        $qty = request()->qty;
        $id = request()->id;
        $specialRequest = request()->specialRequest;

        $cartDB = Cart::find($id);
        $cartDB->qty = $qty;
        $selectedPrice = $cartDB->price;

        $packagePriceList = PackagePrice::where('package_id', $cartDB->package_id)->orderBy('quantity')->get();

        foreach ($packagePriceList as $packagePrice){
            if($qty > $packagePrice->quantity){
                $selectedPrice = $packagePrice->price;
            }
        }
//        dd($qty." | ".$selectedPrice);
        $cartDB->price = $selectedPrice;
        $cartDB->total_price = $selectedPrice * $qty;
        $cartDB->special_request = $specialRequest;
//        dd($cartDB);
        $cartDB->save();

        return redirect()->route('cart-list');
    }

    public function EditQuantityCartJSON(){
        $qty = request()->qty;
        $id = request()->id;

        $cartDB = Cart::find($id);
        $cartDB->qty = $qty;
        $selectedPrice = $cartDB->price;

        $packagePriceList = PackagePrice::where('package_id', $cartDB->package_id)->orderBy('quantity')->get();

        foreach ($packagePriceList as $packagePrice){
            if($qty > $packagePrice->quantity){
                $selectedPrice = $packagePrice->price;
            }
        }
//        dd($qty." | ".$selectedPrice);
        $cartDB->price = $selectedPrice;
        $cartDB->total_price = $selectedPrice * $qty;
//        dd($cartDB);
        $cartDB->save();

        return redirect()->route('cart-list');
    }

    public function DeleteCart($cartId){
//        $cartDB = Cart::find($cartId);
//
//        $totalPriceTem = Cart::where('user_id', 'like', $cartDB->user_id)->sum('total_price');
//        $totalPrice = number_format($totalPriceTem, 0, ",", ".");

        Cart::where('id', '=', $cartId)->delete();

//        //edit session data
//        $userId = Auth::user()->id;
//        $carts = Cart::where('user_id', 'like', $userId)->get();
//        $cartTotal = $carts->count();
//        Session::put('cartList', $carts);
//        Session::put('cartTotal', $cartTotal);

        return redirect()->route('cart-list');
    }
    //
//    public function AddToCart(Request $request){
//        try{
//
//            if (!Auth::check()){
//                return response()->json(['success' => false, 'error' => 'login']);
//            }
//
//            $user = Auth::user();
//            $userId = $user->id;
//
//            $productId   = $request['product_id'];
//
//            // Get added qty
//            $addedQty = intval(Input::get('cartQty'));
//
//            $product = Product::find($productId);
////            if($product->quantity == 0){
////                return response()->json(['success' => false, 'error' => 'stock']);
////            }
//
//            $note = "";
//            if(!empty(Input::get('color'))){
//                $color = ProductProperty::find(Input::get('color'));
//                $note .= 'color='. $color->description. ';';
//            }
//
//            $carts = Cart::where([['user_id', '=', $userId], ['product_id', '=', $productId]])->get()->count();
//
//            if($carts > 0){
//                $cart = Cart::where('user_id', $userId)->where('product_id', $productId);
//                $isExist = false;
//
//                // Get size selection
//                if(!empty(Input::get('size')) && Input::get('size') != '0'){
//                    $size = $product->product_properties()->where('id', Input::get('size'))
//                        ->first();
//                    $cart = $cart->where('size_option', $size->description)->first();
//
//                    // Check if cart has the same selected product property or not
//                    if(!empty($cart)){
//                        $newQuantity = $cart->quantity + $addedQty;+
//                        $cart->quantity = $newQuantity;
//
//                        // Check price
//                        if(!empty($size->price)){
//                            $cart->total_price = $newQuantity * $size->getOriginal('price');
//                        }
//                        else{
//                            $cart->total_price = $newQuantity * $cart->product->getOriginal('price_discounted');
//                        }
//
//                        if(!empty(Input::get('buyerNote'))){
//                            $cart->buyer_note = Input::get('buyerNote');
//                        }
//
//                        $cart->save();
//                    }
//                    else{
//                        $cartCreate = Cart::Create([
//                            'product_id'    => $productId,
//                            'user_id'       => $userId,
//                            'quantity'      => 1,
//                            'size_option'   => $size->description
//                        ]);
//
//                        // Check price
//                        if(!empty($size->price)){
//                            $cartCreate->price = $size->getOriginal('price');
//                            $cartCreate->total_price = $size->getOriginal('price');
//                        }
//                        else{
//                            $cartCreate->price = $cart->product->getOriginal('price_discounted');
//                            $cartCreate->total_price = $cart->product->getOriginal('price_discounted');
//                        }
//
//                        if(!empty(Input::get('buyerNote'))){
//                            $cartCreate->buyer_note = Input::get('buyerNote');
//                        }
//
//                        $cartCreate->save();
//                    }
//                }
//                // Get weight selection
//                elseif(!empty(Input::get('weight')) && Input::get('weight') != '0'){
//                    $weight = $product->product_properties()->where('id', Input::get('weight'))
//                        ->first();
//                    $cart = $cart->where('weight_option', $weight->description)->first();
//
//                    // Check if cart has the same selected product property or not
//                    if(!empty($cart)){
//                        $newQuantity = $cart->quantity + $addedQty;
//                        $cart->quantity = $newQuantity;
//
//                        // Check price
//                        if(!empty($weight->price)){
//                            $cart->price = $weight->getOriginal('price');
//                            $cart->total_price = $newQuantity * $weight->getOriginal('price');
//                        }
//                        else{
//                            $cart->price = $product->getOriginal('price_discounted');
//                            $cart->total_price = $newQuantity * $product->getOriginal('price_discounted');
//                        }
//
//                        if(!empty($note)) $cart->note = $note;
//                        if(!empty(Input::get('buyerNote'))){
//                            $cart->buyer_note = Input::get('buyerNote');
//                        }
//
//                        $cart->save();
//                    }
//                    else{
//                        $cartCreate = Cart::Create([
//                            'product_id'    => $productId,
//                            'user_id'       => $userId,
//                            'quantity'      => 1,
//                            'weight_option' => $weight->description
//                        ]);
//
//                        // Check price
//                        if(!empty($weight->price)){
//                            $cartCreate->price = $weight->getOriginal('price');
//                            $cartCreate->total_price = $weight->getOriginal('price');
//                        }
//                        else{
//                            $cartCreate->price = $product->getOriginal('price_discounted');
//                            $cartCreate->total_price = $product->getOriginal('price_discounted');
//                        }
//
//                        if(!empty($note)) $cartCreate->note = $note;
//                        if(!empty(Input::get('buyerNote'))){
//                            $cartCreate->buyer_note = Input::get('buyerNote');
//                        }
//
//                        $cartCreate->save();
//                    }
//                }
//                // Get qty selection
//                elseif(!empty(Input::get('qty')) && Input::get('qty') != '0'){
//                    $qty = $product->product_properties()->where('id', Input::get('qty'))
//                        ->first();
//                    $cart = $cart->where('qty_option', $qty->description)->first();
//
//                    // Check if cart has the same selected product property or not
//                    if(!empty($cart)){
//                        $newQuantity = $cart->quantity + $addedQty;
//                        $cart->quantity = $newQuantity;
//
//                        // Check price
//                        if(!empty($qty->price)){
//                            $cart->price = $qty->getOriginal('price');
//                            $cart->total_price = $newQuantity * $qty->getOriginal('price');
//                        }
//                        else{
//                            $cart->price = $product->getOriginal('price_discounted');
//                            $cart->total_price = $newQuantity * $product->getOriginal('price_discounted');
//                        }
//
//                        if(!empty($note)) $cart->note = $note;
//                        if(!empty(Input::get('buyerNote'))){
//                            $cart->buyer_note = Input::get('buyerNote');
//                        }
//
//                        $cart->save();
//                    }
//                    else{
//                        $cartCreate = Cart::Create([
//                            'product_id'    => $productId,
//                            'user_id'       => $userId,
//                            'quantity'      => $addedQty,
//                            'qty_option'    => $qty->description
//                        ]);
//
//                        // Check price
//                        if(!empty($qty->price)){
//                            $cartCreate->price = $qty->getOriginal('price');
//                            $cartCreate->total_price = $qty->getOriginal('price');
//                        }
//                        else{
//                            $cartCreate->price = $product->getOriginal('price_discounted');
//                            $cartCreate->total_price = $product->getOriginal('price_discounted');
//                        }
//
//                        if(!empty($note)) $cartCreate->note = $note;
//                        if(!empty(Input::get('buyerNote'))){
//                            $cartCreate->buyer_note = Input::get('buyerNote');
//                        }
//
//                        $cartCreate->save();
//                    }
//                }
//                else{
//                    $cart = $cart->whereNull('weight_option')
//                        ->whereNull('size_option')
//                        ->whereNull('qty_option')
//                        ->first();
//
//                    // Check if cart does not have any selected product property or not
//                    if(!empty($cart)){
//                        $newQuantity = $cart->quantity + 1;
//                        $cart->quantity = $newQuantity;
//                        $cart->price = $product->getOriginal('price_discounted');
//                        $cart->total_price = $newQuantity * $product->getOriginal('price_discounted');
//
//                        if(!empty($note)) $cart->note = $note;
//
//                        $cart->save();
//                    }
//                    else{
//                        $cartCreate = Cart::Create([
//                            'product_id'    => $productId,
//                            'user_id'       => $userId,
//                            'quantity'      => 1,
//                            'price'         => $product->getOriginal('price_discounted'),
//                            'total_price'   => $product->getOriginal('price_discounted')
//                        ]);
//
//                        if(!empty($note)) {
//                            $cartCreate->note = $note;
//                            $cartCreate->save();
//                        }
//
//                        if(!empty(Input::get('buyerNote'))){
//                            $cartCreate->buyer_note = Input::get('buyerNote');
//                            $cartCreate->save();
//                        }
//                    }
//                }
//            }
//            else{
//                $cart = Cart::Create([
//                    'product_id'    => $productId,
//                    'user_id'       => $userId,
//                    'quantity'      => 1,
//                    'price'         => $product->getOriginal('price_discounted'),
//                    'total_price'   => $product->getOriginal('price_discounted')
//                ]);
//
//                error_log('size = '. Input::get('size'));
//                error_log('weight = '. Input::get('weight'));
//
//                if(!empty(Input::get('size')) && Input::get('size') != '0'){
//                    $size = ProductProperty::find(Input::get('size'));
//
//                    $cart->size_option = $size->description;
//
//                    $cart->price = $size->getOriginal('price');
//                    $cart->total_price = $size->getOriginal('price');
//                }
//                elseif(!empty(Input::get('weight')) && Input::get('weight') != '0'){
//                    $weight = ProductProperty::find(Input::get('weight'));
//
//                    $cart->weight_option = $weight->description;
//
//                    $cart->price = $weight->getOriginal('price');
//                    $cart->total_price = $weight->getOriginal('price');
//                }
//                elseif(!empty(Input::get('qty')) && Input::get('qty') != '0'){
//                    $qty = ProductProperty::find(Input::get('qty'));
//
//                    $cart->qty_option = $qty->description;
//
//                    $cart->price = $qty->getOriginal('price');
//                    $cart->total_price = $qty->getOriginal('price');
//                }
//
//                if(!empty($note)) $cart->note = $note;
//
//                if(!empty(Input::get('buyerNote'))){
//                    $cart->buyer_note = Input::get('buyerNote');
//                }
//
//                $cart->save();
//            }
//
//            //edit session data
//            $userId = Auth::user()->id;
//            $carts = Cart::where('user_id', 'like', $userId)->get();
//            $cartTotal = $carts->count();
//            Session::put('cartList', $carts);
//            Session::put('cartTotal', $cartTotal);
//
//            return response()->json(['success' => true]);
//        }
//        catch (\Exception $ex){
//            Utilities::ExceptionLog($ex);
//            return response()->json(['success' => false, 'error' => 'exception']);
//        }
//    }

    //
//    public function DeleteCart($cartId){
////        $cartDB = Cart::find($cartId);
////
////        $totalPriceTem = Cart::where('user_id', 'like', $cartDB->user_id)->sum('total_price');
////        $totalPrice = number_format($totalPriceTem, 0, ",", ".");
//
//        Cart::where('id', '=', $cartId)->delete();
//
//        //edit session data
//        $userId = Auth::user()->id;
//        $carts = Cart::where('user_id', 'like', $userId)->get();
//        $cartTotal = $carts->count();
//        Session::put('cartList', $carts);
//        Session::put('cartTotal', $cartTotal);
//
//        return redirect()->route('cart-list');
//    }

    //
//    public function EditQuantityCart(Request $request){
//        //userId sesuai dengan session
//        $user = Auth::user();
//        $userId = $user->id;
//
//        $cartId   = $request['cart_id'];
//        $quantity   = $request['quantity'];
//
//        $cart = Cart::find($cartId);
//
//        //$price = $CartDB->getOriginal('total_price') / $CartDB->quantity;
//        $price = 0;
//        if(!empty($cart->size_option) && empty($cart->weight_option) && empty($cart->qty_option)){
//            $size = $cart->product->product_properties()->where('name','=','size')
//                ->where('description', $cart->size_option)
//                ->first();
//
//            if(empty($size)){
//                return response()->json([
//                    'success'       => false,
//                    'exception'     => 'property'
//                ]);
//            }
//
//            if(!empty($size->price)){
//                $price = $size->getOriginal('price');
//            }
//            else{
//                $price = $cart->product->getOriginal('price_discounted');
//            }
//        }
//        elseif(empty($cart->size_option) && !empty($cart->weight_option) && empty($cart->qty_option)){
//            $weight = $cart->product->product_properties()->where('name','=','weight')
//                ->where('description', $cart->weight_option)
//                ->first();
//
//            if(empty($weight)){
//                return response()->json([
//                    'success'       => false,
//                    'exception'     => 'property'
//                ]);
//            }
//
//            if(!empty($weight->price)){
//                $price = $weight->getOriginal('price');
//            }
//        }
//        elseif(empty($cart->size_option) && empty($cart->weight_option) && !empty($cart->qty_option)){
//            $qty = $cart->product->product_properties()->where('name','=','qty')
//                ->where('description', $cart->qty_option)
//                ->first();
//
//            if(empty($qty)){
//                return response()->json([
//                    'success'       => false,
//                    'exception'     => 'property'
//                ]);
//            }
//
//            if(!empty($qty->price)){
//                $price = $qty->getOriginal('price');
//            }
//        }
//        else{
//            $price = $cart->product->getOriginal('price_discounted');
//        }
//
//        $newSinglePrice = $quantity * $price;
//        $newSinglePriceFormated = number_format($newSinglePrice, 0, ",", ".");
//
//        $cart->quantity = $quantity;
//        $cart->total_price = $newSinglePrice;
//        $cart->save();
//
//        $totalPriceTem = Cart::where('user_id', 'like', $userId)->sum('total_price');
//        $newTotalPriceFormated = number_format($totalPriceTem, 0, ",", ".");
//
//        //edit session data
//        $carts = Cart::where('user_id', 'like', $userId)->get();
//        $cartTotal = $carts->count();
//        Session::put('cartList', $carts);
//        Session::put('cartTotal', $cartTotal);
//
//        return response()->json([
//            'success'       => true,
//            'totalPrice'    => $newTotalPriceFormated,
//            'singlePrice'   => $newSinglePriceFormated
//        ]);
////        return ['totalPrice' => $newTotalPriceFormated,'singlePrice' => $newSinglePriceFormated];
//    }
//
//    public function getNotes($id){
//        $cart = Cart::find($id);
//
//        $notes = "default";
//        if(!empty($cart->buyer_note)) $notes = $cart->buyer_note;
//
//        return response()->json([
//            'success'   => true,
//            'notes'     => $notes
//        ]);
//    }

//    public function checkNoteForCartAdd(Request $request){
//        try{
//            if (!Auth::check()){
//                return response()->json(['success' => false, 'error' => 'login']);
//            }
//
//            $user = Auth::user();
//            $userId = $user->id;
//
//            $product = Product::find(Input::get('product_id'));
//
//            $notes = "default";
//
//            $note = "";
//            if(!empty(Input::get('color'))){
//                $color = ProductProperty::find(Input::get('color'));
//                $note .= 'color='. $color->description. ';';
//            }
//
//            $carts = Cart::where([['user_id', '=', $userId], ['product_id', '=', $product->id]])->get()->count();
//
//            if($carts > 0){
//                $cart = Cart::where('user_id', $userId)->where('product_id', $product->id);
//                $isExist = false;
//
//                // Get size selection
//                if(!empty(Input::get('size')) && Input::get('size') != '0'){
//                    $size = $product->product_properties()->where('id', Input::get('size'))
//                        ->first();
//                    $cart = $cart->where('size_option', $size->description)->first();
//
//                    // Check if cart has the same selected product property or not
//                    if(!empty($cart)){
//                        $notes = $cart->buyer_note;
//                    }
//                }
//                // Get weight selection
//                elseif(!empty(Input::get('weight')) && Input::get('weight') != '0'){
//                    $weight = $product->product_properties()->where('id', Input::get('weight'))
//                        ->first();
//                    $cart = $cart->where('weight_option', $weight->description)->first();
//
//                    // Check if cart has the same selected product property or not
//                    if(!empty($cart)){
//                        $notes = $cart->buyer_note;
//                    }
//                }
//                // Get qty selection
//                elseif(!empty(Input::get('qty')) && Input::get('qty') != '0'){
//                    $qty = $product->product_properties()->where('id', Input::get('qty'))
//                        ->first();
//                    $cart = $cart->where('qty_option', $qty->description)->first();
//
//                    // Check if cart has the same selected product property or not
//                    if(!empty($cart)){
//                        $notes = $cart->buyer_note;
//                    }
//                }
//                else{
//                    $cart = $cart->whereNull('weight_option')
//                        ->whereNull('size_option')
//                        ->whereNull('qty_option')
//                        ->first();
//
//                    // Check if cart does not have any selected product property or not
//                    if(!empty($cart)){
//                        $notes = $cart->buyer_note;
//                    }
//                }
//            }
//
//            return response()->json([
//                'success'   => true,
//                'notes'     => $notes
//            ]);
//        }
//        catch(\Exception $ex){
//            Utilities::ExceptionLog($ex);
//            return response()->json([
//                'success'   => true,
//                'error'     => 'exception'
//            ]);
//        }
//
//    }

//    public function storeNotes(Request $request){
//        $cart = Cart::find(Input::get('cart_id'));
//        $cart->buyer_note = Input::get('buyer_note');
//        $cart->save();
//
//        return redirect()->route('cart-list');
//    }
}