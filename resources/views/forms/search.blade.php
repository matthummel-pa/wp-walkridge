<form role="search" method="get" class="search-form" action="{{ home_url('/') }}">
  <label>
    <span class="visually-hidden">
      {{ _x('Search for:', 'label', 'walkridge') }}
    </span>

    <input
      type="search"
      placeholder="{{ esc_attr_x('Search &hellip;', 'placeholder', 'walkridge') }}"
      value="{{ esc_attr(get_search_query()) }}"
      name="s"
    >
  </label>

  <button type="submit">{{ _x('Search', 'submit button', 'walkridge') }}</button>
</form>
