@extends('layouts.app')

@php
  use App\Support\Identity;
@endphp

@section('content')
  <nav class="breadcrumb" aria-label="{{ __('Breadcrumb', 'walkridge') }}"><div class="wrap"><ol><li><a href="{{ home_url('/') }}">{{ __('Home', 'walkridge') }}</a></li><li class="sep" aria-hidden="true">/</li><li aria-current="page">{{ __('Contact', 'walkridge') }}</li></ol></div></nav>

  @while(have_posts())
    @php(the_post())
    @php(the_content())
  @endwhile

  <section class="section">
    <div class="wrap">
      <div class="contact-grid reveal">
        <div>
          <span class="eyebrow">{{ __('Reach Us', 'walkridge') }}</span>
          <h2 class="contact-section-heading">{{ __('Ticket office & guest services', 'walkridge') }}</h2>
          <div class="meet-card">
            <p>
              <a href="{{ Identity::phoneHref() }}" class="nap-block nap-block--strong">{{ Identity::phone() }}</a><br>
              @if(Identity::showDemoChrome())
                <span class="nap-note">{{ __('Fiction-range sample number — not a live line', 'walkridge') }}</span><br>
              @endif
              <a href="{{ esc_url('mailto:'.Identity::email()) }}" class="contact-email-link">{{ Identity::email() }}</a>
            </p>
            <p>{!! Identity::hoursHtml() !!}</p>
            <p>{{ Identity::addressLine() }}</p>
          </div>
        </div>

        <form class="contact-form" id="contactForm" method="post" action="{{ esc_url(admin_url('admin-post.php')) }}" data-wr-contact novalidate>
          <input type="hidden" name="action" value="wr_contact">
          {!! wp_nonce_field('wr_contact', 'wr_contact_nonce', true, false) !!}
          <span class="eyebrow">{{ __('Send a Message', 'walkridge') }}</span>
          <h2 class="contact-section-heading">{{ __('Ask us anything', 'walkridge') }}</h2>
          <div class="form-grid">
            <div class="field full">
              <label for="cName">{{ __('Your name', 'walkridge') }}</label>
              <input type="text" id="cName" name="cName" autocomplete="name" required>
            </div>
            <div class="field full">
              <label for="cPhone">{{ __('Phone (optional)', 'walkridge') }}</label>
              <input type="tel" id="cPhone" name="cPhone" autocomplete="tel" placeholder="{{ esc_attr__('(717) 555-0100', 'walkridge') }}">
            </div>
            <div class="field full">
              <label for="cEmail">{{ __('Email address', 'walkridge') }}</label>
              <input type="email" id="cEmail" name="cEmail" autocomplete="email" required>
            </div>
            <div class="field full">
              <label for="cMsg">{{ __('Message', 'walkridge') }}</label>
              <textarea id="cMsg" name="cMsg" required></textarea>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-block">{{ __('Send Message', 'walkridge') }}</button>
          <p id="contactNote" role="status" aria-live="polite" class="contact-form__note">
            @if(isset($_GET['wr_form']) && isset($_GET['wr_msg']))
              {{ sanitize_text_field(wp_unslash($_GET['wr_msg'])) }}
            @endif
          </p>
        </form>
      </div>
    </div>
  </section>
@endsection
