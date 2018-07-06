<?php
/**
 * Created by PhpStorm.
 * User: GMG-Developer
 * Date: 28/06/2018
 * Time: 9:07
 */

namespace App\Http\Controllers\Frontend;


use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\General;
use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\PackageTrip;
use App\Models\Province;
use App\Models\Travelmate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Intervention\Image\Facades\Image;
use Webpatser\Uuid\Uuid;

class TravelmateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:travelmates', ['except' => ['showById']]);
    }

    //
    public function show(){
        $user = \Auth::guard('travelmates')->user();
        if(!empty($user->id_card) && empty($user->passport_no)){
            $identity = 'ID CARD';
        }
        elseif(empty($user->id_card) && !empty($user->passport_no)){
            $identity = 'PASSPORT';
        }
        else{
            $identity = '-';
        }

        $data = [
            'user'      => $user,
            'identity'  => $identity
        ];

        return View('frontend.travelmate.show')->with($data);
    }
    //
    public function showById($id){
        $user = Travelmate::find($id);
        if(!empty($user->id_card) && empty($user->passport_no)){
            $identity = 'ID CARD';
        }
        elseif(empty($user->id_card) && !empty($user->passport_no)){
            $identity = 'PASSPORT';
        }
        else{
            $identity = '-';
        }

        $data = [
            'user'      => $user,
            'identity'  => $identity
        ];

        return View('frontend.travelmate.show')->with($data);
    }

    public function edit(){
        $user = \Auth::guard('travelmates')->user();
        if(!empty($user->id_card) && empty($user->passport_no)){
            $identity = 'ID CARD';
        }
        elseif(empty($user->id_card) && !empty($user->passport_no)){
            $identity = 'PASSPORT';
        }
        else{
            $identity = 'none';
        }

        $data = [
            'user'      => $user,
            'identity'  => $identity
        ];

        return View('frontend.travelmate.profile-edit')->with($data);
    }

    public function updateImage(Request $request){
        try{
            $img = Image::make($request->file('image'));

            // Get image extension
            $extStr = $img->mime();
            $ext = explode('/', $extStr, 2);

            $user = \Auth::guard('travelmates')->user();

            $filename = 'travelmate_'. $user->id.'_'. Carbon::now('Asia/Jakarta')->format('Ymdhms'). '_0.'. $ext[1];

            $img->save(public_path('storage/profile/'. $filename), 75);

            $userObj = Travelmate::find($user->id);
            $oldImage = $userObj->profile_picture;
            $userObj->profile_picture = $filename;
            $userObj->save();

            // Delete old image
            if($oldImage !== 'default.png'){
                $deletedPath = public_path('storage/profile/'. $oldImage);
                if(file_exists($deletedPath)) unlink($deletedPath);
            }

            return response()->json([
                'append'    => true
            ]);
        }
        catch (\Exception $ex){
            error_log($ex);
        }
    }

    public function update(Request $request, Travelmate $user){
        $validator = Validator::make($request->all(), [
            'fname'             => 'required|max:50',
            'lname'             => 'required|max:50',
            'about_me'          => 'max:400',
            'phone'             => 'max:20',
            'nationality'       => 'max:20',
            'idcard-value'      => 'max:50',
            'passport-value'    => 'max:50',
            'language'          => 'max:20',
            'interest'          => 'max:50',
            'youtube'           => 'max:100'
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator->errors());

        // Validate Identity No
        if(Input::get('identity') === 'idcard' && empty(Input::get('idcard-value'))){
            return redirect()->back()->withErrors('ID CARD is required!', 'default')->withInput($request->all());
        }

        if(Input::get('identity') === 'passport' && empty(Input::get('passport-value'))){
            return redirect()->back()->withErrors('PASSPORT is required!', 'default')->withInput($request->all());
        }

        $user->first_name = Input::get('fname');
        $user->last_name = Input::get('lname');
        $user->about_me = Input::get('about_me');
        $user->phone = Input::get('phone');
        $user->nationality = Input::get('nationality');
        $user->speaking_language = Input::get('language');
        $user->travel_interest = Input::get('interest');

        if(Input::get('identity') === 'idcard'){
            $user->id_card = Input::get('idcard-value');
            $user->passport_no = null;
        }
        else{
            $user->id_card = null;
            $user->passport_no = Input::get('passport-value');
        }

        $user->save();

        Session::flash('message', 'Profile Updated!');

        return redirect()->route('travelmate.profile.show');
    }

    public function packages(){
        try{
            $packages = Package::orderBy('created_at', 'desc')->paginate(20);

            $data = [
                'packages'      => $packages
            ];

            return view('frontend.travelmate.packages.index')->with($data);
        }
        catch(\Exception $ex){
            error_log($ex);
        }
    }

    public function myTrips(){
        try{
            $filter = 0;
            $status = request()->status;
            if(!empty($status)){
                $filter = $status;
            }

            $packages = Package::orderBy('created_at', 'desc')->paginate(20);

            $data = [
                'packages'      => $packages,
                'filter'      => $filter
            ];
//            dd($data);
            return view('frontend.travelmate.my-trips')->with($data);
        }
        catch(\Exception $ex){
            error_log($ex);
        }
    }

    public function createPackage(){
        $provinces = Province::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $view = View::make('frontend.travelmate.partials._trip_destination');
        $content = (string) $view;

        $data = [
            'provinces'     => $provinces,
            'categories'    => $categories,
            'content'       => $content
        ];

        return view('frontend.travelmate.packages.create')->with($data);
    }

    public function showPackage($id){
        $package = Package::find($id);
        $packagePrices = $package->package_prices;
        $packageTrips = $package->package_trips;

        $data = [
            'package'       => $package,
            'packagePrices' => $packagePrices,
            'packageTrips'  => $packageTrips
        ];

        return view('frontend.travelmate.packages.show')->with($data);
    }

    public function storePackage(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name'             => 'required',
                'description'             => 'required',
                'start_date'             => 'required',
                'end_date'          => 'required',
                'meeting_point'             => 'required',
                'max_capacity'             => 'required'
            ]);
            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput();

            if (Input::get('province') == "-1") {
                return back()->withErrors("The province is required")->withInput();
            }

            if (Input::get('city') == "-1") {
                return back()->withErrors("The city is required")->withInput();
            }

