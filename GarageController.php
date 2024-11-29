<?php

namespace App\Http\Controllers;

use App\Models\Garage;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GarageController extends Controller
{
    use GeneralTrait;
    private $uploadPath = "assets/images/garage";
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'location' => 'required|string',
                'user_id'=>'required|integer',
                'end'=>'required|after:start',
                'start'=>'required|date_format:H:i:s',
                'license'=>'required|integer',
                'image'=>'required|image|mimes:jpeg,jpg,png,gif'
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }
            $user=User::find($request->user_id);
            if(!$user)
                return $this->returnError(404,'user not found');
            $image = $this->saveImage($request->image, $this->uploadPath);

            $data = Garage::create([
                'name'=>$request->name,
                'location' => $request->location,
                'user_id'=>$request->user_id,
                'end' => $request->end,
                'start'=>$request->start,
                'license'=>$request->license,
                'image'=>$image,
            ]);


            $data->loadMissing('user');
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
                'location' => 'sometimes|string',
                'user_id'=>'sometimes|integer',
                'end'=>'sometimes|date_format:H:i:s',
                'start'=>'sometimes|date_format:H:i:s',
                'license'=>'sometimes|integer',
                'image'=>'sometimes|image|mimes:jpeg,jpg,png,gif'
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }
            if(isset($request->user_id)) {
                $user = User::find($request->user_id);
                if (!$user)
                    return $this->returnError(404, 'user not found');
            }

            $garage = Garage::find($id);
            if (!$garage)
                return $this->returnError(404, 'garage not found');
            $image=null;
            if(isset($request->image))
            {
                $image = $this->saveImage($request->image, $this->uploadPath);
            }
            $garage ->update([
                'name' => isset($request->name) ?$request->name:$garage->name,
                'location' => isset($request->location) ?$request->location:$garage->location,
                'user_id'=> isset($request->user_id) ?$request->user_id:$garage->user_id,
                'end'=> isset($request->end) ?$request->end:$garage->end,
                'start'=> isset($request->start) ?$request->start:$garage->start,
                'license'=> isset($request->license) ?$request->license:$garage->license,
                'image'       => isset($request->image) ? $image : $garage->image,
            ]);
            $garage->loadMissing('user');

            DB::commit();
            return $this->returnData($garage,'operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError(500, 'Please try again later');
        }
    }



    public function delete($id)
    {
        try {
            $data = Garage::find($id);
            if (!$data)
                return $this->returnError(404,'Not found');

            $data->delete();
            return $this->returnSuccessMessage('operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }
    public function getAll(Request $request)
    {
        try {
            $data = Garage::filter($request)->get();;
            if(count($data)>0)
                $data->loadMissing('user');
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }

    public function getById($id)
    {
        try {
            $data=Garage::find($id);
            if (!$data) {
                return $this->returnError(404,'Not found');
            }
            $data->loadMissing('user');
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');

        }
    }
}
