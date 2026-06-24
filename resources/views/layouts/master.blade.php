<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>

    <!-- Meta data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta content="landville" name="description">
    <meta content="JayP UG Private Technologies" name="author">
    <meta name="keywords" content="land, ville, lan, landville, propatiz, linked" />

    <!-- Title -->
    <title>EDUMALL | {{session('title')}}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <!--Favicon -->
    <link rel="icon" href="https://i.imghippo.com/files/R3552HkQ.png" type="image/x-icon" />

    <!-- Bootstrap css -->
    <link href="../../assets/plugins/bootstrap/css/bootstrap.css" rel="stylesheet" />

    <!-- Style css -->
    <link href="../../assets/css/style.css" rel="stylesheet" />

    <!-- Dark css -->
    <link href="../../assets/css/dark.css" rel="stylesheet" />

    <!-- Skins css -->
    <link href="../../assets/css/skins.css" rel="stylesheet" />

    <!-- Animate css -->
    <link href="../../assets/css/animated.css" rel="stylesheet" />

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!--Sidemenu css -->
    <link id="theme" href="../../assets/css/sidemenu.css" rel="stylesheet">

    <!-- P-scroll bar css-->
    <link href="../../assets/plugins/p-scrollbar/p-scrollbar.css" rel="stylesheet" />

    <!---Icons css-->
    <link href="../../assets/plugins/web-fonts/icons.css" rel="stylesheet" />
    <link href="../../assets/plugins/web-fonts/font-awesome/font-awesome.min.css" rel="stylesheet">
    <link href="../../assets/plugins/web-fonts/plugin.css" rel="stylesheet" />

    <!-- Data table css -->
    <link href="../../assets/plugins/datatable/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
    <link href="../../assets/plugins/datatable/css/buttons.bootstrap4.min.css" rel="stylesheet">
    <link href="../../assets/plugins/datatable/responsive.bootstrap4.min.css" rel="stylesheet" />

    <!-- Slect2 css -->
    <link href="../../assets/plugins/select2/select2.min.css" rel="stylesheet" />
    <link href="../../assets/plugins/accordion/accordion.css" rel="stylesheet" />

    <style>
        .image-size {
            height: 200px;
            /* set the desired height */
            object-fit: cover;
            /* set the object-fit property to cover */
        }
    </style>

</head>

