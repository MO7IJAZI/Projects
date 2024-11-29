<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Trip;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookingController2 extends Controller
{
    use GeneralTrait;
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();
            $user=auth('api_guest')->user();

            $validator = Validator::make($request->all(), [
                'trip_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }
            $trip=Trip::find($request->trip_id);
            if(!$trip)
                return $this->returnError(404,'trip not found');
            $exist=$user->bookings()->where('trip_id',$request->trip_id)->whereDate('created_at',Carbon::today()->toDateString())->first();
            if($exist)
                return $this->returnError(405,'already Done');
            $booking_price=intval($trip->price/2);
            $wallet=$user->wallet()->first();
            if ($wallet->value < $booking_price)
                return $this->returnError(402, 'not Enough money in wallet');
            if($trip->num_passengers==0)
                return $this->returnError(403,'Maximum number of passengers reached');


            $wallet->update([
                'value' => $wallet->value - $booking_price
            ]);

            $booking=$trip->bookings()->create([
                'guest_id'=>$user->id,
                'price'=>$booking_price
            ]);
            $trip->decrement('num_passengers');
            $booking->loadMissing(['guest','trip']);
            DB::commit();
            return $this->returnData($booking,'operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError(500, 'Please try again later');
        }
    }


    public function update(Request $request,$id)
    {
        try {
            DB::beginTransaction();
            $user=auth('api_guest')->user();

            $validator = Validator::make($request->all(), [
                'trip_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->returnValidationError(422,$validator);
            }
            $booking=$user->bookings()->find($id);
            if(!$booking)
                return $this->returnError(404,'booking not found');
            if($booking->guest_id!=$user->id)
                return $this->returnError(403,'you can not do it');
            $trip_old=$booking->trip()->first();
            $trip_old->increment('num_passengers');
            $trip=Trip::find($request->trip_id);
            if(!$trip)
                return $this->returnError(404,'trip not found');
            if($trip->num_passengers==0)
                return $this->returnError(403,'Maximum number of passengers reached');
            $booking_price=intval($trip->price/2);
            $wallet=$user->wallet()->first();
            if ($wallet->value < $booking_price)
                return $this->returnError(402, 'not Enough money in wallet');

            $booking->update([
                'guest_id'=>$user->id,
                'price'=>$booking_price
            ]);
            $booking_price_new=$booking_price-intval($trip_old->price/2);
            $wallet->update([
                'value' => $wallet->value - $booking_price_new
            ]);
            $trip->decrement('num_passengers');
            $booking->loadMissing(['guest','trip']);
            DB::commit();
            return $this->returnData($booking,'operation completed successfully');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError(500,'Please try again later');
        }
    }


    public function delete($id)
    {
        try {
            $user=auth('api_guest')->user();
            $data = $user->bookings()->where('id',$id)->first();
            if (!$data)
                return $this->returnError(404,'Not found');

            $data->delete();
            return $this->returnSuccessMessage('operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }
    public function getAllMe()
    {
        try {
            $user=auth('api_guest')->user();
            $data =$user->bookings()->get();
            if(count($data)>0)
                $data->loadMissing(['guest','trip']);
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }

    public function getByIdMe($id)
    {
        try {
            $user=auth()->user();
            $data =$user->bookings()->find($id);
            if (!$data) {
                return $this->returnError(404,'Not found');
            }
            $data->loadMissing(['guest','trip']);
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');

        }
    }


    public function getAll(Request $request)
    {
        try {
            $user = auth('api')->user();
            $line_monitor = $user->line()->first();

            $data=[];
            if ($line_monitor)
            {
                $data = Booking::whereHas('trip', function ($query) use ($line_monitor) {
                    $query->where('line_id', $line_monitor->id);
                })
                    ->filter($request)->get();;
                if (count($data) > 0)
                    $data->loadMissing(['guest', 'trip']);
            }
            return $this->returnData($data, 'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');
        }
    }

    public function getById($id)
    {
        try {
            $user=auth('api')->user();
            $line_monitor=$user->line()->first();

            $data=[];
            if($line_monitor) {
                $data = Booking::where('id', $id)->whereHas('trip', function ($query) use ($line_monitor) {
                    $query->where('line_id', $line_monitor->id);
                })->first();
                if (!$data) {
                    return $this->returnError(404, 'Not found');
                }
                $data->loadMissing(['guest', 'trip']);
            }
            return $this->returnData($data,'operation completed successfully');
        } catch (\Exception $ex) {
            return $this->returnError(500, 'Please try again later');

        }
    }
}
