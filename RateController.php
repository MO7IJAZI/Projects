<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use App\Models\Trip;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RateController extends Controller
{
    use GeneralTrait;
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'rate' => 'required|integer|max:5',
                'trip_id'=>'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }

            $trip=Trip::find($request->trip_id);
            if(!$trip)
                return $this->returnError(404,'trip not found');

            $user=auth('api_guest')->user();
            $exist=$user->bookings()->where('trip_id',$trip->id)->exists();
            if(!$exist)
                return $this->returnError(403,'you can not do it');

            $data = $user->rates()->create([
                'rate'=>$request->rate,
                'trip_id'=>$request->trip_id
            ]);
            $data->loadMissing(['guest','trip']);
            DB::commit();
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError(500, 'Please try again later');
        }
    }


    public function update($id,Request $request)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'rate' => 'required|integer|max:5',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }
            $user=auth('api_guest')->user();
            $rate =$user->rates()->find($id);
            if (!$rate)
                return $this->returnError(404, 'rate not found');
            $rate ->update([
                'rate' => isset($request->rate) ?$request->rate:$rate->rate,
            ]);
            $rate->loadMissing(['guest','trip']);

            DB::commit();
            return $this->returnData($rate,'operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError(500, 'Please try again later');
        }
    }



    public function delete($id)
    {
        try {
            $user=auth()->user();
            $data =$user->rates()->find($id);
            if (!$data)
                return $this->returnError(404,'rate Not found');

            $data->delete();
            return $this->returnSuccessMessage('operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }
    public function getAll()
    {
        try {
            $data = Rate::all();
            if(count($data)>0)
                $data->loadMissing(['guest','trip']);
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }

    public function getById($id)
    {
        try {
            $data=Rate::find($id);
            if (!$data) {
                return $this->returnError(404,'Not found');
            }
            $data->loadMissing(['guest','trip']);
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');

        }
    }
}
