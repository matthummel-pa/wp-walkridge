@extends('layouts.app')

@section('content')
  @php
    $wooRegion = __('Shop', 'walkridge');
    if (function_exists('is_product') && is_product()) {
      $wooRegion = __('Tour product', 'walkridge');
    } elseif (function_exists('is_cart') && is_cart()) {
      $wooRegion = __('Cart', 'walkridge');
    } elseif (function_exists('is_checkout') && is_checkout()) {
      $wooRegion = __('Checkout', 'walkridge');
    } elseif (function_exists('is_account_page') && is_account_page()) {
      $wooRegion = __('Account', 'walkridge');
    }
  @endphp
  <div class="wrap section woocommerce-page-wrap" role="region" aria-label="{{ $wooRegion }}">
    @if(function_exists('woocommerce_content'))
      @php(woocommerce_content())
    @else
      @include('woocommerce.no-plugin')
    @endif
  </div>
@endsection
