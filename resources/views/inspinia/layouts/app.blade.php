<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INSPINIA - @yield('title') </title>


    <link rel="stylesheet" href="{!! asset('inspinia/css/vendor.css') !!}" />
    <link rel="stylesheet" href="{!! asset('inspinia/css/app.css') !!}" />

</head>
<body>

  <!-- Wrapper-->
    <div id="wrapper">

        <!-- Navigation -->
        @include('inspinia.layouts.navigation')

        <!-- Page wraper -->
        <div id="page-wrapper" class="gray-bg">

            <!-- Page wrapper -->
            @include('inspinia.layouts.topnavbar')

            <!-- Main view  -->
            @yield('content')

            <!-- Footer -->
            @include('inspinia.layouts.footer')

        </div>
        <!-- End page wrapper-->

    </div>
    <!-- End wrapper-->

<script src="{!! asset('inspinia/js/app.js') !!}" type="text/javascript"></script>

@section('scripts')
@show

</body>
</html>
