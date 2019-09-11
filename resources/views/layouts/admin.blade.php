<!DOCTYPE html>
<html lang="ru">
<html>
	<head>
		<meta charset="utf-8" />
		<title>Xnova Admin Panel - {{ $title ?? '' }}</title>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport" />
		<meta content="" name="description" />
		<meta content="" name="author" />
		<link rel="shortcut icon" href="/favicon.ico" />

		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,600,700">
		<link href="{{ asset('assets/admin/src/scss/vendors/flaticon/flaticon.css') }}" rel="stylesheet">
		<link href="{{ asset('assets/admin/src/scss/vendors/flaticon2/flaticon.css') }}" rel="stylesheet">
		<link href="{{ asset('node_modules/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet">
		<link href="{{ asset('assets/build/admin.css') }}" rel="stylesheet">

		<script src="{{ asset('node_modules/jquery/dist/jquery.min.js')}}"></script>
		<script src="{{ asset('node_modules/popper.js/dist/umd/popper.js')}}"></script>
		<script src="{{ asset('node_modules/bootstrap/dist/js/bootstrap.min.js')}}"></script>
		<script src="{{ asset('node_modules/perfect-scrollbar/dist/perfect-scrollbar.js')}}"></script>
		<script src="{{ asset('node_modules/js-cookie/src/js.cookie.js')}}"></script>
		<script src="{{ asset('node_modules/sticky-js/dist/sticky.min.js')}}"></script>
		<script src="{{ asset('assets/build/admin.js')}}"></script>

		@stack('assets')
	</head>
	<body class="kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header-mobile--fixed kt-subheader--enabled kt-subheader--fixed kt-subheader--solid kt-aside--enabled kt-aside--fixed">

		<div id="kt_header_mobile" class="kt-header-mobile  kt-header-mobile--fixed ">
			<div class="kt-header-mobile__logo">
				<a href="{{ route('admin.index', [], false) }}">
					GAME FRAMEWORK
				</a>
			</div>
			<div class="kt-header-mobile__toolbar">
				<button class="kt-header-mobile__toggler kt-header-mobile__toggler--left" id="kt_aside_mobile_toggler"><span></span></button>
				<button class="kt-header-mobile__topbar-toggler" id="kt_header_mobile_topbar_toggler"><i class="flaticon-more"></i></button>
			</div>
		</div>

		<div class="kt-grid kt-grid--hor kt-grid--root">
			<div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--ver kt-page">

				<div class="kt-aside  kt-aside--fixed  kt-grid__item kt-grid kt-grid--desktop kt-grid--hor-desktop" id="kt_aside">
					<div class="kt-aside__brand kt-grid__item " id="kt_aside_brand">
						<div class="kt-aside__brand-logo">
							<a href="{{ route('admin.index', [], false) }}">
								GAME FRAMEWORK
							</a>
						</div>
						<div class="kt-aside__brand-tools">
							<button class="kt-aside__brand-aside-toggler" id="kt_aside_toggler">
								<span>
									<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">
										<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
											<polygon points="0 0 24 0 24 24 0 24"/>
											<path d="M5.29288961,6.70710318 C4.90236532,6.31657888 4.90236532,5.68341391 5.29288961,5.29288961 C5.68341391,4.90236532 6.31657888,4.90236532 6.70710318,5.29288961 L12.7071032,11.2928896 C13.0856821,11.6714686 13.0989277,12.281055 12.7371505,12.675721 L7.23715054,18.675721 C6.86395813,19.08284 6.23139076,19.1103429 5.82427177,18.7371505 C5.41715278,18.3639581 5.38964985,17.7313908 5.76284226,17.3242718 L10.6158586,12.0300721 L5.29288961,6.70710318 Z" fill="#000000" fill-rule="nonzero" transform="translate(8.999997, 11.999999) scale(-1, 1) translate(-8.999997, -11.999999) "/>
											<path d="M10.7071009,15.7071068 C10.3165766,16.0976311 9.68341162,16.0976311 9.29288733,15.7071068 C8.90236304,15.3165825 8.90236304,14.6834175 9.29288733,14.2928932 L15.2928873,8.29289322 C15.6714663,7.91431428 16.2810527,7.90106866 16.6757187,8.26284586 L22.6757187,13.7628459 C23.0828377,14.1360383 23.1103407,14.7686056 22.7371482,15.1757246 C22.3639558,15.5828436 21.7313885,15.6103465 21.3242695,15.2371541 L16.0300699,10.3841378 L10.7071009,15.7071068 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(15.999997, 11.999999) scale(-1, 1) rotate(-270.000000) translate(-15.999997, -11.999999) "/>
										</g>
									</svg>
								</span>
								<span>
									<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">
										<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
											<polygon points="0 0 24 0 24 24 0 24"/>
											<path d="M12.2928955,6.70710318 C11.9023712,6.31657888 11.9023712,5.68341391 12.2928955,5.29288961 C12.6834198,4.90236532 13.3165848,4.90236532 13.7071091,5.29288961 L19.7071091,11.2928896 C20.085688,11.6714686 20.0989336,12.281055 19.7371564,12.675721 L14.2371564,18.675721 C13.863964,19.08284 13.2313966,19.1103429 12.8242777,18.7371505 C12.4171587,18.3639581 12.3896557,17.7313908 12.7628481,17.3242718 L17.6158645,12.0300721 L12.2928955,6.70710318 Z" fill="#000000" fill-rule="nonzero"/>
											<path d="M3.70710678,15.7071068 C3.31658249,16.0976311 2.68341751,16.0976311 2.29289322,15.7071068 C1.90236893,15.3165825 1.90236893,14.6834175 2.29289322,14.2928932 L8.29289322,8.29289322 C8.67147216,7.91431428 9.28105859,7.90106866 9.67572463,8.26284586 L15.6757246,13.7628459 C16.0828436,14.1360383 16.1103465,14.7686056 15.7371541,15.1757246 C15.3639617,15.5828436 14.7313944,15.6103465 14.3242754,15.2371541 L9.03007575,10.3841378 L3.70710678,15.7071068 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(9.000003, 11.999999) rotate(-270.000000) translate(-9.000003, -11.999999) "/>
										</g>
									</svg>
								</span>
							</button>
						</div>
					</div>

					<div class="kt-aside-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_aside_menu_wrapper">
						<div
							id="kt_aside_menu"
							class="kt-aside-menu "
							data-ktmenu-vertical="1"
							data-ktmenu-scroll="1" data-ktmenu-dropdown-timeout="500"
						>
							@component('admin.components.sidebar-menu')@endcomponent
						</div>
					</div>
				</div>
				<div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-wrapper" id="kt_wrapper">

					<div id="kt_header" class="kt-header kt-grid__item  kt-header--fixed " >
						<div class="kt-header-menu-wrapper"></div>
						<div class="kt-header__topbar">
							@component('admin.components.top-bar')@endcomponent
						</div>
					</div>
					<div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">
						<div class="kt-subheader kt-grid__item">
							<div class="kt-container kt-container--fluid">
								@component('admin.components.header-bar')@endcomponent
							</div>
						</div>
						<div class="kt-container  kt-container--fluid  kt-grid__item kt-grid__item--fluid">
							@if (session()->has('message'))
								{{ session('message') }}
							@endif
							<div class="row">
								<div class="col-12">
									@yield('content')
								</div>
							</div>
						</div>
					</div>
					<div class="kt-footer  kt-grid__item kt-grid kt-grid--desktop kt-grid--ver-desktop" id="kt_footer">
						<div class="kt-container  kt-container--fluid ">
							<div class="kt-footer__copyright">
								Copyright © {{ date('Y') }}&nbsp;<a href="http://xnova.su" class="kt-link">Xnova Online</a>. All rights reserved.
							</div>

							<div class="kt-footer__menu">
								<!-- <a href="" target="_blank" class="kt-footer__menu-link kt-link">About</a> -->
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script>
			var KTAppOptions = {};
		</script>
	</body>
</html>