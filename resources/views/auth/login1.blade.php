<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Civilbook</title>
    <meta name="description" content="This is meta description Sample. We can add up to 158.">
    <meta name="keywords" content="HTML,CSS,XML,JavaScript">
    <meta name="author" content="XYZ">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name=”robots” content="noindex, nofollow">
    <link href="{{asset('css/bootstrap.min.css')}}" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="{{asset('css/style.css')}}"/>
</head>

<body>
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
                    <p class="fw-700 md-font mb-20">Sign In</p>
                    <div class="bg-blue text-white rounded py-2 px-3 mb-30 d-flex align-items-center justify-content-between">
                        <p class="d-flex align-items-center"><img src="{{asset('img/radio_button_fill_white.svg')}}"
                                                                  class="img-fluid radio-height me-1"/>User</p>
                        <p class="d-flex align-items-center"><img src="{{asset('img/radio_button_fill_white.svg')}}"
                                                                  class="img-fluid radio-height me-1"/>Provider</p>
                        <p class="d-flex align-items-center"><img src="{{asset('img/radio_button_fill_white.svg')}}"
                                                                  class="img-fluid radio-height me-1"/>Admin</p>
                        <p class="d-flex align-items-center"><img src="{{asset('img/radio_button_fill_white.svg')}}"
                                                                  class="img-fluid radio-height me-1"/>Emplyee</p>
                    </div>
                    <form>
                        <div class="mb-10">
                            <input type="text" class="form-control" placeholder="Mobile number (Sign In with OTP)"/>
                        </div>
                        <div class="d-grid">
                            <button class="btn" type="button" data-bs-toggle="modal" data-bs-target="#signinwithotp">
                                Sign In with OTP
                            </button>
                        </div>
                        <div class="my-3 d-flex align-items-center justify-content-center">
                            <p class="pe-2">OR</p>
                            <div class="line-right"></div>
                            <img src="{{asset('img/app_logo.png')}}" class="img-fluid otp-img"/>
                        </div>
                        <div class="mb-10">
                            <input type="text" class="form-control" placeholder="Email (Civilbook account)"/>
                        </div>
                        <div class="mb-10">
                            <input type="text" class="form-control" placeholder="Password"/>
                        </div>
                        <a href="javascript:void(0)">
                            <p class="text-end mb-10">Forgot your Password? </p>
                        </a>
                        <div class="d-grid">
                            <button class="btn" type="button">Sign In with Password</button>
                        </div>
                        <div class="mt-3 d-flex align-items-center justify-content-center">
                            <p class="pe-2">OR</p>
                            <div class="line-right"></div>
                            <a href="#"><img src="{{asset('img/google.svg')}}" class="img-fluid otp-img"/></a>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-4 col-lg-6">
                <div class="py-3 px-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-lg-end pb-5">
                        <p class="fw-700 md-font me-4 mb-1">Not registered with?</p>
                        <button class="btn mb-1" type="button">Sign Up</button>
                    </div>
                    <img src="{{asset('img/img-signin.svg')}}" class="img-fluid pt-4"/>
                </div>
            </div>
        </div>
    </div>
</main>
<!-- Modal -->
<div class="modal fade" id="signinwithotp" tabindex="-1" aria-labelledby="signinwithotpLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content signin-page-modal">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="signinwithotpLabel">OTP Verification</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-20">We've sent you a verification code to your mobile -</p>
                <div class="mb-20">
                    <input type="text" class="form-control" placeholder="Enter Verification Code"/>
                </div>
                <div class="d-grid mb-10">
                    <button class="btn" type="button">Submit</button>
                </div>
                <a href="javascript:void(0)">
                    <p class="text-end mb-10">Did not receive OTP? </p>
                </a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{asset('js/jquery.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/bootstrap.bundle.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/custom.js')}}"></script>
</body>

</html>
