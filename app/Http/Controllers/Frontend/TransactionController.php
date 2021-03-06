<?php
/**
 * Created by PhpStorm.
 * User: yanse
 * Date: 8/31/2017
 * Time: 11:29 AM
 */

namespace App\Http\Controllers\Frontend;

use App\libs\Midtrans;
use App\libs\RajaOngkir;
use App\libs\Utilities;
use App\Http\Controllers\Controller;
use App\Mail\NewBankTransfer;
use App\Mail\NewOrderAdmin;
use App\Mail\NewOrderCustomer;
use App\Models\Address;
use App\Models\Courier;
use App\Models\General;
use App\Models\Package;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionHeader;
use App\Models\TransferConfirmation;
use App\Models\User;
use App\Models\Cart;
use App\Models\DeliveryType;
use App\Models\Voucher;
use App\Notifications\TransactionNotify;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Webpatser\Uuid\Uuid;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function CheckoutProcess(){
        if (Auth::check())
        {

            DB::transaction(function() {
                $userId = Auth::user()->id;

                $carts = Cart::where('user_id', $userId)->get();
                $dateTimeNow = Carbon::now('Asia/Jakarta');
                $transactionID = Uuid::generate();
//            dd($transactionID);
                $packageId = array();
                foreach ($carts as $cart){
                    array_push($packageId, $cart->package_id);
                }
//                $totalPriceTem = Package::wherein('id', $packageId)->sum('price');
                $totalPriceTem = $carts->sum('total_price');
                $transactionHeader = TransactionHeader::Create([
                    'id'       => $transactionID,
                    'user_id'       => $userId,
                    'payment_method_id'       => 1,
                    'total_payment'      => $totalPriceTem,
                    'total_price'    => $totalPriceTem,
                    'admin_fee'    => 0,
                    'status_id'    => 3,
                    'created_at'        => $dateTimeNow->toDateTimeString()
                ]);
//            dd("asdf");
                foreach ($carts as $cart){
                    $detailID = Uuid::generate();
                    $transactionDetail = TransactionDetail::Create([
                        'id'       => $detailID,
                        'user_id'       => $userId,
                        'status_id'    => 13,
                        'header_id'       => $transactionID,
                        'package_id'       => $cart->package_id,
                        'travelmate_id'       => $cart->package->travelmate_id,
                        'price'       => $cart->price,
                        'note'       => $cart->special_request,
                        'selected_date'      => $cart->selected_date,
                        'discount_percent'       => 0,
                        'discount_flat'      => 0,
                        'subtotal'    => $cart->total_price,
                        'updated_at'    => $dateTimeNow->toDateTimeString()
                    ]);

                    //save voucher to DB
                    if(!empty($cart->voucher_code)){
                        $voucherDB = Voucher::where('name', $cart->voucher_code)->first();
                        $price = $cart->package->price;
                        if(!empty($voucherDB->amount)){
                            $transactionDetail->discount_flat = $voucherDB->amount;
                            $transactionDetail->subtotal = $price - $voucherDB->amount;
                            $transactionDetail->save();
                        }
                        else{
                            $transactionDetail->discount_percent = $voucherDB->amount_percentage;
                            $discount = (($voucherDB->amount_percentage * $price) / 100);
                            $transactionDetail->subtotal = $price - $discount;
                            $transactionDetail->save();
                        }
                        $transactionHeader->voucher_id = $voucherDB->id;
                        $transactionHeader->save();

                        //change voucher stock
                    }

                    Cart::where('id', '=', $cart->id)->delete();
                }
            });
            return View('frontend.transactions.payment-result');
        }
        else
        {
            return redirect()->route('login');
        }
    }

    public function Show($id){
        $transactionDetail = TransactionDetail::find($id);
        $package = Package::find($transactionDetail->package_id);

        $packagePrices = $package->package_prices;
        $packageTrips = $package->package_trips;

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


        $data = [
            'transactionDetail' => $transactionDetail,
            'package' => $package,
            'packagePrices' => $packagePrices,
            'packageTrips' => $packageTrips,
            'currencyType' => $currencyType,
            'currencyValue' => $currencyValue
        ];
//        dd($data);
        return View('frontend.transactions.show')->with($data);
    }

    public function CancelBooking(Request $request){
        $validator = Validator::make($request->all(), [
            'request'             => 'required',
            'detail'             => 'required'
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator->errors());

        $detailDB = TransactionDetail::find(Input::get('detail'));
        $detailDB->cancel_note = Input::get('request');
        $detailDB->status_id = 10;
        $detailDB->save();

        return redirect()->route('traveller.transactions', ['flag' => 1]);
    }

    //set address for shipping
    public function CheckoutProcess1(){
//        if (!Auth::check())
//        {
//            return redirect()->route('landing');
//        }
        $id = Auth::user()->id;
        if(!Cart::where('user_id', $id)->exists()){
            return Redirect::route('cart-list');
        }

        $Addressdata = Address::where('user_id', $id)->first();

        return View('frontend.checkout-step1', compact('Addressdata'));
    }


    //show shipping list
    public function CheckoutProcess2(){
        $couriers = Courier::all();
        $deliveryTypes = DeliveryType::all();

        $courierThrow = "";
        $temp = 1;
        //get courier code ex jne:tiki:pos
        foreach($couriers as $courier){
            if($temp < $couriers->count()){
                $courierThrow = $courierThrow.$courier->code.":";
            }
            else{
                $courierThrow = $courierThrow.$courier->code;
            }
            $temp++;
        }
        //address login user
        $id = Auth::user()->id;
        $Addressdata = Address::where('user_id', $id)->first();

        //get product total weight
        $totalWeight = 0;
        $carts = Cart::where('user_id', 'like', $id)->get();
        foreach($carts as $cart){
            if(!empty($cart->weight_option) && empty($cart->qty_option) && empty($cart->size_option)){
                $weight = $cart->product->product_properties()->where('name','=','weight')
                    ->where('description', $cart->weight_option)
                    ->first();

                $totalWeight += (intval($weight->description) * $cart->quantity);
            }
            elseif(empty($cart->weight_option) && !empty($cart->qty_option) && empty($cart->size_option)){
                $qty = $cart->product->product_properties()->where('name','=','qty')
                    ->where('description', $cart->qty_option)
                    ->first();

                $totalWeight += (intval($qty->weight) * $cart->quantity);
            }
            elseif(empty($cart->weight_option) && empty($cart->qty_option) && !empty($cart->size_option)){
                $size = $cart->product->product_properties()->where('name','=','size')
                    ->where('description', $cart->qty_option)
                    ->first();

                if(!empty($size->weight)){
                    $totalWeight += (intval($size->weight) * $cart->quantity);
                }
                else{
                    $totalWeight += ($cart->product->weight * $cart->quantity);
                }
            }
            else{
                $totalWeight += ($cart->product->weight * $cart->quantity);
            }
        }

        $isTesting = false;
        if(Auth::user()->email == 'testing@gmail.com'){
            $isTesting = true;
        }

        //rajaongkir process
        $collect = RajaOngkir::getCost('151', 'city', $Addressdata->city_id, 'city', (string)$totalWeight, $courierThrow);
        $results = $collect->rajaongkir->results;

        $resultCollection = collect();
        foreach ($deliveryTypes as $deliveryType){
            $resultCollection->put($deliveryType->courier->code."-".$deliveryType->code, $deliveryType->courier->code."-".$deliveryType->code);
        }

        foreach($results as $result){
            foreach ($result->costs as $cost){
                if($resultCollection->contains($result->code."-".$cost->service)){
                    $resultCollection[$result->code."-".$cost->service] = $cost->cost[0]->value;
                }

            }
        }

        $data = [
            'resultCollection'      => $resultCollection,
            'deliveryTypes'         => $deliveryTypes,
            'isTesting'             => $isTesting
        ];

        return view('frontend.checkout-step2')->with($data);
    }

    //submit shipping and add data to DB
    public function CheckoutProcess2Submit(Request $request){
        if(empty(Input::get('shippingRadio'))){
            return redirect()->route('checkout2')->withErrors('Select your delivery agent');
        }

        $user = Auth::user();
        $userId = $user->id;

        $selectedShipping   = $request['shippingRadio'];
        $splitedShipping = explode('-', $selectedShipping);

        $carts = Cart::where('user_id', 'like', $userId)->get();
        foreach ($carts as $cart){
            $cart->courier_id = $splitedShipping[0];
            $cart->delivery_type_id = $splitedShipping[1];
            $cart->delivery_fee = $splitedShipping[2];

            $cart->save();
        }
        return redirect()->route('checkout3');
    }

    //checkout item, address, shipping and courier, price
    public function CheckoutProcess3(){
        $user = Auth::user();
        $userId = $user->id;

        //get all item from DB
        $carts = Cart::where('user_id', 'like', $userId)->get();
        $userData = User::where('id', 'like', $userId)->first();
        $userAddress = Address::where('user_id', 'like', $userId)->first();

        $totalPrice = 0;
        $shipping = 0;
        $grandTotal = 0;
        foreach($carts as $cart){
            $totalPriceTem = $cart->getOriginal('total_price');
            $totalPrice +=  $totalPriceTem;
            $shipping = $cart->getOriginal('delivery_fee');

        }
        $grandTotal = $totalPrice + $shipping;

        $totalPrice = number_format($totalPrice, 0, ",", ".");
        $shipping = number_format($shipping, 0, ",", ".");
        $grandTotal = number_format($grandTotal, 0, ",", ".");

        return view('frontend.checkout-step3', compact('carts', 'userData', 'userAddress', 'totalPrice', 'shipping', 'grandTotal'));
    }

    //select payment method
    public function CheckoutProcess4(){
        $user = Auth::user();
        $userId = $user->id;
        $carts = Cart::where('user_id', 'like', $userId)->get();

        $totalPrice = 0;
        $shipping = 0;
        $grandTotal = 0;
        foreach($carts as $cart){
            $totalPriceTem = $cart->getOriginal('total_price');
            $totalPrice +=  $totalPriceTem;
            $shipping = $cart->getOriginal('delivery_fee');

        }
        $grandTotal = $totalPrice + $shipping;

        $totalPrice = number_format($totalPrice, 0, ",", ".");
        $shipping = number_format($shipping, 0, ",", ".");
        $grandTotal = number_format($grandTotal, 0, ",", ".");

        $data = [
            'totalPrice'    => $totalPrice,
            'shipping'      => $shipping,
            'grandTotal'    => $grandTotal,
            'ex'            => request()->ex
        ];

        return view('frontend.checkout-step4')->with($data);
    }

//    //bank transfer
//    public function CheckoutProcessBank(){
//        return view('frontend.checkout-step4-bank');
//    }
//
    //show bank account
    public function CheckoutProcessBankAccount($invoice){
        $data = $invoice;
        return view('frontend.show-account-bank', compact('data'));
    }

    public function CheckoutProcessBank($invoice){
        $trxId = Transaction::where('invoice', $invoice)->first();
        $data = $trxId->id;
        return view('frontend.checkout-step4-bank', compact('data'));
    }

    //bank transfer process
    public function CheckoutProcessBankSubmit(Request $request){
        $user = Auth::user();
        $userId = $user->id;

        $validator = Validator::make($request->all(),[
            'sender_name'                   => 'required',
            'transfer_date'                  => 'required',
            'receiver_bank'                 => 'required',
            'transfer_amount'                => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $id = Uuid::generate();
        $trxId = Input::get('trx_id');

//        dd($trxId);

        $dateTimeNow = Carbon::now('Asia/Jakarta');
        $transferDate = Carbon::createFromFormat('d/m/Y', Input::get('transfer_date'), 'Asia/Jakarta');

        $price = Input::get('transfer_amount');
        $priceDouble = (double) str_replace('.','', $price);

        $transferConfirmation = TransferConfirmation::create([
            'id'                => $id,
            'user_id'           => $userId,
            'transaction_id'    => $trxId,
            'receiver_bank'     => Input::get('receiver_bank'),
            'transfer_amount'   => $priceDouble,
            'sender_name'       => Input::get('sender_name'),
            'transfer_date'     => $transferDate->toDateString(),
            'note'              => Input::get('note'),
            'status_id'         => 3,
            'created_on'        => $dateTimeNow->toDateTimeString(),
            'created_by'        => $userId
        ]);

        $transaction = Transaction::find($trxId);
        $transaction->status_id = 4;
        $transaction->save();

        // Send email
        Mail::to('admin@lowids.com')->send(new NewBankTransfer());

        //return ke page transaction
        return redirect()->route('user-order-list');
    }

    //payment online failed
    public function CheckoutProcessFailed(){

//        $transactionDB = Transaction::where('order_id', '=', '59ba09dc171c4')->first();
//        $userMail = $transactionDB->user;
//
//        $userMail->notify(new TransactionNotify($transactionDB));

//        $userMail = "yansen626@gmail.com";
//        $emailBody = new NewOrderCustomer($transactionDB);
//        Mail::to($userMail)->send($emailBody);

        return view('frontend.checkout-step4-failed');
    }

}