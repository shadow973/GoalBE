<html>
<head>
    <meta charset="utf-8">
    <title>Setanta Admin</title>
    <base href="/admin">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="icon" type="image/png" href="assets/favicon.png">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="/assets/ckeditor/ckeditor.js"></script>
    <style>.cke{visibility:hidden;}</style>
    <link href="/assets/admin/css/styles.bundle.css" rel="stylesheet">
    <link href="/assets/admin/css/styles.custuom.css" rel="stylesheet">

</head>
<body>
<app-root ng-version="2.4.10" _nghost-dsw-2="">
    <router-outlet _ngcontent-dsw-2=""></router-outlet>
    <app-secure _nghost-dsw-5="">
        <app-header _ngcontent-dsw-5="" _nghost-dsw-8="">
            <div _ngcontent-dsw-8="" class="header">
                <div _ngcontent-dsw-8="" class="header__logo">
                    Setanta Admin
                </div>
                <div _ngcontent-dsw-8="" class="header__view">
                    <i _ngcontent-dsw-8="" class="material-icons md-48">toc</i>
                </div>
                <a _ngcontent-dsw-8="" class="header__btn ml-auto" href="//setanta.ge" target="_blank">
                    <i _ngcontent-dsw-8="" class="material-icons">open_in_new</i>
                </a>
                <button _ngcontent-dsw-8="" class="header__btn" type="button">
                    <i _ngcontent-dsw-8="" class="material-icons">exit_to_app</i>
                </button>
            </div>
        </app-header>
        <app-sidebar _ngcontent-dsw-5="" _nghost-dsw-9="">
            <div _ngcontent-dsw-9="" class="sidebar">
                @include('admin.components.menu')
            </div>
        </app-sidebar>
        <app-content _ngcontent-dsw-5="" _nghost-dsw-10="">
            @yield('content')
        </app-content>
    </app-secure>
</app-root>
{{--<script type="text/javascript" src="assets/admin/js/inline.bundle.js"></script>--}}
<script type="text/javascript" src="assets/admin/js/scripts.bundle.js"></script>
{{--<script type="text/javascript" src="assets/admin/js/vendor.bundle.js"></script>--}}
{{--<script type="text/javascript" src="assets/admin/js/main.bundle.js"></script>--}}
<div id="ui-datepicker-div" class="ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all"></div>
</body>
</html>