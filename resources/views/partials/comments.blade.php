@if (! post_password_required())
  <section id="comments" class="comments">
    @if ($responses())
      <h2>
        {!! wp_kses_post($title) !!}
      </h2>

      <ol class="comment-list">
        {!! wp_kses_post($responses) !!}
      </ol>

      @if ($paginated())
        <nav aria-label="{{ __('Comment navigation', 'walkridge') }}">
          <ul class="pager">
            @if ($previous())
              <li class="previous">
                {!! wp_kses_post($previous) !!}
              </li>
            @endif

            @if ($next())
              <li class="next">
                {!! wp_kses_post($next) !!}
              </li>
            @endif
          </ul>
        </nav>
      @endif
    @endif

    @if ($closed())
      <x-alert type="warning">
        {{ __('Comments are closed.', 'walkridge') }}
      </x-alert>
    @endif

    @php(comment_form())
  </section>
@endif
