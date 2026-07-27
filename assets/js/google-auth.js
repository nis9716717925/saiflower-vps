/**
 * Google Identity Services — Continue with Google for login/register pages.
 * Expects window.SAIFLOWER_GOOGLE = { clientId, nonce, csrfToken, redirect, endpoint }
 */
(function () {
  'use strict';

  var cfg = window.SAIFLOWER_GOOGLE;
  if (!cfg || !cfg.clientId) {
    return;
  }

  var statusEl = document.getElementById('google-auth-status');
  var btnWrap = document.getElementById('google-btn-wrap');
  var busy = false;

  function setStatus(message, isError) {
    if (!statusEl) return;
    statusEl.textContent = message || '';
    statusEl.classList.toggle('hidden', !message);
    statusEl.classList.toggle('text-red-600', !!isError);
    statusEl.classList.toggle('bg-red-50', !!isError);
    statusEl.classList.toggle('border-red-100', !!isError);
    statusEl.classList.toggle('text-slate-600', !isError);
    statusEl.classList.toggle('bg-slate-50', !isError);
    statusEl.classList.toggle('border-slate-100', !isError);
  }

  function postCredential(credential) {
    if (busy) return;
    busy = true;
    setStatus('Signing you in…', false);

    fetch(cfg.endpoint || '/actions/google_auth', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify({
        credential: credential,
        csrf_token: cfg.csrfToken,
        redirect: cfg.redirect || '',
      }),
    })
      .then(function (res) {
        return res.json().then(function (data) {
          return { ok: res.ok, status: res.status, data: data };
        });
      })
      .then(function (result) {
        if (result.data && result.data.success) {
          setStatus('Success! Redirecting…', false);
          window.location.href = result.data.redirect || '/';
          return;
        }
        busy = false;
        setStatus(
          (result.data && result.data.message) || 'Google sign-in failed. Please try again.',
          true
        );
      })
      .catch(function () {
        busy = false;
        setStatus('Network error. Check your connection and try again.', true);
      });
  }

  function handleCredentialResponse(response) {
    if (!response || !response.credential) {
      setStatus('Google did not return a credential. Please try again.', true);
      return;
    }
    postCredential(response.credential);
  }

  function initGis() {
    if (!window.google || !google.accounts || !google.accounts.id) {
      setStatus('Google Sign-In is unavailable right now.', true);
      return;
    }

    try {
      google.accounts.id.initialize({
        client_id: cfg.clientId,
        callback: handleCredentialResponse,
        nonce: cfg.nonce || undefined,
        auto_select: false,
        cancel_on_tap_outside: true,
        context: cfg.context || 'signin',
        ux_mode: 'popup',
        itp_support: true,
      });

      if (btnWrap) {
        google.accounts.id.renderButton(btnWrap, {
          type: 'standard',
          theme: 'outline',
          size: 'large',
          text: 'continue_with',
          shape: 'rectangular',
          logo_alignment: 'left',
          width: Math.min(btnWrap.offsetWidth || 360, 400),
        });
      }

      // Optional One Tap (non-blocking; dismiss is fine)
      try {
        google.accounts.id.prompt(function (notification) {
          if (!notification) return;
          if (notification.isNotDisplayed && notification.isNotDisplayed()) {
            // Silently ignore — user can still use the button
            return;
          }
          if (notification.isSkippedMoment && notification.isSkippedMoment()) {
            return;
          }
          if (notification.isDismissedMoment && notification.isDismissedMoment()) {
            // User closed One Tap / popup — no error needed
            return;
          }
        });
      } catch (e) {
        /* One Tap optional */
      }
    } catch (err) {
      setStatus('Could not start Google Sign-In. Please refresh and try again.', true);
    }
  }

  function loadScript() {
    if (window.google && google.accounts && google.accounts.id) {
      initGis();
      return;
    }
    var existing = document.querySelector('script[data-saiflower-gsi]');
    if (existing) {
      existing.addEventListener('load', initGis);
      return;
    }
    var s = document.createElement('script');
    s.src = 'https://accounts.google.com/gsi/client';
    s.async = true;
    s.defer = true;
    s.setAttribute('data-saiflower-gsi', '1');
    s.onload = initGis;
    s.onerror = function () {
      setStatus('Failed to load Google Sign-In. Please check your network.', true);
    };
    document.head.appendChild(s);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadScript);
  } else {
    loadScript();
  }
})();
