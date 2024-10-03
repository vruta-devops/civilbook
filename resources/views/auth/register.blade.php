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
            <p class="fw-700 md-font mb-20">Sign Up</p>

            {{ Form::model($providerdata,['method' => 'POST','route'=>'register', 'enctype'=>'multipart/form-data', 'data-toggle'=>"validator" ,'id'=>'provider'] ) }}
            <!-- <form method="POST" data-toggle="validator" action="{{ url('api/email-login') }}"> -->
            {{csrf_field()}}

            <div class="bg-blue text-white rounded py-2 px-3 mb-30 d-flex justify-content-between">
              <p class="d-flex"><label style="cursor: pointer"><input type="radio" style="cursor: pointer" class="user-type" name="user_type" value="user" checked="checked">
                  User</label></p>
              <p class="d-flex"><label style="cursor: pointer"><input type="radio" style="cursor: pointer" class="user-type" name="user_type" value="provider">
                  Provider</label>
              </p>
            </div>

              <div class="mb-10">
                <div class="form-group">
                <input class="form-control" id="first_name" name="first_name" required value="{{old('first_name')}}" placeholder="{{ __('auth.enter_name',[ 'name' => __('auth.first_name') ]) }}">
                  <small class="help-block with-errors text-danger"></small>
                  <span id="invalid-error" class="text-danger"></span>
                </div>
              </div>

              <input type="hidden" name="last_name" value=''>

              <input type="hidden" name="login_type" value='normal'>
              <input type="hidden" name="request_from" value='web'>

              <div class="mb-10">
                <div class="form-group">
                <input class="form-control" id="username"  name="username" value="{{old('username')}}" required placeholder="{{ __('auth.enter_name',[ 'name' => __('auth.username') ]) }}">
                  <small class="help-block with-errors text-danger"></small>
                  <span id="invalid-error" class="text-danger"></span>
                </div>
              </div>


              <div class="mb-10">
                <div class="form-group">
                <input class="form-control" type="email" id="email"  name="email" value="{{ $emailsession ?: old('email') }}" required placeholder="{{ __('auth.enter_name',[ 'name' => __('auth.email') ]) }}" <?=$emailsession ? 'disabled' : '';?>>
                  <small class="help-block with-errors text-danger"></small>
                  @if(session('errors'))
                      @error('email')
                        <span id="email-error" class="text-danger">{{ $message }}</span>
                      @enderror
                  @endif

                </div>
              </div>

              <div class="mb-10">
                <div class="form-group">
                  <input type="tel" id="phone" name="phone" class="form-control w-100">
                  <div id="country-selector"></div>
                  <small class="help-block with-errors text-danger"></small>
                  <span id="invalid-error" class="text-danger"></span>
                </div>
              </div>

              <div id="provider-section" class="hide">

                <div class="mb-10">
                  <div class="form-group">
                  <input class="form-control" id="designation"  name="designation" value="{{old('designation')}}"  placeholder="{{ __('messages.designation',[ 'name' => __('auth.designation') ]) }}">
                    <small class="help-block with-errors text-danger"></small>
                    <span id="invalid-error" class="text-danger"></span>
                  </div>
                </div>

                @php

                  $categories_id_provider = optional($providerdata->providerCategoryMapping)->pluck('category_id');
                  $categories_provider = $providerdata->providerCategoryMapping->mapWithKeys(function ($item) {
                      return [$item->category_id => optional($item->category)->name];
                  });

                  $sub_categories_id_provider = optional($providerdata->providerCategoryMapping)->pluck('sub_category_id');
                  $sub_categories_provider = $providerdata->providerCategoryMapping->mapWithKeys(function ($item) {
                      return [$item->sub_category_id => optional($item->subCategory)->name];
                  });

                  if(isset($providerdata->providerCategoryMapping[0]) && $providerdata->providerCategoryMapping[0]->is_category_all==1)
                  {
                      $categories_id_provider = 0;
                      $categories_provider = [0 => 'All'];
                  }

                  if(isset($providerdata->providerCategoryMapping[0]) && $providerdata->providerCategoryMapping[0]->is_sub_category_all==1)
                  {
                      $sub_categories_id_provider = 0;
                      $sub_categories_provider = [0 => 'All'];
                  }

                @endphp

                <div class="mb-10">
                  <div class="form-group">
                  {{ Form::select('department_id', [optional($providerdata->department)->id => optional($providerdata->department)->name], optional($providerdata->department)->id, [
                        'class' => 'select2js form-control department',
                        'id' => 'department_id',
                        'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.department') ]),
                        'data-ajax--url' => route('ajax-list', ['type' => 'department', 'is_all_option' => 'no']),
                    ]) }}
                    <small class="help-block with-errors text-danger"></small>
                    <span id="invalid-error" class="text-danger"></span>
                  </div>
                </div>

                <div class="mb-10">
                  <div class="form-group">
                  {{ Form::select('categories[]', $categories_provider, $categories_id_provider, [
                                                    'class' => 'select2js form-control category_id',
                                                    'id' => 'category_id',
                                                    'multiple' => 'multiple',
                                                    'allowClear' => 'true',
                                                    'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.category') ]),

                                                ]) }}
                    <small class="help-block with-errors text-danger"></small>
                    <span id="invalid-error" class="text-danger"></span>
                  </div>
                </div>

                <div class="mb-10">
                  <div class="form-group">
                  {{ Form::select('sub_categories[]', $sub_categories_provider, $sub_categories_id_provider, [
                                                'class' => 'select2js form-control subcategory_id',
                                                'id' => 'subcategory_id',
                                                'multiple' => 'multiple',
                                                'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.subcategory') ]),
                                            ]) }}
                    <small class="help-block with-errors text-danger"></small>
                    <span id="invalid-error" class="text-danger"></span>
                  </div>
                </div>

                <div class="mb-10">
                  <div class="form-group">
                  <input class="form-control" id="address"  name="address" value="{{old('address')}}" placeholder="Enter your address">
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                    <div class="form-group">
                    <div id="mapholder" class="d-none" style="width: 100%; height: 200px; position: relative; overflow: hidden;">
                      <div style="height: 100%; width: 100%; position: absolute; top: 0px; left: 0px; background-color: rgb(229, 227, 223);"></div>
                    </div>
                  </div>
                  <p id="errorMap" class="d-none"></p>
                  <p id="errorMapCode" class="d-none"></p>
                    <small class="help-block with-errors text-danger"></small>
                    <span id="invalid-error" class="text-danger"></span>
                  </div>
                </div>

                <div class="mb-10">
                  <div class="form-group">
                    {{ Form::select('is_role',['' => 'Select Role' , 'provider' => 'Provider' , 'handyman' => 'Handyman' ],old('is_role'),['id' => 'is_role_data', 'class' =>'form-control select2js']) }}
                    <small class="help-block with-errors text-danger"></small>
                    <span id="invalid-error" class="text-danger"></span>
                  </div>
                </div>

                <div class="mb-10">
                  <div class="form-group">
                  {{ Form::select('user_type_id', ['provider' => 'Provider' , 'handyman' => 'Handyman' ], old('user_type_id'), [
                                                'class' => 'select2js form-control',
                                                'id' => 'user_type_id',
                                                'data-placeholder' => 'Select User Type',
                                            ]) }}
                    <small class="help-block with-errors text-danger"></small>
                    <span id="invalid-error" class="text-danger"></span>
                  </div>
                </div>

                <div class="mb-10">
                  <div class="form-group">
                    {{ Form::label('preferred_location_distance',__('messages.preferred_distance').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                    {{ Form::select('preferred_location_distance',$preDiff,old('preferred_distance'),[ 'id' => 'preferred_distance' ,'class' =>'form-control select2js','required']) }}
                    <small class="help-block with-errors text-danger"></small>
                    <span id="invalid-error" class="text-danger"></span>
                  </div>
                </div>

              </div>

              @if (session('hide_password', true))
                <div class="mb-10">
                  <div class="form-group">
                  <input class="form-control" type="password" id="password"  name="password" value="{{old('password')}}" required placeholder="Password">
                    <small class="help-block with-errors text-danger"></small>
                    <span id="invalid-error" class="text-danger"></span>
                  </div>
                </div>
              @endif
              {{ Form::submit( __('messages.save'), ['class'=>'btn btn-md btn-primary float-right']) }}
              {{ Form::close() }}

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

  </x-guest-layout>

  <script src="https://maps.google.com/maps/api/js?key={{env('GOOGLE_API_KEY')}}&libraries=places" type="text/javascript"></script>

  <script type="text/javascript">
  var map;
  function setMap(mapCenter) {
    $("#errorMap").addClass("d-none");
    $("#mapholder").removeClass("d-none");
    map = new google.maps.Map($("#mapholder")[0], {
      center: mapCenter,
      zoom: 15
    });
    var marker = new google.maps.Marker({
      position: mapCenter,
      map: map
    });
  }
  function displayError(error) {

    $("#errorMap").removeClass("d-none");

    var x = document.getElementById("errorMap");
    var y = document.getElementById("errorMapCode");
    y.innerHTML = error.code;

    switch (error.code) {
      case error.PERMISSION_DENIED:
        x.innerHTML = "" //User denied the request for Geolocation.
        break;
      case error.POSITION_UNAVAILABLE:
        x.innerHTML = "Sorry, we could not detect your location. Please select a area by typing in the search box above."
        break;
      case error.TIMEOUT:
        x.innerHTML = "" //The request to get user location timed out.
        break;
      case error.UNKNOWN_ERROR:
        x.innerHTML = "" //An unknown error occurred.
        break;
    }
  }
  function displayLocation(position) {
    var pos = {
      lat: position.coords.latitude,
      lng: position.coords.longitude
    };
    $("#latitude").val(position.coords.latitude);
    $("#longitude").val(position.coords.longitude);

    var mapCenter = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
    setMap(mapCenter);

    var geocoder = geocoder = new google.maps.Geocoder();
    geocoder.geocode({
      'latLng': mapCenter
    }, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        if (results[1]) {
          $("#location").val(results[1].formatted_address);
        }
      }
    });
  }
  function initMap() {

    var existingAddLat = $("#latitude").val();
    var existingAddLng = $("#longitude").val();

    if (existingAddLat == '') {

      if ("geolocation" in navigator) {

        navigator.geolocation.getCurrentPosition(displayLocation, displayError, {
          timeout: 1000000
        });

      } else {
        console.log("Browser doesn't support geolocation!");
      }

    } else {
      var mapCenter = new google.maps.LatLng(existingAddLat, existingAddLng);
      setMap(mapCenter);
    }
    ///

    var geocoder = new google.maps.Geocoder();

    var autocomplete = new google.maps.places.Autocomplete($("#address")[0], {});
    google.maps.event.addListener(autocomplete, 'place_changed', function() {
      console.log(autocomplete);
      var place = autocomplete.getPlace();
      var address = place.formatted_address;
      geocoder.geocode({
        'address': address
      }, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
          var latitude = results[0].geometry.location.lat();
          var longitude = results[0].geometry.location.lng();

          $("#latitude").val(latitude);
          $("#longitude").val(longitude);

          var mapCenter = new google.maps.LatLng(latitude, longitude); //Google map Coordinates
          setMap(mapCenter);
        }
      });
    });
  }

  $(document).ready(function () {
      initMap();


      var is_role_data = $('#is_role_data').val();
      getUserType(is_role_data);
      $("#is_role_data").on("select2:select", function(e) {
          if (e.params.data.id===0) {
              $("#is_role_data option").each(function() {
                  if ($(this).val() !== "0") {
                      $(this).remove();
                  }
              });
              $("#is_role_data").trigger("change");
          } else {
              $("#is_role_data option[value='0']").remove();
              $("#is_role_data").trigger("change");

          }
          const selectedOptions = $(this).val();
          $('#user_type_id').empty();
          getUserType(selectedOptions);
      })
      $('#is_role_data').on("select2:unselect", function(e) {
          const selectedOptions = $(this).val();
          $('#user_type_id').empty();
          getUserType(selectedOptions);
      })

      $('input[type=radio][name=user_type]').change(function() {

        if (this.value == 'provider') {
          $('#first_name').attr("required", true);
          $("#provider-section input").attr("required", true);
          $('#provider-section').toggleClass('hide');
        }
        else{
          $('#first_name').removeAttr("required");
          $("#provider-section input").removeAttr("required");
          $('#provider-section').addClass('hide');
        }
      });

      var category_id = $('#category_id').val();
      getSubCategory(category_id);
      $("#category_id").on("select2:select", function(e) {
          if (e.params.data.id===0) {
              $("#category_id option").each(function() {
                  if ($(this).val() !== "0") {
                      $(this).remove();
                  }
              });
              $("#category_id").trigger("change");
          } else {
              $("#category_id option[value='0']").remove();
              $("#category_id").trigger("change");

          }
          const selectedOptions = $(this).val();
          $('#subcategory_id').empty();
          getSubCategory(selectedOptions);
      })
      $(document).on('change', '#department_id', function () {
          var department_id = $(this).val();
          $('#category_id').empty();
          getCategory(department_id, category_id);
          $('#subcategory_id').trigger('change');
      })
      $('#category_id').on("select2:unselect", function(e) {
          const selectedOptions = $(this).val();
          $('#subcategory_id').empty();
          getSubCategory(selectedOptions);
      })
      $("#subcategory_id").on("select2:select", function (e) {
          if (e.params.data.id == 0) {
              $("#subcategory_id option").each(function() {
                  if ($(this).val() != 0) {
                      $(this).remove();
                  }
              });
              $("#subcategory_id").trigger("change");
          } else {
              $("#subcategory_id option[value='0']").remove();
              $("#subcategory_id").trigger("change");
          }
          const selectedOptions = $('#category_id').val();
          getSubCategory(selectedOptions);
      })
      $('#subcategory_id').on("select2:unselect", function(e) {
          const selectedOptions = $('#category_id').val();
          getSubCategory(selectedOptions);
      })

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
        apiKey: "AIzaSyD01ZirzN4kdhc18wc13YvorEUJoocC4Pc",
        authDomain: "civilbook-local-and-dev.firebaseapp.com",
        projectId: "civilbook-local-and-dev",
        storageBucket: "civilbook-local-and-dev.appspot.com",
        messagingSenderId: "730217664763",
        appId: "1:730217664763:web:e974d05730d07c1182a05e"
      };

      firebase.initializeApp(firebaseConfig);
      $('.select2js').select2({
                  width: '100%',
            // dropdownParent: jQuery(this).parent()
          });
      sendOtpBtn.on('click', () => {
        $(this).prop('disabled', true)
        $(this).text('Please Wait...')

        const sendOtpButtonElement = document.getElementById('sendOtpBtn')


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
  });
      function initAutocomplete() {
          var input = document.getElementById('address');
          var autocomplete = new google.maps.places.Autocomplete(input);

          autocomplete.addListener('place_changed', function () {
              var place = autocomplete.getPlace();
              document.getElementById('latitude').value = place.geometry.location.lat();
              document.getElementById('longitude').value = place.geometry.location.lng();
          });
      }
      function getCategory(department_id, category_id = "") {
          var get_category_list = "{{ route('ajax-list', [ 'type' => 'category','department' =>'']) }}" + department_id;
          get_category_list = get_category_list.replace('amp;', '');

          $.ajax({
              url: get_category_list,
              success: function (result) {
                  $('#category_id').select2({
                      width: '100%',
                      placeholder: "{{ trans('messages.select_name',['select' => trans('messages.category')]) }}",
                      data: result.results
                  });
                  if (category_id != "") {
                      $('#category_id').val(category_id).trigger('change');
                  }
              }
          });
      }

      function getUserType(role_id) {
          var get_subcategory_list =  "{{ url('api/type-list?request_from=web&type=') }}"+role_id;

          get_subcategory_list = get_subcategory_list.split('amp;').join('');

          $.ajax({
              url: get_subcategory_list,
              success: function(result) {
                  console.log(result.data);

                  $('#user_type_id').select2({
                      width: '100%',
                      placeholder: "{{ trans('messages.select_name',['select' => trans('messages.subcategory')]) }}",
                      data: result.results
                  });
                  //$('#subcategory_id').trigger('change');
              }
          });
      }
      function getSubCategory(category_id) {
          var get_subcategory_list = "{{ route('ajax-list', [ 'type' => 'subcategory_list','multiple_category'=>'yes','category_id' =>'']) }}" + category_id;
          get_subcategory_list = get_subcategory_list.split('amp;').join('');

          $.ajax({
              url: get_subcategory_list,
              success: function(result) {

                  $('#subcategory_id').select2({
                      width: '100%',
                      placeholder: "{{ trans('messages.select_name',['select' => trans('messages.subcategory')]) }}",
                      data: result.results
                  });
                  //$('#subcategory_id').trigger('change');
              }
          });
      }
  </script>
