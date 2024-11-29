<?php

namespace App\Http\Controllers;

use App\Models\Garage;
use App\Models\Line;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LineController extends Controller
{
    use GeneralTrait;

    public function create(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'user_id'=>'required|integer',
                'garage_id'=>'required|integer',
                'num_hours'=>'required|integer',
                'start'=>'required|string',
                'end'=>'required|string',
                'number_public'=>'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }
            $manager=auth('api')->user();
            $garage_manager = $manager->garage()->first();
            if (!$garage_manager)
                return $this->returnError(403, 'you can not do it');

            $user=User::find($request->user_id);
            if(!$user)
                return $this->returnError(404,'user not found');

            $garage=Garage::find($request->garage_id);
            if(!$garage)
                return $this->returnError(404,'garage not found');

            if($garage->id!=$garage_manager->id)
                return $this->returnError(403,'you can not do it');


            $data = Line::create([
                'name'=>$request->name,
                'user_id'=>$request->user_id,
                'garage_id'=>$request->garage_id,
                'num_hours'=>$request->num_hours,
                'end'=>$request->end,
                'start'=>$request->start,
                'number_public'=>$request->number_public
            ]);
            $data->loadMissing(['user','garage']);
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
                'user_id'=>'sometimes|integer',
                'garage_id'=>'sometimes|integer',
                'num_hours'=>'sometimes|integer',
                'start'=>'sometimes|string',
                'end'=>'sometimes|string',
                'number_public'=>'sometimes|integer',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }
            $line = Line::find($id);
            if (!$line)
                return $this->returnError(404, 'line not found');
            $manager=auth('api')->user();
            $manager_garage=$manager->garage()->first();
            if(!$manager_garage)
                return $this->returnError(403, 'you can not do it');

            if($line->garage_id!=$manager_garage->id)
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
            }

            $line ->update([
                'name' => isset($request->name) ?$request->name:$line->name,
                'user_id'=> isset($request->user_id) ?$request->user_id:$line->user_id,
                'garage_id'=> isset($request->garage_id) ?$request->garage_id:$line->garage_id,
                'num_hours'=> isset($request->num_hours) ?$request->num_hours:$line->num_hours,
                'end'=> isset($request->end) ?$request->end:$line->end,
                'start'=> isset($request->start) ?$request->start:$line->start,
                'number_public'=>isset($request->number_public) ?$request->number_public:$line->number_public,
            ]);
            $line->loadMissing(['user','garage']);

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

            $line = Line::find($id);
            if (!$line)
                return $this->returnError(404, 'line not found');
            $manager=auth('api')->user();
            $manager_garage=$manager->garage()->first();
            if(!$manager_garage)
                return $this->returnError(403, 'you can not do it');

            if($line->garage_id!=$manager_garage->id)
                return $this->returnError(403, 'you can not do it');

            $line->delete();
            return $this->returnSuccessMessage('operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }
    public function getAll(Request $request)
    {
        try {
            $data = Line::filter($request)->get();;
            if(count($data)>0)
                $data->loadMissing(['user','garage']);
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }

    public function getById($id)
    {
        try {
            $data=Line::find($id);
            if (!$data) {
                return $this->returnError(404,'Not found');
            }
            $data->loadMissing(['user','garage']);
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');

        }
    }
}
