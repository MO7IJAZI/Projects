<?php

namespace App\Http\Controllers;

use App\Models\PrivateTransportation;
use App\Models\PublicTransportation;
use App\Models\Tax;
use App\Models\Trip;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TaxController extends Controller
{
    use GeneralTrait;
    public function add_private(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'value'=>'required|numeric',
                'private_transportation_id'=>'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }
            $private_transportation=PrivateTransportation::find($request->private_transportation_id);
            if(!$private_transportation)
                return $this->returnError(404, 'private transportation not found');

            $data = $private_transportation->taxes()->create([
                'value'=>$request->value,
            ]);
            DB::commit();
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError(500,'Please try again later');
        }
    }

    public function add_public(Request $request)
    {
        //try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'value'=>'required|numeric',
                'public_transportation_id'=>'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }
            $public_transportation=PublicTransportation::find($request->public_transportation_id);
            if(!$public_transportation)
                return $this->returnError(404, 'public transportation not found');

            $data = $public_transportation->taxes()->create([
                'value'=>$request->value,
            ]);
            DB::commit();
            return $this->returnData($data,'operation completed successfully');
//        } catch (\Exception $ex) {
//            DB::rollBack();
//            return $this->returnError(500,'Please try again later');
//        }
    }


    public function update($id,Request $request)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'value'=>'required|numeric',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }

            $data = Tax::find($id);
            if (!$data)
                return $this->returnError(404, 'tax not found');
            $data ->update([
                'value' => isset($request->value) ?$request->value:$data->value,
            ]);

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
            $data =Tax::find($id);
            if (!$data)
                return $this->returnError(404,'Tax Not found');

            $data->delete();
            return $this->returnSuccessMessage('operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(),'Please try again later');
        }
    }
    public function getAll()
    {
        try {
            $data = Tax::all();
            if($data)
                $data->loadMissing('transportation');
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(),'Please try again later');
        }
    }

    public function getById($id)
    {
        try {
            $data=Tax::find($id);
            if (!$data) {
                return $this->returnError(404,'Tax Not found');
            }
            $data->loadMissing('transportation');
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(),'Please try again later');

        }
    }
}
