<?php

namespace App\Http\Controllers;

use App\DataTables\ProviderDocumentDataTable;
use App\Http\Requests\ProviderDocumentRequest;
use App\Models\Certificate;
use App\Models\ProviderDocument;
use App\Models\ServiceCertificate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProviderDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(ProviderDocumentDataTable $dataTable)
    {
        $pageTitle = trans('messages.list_form_title', ['form' => trans('messages.providerdocument')]);
        $auth_user = authSession();
        $assets = ['datatable'];
        return $dataTable->render('providerdocument.index', compact('pageTitle', 'auth_user', 'assets'));
    }

    public function create(Request $request)
    {
        $id = $request->id;
        $auth_user = authSession();

        $providerDocument = ServiceCertificate::with('service', 'certificate')->find($id);
        $pageTitle = trans('messages.update_form_title', ['form' => trans('messages.providerdocument')]);

        if ($providerDocument == null) {
            $pageTitle = trans('messages.add_button_form', ['form' => trans('messages.providerdocument')]);
            $providerDocument = new ProviderDocument;
        }
        return view('providerdocument.create', compact('pageTitle', 'providerDocument', 'auth_user'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(ProviderDocumentRequest $request)
    {
        if (demoUserPermission()) {
            if (request()->is('api/*')) {
                return comman_message_response(__('messages.demo_permission_denied'));
            } else {
                return redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
            }
        }
        $data = $request->all();

        $providerDocument = ServiceCertificate::where('certificate_id', $data['document_id'])
            ->where('provider_id', $data['provider_id'])
            ->where('service_id', $data['service_id'])
            ->first();

        $serviceCertificateId = !empty($providerDocument) ? $providerDocument->id : $request->id;

        $certificate = Certificate::find($data['document_id']);

        $data['is_approved'] = !empty($data['is_verified']) ? $data['is_verified'] : 0;
        $data['is_required'] = !empty($data['is_required']) ? $data['is_required'] : $certificate->is_required;
        $data['certificate_id'] = $data['document_id'];
        $data['provider_id'] = !empty($data['provider_id']) ? $data['provider_id'] : auth()->user()->id;

        $result = ServiceCertificate::updateOrCreate(['id' => $serviceCertificateId], $data);

        storeMediaFile($result, $request->provider_document, 'provider_document');

        if (empty($request->id)) {
            $user = auth()->user();

            $notificationData = [
                'id' => $result->id,
                'type' => 'new_service_document_uploaded',
                'subject' => 'new_service_create',
                'message' => $user->first_name . " has been upload a new service document. Please approve/reject their service document",
            ];

            saveAdminNotification($notificationData);
        }
        $message = __('messages.update_form', ['form' => __('messages.providerdocument')]);
        if ($result->wasRecentlyCreated) {
            $message = __('messages.save_form', ['form' => __('messages.providerdocument')]);
        }
        if ($request->is('api/*')) {
            return comman_message_response($message);
        }
        return redirect(route('providerdocument.index'))->withSuccess($message);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        if (demoUserPermission()) {
            if (request()->is('api/*')) {
                return comman_message_response(__('messages.demo_permission_denied'));
            }
            return redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $provider_document = ServiceCertificate::find($id);
        $msg = __('messages.data_not_found');
        $deleted = false;
        if ($provider_document != '') {
            $provider_document->delete();
            $deleted = true;
            $msg = __('messages.msg_deleted', ['name' => __('messages.providerdocument')]);
        }

        if (request()->is('api/*')) {
            return comman_custom_response(['message' => $msg, 'status' => $deleted]);
        }
        return comman_custom_response(['message' => $msg, 'status' => $deleted]);
    }

    public function action(Request $request)
    {
        $id = $request->id;

        $provider_document = ProviderDocument::withTrashed()->where('id', $id)->first();
        $msg = __('messages.not_found_entry', ['name' => __('messages.providerdocument')]);
        if ($request->type == 'restore') {
            $provider_document->restore();
            $msg = __('messages.msg_restored', ['name' => __('messages.providerdocument')]);
        }
        if ($request->type === 'forcedelete') {
            $provider_document->forceDelete();
            $msg = __('messages.msg_forcedelete', ['name' => __('messages.providerdocument')]);
        }
        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

}
