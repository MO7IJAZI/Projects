<?php

namespace App\Http\Controllers;

use App\Models\Line;
use App\Models\Trip;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TripController extends Controller
{
    use GeneralTrait;
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'destination' => 'required|string',
                'type'=>'required|string',
                'date'=>'required|date',
                'price'=>'required|integer',
                'line_id'=>'required|integer',
                'num_passengers'=>'required|integer|gt:0'
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }

            $line=Line::find($request->line_id);
            if (!$line)
                return $this->returnError(404, 'line not found');

            $monitor=auth('api')->user();
            $line_monitor=$monitor->line()->first();

            if(!$line_monitor)
                return $this->returnError(403,'you can not do it');

            if($line->id!=$line_monitor->id)
                return $this->returnError(403,'you can not do it');


            $data = $line->trips()->create([
                'destination'=>$request->destination,
                'date'=>$request->date,
                'type'=>$request->type,
                'price'=>$request->price,
                'num_passengers'=>$request->num_passengers,
            ]);
            $data->loadMissing('line');
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
                'destination' => 'sometimes|string',
                'type'=>'sometimes|string',
                'date'=>'sometimes|date_format:Y-m-d H:i:s',
                'price'=>'sometimes|integer',
                'line_id'=>'sometimes|integer',
                'num_passengers'=>'sometimes|integer|gt:0'

            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }

            $data = Trip::find($id);
            if (!$data)
                return $this->returnError(404, 'trip not found');
            $monitor=auth('api')->user();
            $line_monitor=$monitor->line()->first();
            if(!$line_monitor)
                return $this->returnError(403,'you can not do it');

            if($data->line_id!=$line_monitor->id)
                return $this->returnError(403,'you can not do it');

            $data ->update([
                'destination' => isset($request->destination) ?$request->destination:$data->destination,
                'date'=> isset($request->date) ?$request->date:$data->date,
                'type'=> isset($request->type) ?$request->type:$data->type,
                'price'=> isset($request->price) ?$request->price:$data->price,
                'line_id'=> isset($request->line_id) ?$request->line_id:$data->line_id,
                'num_passengers'=> isset($request->num_passengers) ?$request->num_passengers:$data->num_passengers,
            ]);
            $data->loadMissing('line');

            DB::commit();
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError(500,'Please try again later');
        }
    }



    public function delete($id)
    {
        try {
            $data =Trip::find($id);
            if(!$data)
                return $this->returnError(404,'not found');

            $monitor=auth('api')->user();
            $line_monitor=$monitor->line()->first();
            if(!$line_monitor)
                return $this->returnError(403,'you can not do it');

            if($data->line_id!=$line_monitor->id)
                return $this->returnError(403,'you can not do it');

            $data->delete();
            return $this->returnSuccessMessage('operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }
    public function getAll(Request $request)
    {
        try {
            $data = Trip::filter($request)->get();
            if(count($data)>0)
                $data->loadMissing('line');
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }

    public function getById($id)
    {
        try {
            $data=Trip::find($id);
            if (!$data) {
                return $this->returnError(404,'Not found');
            }
            $data->loadMissing('line');
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');

        }
    }
}
