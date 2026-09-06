@php
  use App\Support\Identity;
  $shopUrl = Identity::shopUrl();
  $hasFooterMenu = has_nav_menu('footer_navigation');
@endphp

<footer class="site-footer" role="contentinfo">
  <div class="wrap">
    <div class="footer-cta">
      <div>
        <span class="eyebrow">{{ __('Field notes', 'walkridge') }}</span>
        <h2 class="footer-cta-heading">{{ __('Dates, weather calls, and new departures.', 'walkridge') }}</h2>
        <p class="footer-cta-sub">
          @if(Identity::showDemoChrome())
            <?php /* translators: %s: office location / address line */ ?>
            {{ sprintf(__('A short list from %s. Hook this into your ESP via the wr_newsletter_subscribed action.', 'walkridge'), Identity::addressLine()) }}
          @else
            <?php /* translators: %s: office location / address line */ ?>
            {{ sprintf(__('A short list from %s. Dates, weather calls, and new departures.', 'walkridge'), Identity::addressLine()) }}
          @endif
        </p>
        <p data-newsletter-note class="footer-newsletter-note" aria-live="polite" aria-atomic="true">
          @if(isset($_GET['wr_form']) && isset($_GET['wr_msg']))
            {{ sanitize_text_field(wp_unslash($_GET['wr_msg'])) }}
          @endif
        </p>
      </div>
      <form class="newsletter" data-newsletter method="post" action="{{ esc_url(admin_url('admin-post.php')) }}" data-wr-newsletter>
        <input type="hidden" name="action" value="wr_newsletter">
        {!! wp_nonce_field('wr_newsletter', 'wr_newsletter_nonce', true, false) !!}
        <label class="visually-hidden" for="nlEmail">{{ __('Email address for field notes list', 'walkridge') }}</label>
        <input id="nlEmail" name="EMAIL" type="email" autocomplete="email" placeholder="{{ esc_attr__('you@email.com', 'walkridge') }}" required>
        <button class="btn btn-primary" type="submit">{{ __('Join the list', 'walkridge') }}</button>
      </form>
    </div>
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="{{ home_url('/') }}" class="brand">
          <svg class="brand-mark" width="40" height="40" viewBox="0 0 40 40" fill="none" aria-hidden="true"><circle cx="20" cy="20" r="18.5" stroke="currentColor" stroke-width="1.25"/><path d="M20 5l3.2 15L20 35l-3.2-15z" fill="currentColor"/><path d="M5 20l15-3.2L35 20l-15 3.2z" fill="currentColor" opacity=".4"/><circle cx="20" cy="20" r="3.2" fill="currentColor"/></svg>
          <span class="brand-text"><span class="brand-name">{{ Identity::brandName() }}</span><span class="brand-sub">{{ Identity::brandSub() }}</span></span>
        </a>
        <p>{{ Identity::footerBlurb() }}</p>
        <div class="social-row">
          @if(Identity::socialFacebook())
            <?php /* translators: %s: office / brand name */ ?>
            <a href="{{ esc_url(Identity::socialFacebook()) }}" aria-label="{{ sprintf(__('%s on Facebook', 'walkridge'), Identity::brandName()) }}"><svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12a10 10 0 10-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0022 12z"/></svg></a>
          @endif
          @if(Identity::socialInstagram())
            <?php /* translators: %s: office / brand name */ ?>
            <a href="{{ esc_url(Identity::socialInstagram()) }}" aria-label="{{ sprintf(__('%s on Instagram', 'walkridge'), Identity::brandName()) }}"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg></a>
          @endif
          @if(Identity::socialTripadvisor())
            <?php /* translators: %s: office / brand name */ ?>
            <a href="{{ esc_url(Identity::socialTripadvisor()) }}" aria-label="{{ sprintf(__('%s on TripAdvisor', 'walkridge'), Identity::brandName()) }}"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="8" cy="14" r="3.2"/><circle cx="16" cy="14" r="3.2"/><path d="M2 9h6M16 9h6M12 6.5c-2.5 0-4.7 1-6.2 2.5M12 6.5c2.5 0 4.7 1 6.2 2.5"/></svg></a>
          @endif
        </div>
      </div>
      <div>
        <h3>{{ __('Explore', 'walkridge') }}</h3>
        @if($hasFooterMenu)
          {!! wp_nav_menu([
            'theme_location' => 'footer_navigation',
            'container' => false,
            'menu_class' => '',
            'echo' => false,
            'depth' => 1,
            'fallback_cb' => false,
          ]) !!}
        @else
          <ul>
            <li><a href="{{ home_url('/') }}">{{ __('Home', 'walkridge') }}</a></li>
            <li><a href="{{ home_url('/tours') }}">{{ __('All Tours', 'walkridge') }}</a></li>
            <li><a href="{{ home_url('/tours') }}#historical">{{ __('Historical tours', 'walkridge') }}</a></li>
            <li><a href="{{ home_url('/tours') }}#after-dark">{{ __('Lantern walk', 'walkridge') }}</a></li>
            <li><a href="{{ home_url('/guides') }}">{{ __('Our Guides', 'walkridge') }}</a></li>
            <li><a href="{{ home_url('/area') }}">{{ __('The Area', 'walkridge') }}</a></li>
          </ul>
        @endif
      </div>
      <div>
        <h3>{{ __('Plan', 'walkridge') }}</h3>
        <ul>
          <li><a href="{{ $shopUrl }}">{{ __('Book & Pay', 'walkridge') }}</a></li>
          <li><a href="{{ home_url('/contact') }}">{{ __('Contact & FAQ', 'walkridge') }}</a></li>
          <li><a href="{{ home_url('/contact') }}#gifts">{{ __('Gift certificates', 'walkridge') }}</a></li>
          <li><a href="{{ home_url('/contact') }}#groups">{{ __('Groups & schools', 'walkridge') }}</a></li>
          <li><a href="{{ home_url('/refund-policy') }}">{{ __('Refund Policy', 'walkridge') }}</a></li>
        </ul>
      </div>
      <div>
        <h3>{{ __('Hours', 'walkridge') }}</h3>
        <ul class="footer-hours">
          <li>{!! Identity::hoursHtml() !!}</li>
        </ul>
      </div>
      <div>
        <h3>{{ __('Contact', 'walkridge') }}</h3>
        <ul>
          <li>{{ Identity::brandName() }} {{ Identity::brandSub() }}</li>
          <li>{!! Identity::addressHtml() !!}<br><span class="nap-note">{{ __('Concept address — replace under Customize → Identity', 'walkridge') }}</span></li>
          <li><a href="{{ Identity::phoneHref() }}">{{ Identity::phone() }}</a><br><span class="nap-note">{{ __('Fiction-range sample number', 'walkridge') }}</span></li>
          <li><a href="mailto:{{ antispambot(Identity::email()) }}">{{ Identity::email() }}</a></li>
        </ul>
      </div>
    </div>
    <p class="footer-service">{{ __('Proudly serving our local community and the surrounding area.', 'walkridge') }}</p>

    <div class="footer-trust-bar">
      <div class="footer-pay-group">
        <span class="footer-pay-label">{{ __('Checkout ready for', 'walkridge') }}</span>
        <div class="footer-pay-icons" aria-hidden="true">
          <svg width="42" height="27" viewBox="0 0 42 27" aria-hidden="true" focusable="false"><rect width="42" height="27" rx="4" fill="#1a3b6d"/><text x="21" y="18" font-family="Arial" font-size="10" fill="#fff" text-anchor="middle" font-weight="bold">VISA</text></svg>
          <svg width="42" height="27" viewBox="0 0 42 27" aria-hidden="true" focusable="false"><rect width="42" height="27" rx="4" fill="#2b2523"/><circle cx="17" cy="13.5" r="7.5" fill="#eb001b"/><circle cx="25" cy="13.5" r="7.5" fill="#f79e1b" opacity=".9"/></svg>
          <svg width="42" height="27" viewBox="0 0 42 27" aria-hidden="true" focusable="false"><rect width="42" height="27" rx="4" fill="#2e77bc"/><text x="21" y="18" font-family="Arial" font-size="8" fill="#fff" text-anchor="middle" font-weight="bold">AMEX</text></svg>
          <svg width="42" height="27" viewBox="0 0 42 27" aria-hidden="true" focusable="false"><rect width="42" height="27" rx="4" fill="#f4f0e6"/><text x="18" y="18" font-family="Arial" font-size="7.5" fill="#a3341f" text-anchor="middle" font-weight="bold">DISC</text><circle cx="33" cy="13.5" r="7" fill="#e67630" opacity=".9"/></svg>
        </div>
      </div>
      <div class="footer-trust-badges">
        <span class="footer-trust-badge">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
          {{ __('Secure checkout', 'walkridge') }}
        </span>
        <span class="footer-trust-badge">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
          {{ __('Free cancellation window', 'walkridge') }}
        </span>
        <span class="footer-trust-badge">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
          {{ __('Association-licensed guides', 'walkridge') }}
        </span>
      </div>
    </div>

    <div class="footer-bottom">
      <span>&copy; <span data-year>{{ date('Y') }}</span> {{ Identity::brandName() }} {{ Identity::brandSub() }}. {{ __('All rights reserved.', 'walkridge') }}</span>
      <span class="footer-bottom-links">
        <a href="{{ home_url('/refund-policy') }}">{{ __('Refund Policy', 'walkridge') }}</a>
        <a href="{{ home_url('/contact') }}">{{ __('Contact', 'walkridge') }}</a>
        @if(Identity::showCredit())
          <a href="{{ esc_url(Identity::creditUrl()) }}" rel="nofollow noopener">{{ Identity::creditText() }}</a>
        @endif
      </span>
    </div>
  </div>
</footer>
<div class="sticky-book">
  <a href="{{ Identity::phoneHref() }}" class="btn btn-outline btn-sm">{{ __('Call', 'walkridge') }}</a>
  <a href="{{ $shopUrl }}" class="btn btn-primary btn-sm">{{ Identity::ctaLabel() }}</a>
</div>
