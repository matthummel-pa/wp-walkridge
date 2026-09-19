@extends('layouts.app')

@section('content')
  <div class="wrap section woocommerce-page-wrap" role="region" aria-label="{{ __('Shop', 'walkridge') }}">
    @if(function_exists('woocommerce_content'))
      @php(woocommerce_content())
    @else
      @include('woocommerce.no-plugin')
    @endif
  </div>
@endsection
