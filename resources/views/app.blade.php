<!DOCTYPE html>
<html lang="{{ App::currentLocale() }}">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet" />
    <script src="{{ mix('/js/app.js') }}" defer></script>

    <noscript>
        <title>Mismatch Finder</title>
        <link href="{{ mix('/css/noscript.css') }}" rel="stylesheet" />
       <main class="content-wrap">
           <header>
               <a href="/" class="logo-link"><div class="mismatch-finder-logo"></div></a>
           </header>
            <div class="message-wrapper">
                <div class="cdx-message cdx-message--block cdx-message--warning" role="alert">
                    <span class="cdx-message__icon"></span>
                    <div class="cdx-message__content">
                        <p><strong>Your browser doesn’t support Wikidata’s Mismatch Finder..</strong></p>
                        <p>In order to view and use this tool, you must switch to another browser or enable JavaScript. Learn how to do that in <a href="https://support.google.com/adsense/answer/12654">Chrome</a>, <a href="https://support.mozilla.org/en-US/kb/javascript-settings-for-interactive-web-pages#w_websites-ask-you-to-enable-javascript">Firefox</a>, <a href="https://support.apple.com/lt-lt/guide/safari/ibrw1074/mac">Safari</a>, <a href="https://www.enablejavascript.io/en/how-to-enable-javascript-on-microsoft-edge">Edge</a> or <a href="https://help.opera.com/en/latest/web-preferences/">Opera</a>.</p>
                    </div>
                </div>
            </div>
           <div class="description">
               <h2 class="h5">About Mismatch Finder</h2>
               <p>Mismatch Finder shows you data in Wikidata that differs from the data in other databases, catalogs or websites. You can use this tool to review and correct said mismatches. <a href="https://www.wikidata.org/wiki/Wikidata:Mismatch_Finder">More information</a></p>
           </div>
       </main>
    </noscript>
  </head>
  <body class="app-container" dir="auto">
    @inertia
  </body>
</html>
