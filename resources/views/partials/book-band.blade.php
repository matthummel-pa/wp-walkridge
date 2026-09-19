@php
  use App\Support\Identity;
  $attrs = is_array($attrs ?? null) ? $attrs : [];
  $heading = $heading ?: __('Reserve your spot in three minutes.', 'walkridge');
  $text = $text ?: __('Pick a tour, choose a date, and check out securely. You will get a confirmation with your ticket number by email.', 'walkridge');
  $buttonLabel = $buttonLabel ?: __('Start Booking', 'walkridge');
  $shopUrl = Identity::shopUrl();
  $sectionClass = function_exists('App\\wr_band_section_class')
    ? \App\wr_band_section_class($attrs, 'section')
    : 'section';
  $headClass = function_exists('App\\wr_head_class')
    ? \App\wr_head_class($attrs, '')
    : '';
  $headTag = function_exists('App\\wr_heading_tag')
    ? \App\wr_heading_tag($attrs)
    : 'h2';
@endphp

<section class="{{ esc_attr($sectionClass) }}">
  <div class="wrap">
    <div class="book-band reveal {{ esc_attr($headClass) }}">
      <div>
        <span class="eyebrow eyebrow--light">{{ __('Ready When You Are', 'walkridge') }}</span>
        <{{ esc_attr($headTag) }}>{{ $heading }}</{{ esc_attr($headTag) }}>
        <p>{{ $text }}</p>
        <div class="trust-strip">
          <div class="trust-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>
            {{ __('Secure checkout', 'walkridge') }}
          </div>
          <div class="trust-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
            {{ __('Free cancellation up to 24h', 'walkridge') }}
          </div>
          <div class="card-icons" aria-hidden="true">
            <svg width="34" height="22" viewBox="0 0 34 22"><rect width="34" height="22" rx="4" fill="#1a3b6d"/><text x="17" y="14" font-family="Arial" font-size="8" fill="#fff" text-anchor="middle" font-weight="bold">VISA</text></svg>
            <svg width="34" height="22" viewBox="0 0 34 22"><rect width="34" height="22" rx="4" fill="#2b2523"/><circle cx="14" cy="11" r="6" fill="#eb001b"/><circle cx="20" cy="11" r="6" fill="#f79e1b" opacity="0.85"/></svg>
            <svg width="34" height="22" viewBox="0 0 34 22"><rect width="34" height="22" rx="4" fill="#2e77bc"/><text x="17" y="14" font-family="Arial" font-size="6.5" fill="#fff" text-anchor="middle" font-weight="bold">AMEX</text></svg>
            <svg width="34" height="22" viewBox="0 0 34 22"><rect width="34" height="22" rx="4" fill="#f4f0e6"/><text x="17" y="14" font-family="Arial" font-size="6" fill="#a3341f" text-anchor="middle" font-weight="bold">DISC</text></svg>
          </div>
        </div>
      </div>
      <a href="{{ esc_url($shopUrl) }}" class="btn btn-primary book-band__cta">{{ $buttonLabel }}</a>
    </div>
  </div>
</section>