//            dd($request);
            $tripStartDates = Input::get('trip_start_date');
            $tripEndDates = Input::get('trip_end_date');
            $tripImages = $request->file('trip_image');
            $tripDescriptions = Input::get('trip_description');

//            dd($tripImages);
            $isNullTripStartDates = in_array(null, $tripStartDates, true);
            $isNullTripEndDates = in_array(null, $tripEndDates, true);
            $isNullTripImages = in_array(null, $tripImages, true);
            $isNullTripDescriptions = in_array(null, $tripDescriptions, true);

            if($isNullTripStartDates && $isNullTripEndDates && $isNullTripImages && $isNullTripDescriptions){
                return back()->withErrors("All Trip field required")->withInput();
            }

            $pricingQuantities = Input::get('qty');
            $pricingPrice = Input::get('price');
            $isNullPricingQuantities = in_array(null, $pricingQuantities, true);
            $isNullPricingPrice = in_array(null, $pricingPrice, true);

            if($isNullPricingQuantities && $isNullPricingPrice){
                return back()->withErrors("All Pricing field required")->withInput();
            }
            $user = \Auth::guard('travelmates')->user();

            $packageID = Uuid::generate();

//            $startDateTrip = Carbon::createFromFormat('d M Y H:i', $tripStartDates[0], 'Asia/Jakarta');
//            dd($startDateTrip);
            DB::transaction(function() use ($request, $packageID, $user, $tripStartDates,
                $tripEndDates, $tripImages, $tripDescriptions, $pricingQuantities, $pricingPrice) {

                $startDate = Carbon::createFromFormat('d M Y', Input::get('start_date'), 'Asia/Jakarta');
                $endDate = Carbon::createFromFormat('d M Y', Input::get('end_date'), 'Asia/Jakarta');
                $dateTimeNow = Carbon::now('Asia/Jakarta');
                $newPackage = Package::create([
                    'id' =>$packageID,
                    'travelmate_id' => $user->id,
                    'name' => Input::get('name'),
                    'category_id' => Input::get('category'),
                    'province_id' => Input::get('province'),
                    'city_id' => Input::get('city'),
                    'description' => Input::get('description'),
                    'meeting_point' => Input::get('meeting_point'),
                    'max_capacity' => Input::get('max_capacity'),
                    'price' => min($pricingPrice),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status_id' => 1,
                    'created_at'        => $dateTimeNow->toDateTimeString()
                ]);

                $img = Image::make($request->file('cover'));
                $extStr = $img->mime();
                $ext = explode('/', $extStr, 2);

                $filename = $packageID.'_featured_'.Carbon::now('Asia/Jakarta')->format('Ymdhms'). '.'. $ext[1];

                $img->save(public_path('storage/package_image/'. $filename), 75);
                $newPackage->featured_image = $filename;
                $newPackage->save();

                //package trips
                for($i=0;$i<sizeof($tripDescriptions);$i++){

                    $startDateTrip = Carbon::createFromFormat('d M Y H:i', $tripStartDates[$i], 'Asia/Jakarta');
                    $endDateTrip = Carbon::createFromFormat('d M Y H:i', $tripEndDates[$i], 'Asia/Jakarta');
                    $newPackageTrip = PackageTrip::create([
                        'package_id' => $packageID,
                        'start_date' => $startDateTrip,
                        'end_date' => $endDateTrip,
                        'description' => $tripDescriptions[$i]
                    ]);

                    $img = Image::make($tripImages[$i]);
                    $extStr = $img->mime();
                    $ext = explode('/', $extStr, 2);

                    $filename = $packageID.'_trip_'.Carbon::now('Asia/Jakarta')->format('Ymdhms'). '.'. $ext[1];

                    $img->save(public_path('storage/package_image/'. $filename), 75);
                    $newPackageTrip->featured_image = $filename;
                    $newPackageTrip->save();
                }

                //package pricing
                $serviceFee = General::find(1);
                for($i=0;$i<sizeof($pricingQuantities);$i++){
                    $total = $pricingQuantities[$i] * $pricingPrice[$i];
                    $final = $total - ((10/100) * $total);

                    $newPackagePrice = PackagePrice::create([
                        'package_id' => $packageID,
                        'quantity' => $pricingQuantities[$i],
                        'price' => $pricingPrice[$i],
                        'service_fee' => $serviceFee->service_fee,
                        'final_price' => $final
                    ]);

                }
            });
            return redirect()->route('travelmate.packages.index');

        }catch(\Exception $ex){
            error_log($ex);
            return back()->withErrors("Something Went Wrong")->withInput();
        }
    }

    public function editPackageInformation(Package $package){
        $provinces = Province::orderBy('name')->get();
        $cities = City::where('province_id', $package->province_id)->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        $data = [
            'package'       => $package,
            'provinces'     => $provinces,
            'cities'        => $cities,
            'categories'    => $categories
        ];

        return view('frontend.travelmate.packages.edit-info')->with($data);
    }

    public function updatePackageInformation(Package $package, Request $request){

    }

    public function getCities(){
        $provinceId = request()->province;

        $cities = City::where('province_id', $provinceId)->get();

        $returnHtml = View('frontend.travelmate.partials._city_options',['cities' => $cities])->render();

        return response()->json( array('success' => true, 'html' => $returnHtml) );
    }
}