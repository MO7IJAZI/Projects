<?php

namespace App\Http\Controllers;

use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use App\Http\Requests\PermissionRequest;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use GeneralTrait;

    public function getAll()
    {
        try {
            $data = Permission::all();
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
                 return $this->returnError($ex->getCode(),'Please try again later');
        }
    }

    public function getById($id)
    {
        try {
            $data = Permission::find($id);
            if (!$data)
                return $this->returnError("401",'Not found');

            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
                 return $this->returnError($ex->getCode(),'Please try again later');
        }
    }


    public function update($id,PermissionRequest $request)
    {
        try {
            $data = Permission::find($id);
            if (!$data)
                return $this->returnError("401",'Not found');

            $data->update([
                'name'=>$request->name,
            ]);

            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(),'Please try again later');
        }
    }

    public function create(PermissionRequest $request)
    {
        try {

            $data=Permission::create([
                'name'=>$request->name,
            ]);
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError($ex->getCode(),'Please try again later');
        }
    }

}
