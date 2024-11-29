<?php

namespace App\Http\Controllers;

use App\Models\Garage;
use App\Models\PrivateTransportation;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PrivateTransportationController extends Controller
{
    use GeneralTrait;
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'number' => 'required|integer',
                'user_id'=>'required|integer',
                'garage_id'=>'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }
            $manager=auth('api')->user();
            $garage_manager=$manager->garage()->first();
            if(!$garage_manager)
                return $this->returnError(403,'you can not do it');
            $garage=Garage::find($request->garage_id);
            if(!$garage)
                return $this->returnError(404,'garage not found');

            if($garage->id!=$garage_manager->id)
                return $this->returnError(403,'you can not do it');
            $user=User::find($request->user_id);
            if(!$user)
                return $this->returnError(404,'user not found');

            $data = PrivateTransportation::create([
                'number'=>$request->number,
                'user_id'=>$request->user_id,
            ]);
            $data->garages()->attach($garage->id);
            $data->loadMissing(['user','garages']);
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
                'number' => 'sometimes|integer',
                'user_id'=>'sometimes|integer',
                'garage_id'=>'sometimes|integer',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }
            $privateTransportation = PrivateTransportation::find($id);
            if (!$privateTransportation)
                return $this->returnError(404, 'private Transportation not found');

            $user=auth('api')->user();
            $manager_garage=$user->garage()->first();
            if(!$manager_garage)
                return $this->returnError(403, 'you can not do it');
            $is=$privateTransportation->whereHas('garages',function($query) use($manager_garage){
                $query->where('garages.id',$manager_garage->id);
            })->first();

            if(!$is)
                return $this->returnError(403, 'you can not do it');

            if(isset($request->user_id)) {
                $user = User::find($request->user_id);
                if (!$user)
                    return $this->returnError(404, 'user not found');
            }
            if(isset($request->garage_id)) {
                $garage = Garage::find($request->garage_id);
                if (!$garage)
                    return $this->returnError(404, 'garage not found');
                if($manager_garage->id!=$request->garage_id)
                    return $this->returnError(403, 'you can not do it');
                $privateTransportation->garages()->attach($garage->id);
            }

            $privateTransportation ->update([
                'number' => isset($request->number) ?$request->number:$privateTransportation->number,
                'user_id'=> isset($request->user_id) ?$request->user_id:$privateTransportation->user_id,
            ]);
            $privateTransportation->loadMissing(['user','garages']);

            DB::commit();
            return $this->returnData($privateTransportation,'operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError(500, 'Please try again later');
        }
    }



    public function delete($id)
    {
        try {
            $data = PrivateTransportation::find($id);
            if (!$data)
                return $this->returnError(404,'Not found');
            $user=auth('api')->user();
            $manager_garage=$user->garage()->first();
            if(!$manager_garage)
                return $this->returnError(403, 'you can not do it');

            $is=$data->whereHas('garages',function($query) use($manager_garage){
                $query->where('garages.id',$manager_garage->id);
            })->first();
            if($is)
                return $this->returnError(403, 'you can not do it');

            $data->delete();
            return $this->returnSuccessMessage('operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }

    public function delete_private_from_garage($id,Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'garage_id'=>'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }
            $data = PrivateTransportation::find($id);
            if (!$data)
                return $this->returnError(404,'Not found');

            $user=auth('api')->user();
            $manager_garage=$user->garage()->first();
            if(!$manager_garage)
                return $this->returnError(403, 'you can not do it');

            $garage = Garage::find($request->garage_id);
            if (!$garage)
                return $this->returnError(404,'garage Not found');

            $is=$data->whereHas('garages',function($query) use($request){
                $query->where('garages.id',$request->garage_id);
            })->first();
            if(!$is || $manager_garage->id!=$request->garage_id)
                return $this->returnError(403, 'you can not do it');

            $garage->private_transportations()->detach([
                $request->garage_id
            ]);
            return $this->returnSuccessMessage('operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }

    public function getAll(Request $request)
    {
        try {
            $data = PrivateTransportation::filter($request)->get();;
            if(count($data)>0)
               $data->loadMissing(['user','garages']);
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }

    public function getById($id)
    {
        try {
            $data=PrivateTransportation::find($id);
            if (!$data) {
                return $this->returnError(404,'Not found');
            }
            $data->loadMissing(['user','garages']);
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');

        }
    }
}
