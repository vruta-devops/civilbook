<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\CouponResource;
use App\Models\Coupon;
use App\Models\Service;
use App\Models\ServiceProviderMapping;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    public function getCouponList(Request $request)
    {

        $service_id = $request->service_id;

        $coupon = Coupon::where('status', 1)
            ->with('serviceAdded')
            ->whereHas('serviceAdded', function ($query) use ($service_id) {
                $query->where('service_id', $service_id);
            })->orderBy('created_at', 'desc')->get();

        $currentDate = Carbon::today();

        $expire_cupon = $coupon->where('expire_date', '<', $currentDate);

        $valid_cupon = $coupon->where('expire_date', '>', $currentDate);

        $response = [
            'expire_cupon' => CouponResource::collection($expire_cupon),
            'valid_cupon' => CouponResource::collection($valid_cupon),
        ];

        return comman_custom_response($response);
    }

    public function index()
    {
        $provider = auth()->user();
        $serviceIds = ServiceProviderMapping::where('provider_id', $provider->id)->pluck('service_id');

        $coupons = Coupon::with('serviceAdded.service')->whereHas('serviceAdded', function ($query) use ($serviceIds) {
            $query->whereIn('service_id', $serviceIds);
        })->orderBy('created_at', 'desc')
            ->get();

        return comman_custom_response([
            'data' => $coupons
        ]);
    }

    public function show($id)
    {
        $coupon = Coupon::with('serviceAdded')->where('id', $id)
            ->first();

        return comman_custom_response($coupon);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();

            $coupon = Coupon::updateOrCreate(['id' => $data['id']], $data);

            if (count($data['service_id']) > 0) {
                $coupon->serviceAdded()->delete();
                if ($data['service_id'] != null) {
                    foreach ($data['service_id'] as $service) {
                        $service_data = [
                            'coupon_id' => $coupon->id,
                            'service_id' => $service
                        ];
                        $coupon->serviceAdded()->insert($service_data);
                    }
                }
            }
            $message = trans('messages.update_form', ['form' => trans('messages.coupon')]);

            if ($coupon->wasRecentlyCreated) {
                $message = trans('messages.save_form', ['form' => trans('messages.coupon')]);
            }

            DB::commit();

            return comman_custom_response([
                'message' => $message,
                'coupon' => $coupon
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return comman_custom_response([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function destroy($id)
    {
        $coupon = Coupon::where('id', $id)->first();

        $status = false;
        $msg = __('messages.msg_fail_to_delete', ['item' => __('messages.coupon')]);
        if (!empty($coupon)) {
            $status = true;
            $coupon->delete();
            $msg = __('messages.msg_deleted', ['name' => __('messages.coupon')]);
        }

        return comman_custom_response([
            'message' => $msg,
            'success' => $status
        ]);
    }

    public function serviceList(Request $request)
    {
        $provider = auth()->user();

        $services = Service::whereHas('serviceProviderMapping', function ($q) use ($provider) {
            return $q->where('provider_id', $provider->id);
        })
            ->where('end_date', '>=', Carbon::now()->toDateString())
            ->get([
                'id',
                'name'
            ]);

        return comman_custom_response($services);
    }
}
