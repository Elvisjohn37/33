<!DOCTYPE html> 
<html lang="@lang_id()">

    <head>
        <title>@Lang('custom.theme.title')</title>

        {{-- Icons  --}}
        <link rel='shortcut icon'               href="{{ asset('assets/images/favicon/favicon32.png') }}">
        <link rel='apple-touch-icon image_src'  href="{{ asset('assets/images/favicon/favicon64.png') }}">
        
        {{-- Metas --}}
        <meta charset='utf-8'>
        <meta name='apple-touch-fullscreen'       content='yes'>
        <meta name='apple-mobile-web-app-capable' content='yes'>
        <meta http-equiv='X-UA-Compatible' 	      content='IE=edge'>
        <meta name='apple-mobile-web-app-capable' content='yes'>
        <meta name='mobile-web-app-capable'       content='yes'>
        <meta name='description'                  content="@Lang('custom.seo.meta_desc')">
        <meta name='viewport' 					  content='width=768px'>
        <meta name="theme-color"                  content="#600001" />

        {{-- Inline style  - Style for loading screen only --}}
        <style>@inline_script('css','ps_init.css')</style>

        {{-- Inline script - Assets config and Site initiator --}}
        <script type = 'text/javascript' id= 'ps_js-initjs'>
            var ps_global_config = {
                                    @if (isset($view_data))
                                        view_data: {!! json_encode($view_data) !!},
                                    @endif

                                    payload        : {!! json_encode($payload) !!},
                                    token          : {!! json_encode($token) !!},
                                    rso            : @rso_full(),
                                    debug          : @config('app.debug'),
                                    root_url       : {!! json_encode(URL::to('')) !!},
                                    site_signature : @config('settings.WL_CODE')+@config('settings.IS_MOBILE_PLATFORM')
                                };
            @inline_script('js', 'ps_init.js')
        </script>
    </head>

    <body class="{{ Auth::check() ? 'ps_core-after_login' : 'ps_core-before_login'  }}">
        {{-- Seo --}}
        <section class='ps-secret_weapon'>
            <h1>@Lang('custom.seo.h1')</h1>
            <h2>@Lang('custom.seo.h2')</h2>
            <h3>@Lang('custom.seo.h3')</h3>
        </section>

        {{-- Each view type has its own markup builds --}}
        @include('types.'.$view_type)

        {{-- Loading Screen --}}
        <section class='ps_js-loading_screen ps_init-loading_screen'> 
            <div class='ps_init-loading_stage'>
                <div class='ps_init-loading_wrap'>
                    <div class='ps_init-logo_circle ps_init-logo ps_init-logo_left'></div>
                    <div class='ps_init-logo_right'>
                        <div class='ps_init-logo_text ps_init-logo'></div>
                        <div class='ps_init-logo_sub_text'>
                            <span class='ps_init-sub_text'>ONLINE</span>
                            <span class='ps_init-sub_text'>CASINO</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </body>
</html>