<script src="{{ URL::asset('build/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/node-waves/waves.min.js') }}"></script>
<script src="{{ URL::asset('build/libs/feather-icons/feather.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
<script src="{{ URL::asset('build/js/plugins.js') }}"></script>
<!-- App Js: sidebar scroll (SimpleBar), menu active-state, layout init -
     must load on every page, not just the ones that happened to include
     it in their own page-level script section, or the sidebar never
     becomes scrollable and menu behavior is inconsistent page to page. -->
<script src="{{ URL::asset('build/js/app.js') }}"></script>
@yield('script')
@yield('script-bottom')
