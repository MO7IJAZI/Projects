<?php

namespace App\Http\Controllers;

use App\Models\Ads;
use App\Traits\GeneralTrait;

class LikeController extends Controller
{
    use GeneralTrait;
    public function store($id)
    {
        $user_id=auth('api_guest')->user()->id;
        $ads=Ads::find($id);
        if(!$ads)
             return $this->returnError(404,'Not found');
        if($ads->likes()->where('guest_id',$user_id)->exists()){
            $ads->likes()->where('guest_id',$user_id)->delete();
        }

        else{
            $ads->likes()->create([
                'guest_id'=>$user_id
            ]);
        }
        $ads->loadMissing('likes');

        return $this->returnData($ads,'operation completed successfully');

    }

}
