@php
    $disable = '';
    if ($wallet->status != 'pending') {
        $disable='disabled';
    }
@endphp
@if(auth()->user()->hasAnyRole(['admin']))
    <div class="d-flex justify-content-end align-items-center">
        <button class="btn btn-primary btnPaid" id="{{$wallet->id}}" {{$disable}}>
            Paid
        </button>
        <button class="btn btn-danger ml-2 btnReject" id="{{$wallet->id}}" {{$disable}}>
            Reject
        </button>
    </div>
@endif

<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectLabel" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content signin-page-modal">
            <div class="modal-header">
                <h3 class="modal-title fs-5" id="rejectLabel">Reject Wallet Withdraw</h3>
            </div>
            <form method="POST" data-toggle="validator">
                {{csrf_field()}}
                <div class="modal-body">
                    <div>
                        <div class="form-group">
                            <input type="text" class="form-control" id="reject-note"
                                   placeholder="Enter Reason For Reject" required/>
                            <small class="help-block with-errors text-danger"></small>
                        </div>
                    </div>
                    <div class="d-grid mb-10">
                        <button class="btn btn-primary rejectRequest" type="button" id="{{$wallet->id}}" disabled>
                            Submit
                        </button>
                        <button class="btn btn-default" type="button" id="hideModal">Cancel</button>
                        <p class="text-danger font-bold hide" id="codeError"></p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="application/javascript">
    const btnPaid = $('.btnPaid')
    const btnReject = $('.btnReject')
    const rejectModal = $('#rejectModal')
    const rejectRequest = $('.rejectRequest')
    const rejectNote = $('#reject-note')
    let note = "";

    $(document).ready(function () {
        const requestStatus = "{{$wallet->status}}";

        btnPaid.on("click", function (e) {
            if (requestStatus !== 'pending') {
                return false;
            }

            updateWalletWithdrawRequest('paid', this.id);
        });

        btnReject.on("click", function () {
            rejectModal.modal('show');
        })

        $(document).on('input', '#reject-note', function () {
            note = $(this).val();

            rejectRequest.prop('disabled', true)

            if (note.length > 0) {
                rejectRequest.prop('disabled', false)
            }
        });

        rejectRequest.on("click", function () {
            updateWalletWithdrawRequest('rejected', this.id, note)
            rejectModal.modal('hide');
        })

        $('#hideModal').on("click", function () {
            rejectModal.modal('hide');
        })

        function updateWalletWithdrawRequest(status, id, reason = "") {
            $.ajax({
                type: "GET",
                dataType: "json",
                url: "{{ route('changeStatus') }}",
                data: {'status': status, 'id': id, 'type': 'wallet_withdraw', reason: reason},
                success: function (data) {
                    console.log("data================", data);
                    if (data.status == false) {
                        errorMessage(data.message)
                    } else {
                        showMessage(data.message);
                        $('#status_' + id).text(status.toUpperCase());
                        console.log("reason================", reason);
                        if (reason.length > 0) {
                            window.location.reload()
                        }
                        btnPaid.prop('disabled', true)
                        btnReject.prop('disabled', true)
                    }
                }
            });
        }

        function errorMessage(message) {
            Snackbar.show({
                text: message,
                pos: 'bottom-center',
                backgroundColor: '#dc3545',
                actionTextColor: 'white'
            });
        }

        function showMessage(message) {
            Snackbar.show({
                text: message,
                pos: 'bottom-center'
            });
        }
    });
</script>
