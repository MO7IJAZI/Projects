<?php

namespace App\Http\Controllers;

use App\Models\RequestWallet;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RequestWalletController extends Controller
{

    use GeneralTrait;

    private $uploadPath = "assets/images/requests_wallet";

    public function get_request_wallet()
    {
        try {
            DB::beginTransaction();
            $request_wallet = RequestWallet::where('type', 'charge')
                ->get();
            if(count($request_wallet)>0)
                $request_wallet->loadMissing('wallet.guest');
            DB::commit();
            return $this->returnData($request_wallet, 'operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError(500, 'Please try again later');
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'image_transactions' => 'required|image|mimes:jpeg,jpg,png,gif',
                'type' => 'required|string',
                'amount' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }

            $image_transactions = $this->saveImage($request->image_transactions, $this->uploadPath);

            $wallet = auth('api_guest')->user()->wallet()->first();

            $request_wallet = $wallet->requests_wallet()->create([
                'amount' =>$request->amount,
                'type' =>$request->type,
                'image_transactions' => $image_transactions,

            ]);
            $request_wallet->loadMissing('wallet');
            DB::commit();
            return $this->returnData($request_wallet, 'operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError(500, 'Please try again later');
        }
    }

    public function get_my_request()
    {
        try {
            DB::beginTransaction();
            $wallet= auth('api_guest')->user()->wallet()->first();
            $data =$wallet->requests_wallet()->get();
            DB::commit();
            return $this->returnData($data, 'operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError(500, 'Please try again later');
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $request_wallet = RequestWallet::find($id);
            if (!$request_wallet) {
                return $this->returnError(404, 'not found');
            }
            $request_wallet->delete();
            DB::commit();
            return $this->returnSuccessMessage('operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError(500, 'Please try again later');
        }
    }


    public function accept_request_wallet($id)
    {
        try {
            DB::beginTransaction();
            $request_wallet = RequestWallet::find($id);
            $wallet=$request_wallet->wallet()->first();
            if (!$request_wallet) {
                return $this->returnError(404, 'not found');
            }
            $request_wallet->wallet()->update([
                'value' => $request_wallet->amount + $wallet->value,
            ]);
            $request_wallet->delete();
            DB::commit();
            return $this->returnSuccessMessage('operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError(500, 'Please try again later');
        }
    }
}
