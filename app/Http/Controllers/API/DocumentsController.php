<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DocumentsController extends Controller
{
    public function getDocumentList(Request $request){
        $provider = auth()->user();

        $providerDepartment = Department::with(['requiredDepartmentCertificates', 'optionalDepartmentCertificates'])->where('id', $provider->department_id)
            ->first();

        $documents = [];

        if (!empty($providerDepartment)) {
            if (!empty($providerDepartment->requiredDepartmentCertificates)) {
                foreach ($providerDepartment->requiredDepartmentCertificates as $requiredDepartmentCertificate) {
                    $documents[] = [
                        'id' => $requiredDepartmentCertificate->id,
                        'name' => $requiredDepartmentCertificate->name,
                        'is_required' => 1
                    ];
                }
            }

            if (!empty($providerDepartment->optionalDepartmentCertificates)) {
                foreach ($providerDepartment->optionalDepartmentCertificates as $optionalDepartmentCertificate) {
                    $documents[] = [
                        'id' => $optionalDepartmentCertificate->id,
                        'name' => $optionalDepartmentCertificate->name,
                        'is_required' => 0
                    ];
                }
            }
        }
        $response = [
            'data' => $documents,
        ];

        return comman_custom_response($response);
    }
}
