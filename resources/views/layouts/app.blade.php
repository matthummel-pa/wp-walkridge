<!doctype html>
<html @php(language_attributes())>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Inline theme script — runs before first paint to prevent light-mode flash --}}
    <script>
      (function(){
        var s = localStorage.getItem('wr-theme');
        var p = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
        if((s || p) === 'light') document.documentElement.setAttribute('data-theme','light');
      })();
    </script>
    @php(do_action('get_header'))
    @php(wp_head())

    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>

  <body @php(body_class())>
    @php(wp_body_open())

    <div id="app">
      <a class="skip-link" href="#main">
        {{ __('Skip to main content', 'walkridge') }}
      </a>

      @include('sections.header')

      <main id="main" class="main" tabindex="-1">
        @yield('content')
      </main>

      @hasSection('sidebar')
        <aside class="sidebar">
          @yield('sidebar')
        </aside>
      @endif

      @include('sections.footer')
    </div>

    @if(\App\Support\Identity::showDemoChrome())
      <a href="{{ home_url('/') }}" class="concept-badge" aria-label="{{ __('This is a design concept by Matt Hummel', 'walkridge') }}">
        <span class="dot" aria-hidden="true"></span> {{ __('Concept · Matt Hummel', 'walkridge') }}
      </a>
    @endif

    @php(do_action('get_footer'))
    @php(wp_footer())
  </body>
</html>
