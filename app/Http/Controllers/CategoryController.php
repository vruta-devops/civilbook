<?php

namespace App\Http\Controllers;

use App\DataTables\CategoryDataTable;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Models\Department;
use App\Models\ProviderType;
use App\Models\Service;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(CategoryDataTable $dataTable)
    {
        $pageTitle = trans('messages.list_form_title',['form' => trans('messages.category')] );
        $auth_user = authSession();
        $assets = ['datatable'];
        return $dataTable->render('category.index', compact('pageTitle','auth_user','assets'));
        }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $id = $request->id;
        $auth_user = authSession();

        $categorydata = Category::find($id);

        $pageTitle = trans('messages.update_form_title',['form'=>trans('messages.category')]);

        if($categorydata == null){
            $pageTitle = trans('messages.add_button_form',['form' => trans('messages.category')]);
            $categorydata = new Category;
        }

        $department = Department::where('status', 1)->pluck('name', 'id');

        return view('category.create', compact('pageTitle', 'categorydata', 'auth_user', 'department'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CategoryRequest $request)
    {
        if(demoUserPermission()){
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $data = $request->all();

        $data['is_featured'] = 0;
        if($request->has('is_featured')){
			$data['is_featured'] = 1;
		}
        if(!$request->is('api/*')) {
            if($request->id == null ){
                if(!isset($data['category_image'])){
                    return  redirect()->back()->withErrors(__('validation.required',['attribute' =>'attachments']));
                }
            }
        }
        $result = Category::updateOrCreate(['id' => $data['id'] ],$data);

        storeMediaFile($result,$request->category_image, 'category_image');

        $message = trans('messages.update_form',['form' => trans('messages.category')]);
        if($result->wasRecentlyCreated){
            $message = trans('messages.save_form',['form' => trans('messages.category')]);
        }
        if($request->is('api/*')) {
            return comman_message_response($message);
		}
        return redirect(route('category.index'))->withSuccess($message);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(demoUserPermission()){
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $category = Category::find($id);

        $msg= __('messages.msg_fail_to_delete',['name' => __('messages.category')] );

        if($category!='') {

            $service = Service::where('category_id',$id)->first();

            $category->delete();
            $msg= __('messages.msg_deleted',['name' => __('messages.category')] );
        }
        if(request()->is('api/*')) {
            return comman_message_response($msg);
		}
        return comman_custom_response(['message'=> $msg , 'status' => true]);
    }
    public function action(Request $request){
        $id = $request->id;

        $category  = Category::withTrashed()->where('id',$id)->first();
        $msg = __('messages.not_found_entry',['name' => __('messages.category')] );
        if($request->type == 'restore') {
            $category->restore();
            $msg = __('messages.msg_restored',['name' => __('messages.category')] );
        }
        if($request->type === 'forcedelete'){
            $category->forceDelete();
            $msg = __('messages.msg_forcedelete',['name' => __('messages.category')] );
        }
        if(request()->is('api/*')){
            return comman_message_response($msg);
		}
        return comman_custom_response(['message'=> $msg , 'status' => true]);
    }


    public function check_in_trash(Request $request)
    {
        $ids = $request->ids;
        $type = $request->datatype;

        switch($type){
            case 'category':
                $InTrash = Category::withTrashed()->whereIn('id', $ids)->whereNotNull('deleted_at')->get();
            break;
            case 'subcategory':
                $InTrash = SubCategory::withTrashed()->whereIn('id', $ids)->whereNotNull('deleted_at')->get();
            break;
            case 'service':
                $InTrash = Service::withTrashed()->whereIn('id', $ids)->whereNotNull('deleted_at')->get();
            break;
            case 'servicepackage':
                $InTrash = ServicePackage::withTrashed()->whereIn('id', $ids)->whereNotNull('deleted_at')->get();
            break;
            case 'booking':
                $InTrash = Booking::withTrashed()->whereIn('id', $ids)->whereNotNull('deleted_at')->get();
            break;
            case 'user':
                $InTrash = User::withTrashed()->whereIn('id', $ids)->whereNotNull('deleted_at')->get();
            break;
            case 'providertype':
                $InTrash = ProviderType::withTrashed()->whereIn('id', $ids)->whereNotNull('deleted_at')->get();
            break;
            case 'providerdocument':
                $InTrash = ProviderDocument::withTrashed()->whereIn('id', $ids)->whereNotNull('deleted_at')->get();
            break;
            case 'coupon':
                $InTrash = Coupon::withTrashed()->whereIn('id', $ids)->whereNotNull('deleted_at')->get();
            break;
            case 'slider':
                $InTrash = Slider::withTrashed()->whereIn('id', $ids)->whereNotNull('deleted_at')->get();
            break;
            case 'document':
                $InTrash = Documents::withTrashed()->whereIn('id', $ids)->whereNotNull('deleted_at')->get();
            break;
            case 'blog':
                $InTrash = Blog::withTrashed()->whereIn('id', $ids)->whereNotNull('deleted_at')->get();
            break;

            default:
            break;
        }

        if (count($InTrash) === count($ids)) {
            return response()->json(['all_in_trash' => true]);
        }

        return response()->json(['all_in_trash' => false]);
    }


}
