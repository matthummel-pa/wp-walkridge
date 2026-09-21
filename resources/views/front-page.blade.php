@extends('layouts.app')

@section('content')
  @while(have_posts())
    @php
      the_post();
      $raw = (string) get_post_field('post_content', get_the_ID());
      $heroOk = str_contains($raw, '<!-- wp:walkridge/home-hero');
    @endphp
    @unless($heroOk)
      <h1 class="visually-hidden">{{ get_bloginfo('name') }}</h1>
    @endunless
    @php(the_content())
  @endwhile
@endsection
