@extends('layouts.app')

@section('content')
  @php
    $is_shop     = function_exists('is_shop') && is_shop();
    $is_category = function_exists('is_product_category') && is_product_category();
    $is_archive  = $is_shop || $is_category;
  @endphp

  {{-- ── Shop / category hero ──────────────────────────────────────────── --}}
  @if($is_archive)
    <section class="shop-hero" aria-label="{{ __('Shop header', 'walkridge') }}">
      <div class="wrap">
        @if($is_category)
          <nav class="woocommerce-breadcrumb shop-hero__crumbs" aria-label="{{ __('Breadcrumb', 'walkridge') }}">
            <a href="{{ home_url('/') }}">{{ __('Home', 'walkridge') }}</a>
            <span aria-hidden="true"> / </span>
            <a href="{{ function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : '#' }}">{{ __('Shop', 'walkridge') }}</a>
            <span aria-hidden="true"> / </span>
            <span aria-current="page">{{ single_term_title('', false) }}</span>
          </nav>
        @endif

        <span class="eyebrow eyebrow--light">
          {{ $is_category ? __('Gettysburg Battlefield Tours', 'walkridge') : __('All Experiences', 'walkridge') }}
        </span>

        <h1 class="shop-hero__title">
          {!! $is_category
              ? esc_html(single_term_title('', false))
              : __('Choose Your Tour', 'walkridge') !!}
        </h1>

        @if($is_shop)
          <p class="shop-hero__intro">
            {{ __('Licensed guides. Small groups. Real history — experienced where it happened. Every tour departs from the heart of the Gettysburg battlefield.', 'walkridge') }}
          </p>
        @elseif($is_category)
          @php $term = get_queried_object(); @endphp
          @if($term && $term->description)
            <p class="shop-hero__intro">{{ wp_strip_all_tags($term->description) }}</p>
          @endif
        @endif

        {{-- Category filter tabs (shop archive only) --}}
        @if($is_shop)
          @php
            $cats = get_terms([
              'taxonomy'   => 'product_cat',
              'hide_empty' => true,
              'exclude'    => [get_option('default_product_cat')],
              'orderby'    => 'count',
              'order'      => 'DESC',
            ]);
          @endphp
          @if(!is_wp_error($cats) && count($cats))
            <nav class="shop-cat-tabs" aria-label="{{ __('Filter by tour type', 'walkridge') }}">
              <a href="{{ function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : '#' }}"
                 class="shop-cat-tab{{ !$is_category ? ' is-active' : '' }}">
                {{ __('All Tours', 'walkridge') }}
              </a>
              @foreach($cats as $cat)
                <a href="{{ get_term_link($cat) }}"
                   class="shop-cat-tab{{ (is_product_category($cat->slug)) ? ' is-active' : '' }}">
                  {{ esc_html($cat->name) }}
                  <span class="shop-cat-tab__count">{{ (int) $cat->count }}</span>
                </a>
              @endforeach
            </nav>
          @endif
        @endif

        {{-- Trust strip --}}
        @if($is_shop)
          <div class="shop-hero__trust">
            <span class="shop-trust-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              {{ __('Licensed guides', 'walkridge') }}
            </span>
            <span class="shop-trust-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
              {{ __('Free cancellation 24 h', 'walkridge') }}
            </span>
            <span class="shop-trust-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>
              {{ __('Secure checkout', 'walkridge') }}
            </span>
            <span class="shop-trust-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
              {{ __('Small groups', 'walkridge') }}
            </span>
          </div>
        @endif
      </div>
    </section>
  @endif

  {{-- ── Main WooCommerce output ─────────────────────────────────────────── --}}
  <div class="wrap section woocommerce-page-wrap" role="region" aria-label="{{ __('Shop', 'walkridge') }}">
    @if(function_exists('woocommerce_content'))
      @php(woocommerce_content())
    @else
      <p class="notice">{{ __('The shop is not available right now.', 'walkridge') }}</p>
    @endif
  </div>

  {{-- ── SEO + FAQ content (shop archive only) ─────────────────────────── --}}
  @if($is_shop)
    @include('partials.shop-seo')
  @endif

@endsection
