<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\CategoryResource;
use App\Http\Resources\API\ServicePackageResource;
use App\Models\Category;
use App\Models\PackageServiceMapping;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;


class CategoryController extends Controller
{
    public function getCategoryList(Request $request){
        $category = Category::where('status',1);

        if(auth()->user() !== null){
            if(auth()->user()->hasRole('admin')){
                $category = new Category();
                $category = $category->withTrashed();
            }
        }

        if (!empty($request->provider_id)) {
            $provider_data = User::find($request->provider_id);

            $categories = $provider_data->providerCategoryMapping->pluck('category_id');

            if ($categories != null) {
                $category->whereIn('id', $categories);
            }
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
                    $category->whereIn('id', $categoryIds);
                }
            }
        }

        if ($request->has('department_id') && !empty($request->department_id)) {
            $category = $category->where('department_id', $request->department_id);
        }

        if (!empty($request->search)) {
            $category = $category->where('name', 'like', '%' . $request->search . '%');
        }

        if($request->has('is_featured')){
            $category->where('is_featured',$request->is_featured);
        }

        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $category->count();
            }
        }

        $multiCategories = $category;

        $category = $category->orderBy('name','asc')->paginate($per_page);

        $items = CategoryResource::collection($category);

        $servicePackages = [];
        $multiCategories = $multiCategories->pluck('id');

        if (!empty($multiCategories)) {
            $servicePackages = ServicePackage::whereIn('category_id', $multiCategories)
                ->where('status', 1)
                ->where('end_at', '>=', Carbon::now()->format('Y-m-d H:i:s'))
                ->where('package_type', 'single')
                ->orderBy('id', 'desc')
                ->get();

            $services = Service::whereIn('category_id', $multiCategories)->pluck('id');

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
