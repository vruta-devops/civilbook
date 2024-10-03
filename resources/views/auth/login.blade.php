<x-guest-layout>

<main class="login">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-2 ps-0">
        <div class="sidebar-height">
          <img src="{{asset('img/app_logo.png')}}" class="img-fluid pt-3"/>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="py-3">
          <x-auth-session-status class="mb-4" :status="session('status')" />
          <p class="fw-700 md-font mb-20">Sign In</p>
          <form method="POST" data-toggle="validator" action="{{ url('api/email-login') }}">
          <div class="bg-blue text-white rounded py-2 px-3 mb-30 d-flex justify-content-between">
            <p class="d-flex"><label style="cursor: pointer"><input type="radio" style="cursor: pointer"
                                                                    class="user-type" name="user_type" value="2">
                User</label></p>
            <p class="d-flex"><label style="cursor: pointer"><input type="radio" style="cursor: pointer"
                                                                    class="user-type" name="user_type" value="3">
                Provider</label>
            </p>
            <p class="d-flex"><label style="cursor: pointer"><input type="radio" style="cursor: pointer"
                                                                    class="user-type" name="user_type" checked
                                                                    value="1">
                Admin</label></p>
          </div>

          {{csrf_field()}}
            <div class="mb-10">
              <div class="form-group">
                <input type="tel" id="phone" name="phone" class="form-control w-100">
                <div id="country-selector"></div>
                <small class="help-block with-errors text-danger"></small>
                <span id="invalid-error" class="text-danger"></span>
              </div>
            </div>
            <div class="d-grid">
              <div id="recaptcha-container"></div>
              <button class="btn mt-2" type="button" disabled id="sendOtpBtn">
                Sign In with OTP
              </button>
            </div>
            <div class="alert alert-success" id="sentSuccess" style="display: none;"></div>
                        <div class="alert alert-danger" id="error" style="display: none;"></div>
            <div class="my-3 d-flex align-items-center justify-content-center">
              <p class="pe-2">OR</p>
              <div class="line-right"></div>
              <img src="{{asset('img/app_logo.png')}}" class="img-fluid otp-img"/>
            </div>
            <div class="mb-10">
              @if(session('errors'))
                @error('email')
                <div class="alert alert-danger alert-dismissible fade show">{{ $message }}</div>
                @enderror
              @endif
              <div class="form-group">
                <input name="email" type="email" class="form-control" placeholder="Email (Civilbook account)" required/>
                <small class="help-block with-errors text-danger"></small>
              </div>
            </div>
            <div class="mb-10">
              <div class="form-group">
                <input name="password" type="password" class="form-control" placeholder="Password" required/>
                <small class="help-block with-errors text-danger"></small>
                <input type="hidden" name="user_type" id="user-type" value="1"/>
              </div>
            </div>
            <a href="{{route('password.request')}}">
              <p class="text-end mb-10">Forgot your Password? </p>
            </a>
            <div class="d-grid">
              <button class="btn" type="submit">Sign In with Password</button>
            </div>
            <div class="mt-3 d-flex align-items-center justify-content-center">
              <p class="pe-2">OR</p>
              <div class="line-right"></div>

              <a href="#" id="google-login"><img src="{{asset('img/google.svg')}}" class="img-fluid otp-img"/></a>
            </div>
          </form>
        </div>
      </div>
      <div class="col-md-4 col-lg-6">
        <div class="py-3 px-3">
          <div class="d-flex flex-wrap align-items-center justify-content-lg-end pb-5">

          </div>
          <img src="{{asset('img/img-signin.svg')}}" class="img-fluid pt-4"/>
        </div>
      </div>
    </div>
  </div>
