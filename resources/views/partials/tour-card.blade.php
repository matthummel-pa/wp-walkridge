@php
  /** @var array $tour */
  $btnClass = ! empty($tour['lantern']) ? 'btn btn-lantern btn-block' : 'btn btn-primary btn-block';
@endphp
<article class="tour-card" data-category="{{ esc_attr($tour['category']) }}">
  <div class="tour-banner {{ esc_attr($tour['banner']) }}">
    <span class="tour-kicker">{{ $tour['kicker'] }}</span>
  </div>
  <div class="tour-body">
    <div class="tour-meta">
      @if(!empty($tour['duration']))
        <span class="chip">{{ $tour['duration'] }}</span>
      @endif
      @if(!empty($tour['capacity']))
        <span class="chip">{{ $tour['capacity'] }}</span>
      @endif
      @if(!empty($tour['difficulty']))
        <span class="chip {{ esc_attr($tour['difficulty_class'] ?? '') }}">{{ $tour['difficulty'] }}</span>
      @endif
    </div>
    <h3 class="tour-card__title">{{ $tour['title'] }}</h3>
    @if(!empty($tour['excerpt']))
      <p class="desc">{{ $tour['excerpt'] }}</p>
    @endif
    <div class="tour-price-row">
      <span class="tour-price price">
        @if(!empty($tour['price_html']))
          {!! $tour['price_html'] !!}<small> {{ __(' /adult', 'walkridge') }}</small>
        @elseif(!empty($tour['price']))
          ${{ number_format((float) $tour['price'], 0) }}<small> {{ __(' /adult', 'walkridge') }}</small>
        @endif
      </span>
    </div>
    <a href="{{ esc_url($tour['url']) }}" class="{{ $btnClass }}">{{ __('Book This Tour', 'walkridge') }}</a>
  </div>
</article>
