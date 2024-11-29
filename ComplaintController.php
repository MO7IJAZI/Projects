<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Garage;
use App\Models\Trip;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ComplaintController extends Controller
{
    use GeneralTrait;
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'reason' => 'required',
                'garage_id'=>'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }
            $user=auth('api_guest')->user();
            $trip=Garage::find($request->garage_id);
            if(!$trip)
                return $this->returnError(404,'garage not found');

            $data = $user->complaints()->create([
                'reason'=>$request->reason,
                'garage_id'=>$request->garage_id,
            ]);
            $data->loadMissing(['garage','guest']);
            DB::commit();
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError($ex->getCode(),'Please try again later');
        }
    }


    public function update($id,Request $request)
    {
        try {
            $user=auth('api_guest')->user();
            $data=$user->complaints()->find($id);
            if (!$data)
                return $this->returnError(404, 'complaint not found');
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'reason' => 'sometimes',
                'garage_id'=>'sometimes|integer',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }

            if(isset($request->trip_id)) {
                $garage = Garage::find($request->garage_id);
                if (!$garage)
                    return $this->returnError(404, 'garage not found');
            }

            $data ->update([
                'reason' => isset($request->reason) ?$request->reason:$data->reason,
                'garage_id'=> isset($request->garage_id) ?$request->garage_id:$data->garage_id,
            ]);
            $data->loadMissing(['user','garage']);

            DB::commit();
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError(500, 'Please try again later');
        }
    }



    public function delete($id)
    {
        try {
            $user=auth('api_guest')->user();
            $data = $user->complaints()->find($id);
            if (!$data)
                return $this->returnError(404,'Not found');

            $data->delete();
            return $this->returnSuccessMessage('operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }
    public function getAll()
    {
        try {
            $user=auth('api')->user();
            $manager_garage=$user->garage()->first();
            $data=[];
            if($manager_garage) {
                $data = Complaint::where('garage_id', $manager_garage->id)->get();
                if (count($data) > 0)
                    $data->loadMissing(['guest', 'garage']);
            }
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }

    public function getById($id)
    {
        try {
            $user=auth('api')->user();
            $manager_garage=$user->garage()->first();
            $data=[];
            if($manager_garage) {
                $data = Complaint::where('id',$id)->where('id',$manager_garage->id)->first();
                if (!$data) {
                    return $this->returnError(404, 'Not found');
                }
            }
            $data->loadMissing(['guest','garage']);
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');

        }
    }

    public function getAllMe()
    {
        try {
            $user=auth('api_guest')->user();
            $data =$user->complaints()->get();
            if(count($data)>0)
                $data->loadMissing(['guest','garage']);
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }

    public function getByIdMe($id)
    {
        try {
            $user=auth('api_guest')->user();
            $data=$user->complaints()->find($id);
            if (!$data) {
                return $this->returnError(404,'Not found');
            }
            $data->loadMissing(['guest','garage']);
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');

        }
    }
}
