<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\Request;


class DepartmentController extends Controller
{
    public function getDepartmentList(Request $request)
    {
        $departments = Department::with(['providers', 'departmentShiftHoursMapping.shifthours.shiftType', 'departmentPriceTypesMapping.priceTypes', 'departmentMaterialUnitsMapping.materialUnits'])->where('status', 1);

        if (!empty($request->provider_id)) {
            $departments = $departments->whereHas('providers', function ($query) use ($request) {
                $query->where('id', $request->provider_id);
            });
        }

        $auth_user = auth()->user();

        if ($auth_user !== null) {
            if ($auth_user->hasRole('admin')) {
                $departments = new Department();
                $departments = $departments->withTrashed();
            }
        }

        $per_page = config('constant.PER_PAGE_LIMIT');

        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $departments->count();
            }
        }

        $departments = $departments->orderBy('name')->paginate($per_page);
        $items = DepartmentResource::collection($departments);

        $response = [
            'pagination' => [
                'total_items' => $items->total(),
                'per_page' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'totalPages' => $items->lastPage(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
                'next_page' => $items->nextPageUrl(),
                'previous_page' => $items->previousPageUrl(),
            ],
            'data' => $items,
        ];

        return comman_custom_response($response);
    }

}
