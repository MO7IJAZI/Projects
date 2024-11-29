<?php

namespace App\Http\Controllers;

use App\Models\Ads;
use App\Models\Line;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdsController extends Controller
{
    use GeneralTrait;
    private $uploadPath = "assets/images/ads";


    public function index()
    {
        try {
            $ads = Ads::all();
            if(count($ads)>0)
                $ads->loadMissing('user');
            return $this->returnData($ads, 'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, "Please try again later");
        }
    }



    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'description'=>'required',
                'image'=>'required|image|mimes:jpeg,jpg,png,gif'
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }
            $user = auth('api')->user();

            $image = $this->saveImage($request->image, $this->uploadPath);


            $ads =$user->ads()->create([
                'description' => $request->description,
                'image'=>$image
            ]);
            $ads->loadMissing('user');


            DB::commit();
            return $this->returnData($ads, 'operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError(500, 'Please try again later');
        }
    }



    public function getById($id)
    {
        try {
            $data=Ads::find($id);
            if (!$data) {
                return $this->returnError(404, "Not found");
            }
            $data->increment('views');
            $data->loadMissing('user');
            return $this->returnData($data, 'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }



    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'description'=>'sometimes',
                'image'=>'sometimes|image|mimes:jpeg,jpg,png,gif'
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }
            $user = auth('api')->user();

            $ads=$user->ads()->find($id);

            if (!$ads)
                return $this->returnError(404, 'ads not found');
            $image=null;
            if(isset($request->image))
            {
                $image = $this->saveImage($request->image, $this->uploadPath);
            }

            $ads->update([
                'description' => isset($request->description) ? $request->description : $ads->description,
                'image'       => isset($request->image) ? $image : $ads->image
            ]);
            $ads->loadMissing('user');

            DB::commit();
            return $this->returnData($ads, 'operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError(500, 'Please try again later');
        }
    }



    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $user = auth('api')->user();

            $ads = $user->ads()->find($id);
            if (!$ads)
                return $this->returnError(404, 'not found');

            $ads->delete();
            DB::commit();
            return $this->returnSuccessMessage('operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError(500, 'Please try again later');
        }
    }


}
