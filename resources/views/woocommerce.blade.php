@extends('layouts.app')

@section('content')
  <div class="wrap section woocommerce-page-wrap" role="region" aria-label="{{ __('Shop', 'walkridge') }}">
    @if(function_exists('woocommerce_content'))
      @php(woocommerce_content())
    @else
      <p class="notice">{{ __('The shop is not available right now.', 'walkridge') }}</p>
    @endif
  </div>
@endsection
