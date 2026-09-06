@extends('layouts.app')

@section('content')
  <nav class="breadcrumb" aria-label="{{ __('Breadcrumb', 'walkridge') }}"><div class="wrap"><ol><li><a href="{{ home_url('/') }}">{{ __('Home', 'walkridge') }}</a></li><li class="sep" aria-hidden="true">/</li><li aria-current="page">{{ __('Tours', 'walkridge') }}</li></ol></div></nav>
  @while(have_posts())
    @php(the_post())
    @php(the_content())
  @endwhile
@endsection
