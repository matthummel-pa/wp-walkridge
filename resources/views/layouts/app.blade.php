<!doctype html>
<html @php(language_attributes()) data-wr-default-theme="{{ \App\Support\Identity::colorScheme() }}" @if(\App\Support\Identity::colorScheme() === 'light') data-theme="light" @endif>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- First paint is light unless Theme Settings is dark or the visitor used the 1.7+ toggle. --}}
    <script>
      (function(){
        var html = document.documentElement;
        var site = html.getAttribute('data-wr-default-theme') || 'light';
        var s = '';
        try {
          localStorage.removeItem('wr-theme');
          s = localStorage.getItem('wr-theme-pref') || '';
        } catch (e) {}
        var theme = (s === 'light' || s === 'dark') ? s : site;
        if (theme === 'light') html.setAttribute('data-theme', 'light');
        else html.removeAttribute('data-theme');
        html.style.colorScheme = theme === 'dark' ? 'dark' : 'light';
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
