{{--
  Shop SEO content — appended below the product grid on the main /shop archive.
  Contains: why-us feature grid, FAQ with FAQPage schema, and a closing editorial blurb.
--}}

{{-- ── Why walk the ground ─────────────────────────────────────────────────── --}}
<section class="shop-seo section" aria-labelledby="shop-seo-heading">
  <div class="wrap shop-seo__inner">

    <header class="shop-seo__lead">
      <span class="eyebrow">{{ __('Why Walk the Ground?', 'walkridge') }}</span>
      <h2 id="shop-seo-heading">{{ __('Gettysburg Is Best Understood on Foot', 'walkridge') }}</h2>
      <p>{{ __("Maps and monuments tell part of the story. Walking the terrain — climbing Seminary Ridge, standing where Pickett's men formed up, tracing the arc of the High Water Mark — gives you a spatial intelligence no book can match. Our licensed guides have walked every acre of this ground for years; they know which detail stops visitors cold every single time.", 'walkridge') }}</p>
      <p>{{ __('The Gettysburg battlefield spans 6,000 acres and more than 1,300 monuments. Without a guide, most visitors see ten percent of it. With ours, you leave understanding what was at stake, where it was decided, and why the ground itself shaped the outcome.', 'walkridge') }}</p>
    </header>

    <div class="shop-seo__grid" role="list">

      <article class="shop-seo__feature" role="listitem">
        <div class="shop-seo__icon" aria-hidden="true">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <h3>{{ __('Association-Licensed Guides', 'walkridge') }}</h3>
        <p>{{ __('Every guide carries an official Gettysburg Licensed Battlefield Guide license — one of the most rigorous certifications in American heritage tourism, awarded after a multi-year apprenticeship and written exam.', 'walkridge') }}</p>
      </article>

      <article class="shop-seo__feature" role="listitem">
        <div class="shop-seo__icon" aria-hidden="true">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        </div>
        <h3>{{ __('Small Groups, Big Stories', 'walkridge') }}</h3>
        <p>{{ __('We cap group sizes so every guest can hear the guide clearly, ask questions freely, and linger at the spots that hit hardest — no megaphones, no hustle, no shouting over a crowd of fifty.', 'walkridge') }}</p>
      </article>

      <article class="shop-seo__feature" role="listitem">
        <div class="shop-seo__icon" aria-hidden="true">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        </div>
        <h3>{{ __('Book in Minutes', 'walkridge') }}</h3>
        <p>{{ __('Choose a date, pick your time, and check out securely. Confirmation arrives by email instantly. Free cancellation is available up to 24 hours before departure — no phone tag required.', 'walkridge') }}</p>
      </article>

      <article class="shop-seo__feature" role="listitem">
        <div class="shop-seo__icon" aria-hidden="true">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <h3>{{ __('Tours for Every Schedule', 'walkridge') }}</h3>
        <p>{{ __("Two-hour walking tours, half-day bus experiences, intimate lantern walks at dusk, and private sunrise tours — there's a format for every itinerary, interest level, and mobility need.", 'walkridge') }}</p>
      </article>

    </div>
  </div>
</section>

{{-- ── FAQ ─────────────────────────────────────────────────────────────────── --}}
@php
$faqs = [
  [
    'q' => __('Do I need to buy tickets in advance?', 'walkridge'),
    'a' => __('We strongly recommend it — tours fill quickly from May through October. Booking online takes under three minutes and guarantees your spot. Walk-ups are welcome only on remaining space, which is never guaranteed.', 'walkridge'),
  ],
  [
    'q' => __('How long is a typical tour?', 'walkridge'),
    'a' => __('Walking tours run approximately 90 minutes to 2 hours. Bus tours are 2 to 3.5 hours. Lantern walks last about 75 minutes. Private sunrise experiences are customizable but typically 2 to 3 hours.', 'walkridge'),
  ],
  [
    'q' => __('Where do tours depart from?', 'walkridge'),
    'a' => __('All tours depart from our designated meeting point on the battlefield. Exact directions and a location pin are included in your booking confirmation email.', 'walkridge'),
  ],
  [
    'q' => __('What should I wear and bring?', 'walkridge'),
    'a' => __('Comfortable, broken-in walking shoes are essential — battlefield terrain is uneven. Layers are recommended; mornings on the ridge can be cool even in summer. Bring water and sunscreen for afternoon tours. Rain cancellations are handled case-by-case.', 'walkridge'),
  ],
  [
    'q' => __('Are tours suitable for children?', 'walkridge'),
    'a' => __("Yes. Our guides are practiced at adjusting detail and storytelling to match a group's age and interest level. Children under 10 often find the lantern walk most engaging; older kids tend to connect most with the walking tours.", 'walkridge'),
  ],
  [
    'q' => __('Are tours ADA accessible?', 'walkridge'),
    'a' => __('Our bus tour is fully seated and ADA accessible. Walking tours cover uneven terrain and may not be suitable for all mobility levels. Contact us before booking if you have specific accessibility needs.', 'walkridge'),
  ],
  [
    'q' => __('What is your cancellation policy?', 'walkridge'),
    'a' => __('Full refunds are available for cancellations made at least 24 hours before departure. Cancellations made within 24 hours are not eligible for a refund. See our full refund policy for details.', 'walkridge'),
  ],
  [
    'q' => __('Can I book a private tour for my group?', 'walkridge'),
    'a' => __('Yes — our Sunrise Private Battlefield Experience is designed for up to six guests and can be customized to your interests, pace, and focus areas. It is also the most flexible option for special occasions.', 'walkridge'),
  ],
];
@endphp

