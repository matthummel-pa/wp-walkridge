/* Global behaviors — Walkridge
   hamburger, mega-nav, scroll reveal, FAQ, tour filters, newsletter demo,
   light/dark theme toggle, accessibility enhancements */
(function(){
  "use strict";

  /* ── Current year ───────────────────────────────────────── */
  var yr = String(new Date().getFullYear());
  document.querySelectorAll("[data-year]").forEach(function(el){ el.textContent = yr; });

  /* ── Theme toggle ─────────────────────────────────────────
     Reads prefers-color-scheme as the default, then defers to
     localStorage('wr-theme') if the user has made a choice.  */
  (function(){
    var STORAGE_KEY = "wr-theme";
    var html = document.documentElement;

    function getPreferred(){
      return window.matchMedia("(prefers-color-scheme: light)").matches ? "light" : "dark";
    }

    function applyTheme(theme){
      if(theme === "light"){
        html.setAttribute("data-theme","light");
      } else {
        html.removeAttribute("data-theme");
      }
      // Update toggle button aria-label & title
      document.querySelectorAll(".theme-toggle").forEach(function(btn){
        var next = theme === "light" ? "dark" : "light";
        btn.setAttribute("aria-label","Switch to " + next + " mode");
        btn.setAttribute("title","Switch to " + next + " mode");
        btn.setAttribute("data-current-theme", theme);
      });
    }

    // Apply saved preference (or OS default) immediately — before paint
    var saved = localStorage.getItem(STORAGE_KEY);
    applyTheme(saved || getPreferred());

    // Wire toggle buttons (may not be in DOM yet — use delegation)
    document.addEventListener("click", function(e){
      var btn = e.target.closest(".theme-toggle");
      if(!btn) return;
      var current = html.getAttribute("data-theme") || "dark";
      var next = current === "light" ? "dark" : "light";
      applyTheme(next);
      localStorage.setItem(STORAGE_KEY, next);
    });

    // Respond to OS preference changes (if no explicit user choice)
    window.matchMedia("(prefers-color-scheme: light)").addEventListener("change", function(e){
      if(!localStorage.getItem(STORAGE_KEY)){
        applyTheme(e.matches ? "light" : "dark");
      }
    });
  })();

  /* ── Scrolled shadow ────────────────────────────────────── */
  var header = document.querySelector(".site-header");
  if(header){
    var onScroll = function(){
      if(window.scrollY > 8){ header.classList.add("is-scrolled"); }
      else { header.classList.remove("is-scrolled"); }
    };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive:true });
  }

  /* ── Mobile nav (dialog + focus trap) ───────────────────── */
  var hamburgerBtn = document.getElementById("hamburgerBtn");
  var mobileNav = document.getElementById("mobileNav");
  if(hamburgerBtn && mobileNav){
    var lastFocus = null;
    var getFocusable = function(){
      return Array.prototype.slice.call(
        mobileNav.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])')
      ).filter(function(el){ return !el.hasAttribute("disabled") && el.offsetParent !== null; });
    };
    var trapFocus = function(e){
      if(e.key !== "Tab" || !mobileNav.classList.contains("is-open")) return;
      var nodes = getFocusable();
      if(!nodes.length) return;
      var first = nodes[0];
      var last = nodes[nodes.length - 1];
      if(e.shiftKey && document.activeElement === first){
        e.preventDefault();
        last.focus();
      } else if(!e.shiftKey && document.activeElement === last){
        e.preventDefault();
        first.focus();
      }
    };
    var closeMobileNav = function(){
      mobileNav.classList.remove("is-open");
      mobileNav.setAttribute("hidden","");
      hamburgerBtn.setAttribute("aria-expanded","false");
      hamburgerBtn.setAttribute("aria-label","Open menu");
      document.body.classList.remove("modal-locked");
      document.removeEventListener("keydown", trapFocus, true);
      if(lastFocus && typeof lastFocus.focus === "function") lastFocus.focus();
    };
    hamburgerBtn.addEventListener("click", function(){
      var open = !mobileNav.classList.contains("is-open");
      if(open){
        lastFocus = document.activeElement;
        mobileNav.classList.add("is-open");
        mobileNav.removeAttribute("hidden");
        hamburgerBtn.setAttribute("aria-expanded","true");
        hamburgerBtn.setAttribute("aria-label","Close menu");
        document.body.classList.add("modal-locked");
        document.addEventListener("keydown", trapFocus, true);
        var firstLink = mobileNav.querySelector("a");
        if(firstLink) firstLink.focus();
      } else {
        closeMobileNav();
      }
    });
    mobileNav.querySelectorAll("a").forEach(function(a){ a.addEventListener("click", closeMobileNav); });
    document.addEventListener("keydown", function(e){ if(e.key === "Escape") closeMobileNav(); });
  }

  /* ── Mega-nav keyboard support ──────────────────────────── */
  document.querySelectorAll(".has-sub").forEach(function(item){
    var trigger = item.querySelector(".nav-trigger");
    var panel   = item.querySelector(".nav-panel");
    if(!trigger) return;

    function openPanel(){
      item.classList.add("is-open");
      trigger.setAttribute("aria-expanded","true");
      if(panel) panel.removeAttribute("hidden");
    }
    function closePanel(){
      item.classList.remove("is-open");
      trigger.setAttribute("aria-expanded","false");
      if(panel) panel.setAttribute("hidden","");
    }

    trigger.addEventListener("click", function(){
      item.classList.contains("is-open") ? closePanel() : openPanel();
    });

    // Close when focus leaves the entire .has-sub group
    item.addEventListener("focusout", function(e){
      if(!item.contains(e.relatedTarget)){
        closePanel();
      }
    });

    // Escape closes the panel and returns focus to trigger
    item.addEventListener("keydown", function(e){
      if(e.key === "Escape"){
        closePanel();
        trigger.focus();
      }
    });
  });

  // Close all mega-nav panels when clicking outside
  document.addEventListener("click", function(e){
    if(!e.target.closest(".has-sub")){
      document.querySelectorAll(".has-sub.is-open").forEach(function(item){
        item.classList.remove("is-open");
        var t = item.querySelector(".nav-trigger");
        var p = item.querySelector(".nav-panel");
        if(t) t.setAttribute("aria-expanded","false");
        if(p) p.setAttribute("hidden","");
      });
    }
  });

  var revealEls = document.querySelectorAll(".reveal");
  if("IntersectionObserver" in window){
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){
          entry.target.classList.add("is-visible");
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: "0px 0px -40px 0px" });
    revealEls.forEach(function(el){ io.observe(el); });
  } else {
    revealEls.forEach(function(el){ el.classList.add("is-visible"); });
  }

  var filterBar = document.querySelector("[data-tour-filter]");
  if(filterBar){
    var cards = document.querySelectorAll("[data-category]");
    var applyFilter = function(val){
      filterBar.querySelectorAll("button").forEach(function(b){
        b.setAttribute("aria-pressed", b.getAttribute("data-filter") === val ? "true" : "false");
      });
      cards.forEach(function(card){
        var show = val === "all" || card.getAttribute("data-category") === val;
        card.classList.toggle("is-hidden", !show);
      });
    };
    filterBar.querySelectorAll("button").forEach(function(btn){
      btn.addEventListener("click", function(){ applyFilter(btn.getAttribute("data-filter")); });
    });
    var hash = (window.location.hash || "").replace("#","");
    if(hash === "after-dark" || hash === "historical") applyFilter(hash);
  }

  function wrPost(action, nonce, data, onOk, onErr) {
    var cfg = window.wrForms || {};
    var body = new FormData();
    body.append("action", action);
    body.append("nonce", nonce || "");
    Object.keys(data).forEach(function(k){ body.append(k, data[k]); });
    fetch(cfg.ajaxUrl || "/wp-admin/admin-ajax.php", {
      method: "POST",
      credentials: "same-origin",
      body: body
    }).then(function(r){ return r.json(); }).then(function(json){
      if(json && json.success){ onOk(json.data && json.data.message ? json.data.message : "Saved."); }
      else { onErr((json && json.data && json.data.message) || "Something went wrong."); }
    }).catch(function(){ onErr("Network error — please try again."); });
  }

  document.querySelectorAll("[data-newsletter], [data-wr-newsletter]").forEach(function(form){
    form.addEventListener("submit", function(e){
      e.preventDefault();
      var input = form.querySelector("input[type=email]");
      var note = form.querySelector("[data-newsletter-note]") || (form.parentElement && form.parentElement.querySelector("[data-newsletter-note]"));
      if(!input || !input.value.trim()){
        if(note) note.textContent = "Add an email to join the field notes list.";
        return;
      }
      var cfg = window.wrForms || {};
      wrPost("wr_newsletter", cfg.newsletterNonce, { EMAIL: input.value.trim() }, function(msg){
        if(note) note.textContent = msg;
        form.reset();
      }, function(msg){
        if(note) note.textContent = msg;
      });
    });
  });

  var contactForm = document.getElementById("contactForm");
  if(contactForm){
    contactForm.addEventListener("submit", function(e){
      e.preventDefault();
      var n = (document.getElementById("cName") || {}).value;
      var em = (document.getElementById("cEmail") || {}).value;
      var msg = (document.getElementById("cMsg") || {}).value;
      var note = document.getElementById("contactNote");
      n = n ? n.trim() : "";
      em = em ? em.trim() : "";
      msg = msg ? msg.trim() : "";
      if(!n || !em || !msg){ if(note) note.textContent = "Please add your name, email, and a message."; return; }
      var cfg = window.wrForms || {};
      wrPost("wr_contact", cfg.contactNonce, { cName: n, cEmail: em, cMsg: msg }, function(m){
        if(note) note.textContent = m;
        contactForm.reset();
      }, function(m){
        if(note) note.textContent = m;
      });
    });
  }

})();
