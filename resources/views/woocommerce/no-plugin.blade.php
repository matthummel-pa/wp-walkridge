@php
  use App\Support\Identity;
@endphp
<section class="wr-empty-shop" aria-labelledby="wr-shop-offline-heading">
  <p class="eyebrow">{{ __('Book a Tour', 'walkridge') }}</p>
  <h1 id="wr-shop-offline-heading">{{ __('The shop needs WooCommerce.', 'walkridge') }}</h1>
  <p>
    {{ __('Walkridge’s marketing pages run without WooCommerce. Activate WooCommerce, turn off Coming soon mode, and publish tour products to sell dates from this URL.', 'walkridge') }}
  </p>
  <ul class="wr-empty-shop__points">
    <li>{{ __('Start on Tours to compare walking, bus, hike, private sunrise, and the lantern walk.', 'walkridge') }}</li>
    <li>{{ __('Sample desk: (717) 555-0100 · tours@walkridge.test — fiction only.', 'walkridge') }}</li>
  </ul>
  <p class="wr-empty-shop__actions">
    <a class="btn btn-primary" href="{{ home_url('/tours') }}">{{ __('See all tours', 'walkridge') }}</a>
    <a class="btn btn-outline" href="{{ esc_url(Identity::phoneHref()) }}">{{ sprintf(__('Call %s', 'walkridge'), Identity::phone()) }}</a>
  </p>
</section>
