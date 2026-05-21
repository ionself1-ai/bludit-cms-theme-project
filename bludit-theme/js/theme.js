// Bludit Theme JS — theme toggle, dropdowns, search

(function () {
  'use strict';

  // ── Theme ──────────────────────────────────────────────────────────────
  var THEME_KEY = 'bludit-theme';

  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    var moon = document.getElementById('iconMoon');
    var sun  = document.getElementById('iconSun');
    if (moon) moon.style.display = theme === 'dark' ? 'none' : 'block';
    if (sun)  sun.style.display  = theme === 'dark' ? 'block' : 'none';
    localStorage.setItem(THEME_KEY, theme);
  }

  window.toggleTheme = function () {
    var current = document.documentElement.getAttribute('data-theme') || 'light';
    applyTheme(current === 'light' ? 'dark' : 'light');
  };

  // Apply saved theme on load
  var saved = localStorage.getItem(THEME_KEY);
  if (saved) {
    applyTheme(saved);
  } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    applyTheme('dark');
  }

  // ── Dropdowns ──────────────────────────────────────────────────────────
  window.toggleDropdown = function (btn) {
    var dropdown = btn.closest('.dropdown');
    var isOpen = dropdown.classList.contains('open');

    // Close all dropdowns
    document.querySelectorAll('.dropdown.open').forEach(function (d) {
      d.classList.remove('open');
    });

    if (!isOpen) {
      dropdown.classList.add('open');
    }
  };

  // Close dropdowns on outside click
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.dropdown')) {
      document.querySelectorAll('.dropdown.open').forEach(function (d) {
        d.classList.remove('open');
      });
    }
  });

  // ── Search ─────────────────────────────────────────────────────────────
  window.toggleSearch = function () {
    var box    = document.getElementById('searchBox');
    var toggle = document.getElementById('searchToggle');
    var input  = document.getElementById('searchInput');

    if (!box) return;

    var isOpen = box.classList.contains('open');
    if (isOpen) {
      box.classList.remove('open');
      toggle && (toggle.style.display = 'flex');
    } else {
      box.classList.add('open');
      toggle && (toggle.style.display = 'none');
      if (input) setTimeout(function () { input.focus(); }, 50);
    }
  };

  // Close search on Escape
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      var box = document.getElementById('searchBox');
      var toggle = document.getElementById('searchToggle');
      if (box && box.classList.contains('open')) {
        box.classList.remove('open');
        toggle && (toggle.style.display = 'flex');
      }
      document.querySelectorAll('.dropdown.open').forEach(function (d) {
        d.classList.remove('open');
      });
    }
  });

})();
