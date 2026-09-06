@if (! post_password_required())
  <section id="comments" class="comments">
    @if ($responses())
      <h2>
        {!! $title !!}
      </h2>

      <ol class="comment-list">
        {!! $responses !!}
      </ol>

      @if ($paginated())
        <nav aria-label="{{ __('Comment navigation', 'walkridge') }}">
          <ul class="pager">
            @if ($previous())
              <li class="previous">
                {!! $previous !!}
              </li>
            @endif

            @if ($next())
              <li class="next">
                {!! $next !!}
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
