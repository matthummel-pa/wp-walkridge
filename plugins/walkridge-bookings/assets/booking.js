/**
 * Walkridge Bookings — multi-step booking widget.
 *
 * Injected on single product pages for bookable tours.
 * Steps:
 *   1. Calendar  — pick a departure date (month view, slots colour-coded)
 *   2. Party     — adults / children / seniors + running price + deposit info
 *   3. Review    — summary, payment methods, special requests, Add to Cart
 *
 * Talks to: /wp-json/wr-bookings/v1/{slots,availability,product-config,payment-methods}
 * Submits:  wrb_add_to_cart AJAX action
 */
(function () {
  'use strict';

  /* ── Config injected by wp_localize_script ─────────────────────────── */
  const cfg = window.hgbConfig || {};
  const i18n = cfg.i18n || {};
  const API = cfg.root || '';
  const NONCE = cfg.nonce || '';
  const AJAX = cfg.ajaxUrl || '';
  const AJAX_NONCE = cfg.ajaxNonce || '';

  /* ── REST helpers ──────────────────────────────────────────────────── */
  async function apiFetch(path) {
    const r = await fetch(API + path, {
      headers: { 'X-WP-Nonce': NONCE, 'Content-Type': 'application/json' },
    });
    if (!r.ok) throw new Error('API error ' + r.status);
    return r.json();
  }

  /* ── State ──────────────────────────────────────────────────────────── */
  const state = {
    productId: 0,
    config: null,       // product-config response
    payments: [],       // payment gateways
    month: '',          // 'YYYY-MM'
    slots: {},          // { date: [{id,time,available,status}] }
    selectedDate: '',
    selectedSlot: null,
    adults: 1,
    children: 0,
    seniors: 0,
    specialRequests: '',
    step: 1,            // 1=calendar, 2=party, 3=review
  };

  /* ── Price calculation ──────────────────────────────────────────────── */
  function calcPrice() {
    if (!state.config) return { full: 0, deposit: 0, balance: 0, isDeposit: false };
    const p = state.config.pricing;
    const full = (state.adults * p.adult) + (state.children * p.child) + (state.seniors * p.senior);
    const dep  = state.config.deposit;
    let charge = full;
    if (dep.type === 'percent' && dep.value > 0) {
      charge = Math.round(full * dep.value) / 100;
    } else if (dep.type === 'fixed' && dep.value > 0) {
      charge = Math.min(full, dep.value);
    }
    return {
      full: full,
      deposit: charge,
      balance: Math.max(0, Math.round((full - charge) * 100) / 100),
      isDeposit: charge < full,
    };
  }

  function formatPrice(amount) {
    const sym = cfg.currency || '$';
    return sym + parseFloat(amount).toFixed(2);
  }

  /* ── Month navigation ───────────────────────────────────────────────── */
  function parseMonth(ym) {
    const [y, m] = ym.split('-').map(Number);
    return { year: y, month: m };
  }
  function prevMonth(ym) {
    let { year, month } = parseMonth(ym);
    month--;
    if (month < 1) { month = 12; year--; }
    return `${year}-${String(month).padStart(2, '0')}`;
  }
  function nextMonth(ym) {
    let { year, month } = parseMonth(ym);
    month++;
    if (month > 12) { month = 1; year++; }
    return `${year}-${String(month).padStart(2, '0')}`;
  }
  function monthLabel(ym) {
    const [y, m] = ym.split('-').map(Number);
    return new Date(y, m - 1, 1).toLocaleDateString(navigator.language || 'en-US', { month: 'long', year: 'numeric' });
  }
  function daysInMonth(ym) {
    const [y, m] = ym.split('-').map(Number);
    return new Date(y, m, 0).getDate();
  }
  function dayOfWeek(ym, day) { // 0=Sun
    const [y, m] = ym.split('-').map(Number);
    return new Date(y, m - 1, day).getDay();
  }
  function todayStr() {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
  }

  /* ── Fetch slot data ────────────────────────────────────────────────── */
  async function fetchSlots(month) {
    try {
      const data = await apiFetch(`slots?product_id=${state.productId}&month=${month}`);
      return data.slots || {};
    } catch {
      return {};
    }
  }

  async function fetchPayments() {
    try {
      const data = await apiFetch('payment-methods');
      return data.gateways || [];
    } catch {
      return [];
    }
  }

  async function fetchConfig(productId) {
    try {
      return await apiFetch(`product-config?product_id=${productId}`);
    } catch {
      return null;
    }
  }

  /* ── Widget mount ───────────────────────────────────────────────────── */
  async function mountWidget(container, productId) {
    state.productId = productId;
    state.month = new Date().toISOString().slice(0, 7);

    container.innerHTML = `<div class="hgb-widget"><div class="hgb-widget-loading">
      <svg class="hgb-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="28" height="28">
        <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
      </svg>
    </div></div>`;

    const [config, payments] = await Promise.all([
      fetchConfig(productId),
      fetchPayments(),
    ]);

    if (!config || !config.enabled) {
      container.innerHTML = '';
      return;
    }

    state.config   = config;
    state.payments = payments;

    const slots = await fetchSlots(state.month);
    state.slots = slots;

    render(container);
  }

  /* ── Render ─────────────────────────────────────────────────────────── */
  function render(container) {
    const widget = container.querySelector('.hgb-widget') || (() => {
      const w = document.createElement('div');
      w.className = 'hgb-widget';
      container.innerHTML = '';
      container.appendChild(w);
      return w;
    })();

    widget.innerHTML = buildWidget();
    attachEvents(widget, container);
  }

  function buildWidget() {
    const steps = [
      { n: 1, label: 'Date' },
      { n: 2, label: 'Party' },
      { n: 3, label: 'Review' },
    ];
    const stepDots = steps.map(s => {
      const cls = s.n < state.step ? 'done' : s.n === state.step ? 'active' : '';
      return `<div class="hgb-step ${cls}"><span>${s.n}</span><small>${s.label}</small></div>`;
    }).join('<div class="hgb-step-line"></div>');

    const header = state.config ? `
      <div class="hgb-widget-header">
        <div class="hgb-widget-meta">
          ${state.config.duration ? `<span>${state.config.duration}</span>` : ''}
          ${state.config.meeting_point ? `<span>${escHtml(state.config.meeting_point)}</span>` : ''}
        </div>
        <div class="hgb-steps">${stepDots}</div>
      </div>` : '';

    const body = state.step === 1 ? buildCalendarStep()
               : state.step === 2 ? buildPartyStep()
               : buildReviewStep();

    return `${header}<div class="hgb-step-body">${body}</div>`;
  }

  /* ── Step 1: Calendar ───────────────────────────────────────────────── */
  function buildCalendarStep() {
    const { year, month } = parseMonth(state.month);
    const today = todayStr();
    const days  = daysInMonth(state.month);
    const startDow = dayOfWeek(state.month, 1); // 0=Sun
    const monthSlots = state.slots || {};

    // Day-of-week headers
    const dowHeaders = ['Su','Mo','Tu','We','Th','Fr','Sa']
      .map(d => `<div class="hgb-cal-dow">${d}</div>`).join('');

    let cells = '';
    // Blank cells before the 1st
    for (let i = 0; i < startDow; i++) {
      cells += `<div class="hgb-cal-day hgb-cal-blank"></div>`;
    }
    for (let d = 1; d <= days; d++) {
      const dateStr = `${year}-${String(month).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
      const daySlots = monthSlots[dateStr] || [];
      const isPast   = dateStr < today;
      const isToday  = dateStr === today;
      const isSel    = dateStr === state.selectedDate;
      const hasSold  = daySlots.length > 0 && daySlots.every(s => s.status === 'sold-out');
      const hasFew   = !hasSold && daySlots.some(s => s.status === 'few-left');
      const hasOpen  = daySlots.some(s => s.status === 'open');

      let cls = 'hgb-cal-day';
      if (isPast)    cls += ' hgb-past';
      if (isToday)   cls += ' hgb-today';
      if (isSel)     cls += ' hgb-selected';
      if (hasSold)   cls += ' hgb-sold-out';
      else if (hasFew) cls += ' hgb-few-left';
      else if (hasOpen) cls += ' hgb-open';
      if (!daySlots.length || isPast) cls += ' hgb-no-slots';

      const dot  = daySlots.length ? `<span class="hgb-cal-dot ${hasSold ? 'sold' : hasFew ? 'few' : 'open'}"></span>` : '';
      const attr = (!isPast && daySlots.length) ? `data-date="${dateStr}"` : '';

      cells += `<div class="${cls}" ${attr}><span>${d}</span>${dot}</div>`;
    }

    // Slot time pickers (shown when a date is selected)
    let timeRow = '';
    if (state.selectedDate) {
      const daySlots = (state.slots[state.selectedDate] || []);
      if (daySlots.length) {
        const timeBtns = daySlots.map(s => {
          const isSel = state.selectedSlot && state.selectedSlot.id === s.id;
          const isSO  = s.status === 'sold-out';
          let cls = 'hgb-time-btn' + (isSel ? ' selected' : '') + (isSO ? ' sold-out' : '');
          const tag = isSO ? 'span' : 'button';
          const attr = !isSO ? `type="button" data-slot='${JSON.stringify(s)}'` : '';
          return `<${tag} class="${cls}" ${attr}>
            ${escHtml(s.time)}
            <small>${isSO ? i18n.soldOut : (s.status === 'few-left' ? `${s.available} ${i18n.fewLeft}` : `${s.available} spots`)}</small>
          </${tag}>`;
        }).join('');
        timeRow = `<div class="hgb-time-row">${timeBtns}</div>`;
      }
    }

    return `
      <div class="hgb-calendar">
        <div class="hgb-cal-nav">
          <button type="button" class="hgb-cal-prev btn-ghost" data-month="${prevMonth(state.month)}">&#8249;</button>
          <span class="hgb-cal-month">${monthLabel(state.month)}</span>
          <button type="button" class="hgb-cal-next btn-ghost" data-month="${nextMonth(state.month)}">&#8250;</button>
        </div>
        <div class="hgb-cal-legend">
          <span class="dot open"></span> Available
          <span class="dot few"></span> Few left
          <span class="dot sold"></span> Sold out
        </div>
        <div class="hgb-cal-grid">${dowHeaders}${cells}</div>
        ${timeRow}
        ${!Object.keys(state.slots).length ? `<p class="hgb-no-slots-msg">${i18n.noSlots}</p>` : ''}
      </div>
      <div class="hgb-step-nav">
        <span></span>
        <button type="button" class="hgb-btn-primary hgb-next-step"
          ${!state.selectedSlot ? 'disabled' : ''}>
          Continue &rarr;
        </button>
      </div>`;
  }

  /* ── Step 2: Party size ─────────────────────────────────────────────── */
  function buildPartyStep() {
    const pricing   = state.config.pricing;
    const priceInfo = calcPrice();
    const maxGroup  = state.config.max_group || 99;

    const row = (label, key, price, val) => `
      <div class="hgb-party-row">
        <div class="hgb-party-label">
          <span>${label}</span>
          <small>${formatPrice(price)} each</small>
        </div>
        <div class="hgb-stepper">
          <button type="button" class="hgb-decrement" data-key="${key}" ${val <= 0 ? 'disabled' : ''}>−</button>
          <output class="hgb-count" id="wrb_${key}">${val}</output>
          <button type="button" class="hgb-increment" data-key="${key}" ${val >= maxGroup ? 'disabled' : ''}>+</button>
        </div>
      </div>`;

    const depositNote = priceInfo.isDeposit ? `
      <div class="hgb-deposit-note">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
        <span>
          <strong>Deposit now: ${formatPrice(priceInfo.deposit)}</strong>
          — Balance due at tour: ${formatPrice(priceInfo.balance)}
        </span>
      </div>` : '';

    const slotInfo = state.selectedSlot ? `
      <div class="hgb-slot-summary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        <span>${formatDate(state.selectedDate)} @ ${state.selectedSlot.time}
              &bull; ${state.selectedSlot.available} spot(s) available</span>
      </div>` : '';

    return `
      ${slotInfo}
      <div class="hgb-party">
        ${row(i18n.adults,   'adults',   pricing.adult,  state.adults  )}
        ${row(i18n.children, 'children', pricing.child,  state.children)}
        ${row(i18n.seniors,  'seniors',  pricing.senior, state.seniors )}
      </div>
      <div class="hgb-price-total">
        <span>Total</span>
        <strong>${formatPrice(priceInfo.full)}</strong>
      </div>
      ${depositNote}
      <div class="hgb-step-nav">
        <button type="button" class="hgb-btn-ghost hgb-prev-step">&larr; Back</button>
        <button type="button" class="hgb-btn-primary hgb-next-step"
          ${(state.adults + state.children + state.seniors) < 1 ? 'disabled' : ''}>
          Review booking &rarr;
        </button>
      </div>`;
  }

  /* ── Step 3: Review + payment ───────────────────────────────────────── */
  function buildReviewStep() {
    const priceInfo = calcPrice();
    const pricing   = state.config.pricing;
    const policy    = cfg.cancellationPolicy || 'Full refund 24+ hours before tour.';

    const partyLines = [
      state.adults   > 0 ? `${state.adults}   adult${state.adults>1?'s':''}   × ${formatPrice(pricing.adult)}`   : '',
      state.children > 0 ? `${state.children} child${state.children>1?'ren':''}  × ${formatPrice(pricing.child)}`  : '',
      state.seniors  > 0 ? `${state.seniors}  senior${state.seniors>1?'s':''}  × ${formatPrice(pricing.senior)}` : '',
    ].filter(Boolean).map(l => `<li>${l}</li>`).join('');

    // Payment method icons
    const pmIcons = state.payments.length ? `
      <div class="hgb-payment-methods">
        <span class="hgb-pm-label">Pay securely with:</span>
        <div class="hgb-pm-icons">
          <svg width="34" height="22" viewBox="0 0 34 22"><rect width="34" height="22" rx="4" fill="#1a3b6d"/><text x="17" y="14" font-family="Arial" font-size="8" fill="#fff" text-anchor="middle" font-weight="bold">VISA</text></svg>
          <svg width="34" height="22" viewBox="0 0 34 22"><rect width="34" height="22" rx="4" fill="#2b2523"/><circle cx="14" cy="11" r="6" fill="#eb001b"/><circle cx="20" cy="11" r="6" fill="#f79e1b" opacity=".85"/></svg>
          <svg width="34" height="22" viewBox="0 0 34 22"><rect width="34" height="22" rx="4" fill="#2e77bc"/><text x="17" y="14" font-family="Arial" font-size="6.5" fill="#fff" text-anchor="middle" font-weight="bold">AMEX</text></svg>
          <svg width="34" height="22" viewBox="0 0 34 22"><rect width="34" height="22" rx="4" fill="#f4f0e6"/><text x="17" y="14" font-family="Arial" font-size="6" fill="#a3341f" text-anchor="middle" font-weight="bold">DISC</text></svg>
          ${state.payments.some(g => /paypal/i.test(g.id)) ? `
          <svg width="54" height="22" viewBox="0 0 54 22"><rect width="54" height="22" rx="4" fill="#003087"/><text x="27" y="14" font-family="Arial" font-size="8" fill="#009cde" text-anchor="middle" font-weight="bold">Pay</text><text x="35" y="14" font-family="Arial" font-size="8" fill="#012169" text-anchor="middle" font-weight="bold">Pal</text></svg>` : ''}
          ${state.payments.some(g => /apple/i.test(g.id)) ? `
          <svg width="44" height="22" viewBox="0 0 44 22"><rect width="44" height="22" rx="4" fill="#000"/><text x="22" y="15" font-family="Arial" font-size="9" fill="#fff" text-anchor="middle"> Pay</text></svg>` : ''}
          ${state.payments.some(g => /google/i.test(g.id)) ? `
          <svg width="48" height="22" viewBox="0 0 48 22"><rect width="48" height="22" rx="4" fill="#fff" stroke="#ddd"/><text x="24" y="15" font-family="Arial" font-size="8" fill="#555" text-anchor="middle">G Pay</text></svg>` : ''}
          ${state.payments.some(g => /stripe/i.test(g.id) || g.id === 'woocommerce_payments') ? `
          <svg width="42" height="22" viewBox="0 0 42 22"><rect width="42" height="22" rx="4" fill="#635bff"/><text x="21" y="15" font-family="Arial" font-size="8.5" fill="#fff" text-anchor="middle" font-weight="bold">stripe</text></svg>` : ''}
        </div>
        ${state.payments.map(g => `<span class="hgb-gw-name">${escHtml(g.title)}</span>`).join(', ')}
      </div>` : '';

    return `
      <div class="hgb-review">
        <div class="hgb-review-card">
          <div class="hgb-review-tour">${escHtml(document.querySelector('.product_title, h1.entry-title')?.textContent || '')}</div>
          <ul class="hgb-review-list">
            <li>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
              ${formatDate(state.selectedDate)} @ ${state.selectedSlot.time}
            </li>
            ${partyLines}
          </ul>
          <div class="hgb-review-total">
            <span>To pay now</span>
            <strong>${formatPrice(priceInfo.deposit)}</strong>
          </div>
          ${priceInfo.isDeposit ? `
          <div class="hgb-review-balance">Balance due at tour: ${formatPrice(priceInfo.balance)}</div>` : ''}
        </div>

        <div class="hgb-special-wrap">
          <label for="wrb_requests">
            Special requests <small>(optional)</small>
          </label>
          <textarea id="wrb_requests" class="hgb-requests" rows="3"
            placeholder="Dietary needs, accessibility requirements, notes for your guide…"
          >${escHtml(state.specialRequests)}</textarea>
        </div>

        ${pmIcons}

        <div class="hgb-trust-row">
          <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg> Secure checkout</span>
          <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Free cancellation 24 h</span>
        </div>

        <div class="hgb-step-nav hgb-step-nav--submit">
          <button type="button" class="hgb-btn-ghost hgb-prev-step">&larr; Back</button>
          <button type="button" class="hgb-btn-cta hgb-submit-booking">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
            ${priceInfo.isDeposit ? `Pay deposit ${formatPrice(priceInfo.deposit)}` : `Book &amp; pay ${formatPrice(priceInfo.full)}`}
          </button>
        </div>
        <p class="hgb-policy-note">${policy}</p>
      </div>`;
  }

  /* ── Event binding ──────────────────────────────────────────────────── */
  function attachEvents(widget, container) {
    // Calendar month navigation
    widget.querySelectorAll('[data-month]').forEach(btn => {
      btn.addEventListener('click', async () => {
        const m = btn.dataset.month;
        state.month = m;
        widget.querySelectorAll('[data-month]').forEach(b => b.disabled = true);
        state.slots = await fetchSlots(m);
        render(container);
      });
    });

    // Date selection
    widget.querySelectorAll('.hgb-cal-day[data-date]').forEach(cell => {
      cell.addEventListener('click', () => {
        const d = cell.dataset.date;
        if (state.selectedDate === d) return;
        state.selectedDate = d;
        state.selectedSlot = null; // reset slot selection
        const daySlots = state.slots[d] || [];
        // Auto-select the only available slot if there's just one
        const openSlots = daySlots.filter(s => s.status !== 'sold-out');
        if (openSlots.length === 1) {
          state.selectedSlot = openSlots[0];
        }
        render(container);
      });
    });

    // Time slot selection
    widget.querySelectorAll('[data-slot]').forEach(btn => {
      btn.addEventListener('click', () => {
        state.selectedSlot = JSON.parse(btn.dataset.slot);
        render(container);
      });
    });

    // Step navigation
    const nextBtn = widget.querySelector('.hgb-next-step');
    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        if (state.step < 3) { state.step++; render(container); }
      });
    }
    widget.querySelectorAll('.hgb-prev-step').forEach(btn => {
      btn.addEventListener('click', () => {
        if (state.step > 1) { state.step--; render(container); }
      });
    });

    // Party steppers
    widget.querySelectorAll('.hgb-increment, .hgb-decrement').forEach(btn => {
      btn.addEventListener('click', () => {
        const key  = btn.dataset.key;
        const delta = btn.classList.contains('hgb-increment') ? 1 : -1;
        state[key] = Math.max(0, (state[key] || 0) + delta);
        // Validate against slot capacity
        if (state.selectedSlot) {
          const total = state.adults + state.children + state.seniors;
          if (total > state.selectedSlot.available) {
            state[key] -= delta; // revert
          }
        }
        render(container);
      });
    });

    // Special requests
    const textarea = widget.querySelector('.hgb-requests');
    if (textarea) {
      textarea.addEventListener('input', () => {
        state.specialRequests = textarea.value;
      });
    }

    // Submit
    const submitBtn = widget.querySelector('.hgb-submit-booking');
    if (submitBtn) {
      submitBtn.addEventListener('click', () => handleSubmit(widget, container));
    }
  }

  /* ── Submit ─────────────────────────────────────────────────────────── */
  async function handleSubmit(widget, container) {
    const submitBtn = widget.querySelector('.hgb-submit-booking');
    if (!submitBtn) return;

    const requests = widget.querySelector('.hgb-requests');
    if (requests) state.specialRequests = requests.value;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<svg class="hgb-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Adding to cart…';

    const form = new FormData();
    form.append('action',           'wrb_add_to_cart');
    form.append('nonce',            AJAX_NONCE);
    form.append('product_id',       state.productId);
    form.append('slot_id',          state.selectedSlot ? state.selectedSlot.id : 0);
    form.append('adults',           state.adults);
    form.append('children',         state.children);
    form.append('seniors',          state.seniors);
    form.append('special_requests', state.specialRequests);

    try {
      const resp = await fetch(AJAX, { method: 'POST', body: form });
      const data = await resp.json();
      if (data.success) {
        // Show success state
        widget.innerHTML = `
          <div class="hgb-success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="48" height="48">
              <circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/>
            </svg>
            <h3>${data.data.message || i18n.added}</h3>
            <a href="${data.data.cart_url}" class="hgb-btn-primary">View cart &rarr;</a>
            <a href="${cfg.cartUrl}" class="hgb-btn-ghost" style="margin-top:8px;">Continue browsing</a>
          </div>`;
        // Update WC mini-cart
        const event = new CustomEvent('wc_fragment_refresh');
        document.body.dispatchEvent(event);
        if (typeof jQuery !== 'undefined') {
          jQuery(document.body).trigger('wc_fragment_refresh');
        }
      } else {
        const msg = data.data?.message || 'An error occurred. Please try again.';
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Retry';
        showError(widget, msg);
      }
    } catch (e) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = 'Retry';
      showError(widget, 'Network error — please try again.');
    }
  }

  function showError(widget, msg) {
    let err = widget.querySelector('.hgb-error');
    if (!err) {
      err = document.createElement('p');
      err.className = 'hgb-error';
      widget.querySelector('.hgb-step-nav--submit')?.before(err);
    }
    err.textContent = msg;
  }

  /* ── Admin: slot manager ────────────────────────────────────────────── */
  function initAdminSlotManager() {
    const manager = document.querySelector('.hgb-slot-manager');
    if (!manager) return;
    const productId = manager.dataset.productId;
    const list      = document.getElementById('wrb_slots_list');
    const addBtn    = document.getElementById('wrb_add_slot_btn');
    const msg       = document.getElementById('wrb_slot_msg');

    // Load existing slots
    fetchAdminSlots(productId, list);

    if (addBtn) {
      addBtn.addEventListener('click', async () => {
        const date     = document.getElementById('wrb_new_date')?.value;
        const time     = document.getElementById('wrb_new_time')?.value || '09:00';
        const capacity = document.getElementById('wrb_new_capacity')?.value || '15';
        if (!date) { alert('Please select a date.'); return; }

        addBtn.disabled = true;
        const form = new FormData();
        form.append('action',      'wrb_add_slot');
        form.append('nonce',       AJAX_NONCE);
        form.append('product_id',  productId);
        form.append('slot_date',   date);
        form.append('slot_time',   time);
        form.append('capacity',    capacity);

        try {
          const r = await fetch(AJAX, { method: 'POST', body: form });
          const d = await r.json();
          if (d.success) {
            if (msg) { msg.textContent = d.data.message; setTimeout(() => msg.textContent='', 3000); }
            fetchAdminSlots(productId, list);
          } else {
            alert(d.data || 'Error adding slot.');
          }
        } finally {
          addBtn.disabled = false;
        }
      });
    }

    list.addEventListener('click', async (e) => {
      const del = e.target.closest('[data-delete-slot]');
      if (!del) return;
      if (!confirm(i18n.confirmDelete)) return;
      const slotId = del.dataset.deleteSlot;
      const form = new FormData();
      form.append('action',   'wrb_delete_slot');
      form.append('nonce',    AJAX_NONCE);
      form.append('slot_id',  slotId);
      try {
        const r = await fetch(AJAX, { method: 'POST', body: form });
        const d = await r.json();
        if (d.success) { fetchAdminSlots(productId, list); }
        else { alert(d.data || 'Error deleting slot.'); }
      } catch {}
    });
  }

  async function fetchAdminSlots(productId, list) {
    try {
      const r = await fetch(`${AJAX}?action=wrb_get_slots&nonce=${AJAX_NONCE}&product_id=${productId}`);
      const d = await r.json();
      if (!d.success || !d.data.slots.length) {
        list.innerHTML = '<em style="color:#999;font-size:12px;">No slots added yet.</em>';
        return;
      }
      list.innerHTML = `<table style="font-size:12px;border-collapse:collapse;width:100%;max-width:520px;">
        <thead><tr style="text-align:left;">
          <th style="padding:4px 8px;">Date</th>
          <th style="padding:4px 8px;">Time</th>
          <th style="padding:4px 8px;">Capacity</th>
          <th style="padding:4px 8px;">Booked</th>
          <th style="padding:4px 8px;">Status</th>
          <th></th>
        </tr></thead><tbody>
        ${d.data.slots.map(s => `<tr>
          <td style="padding:4px 8px;">${s.date}</td>
          <td style="padding:4px 8px;">${s.time}</td>
          <td style="padding:4px 8px;text-align:center;">${s.capacity}</td>
          <td style="padding:4px 8px;text-align:center;">${s.booked}</td>
          <td style="padding:4px 8px;">${s.status}</td>
          <td style="padding:4px 8px;">
            ${s.booked === 0 ? `<button type="button" class="button button-small" data-delete-slot="${s.id}" style="color:#a00;">Delete</button>` : ''}
          </td>
        </tr>`).join('')}
        </tbody></table>`;
    } catch {
      list.innerHTML = '<em style="color:#999;font-size:12px;">Could not load slots.</em>';
    }
  }

  /* ── Helpers ────────────────────────────────────────────────────────── */
  function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString(navigator.language || 'en-US', { weekday:'short', month:'short', day:'numeric', year:'numeric' });
  }

  /* ── Boot ───────────────────────────────────────────────────────────── */
  document.addEventListener('DOMContentLoaded', function () {
    // Admin slot manager
    if (cfg.isAdmin) {
      initAdminSlotManager();
      return;
    }

    // Frontend booking widget — look for product pages with a .hgb-booking-root
    document.querySelectorAll('.hgb-booking-root[data-product-id]').forEach(el => {
      mountWidget(el, parseInt(el.dataset.productId, 10));
    });
  });

})();