</main>
<!-- Modal -->
<div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="signinwithotpLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content signin-page-modal">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="signinwithotpLabel">OTP Verification</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" data-toggle="validator">
        {{csrf_field()}}
        <div class="modal-body">
          <p class="mb-20">We've sent you a verification code to your mobile</p>
          <div class="mb-20">
            <div class="form-group">
              <input type="text" class="form-control" id="otp" placeholder="Enter Verification Code" required/>
              <small class="help-block with-errors text-danger"></small>
            </div>
          </div>
          <div class="d-grid mb-10">
            <button class="btn" type="button" id="verifyOtp">Submit</button>
            <p class="text-danger font-bold hide" id="codeError"></p>
          </div>
          <a href="javascript:void(0)" class="disabledLink" id="resendOtpLink">
            <p class="text-end mb-10">Did not receive OTP? (<span id="timer">60</span>s)</p>
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
</x-guest-layout>
<script type="text/javascript">
  $(document).ready(function () {
    $('.user-type').on('change', function () {
      $('#user-type').val($(this).val());
    });

    $('#google-login').on('click', function (event) {
      event.preventDefault();
      window.location.href = "{{url('auth/google-login')}}" + "?user_type=" + $('input.user-type:checked').val()
    });

    setTimeout(function () {
      $('.alert').alert('close');
    }, 5000); // 5 seconds
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    let interval = null;
    let mobileNumber = "";

    const sendOtpBtn = $('#sendOtpBtn');
    const input = document.querySelector("#phone");
    const mobileInput = $("#phone");
    const codeError = $("#codeError")

    const iti = window.intlTelInput(input, {
      utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.4/build/js/utils.js",
      initialCountry: "IN",
      strictMode: true,
    });

    const firebaseConfig = {
      apiKey: "AIzaSyDDTWvEUlDvId8UhjW4pN5Y0D3ZCOqIamA",
      authDomain: "civilbook.firebaseapp.com",
      projectId: "civilbook",
      storageBucket: "civilbook.appspot.com",
      messagingSenderId: "1050520932388",
      appId: "1:1050520932388:web:ed08672bd44a99176248b1",
      measurementId: "G-229ZNH9M8V"
    };

    firebase.initializeApp(firebaseConfig);

    window.onload = function () {
      render();
    };

    sendOtpBtn.on('click', () => {
      $(this).prop('disabled', true)
      $(this).text('Please Wait...')

      const sendOtpButtonElement = document.getElementById('sendOtpBtn')

      firebase.auth().signInWithPhoneNumber(mobileNumber, window.recaptchaVerifier).then((confirmationResult) => {
        window.confirmationResult = confirmationResult;

        codeResult = confirmationResult;

        $("#sentSuccess").text("Message Sent Successfully.");
        $("#sentSuccess").show();
        $('#otpModal').modal('show');

        codeError.text('');
        startOtpTimer();
        sendOtpButtonElement.innerText = "Send OTP"
      }).catch(function (error) {
        sendOtpButtonElement.removeAttribute('disabled')
        sendOtpButtonElement.innerText = "Send OTP"
        console.log(error);
        $("#error").text(error.message);
        $("#error").show();
      });
    });

    input.addEventListener("countrychange", function () {
      $('#invalid-error').html('');
      const country = iti.getSelectedCountryData();
      mobileInput.val("");
      sendOtpBtn.prop("disabled", true);
    });

    mobileInput.on("input", function () {

      $('#invalid-error').html('');
      const isValid = iti.isValidNumber();

      sendOtpBtn.prop("disabled", true);

      if (isValid) {
        sendOtpBtn.prop("disabled", false);
        mobileNumber = iti.getNumber();

      }
    });

    $('#resendOtpLink').on('click', (event) => {
      event.preventDefault();
      stopOtpTimer()
    });

    const render = () => {
      window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
        'callback': function (response) {
          // reCAPTCHA verified, enable the button
          document.getElementById('sendOtpBtn').removeAttribute('disabled');
        },
        'expired-callback': function () {
          // Reset the button state if reCAPTCHA expires
          document.getElementById('sendOtpBtn').setAttribute('disabled', 'disabled');
        }
      });

      recaptchaVerifier.render();
    }

    $('#verifyOtp').on('click', () => {
      var errors = $('.has-error')
      if (!errors.length) {
        $(this).prop('disabled', true)
        $(this).text('Please Wait...')

        const code = $("#otp").val();

        codeResult.confirm(code).then(function (result) {
          const phone = $("#phone").val();
          const coreNumber = phone.replace(/\D/g, '');
          $.ajax({
              url: "{{ url('api/web-login') }}",
              method: 'POST',
              data: {
                phone: coreNumber,
                user_type: $('input.user-type:checked').val()
              },
              success: function(xhr, status,response) {
                if (response.status == 201) {
                  $('#otpModal').modal('hide');
                  $('#invalid-error').html(response.responseJSON.message);
                  window.location.href = "{{ url('register') }}"
                }

                if (response.responseJSON && response.responseJSON.message && response.responseJSON.message.redirect_url) {
                  window.location.href = response.responseJSON.message.redirect_url;
                }
              },
              error: function(xhr, status, error) {
                console.log('Error:', error);
              }
          });
          codeError.addClass('hide')
        }).catch(function (error) {
          codeError.removeClass('hide')
          codeError.text(error.message);
          $("#error").show();
        });
      }
    });

    const startOtpTimer = () => {
      let timer = 5;

      interval = setInterval(function () {
        timer--;
        $('#timer').text(timer);
        if (timer <= 0) {
          clearInterval(interval);
          $('#resendOtpLink').prop('disabled', false).removeClass('disabledLink').html('<p class="text-end mb-10">Click Me For Resend OTP.!</p>');
        }
      }, 1000);
    }

    const stopOtpTimer = () => {
      clearInterval(interval)
    }
  });
</script>
