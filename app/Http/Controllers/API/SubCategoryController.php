<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\ServicePackageResource;
use App\Http\Resources\API\SubCategoryResource;
use App\Models\PackageServiceMapping;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\SubCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;


class SubCategoryController extends Controller
{
    public function getSubCategoryList(Request $request){
        $subcategory = SubCategory::where('status',1);
        if(auth()->user() !== null){
            if(auth()->user()->hasRole('admin')){
                $subcategory = new SubCategory();
                $subcategory = $subcategory->withTrashed();
            }
        }
        if ($request->has('provider_id') && !empty($request->provider_id)) {
            $provider_data = User::find($request->provider_id);

            $subCategories = $provider_data->providerCategoryMapping->pluck('sub_category_id');
            if ($subCategories != null) {
                $subcategory->whereIn('id', $subCategories);
            }
        }
        if (!empty($request->search)) {
            $subcategory->where('name', 'like', '%' . $request->search . '%');
        }

        if (!empty($request->provider_ids)) {
            $providers = User::with(['providerCategoryMapping'])->whereIn('id', explode(",", $request->provider_ids))->get();

            $categoryIds = [];
            if (!empty($providers)) {
                foreach ($providers as $provider) {
                    if (!empty($provider->providerCategoryMapping)) {
                        foreach ($provider->providerCategoryMapping as $providerCategory) {
                            array_push($categoryIds, $providerCategory->category_id);
                        }
                    }
                }
                if (!empty($categoryIds)) {
                    $categoryIds = array_unique($categoryIds);
                    if (!empty($request->category_id)) {
                        $subcategory->orWhereIn('category_id', $categoryIds);
                    } else {
                        $subcategory->whereIn('category_id', $categoryIds);
                    }
                }
            }
        }

        if($request->has('is_featured')){
            $subcategory->where('is_featured',$request->is_featured);
        }
        if($request->has('category_id')){
            $subcategory->where('category_id',$request->category_id);
        }
        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $subcategory->count();
            }
        }

        $subcategory = $subcategory->orderBy('name','asc')->paginate($per_page);

        $items = SubCategoryResource::collection($subcategory);

        $servicePackages = [];

        $categoryId = $request->category_id;
        if (!empty($categoryId)) {
            $servicePackages = ServicePackage::where('category_id', $categoryId)
                ->where('status', 1)
                ->where('end_at', '>=', Carbon::now()->format('Y-m-d H:i:s'))
                ->where('package_type', 'single')
                ->orderBy('id', 'desc')
                ->get();

            $services = Service::where('category_id', $categoryId)->pluck('id');

            if (!empty($services)) {
                $packageServices = PackageServiceMapping::whereIn('service_id', $services)->pluck('service_package_id');

                if (!empty($packageServices)) {
                    $multiplePackages = ServicePackage::whereIn('id', $packageServices)
                        ->where('status', 1)
                        ->where('end_at', '>=', Carbon::now()->format('Y-m-d H:i:s'))
                        ->where('package_type', 'multiple')
                        ->orderBy('id', 'desc')
                        ->get();
                }

                if (!empty($servicePackages) && !empty($multiplePackages)) {
                    $servicePackages = $servicePackages->merge($multiplePackages);
                }
            }

            $servicePackages = ServicePackageResource::collection($servicePackages);
        }

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
            'service_packages' => $servicePackages
        ];

        return comman_custom_response($response);
    }

}
