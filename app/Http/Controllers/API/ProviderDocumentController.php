<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\ProviderDocumentResource;
use App\Models\ServiceCertificate;
use Illuminate\Http\Request;

class ProviderDocumentController extends Controller
{
    public function getProviderDocumentList(Request $request){
        $serviceId = $request->service_id;
        $providerId = !empty($request->provider_id) ? $request->provider_id : auth()->user()->id;

        $serviceCertificates = ServiceCertificate::with(['certificate'])->where('service_id', $serviceId)->where('provider_id', $providerId)->orderBy('created_at', 'desc')->get();

        $items = ProviderDocumentResource::collection($serviceCertificates);

        $response = [
            'data' => $items,
        ];

        return comman_custom_response($response);
    }
}