<body class="app sidebar-mini light-mode default-sidebar">

    <!---Global-loader-->
    {{--<div id="global-loader">--}}
        {{--<img src="../../assets/images/svgs/loader.svg" alt="loader">--}}
        {{--</div>--}}

    <div class="page">
        <div class="page-main">
            <!--aside open-->
            <div class="app-sidebar app-sidebar2">
                <div class="app-sidebar__logo">
                    <a class="header-brand" href="{{route('dashboard')}}">
                        {{--<h3 style="color: #5eb95d; margin-top: 10px;">{{config('app.name')}}</h3>--}}
                        <h4
                            style="color: white; margin-top: 10px; background: #00138f; padding-top: 12px; padding-bottom:12px; border-radius: 6px">
                            EDUMALL</h4>

                    </a>
                </div>
            </div>
            <aside class="app-sidebar app-sidebar3">

                <ul class="side-menu">
                    <li class="slide">
                        <a class="side-menu__item" href="{{route('dashboard')}}">
                            <svg class="side-menu__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="26"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg>
                            <span class="side-menu__label">Dashboard</span></a>

                    </li>
                    <li class="slide">
                        <a class="side-menu__item" href="{{route('labs.index')}}">
                             <svg class="side-menu__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                                <polyline points="2 17 12 22 22 17"></polyline>
                                <polyline points="2 12 12 17 22 12"></polyline>
                            </svg>
                            <span class="side-menu__label">Products</span></a>
                    </li>
                    <li class="slide">
                        <a class="side-menu__item" href="{{route('web.schools.all')}}">
                            <svg class="side-menu__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span class="side-menu__label">Schools</span></a>
                    </li>
                    <li class="slide">
                        <a class="side-menu__item" href="{{route('users.index')}}">

                            <svg class="side-menu__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span class="side-menu__label">Users</span></a>
                    </li>
                    <li class="slide">
                        <a class="side-menu__item" href="{{route('orders.index')}}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="side-menu__icon">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                            <span class="side-menu__label">Orders Made</span></a>
                    </li>
                    <li class="slide">
                        <a class="side-menu__item" href="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="side-menu__icon">
                                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
                            </svg>
                            <span class="side-menu__label">Suppliers</span></a>
                    </li>




                </ul>

            </aside>
            <!--aside closed-->

            <div class="app-content main-content">
                <div class="side-app">

                    <!--app header-->
                    <div class="app-header header top-header">
                        <div class="container-fluid">
                            <div class="d-flex">
                                <a class="header-brand" href="{{route('dashboard')}}">

                                </a>
                                <div class="dropdown side-nav">
                                    <div class="app-sidebar__toggle" data-toggle="sidebar">
                                        <a class="open-toggle" href="#">
                                            <svg class="header-icon mt-1" xmlns="http://www.w3.org/2000/svg" width="24"
                                                height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                                <line x1="3" y1="18" x2="21" y2="18"></line>
                                            </svg>
                                        </a>
                                        <a class="close-toggle" href="#">
                                            <svg class="header-icon mt-1" xmlns="https://i.imghippo.com/files/ynwR3604kw.png" height="24"
                                                viewBox="0 0 24 24" width="24">
                                                <path d="M0 0h24v24H0V0z" fill="none" />
                                                <path
                                                    d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="dropdown  header-option">
                                    <a class="nav-link icon p-0" data-toggle="dropdown">
                                        <span class="nav-span">
                                            <h3 style="margin-top: 20px;">ADMIN PANEL</h3>
                                        </span>
                                    </a>
                                </div>
                                <div class="d-flex order-lg-2 ml-auto">


                                    <div class="dropdown profile-dropdown">
                                        <a href="#" class="nav-link pr-0 leading-none" data-toggle="dropdown">
                                            <span>
                                                @if(Auth::check() && Auth::user()->image)
                                                    <img src="{{ asset(Auth::user()->image) }}" alt="img" style="height: 60px; width:60px;">
                                                @else
                                                    <img src="{{ asset('images/placeholder.png') }}" alt="img" style="height: 60px; width:60px;">
                                                @endif
                                                <img src="https://i.imghippo.com/files/R3552HkQ.png"
                                                    class="avatar avatar-sm brround" style="height: 60px; width:60px;">
                                            </span>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow animated">
                                            <div class="text-center">
                                               @if(Auth::check())
                                                    <a href="#"
                                                    class="dropdown-item text-center user pb-0 font-weight-bold">
                                                    {{ Auth::user()->first_name . " " . Auth::user()->last_name }}
                                                    </a>
                                                    <span class="text-center user-semi-title">System Administrator</span>
                                                    <div class="dropdown-divider"></div>
                                                @endif
                                            </div>
                                            {{--<a class="dropdown-item d-flex" href="#">--}}
                                                {{--<svg class="header-icon mr-3" x="1008" y="1248" viewBox="0 0 24 24"
                                                    height="100%" width="100%" preserveAspectRatio="xMidYMid meet"
                                                    focusable="false">--}}
                                                    {{--
                                                    <path d="M0 0h24v24H0V0z" fill="none" />
                                                    <path d="M12 16c-2.69 0-5.77 1.28-6 2h12c-.2-.71-3.3-2-6-2z"
                                                        opacity=".3" />
                                                    <circle cx="12" cy="8" opacity=".3" r="2" />
                                                    <path
                                                        d="M12 14c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4zm-6 4c.22-.72 3.31-2 6-2 2.7 0 5.8 1.29 6 2H6zm6-6c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0-6c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2z" />
                                                    --}}
                                                    {{--
                                                </svg>--}}
                                                {{--<div class="mt-1">Profile</div>--}}
                                                {{--</a>--}}
                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf

                                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
                                                this.closest('form').submit();" class="dropdown-item d-flex">

                                                    <svg class="header-icon mr-3" x="1008" y="1248" viewBox="0 0 24 24"
                                                        height="100%" width="100%" preserveAspectRatio="xMidYMid meet"
                                                        focusable="false">
                                                        <path d="M0 0h24v24H0V0zm0 0h24v24H0V0z" fill="none" />
                                                        <path d="M6 20h12V10H6v10zm2-6h3v-3h2v3h3v2h-3v3h-2v-3H8v-2z"
                                                            opacity=".3" />
                                                        <path
                                                            d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM8.9 6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2H8.9V6zM18 20H6V10h12v10zm-7-1h2v-3h3v-2h-3v-3h-2v3H8v2h3z" />
                                                    </svg>
                                                    <div class="mt-1">
                                                        {{ __('Log Out') }}
                                                    </div>
                                                </x-dropdown-link>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--/app header-->

                    @yield('content')

                </div>
            </div><!-- end app-content-->
        </div>

        <!--Footer-->
        <footer class="footer">
            <div class="container">
                <div class="row align-items-center flex-row-reverse">
                    <div class="col-md-12 col-sm-12 mt-3 mt-lg-0 text-center">
                        Copyright © 2026 <a href="#">{{config('app.name')}}</a>. Designed by <a href="#">Sir JayP</a>
                        All rights reserved.
                    </div>
                </div>
            </div>
        </footer>
        <!-- End Footer-->

    </div>

    <!-- Back to top -->
    <a href="#top" id="back-to-top">
        <svg class="svg-icon" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24">
            <path d="M0 0h24v24H0V0z" fill="none" />
            <path d="M4 12l1.41 1.41L11 7.83V20h2V7.83l5.58 5.59L20 12l-8-8-8 8z" />
        </svg>
    </a>



    <!-- Jquery js-->
    <script src="../../assets/js/vendors/jquery-3.5.1.min.js"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Sidebar Navigation
    document.querySelectorAll('.sidebar a').forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        document.querySelectorAll('.content-section').forEach(section => {
          section.classList.add('d-none');
        });
        document.querySelector(targetId).classList.remove('d-none');
        document.querySelectorAll('.sidebar a').forEach(link => {
          link.classList.remove('active');
        });
        this.classList.add('active');
      });
    });

    // General preview functions (for images/videos) as in your existing code...
    function previewImage(input) {
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          $(input).closest('.card-upload').find('.preview-image').attr('src', e.target.result).show();
        }
        reader.readAsDataURL(input.files[0]);
      }
    }

    function previewMedia(input) {
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        const isImage = input.files[0].type.startsWith('image/');
        const isVideo = input.files[0].type.startsWith('video/');
        reader.onload = function(e) {
          const cardUpload = $(input).closest('.card-upload');
          const imagePreview = cardUpload.find('.preview-image');
          const videoPreview = cardUpload.find('.preview-video');
          const videoSource = videoPreview.find('source');
          if (isImage) {
            imagePreview.attr('src', e.target.result).show();
            videoPreview.hide();
          } else if (isVideo) {
            videoSource.attr('src', e.target.result);
            videoPreview[0].load();
            videoPreview.show();
            imagePreview.hide();
          }
        }
        reader.readAsDataURL(input.files[0]);
      }
    }
  </script>

    <!-- Bootstrap4 js-->
    <script src="../../assets/plugins/bootstrap/popper.min.js"></script>
    <script src="../../assets/plugins/bootstrap/js/bootstrap.min.js"></script>

    <!--Othercharts js-->
    <script src="../../assets/plugins/othercharts/jquery.sparkline.min.js"></script>

    <!-- Circle-progress js-->
    <script src="../../assets/js/vendors/circle-progress.min.js"></script>

    <!-- Jquery-rating js-->
    <script src="../../assets/plugins/rating/jquery.rating-stars.js"></script>

    <!--Sidemenu js-->
    <script src="../../assets/plugins/sidemenu/sidemenu.js"></script>

    <!-- P-scroll js-->
    <script src="../../assets/plugins/p-scrollbar/p-scrollbar.js"></script>
    <script src="../../assets/plugins/p-scrollbar/p-scroll1.js"></script>

    <!-- Data tables -->
    <script src="../../assets/plugins/datatable/js/jquery.dataTables.js"></script>
    <script src="../../assets/plugins/datatable/js/dataTables.bootstrap4.js"></script>
    <script src="../../assets/plugins/datatable/js/dataTables.buttons.min.js"></script>
    <script src="../../assets/plugins/datatable/js/buttons.bootstrap4.min.js"></script>
    <script src="../../assets/plugins/datatable/js/jszip.min.js"></script>
    <script src="../../assets/plugins/datatable/js/pdfmake.min.js"></script>
    <script src="../../assets/plugins/datatable/js/vfs_fonts.js"></script>
    <script src="../../assets/plugins/datatable/js/buttons.html5.min.js"></script>
    <script src="../../assets/plugins/datatable/js/buttons.print.min.js"></script>
    <script src="../../assets/plugins/datatable/js/buttons.colVis.min.js"></script>
    <script src="../../assets/plugins/datatable/dataTables.responsive.min.js"></script>
    <script src="../../assets/plugins/datatable/responsive.bootstrap4.min.js"></script>
    <script src="../../assets/js/datatables.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
        var elements = document.querySelectorAll(".limited-desc");
        elements.forEach(function(element) {
            var originalText = element.textContent;
            var maxLength = 20; // Adjust this to your desired character limit
            if (originalText.length > maxLength) {
                var truncatedText = originalText.substr(0, maxLength) + "…"; // Adding an ellipsis
                element.textContent = truncatedText;
            }
        });
    });
    </script>
    <!-- Select2 js -->
    <script src="../../assets/plugins/select2/select2.full.min.js"></script>

    <!-- Custom js-->
    <script src="../../assets/js/custom.js"></script>

    <!-- Accordion js-->
    <script src="../../assets/plugins/accordion/accordion.min.js"></script>
    <script src="../../assets/js/accordion.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('scripts')
</body>

</html>
