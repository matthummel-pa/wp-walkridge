@php
  use App\Support\Identity;
  $shopUrl = Identity::shopUrl();
  $hasPrimary = has_nav_menu('primary_navigation');
  $req = trim($GLOBALS['wp']->request ?? '', '/');
  $isHome = ($req === '' || is_front_page());
  $isTours = str_starts_with($req, 'tours');
  $isGuides = str_starts_with($req, 'guides');
  $isArea = str_starts_with($req, 'area');
  $isContact = str_starts_with($req, 'contact');
@endphp

<header class="site-header" role="banner">
  <div class="header-rail">
    <div class="wrap">
      <span>{{ Identity::railLeft() }}</span>
      <span>{{ Identity::railRight() }}</span>
    </div>
  </div>
  <div class="wrap header-inner">
    @if(has_custom_logo())
      <div class="brand brand--logo">@php(the_custom_logo())</div>
    @else
      <a href="{{ home_url('/') }}" class="brand">
        <svg class="brand-mark" width="40" height="40" viewBox="0 0 40 40" fill="none" aria-hidden="true"><circle cx="20" cy="20" r="18.5" stroke="currentColor" stroke-width="1.25"/><path d="M20 5l3.2 15L20 35l-3.2-15z" fill="currentColor"/><path d="M5 20l15-3.2L35 20l-15 3.2z" fill="currentColor" opacity=".4"/><circle cx="20" cy="20" r="3.2" fill="currentColor"/></svg>
        <span class="brand-text">
          <span class="brand-name">{{ Identity::brandName() }}</span>
          <span class="brand-sub">{{ Identity::brandSub() }}</span>
        </span>
      </a>
    @endif
    <nav class="main-nav" aria-label="{{ __('Primary', 'walkridge') }}">
      @if($hasPrimary)
        {!! wp_nav_menu([
          'theme_location' => 'primary_navigation',
          'container' => false,
          'menu_class' => '',
          'echo' => false,
          'depth' => 2,
          'fallback_cb' => false,
        ]) !!}
      @else
        <ul>
          <li class="has-sub">
            <button type="button" class="nav-trigger @if($isTours) is-active @endif" aria-expanded="false" aria-controls="nav-tours-panel" id="nav-tours-trigger">{{ __('Tours', 'walkridge') }}</button>
            <div class="nav-panel" id="nav-tours-panel" role="region" aria-labelledby="nav-tours-trigger" hidden>
              <a href="{{ home_url('/tours') }}#historical">{{ __('Historical battlefield tours', 'walkridge') }}<small>{{ __('Walking, bus, hike, and private sunrise', 'walkridge') }}</small></a>
              <a href="{{ home_url('/tours') }}#after-dark">{{ __('After-dark lantern walk', 'walkridge') }}<small>{{ __('History after dark downtown — not a gimmick', 'walkridge') }}</small></a>
              <a href="{{ home_url('/tours') }}">{{ __('See all tours', 'walkridge') }}<small>{{ __('Filter by day field or evening', 'walkridge') }}</small></a>
              <a href="{{ home_url('/contact') }}#groups">{{ __('Groups & schools', 'walkridge') }}<small>{{ __('Families, reunions, and classrooms', 'walkridge') }}</small></a>
              <a href="{{ home_url('/contact') }}#gifts">{{ __('Gift certificates', 'walkridge') }}<small>{{ __('Give the gift of history', 'walkridge') }}</small></a>
            </div>
          </li>
          <li><a href="{{ home_url('/guides') }}" @if($isGuides) class="is-active" aria-current="page" @endif>{{ __('Guides', 'walkridge') }}</a></li>
          <li><a href="{{ home_url('/area') }}" @if($isArea) class="is-active" aria-current="page" @endif>{{ __('The Area', 'walkridge') }}</a></li>
          <li><a href="{{ home_url('/contact') }}" @if($isContact) class="is-active" aria-current="page" @endif>{{ __('Contact', 'walkridge') }}</a></li>
        </ul>
      @endif
    </nav>
    <div class="header-actions">
      <a href="{{ Identity::phoneHref() }}" class="btn btn-ghost btn-sm header-phone">{{ Identity::phone() }}</a>
      <a href="{{ $shopUrl }}" class="btn btn-primary btn-sm">{{ Identity::ctaLabel() }}</a>
      <button type="button" class="theme-toggle" id="themeToggle"
              aria-label="{{ __('Toggle colour theme', 'walkridge') }}"
              data-label-light="{{ __('Switch to dark mode', 'walkridge') }}"
              data-label-dark="{{ __('Switch to light mode', 'walkridge') }}">
        <svg class="icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z"/>
        </svg>
        <svg class="icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <circle cx="12" cy="12" r="5"/>
          <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
        </svg>
      </button>
      <button type="button" class="hamburger" id="hamburgerBtn"
              aria-expanded="false" aria-controls="mobileNav" aria-label="{{ __('Open menu', 'walkridge') }}">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>
<nav class="mobile-nav" id="mobileNav" aria-label="{{ __('Mobile navigation', 'walkridge') }}" role="dialog" aria-modal="true" hidden>
  <ul>
    <li><a href="{{ home_url('/') }}" @if($isHome) class="is-active" aria-current="page" @endif>{{ __('Home', 'walkridge') }}</a></li>
    <li><a href="{{ home_url('/tours') }}" @if($isTours) class="is-active" aria-current="page" @endif>{{ __('All Tours', 'walkridge') }}</a></li>
    <li class="sub"><a href="{{ home_url('/tours') }}#historical">{{ __('Historical tours', 'walkridge') }}</a></li>
    <li class="sub"><a href="{{ home_url('/tours') }}#after-dark">{{ __('After-dark lantern walk', 'walkridge') }}</a></li>
    <li><a href="{{ home_url('/guides') }}" @if($isGuides) class="is-active" aria-current="page" @endif>{{ __('Our Guides', 'walkridge') }}</a></li>
    <li><a href="{{ home_url('/area') }}" @if($isArea) class="is-active" aria-current="page" @endif>{{ __('The Area', 'walkridge') }}</a></li>
    <li><a href="{{ home_url('/contact') }}" @if($isContact) class="is-active" aria-current="page" @endif>{{ __('Contact', 'walkridge') }}</a></li>
    <li><a href="{{ $shopUrl }}">{{ __('Book & Pay', 'walkridge') }}</a></li>
  </ul>
  <?php /* translators: %s: office phone number */ ?>
  <a href="{{ Identity::phoneHref() }}" class="btn btn-outline btn-block">{{ sprintf(__('Call %s', 'walkridge'), Identity::phone()) }}</a>
  <a href="{{ $shopUrl }}" class="btn btn-primary btn-block">{{ Identity::ctaLabel() }}</a>
</nav>
