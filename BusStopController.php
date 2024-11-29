<?php

namespace App\Http\Controllers;

use App\Models\BusStop;
use App\Models\Line;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BusStopController extends Controller
{
    use GeneralTrait;
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'line_id'=>'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }
            $line=Line::find($request->line_id);
            if(!$line)
                return $this->returnError(404,'line not found');
            $user=auth()->user();
            $monitor_line=$user->line()->first();
            if(!$monitor_line)
                return $this->returnError(403,'you can not do it');
            if($monitor_line->id!=$line->id)
                return $this->returnError(403,'you can not do it');

            $data = $line->busstops()->create([
                'name'=>$request->name,
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
                'name' => 'sometimes|string',
                'line_id'=>'sometimes|integer',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }
            $data = BusStop::find($id);
            if (!$data)
                return $this->returnError(404, 'bus stop not found');
            $monitor=auth('api')->user();
            $line_monitor=$monitor->line()->first();
            if(!$line_monitor)
                return $this->returnError(403,'you can not do it');

            if($data->line_id!=$line_monitor->id)
                return $this->returnError(403,'you can not do it');
            if(isset($request->line_id)) {
                $line = Line::find($request->line_id);
                if (!$line)
                    return $this->returnError(404, 'line not found');
            }

            $data ->update([
                'name' => isset($request->name) ?$request->name:$data->name,
                'line_id'=> isset($request->line_id) ?$request->line_id:$data->line_id,
            ]);
            $data->loadMissing('line');

            DB::commit();
            return $this->returnData($line,'operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError(500, 'Please try again later');
        }
    }



    public function delete($id)
    {
        try {
            $data = BusStop::find($id);
            if (!$data)
                return $this->returnError(404,'Not found');
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
    public function getAll()
    {
        try {
            $data = BusStop::all();
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
            $data=BusStop::find($id);
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
