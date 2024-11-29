<?php

namespace App\Http\Controllers;

use App\Models\Line;
use App\Models\PublicTransportation;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PublicTransportationController extends Controller
{
    use GeneralTrait;
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'price' => 'required|numeric',
                'category' => 'required|string',
                'type' => 'required|string',
                'user_id'=>'required|integer',
                'line_id'=>'required|integer',
                'number'=>'required|integer',
                'description'=>'required'
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }

            $monitor=auth('api')->user();
            $line_monitor = $monitor->line()->first();
            if (!$line_monitor)
                return $this->returnError(403, 'you can not do it');
            $user=User::find($request->user_id);
            if(!$user)
                return $this->returnError(404,'user not found');
            $line=Line::find($request->line_id);
            if(!$line)
                return $this->returnError(404,'line not found');

            if($line->id!=$line_monitor->id)
                return $this->returnError(403,'you can not do it');

            $data = PublicTransportation::create([
                'price'=>$request->price,
                'category'=>$request->category,
                'type'=>$request->type,
                'user_id'=>$request->user_id,
                'line_id'=>$request->line_id,
                'number'=>$request->number,
                'description'=>$request->description
            ]);
            $data->loadMissing(['user','line']);
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
                'price' => 'sometimes|numeric',
                'category' => 'sometimes|string',
                'type' => 'sometimes|string',
                'user_id'=>'sometimes|integer',
                'line_id'=>'sometimes|integer',
                'number'=>'sometimes|integer',
                'description'=>'sometimes'
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }

            $publicTransportation = PublicTransportation::find($id);
            if (!$publicTransportation)
                return $this->returnError(404, 'public Transportation not found');

            $monitor=auth('api')->user();
            $line_monitor = $monitor->line()->first();
            if (!$line_monitor)
                return $this->returnError(403, 'you can not do it');

            if($publicTransportation->line_id!=$line_monitor->id)
                return $this->returnError(403, 'you can not do it');

            if(isset($request->user_id)) {
                $user = User::find($request->user_id);
                if (!$user)
                    return $this->returnError(404, 'user not found');
            }
            if(isset($request->user_id)) {
                $line = Line::find($request->line_id);
                if (!$line)
                    return $this->returnError(404, 'line not found');
            }


            $publicTransportation ->update([
                'type' => isset($request->type) ?$request->type:$publicTransportation->type,
                'category' => isset($request->category) ?$request->category:$publicTransportation->category,
                'price' => isset($request->price) ?$request->price:$publicTransportation->price,
                'user_id'=> isset($request->user_id) ?$request->user_id:$publicTransportation->user_id,
                'line_id'=> isset($request->line_id) ?$request->line_id:$publicTransportation->line_id,
                'number'=> isset($request->number) ?$request->number:$publicTransportation->number,
                'description'=> isset($request->description) ?$request->description:$publicTransportation->description,
            ]);
            $publicTransportation->loadMissing(['user','line']);
            DB::commit();
            return $this->returnData($publicTransportation,'operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError(500, 'Please try again later');
        }
    }



    public function delete($id)
    {
        try {
            $data = PublicTransportation::find($id);

            if (!$data)
                return $this->returnError(404,'Not found');
            $monitor=auth('api')->user();
            $line_monitor = $monitor->line()->first();
            if (!$line_monitor)
                return $this->returnError(403, 'you can not do it');
            if($data->line_id!=$line_monitor->id)
                return $this->returnError(403, 'you can not do it');

            $data->delete();
            return $this->returnSuccessMessage('operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }
    public function getAll(Request $request)
    {
        try {
            $data = PublicTransportation::filter($request)->get();;
            if(count($data)>0)
                $data->loadMissing(['user','line']);
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }

    public function getById($id)
    {
        try {
            $data=PublicTransportation::find($id);
            if (!$data) {
                return $this->returnError(404,'Not found');
            }
            $data->loadMissing(['user','line']);
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');

        }
    }
}