<section class="shop-faq section" aria-labelledby="shop-faq-heading"
         itemscope itemtype="https://schema.org/FAQPage">
  <div class="wrap">
    <span class="eyebrow">{{ __('Before You Book', 'walkridge') }}</span>
    <h2 id="shop-faq-heading">{{ __('Frequently Asked Questions', 'walkridge') }}</h2>

    <div class="shop-faq__grid">
      @foreach($faqs as $faq)
        <div class="shop-faq__item"
             itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
          <h3 class="shop-faq__q" itemprop="name">{{ $faq['q'] }}</h3>
          <div class="shop-faq__a"
               itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
            <p itemprop="text">{{ $faq['a'] }}</p>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── Editorial closing / long-tail SEO ──────────────────────────────────── --}}
<section class="shop-editorial section" aria-label="{{ __('About Gettysburg tours', 'walkridge') }}">
  <div class="wrap shop-editorial__inner">
    <div class="shop-editorial__body">
      <span class="eyebrow">{{ __('Plan Your Visit', 'walkridge') }}</span>
      <h2>{{ __('Gettysburg Battlefield Tours — What to Expect', 'walkridge') }}</h2>
      <p>{{ __('Gettysburg, Pennsylvania, is one of the most significant and carefully preserved military landscapes in the world. The three-day battle of July 1–3, 1863, was the largest ever fought on American soil and a turning point of the Civil War. More than 50,000 soldiers became casualties across fields, orchards, ravines, and ridgetops that you can still walk today.', 'walkridge') }}</p>
      <p>{{ __('Our tours are designed around that ground. Rather than moving quickly from marker to marker, we pause at the locations where the decisions were made — Cemetery Hill, Little Round Top, the Copse of Trees. A licensed guide can read the terrain the way a general would have: where the lines of sight were, why a ridge mattered, what the soldiers could and could not see from their positions.', 'walkridge') }}</p>
      <p>{{ __("Whether you're visiting Gettysburg for the first time or returning for deeper study, there is always more to find. Our guides draw on current scholarship and years of on-site experience to give you an interpretation you won't find in any guidebook.", 'walkridge') }}</p>
    </div>
    <aside class="shop-editorial__aside">
      <div class="shop-editorial__card">
        <span class="eyebrow eyebrow--light">{{ __('Visiting Gettysburg', 'walkridge') }}</span>
        <h3>{{ __('Plan Your Day', 'walkridge') }}</h3>
        <ul class="shop-editorial__list">
          <li>{{ __('Battlefield tours run year-round, weather permitting.', 'walkridge') }}</li>
          <li>{{ __('Peak season: May through October — book early.', 'walkridge') }}</li>
          <li>{{ __('Off-season: November through April — smaller groups, quieter ground.', 'walkridge') }}</li>
          <li>{{ __('Combine a walking tour with a bus tour for the full picture.', 'walkridge') }}</li>
          <li>{{ __("The lantern walk is an excellent evening addition to a day's visit.", 'walkridge') }}</li>
        </ul>
        <a href="{{ home_url('/area') }}" class="btn btn-secondary btn-sm" style="margin-top:1.2rem;">
          {{ __('Visitor Area Guide →', 'walkridge') }}
        </a>
      </div>
    </aside>
  </div>
</section>
