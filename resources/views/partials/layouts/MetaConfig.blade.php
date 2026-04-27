{{--
    resources/views/partials/meta-config.blade.php
    Di-include di layouts/app.blade.php di dalam <head>
    Inject config dari .env ke JS via meta tag — aman karena tidak expose secret
--}}
<meta name="gas-data-url"    content="{{ config('gas.data_url') }}">
<meta name="gas-historis-url" content="{{ config('gas.historis_url') }}">