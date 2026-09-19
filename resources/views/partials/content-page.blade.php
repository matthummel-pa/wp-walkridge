@php(the_content())

@if ($pagination())
  <nav class="page-nav" aria-label="{{ __('Page navigation', 'walkridge') }}">
    {!! wp_kses_post($pagination) !!}
  </nav>
@endif
