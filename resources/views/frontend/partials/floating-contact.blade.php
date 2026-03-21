{{-- ═══ Floating Contact Widget ═══ --}}
<div class="floating-contact" id="floatingContact">
  {{-- Contact Icons --}}
  <div class="floating-contact__icons" id="floatingContactIcons">
    {{-- Zalo (tạm ẩn – chưa có Zalo OA)
    <a href="https://zalo.me/0964047698" target="_blank" rel="noopener noreferrer"
       class="floating-contact__btn floating-contact__btn--zalo" title="Chat Zalo">
      <svg viewBox="0 0 48 48" width="28" height="28" fill="none">
        <circle cx="24" cy="24" r="22" stroke="#0068FF" stroke-width="2.5" fill="none"/>
        <text x="24" y="30" text-anchor="middle" font-size="14" font-weight="700" fill="#0068FF" font-family="Arial,sans-serif">Zalo</text>
      </svg>
    </a>
    --}}

    {{-- 🎵 Background Music Toggle --}}
    <button class="floating-contact__btn floating-contact__btn--music" id="bgMusicToggle" title="Bật/Tắt nhạc nền" aria-label="Toggle background music">
      {{-- Volume ON icon --}}
      <svg class="bgm-icon-on" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
        <path d="M19.07 4.93a10 10 0 0 1 0 14.14"/>
        <path d="M15.54 8.46a5 5 0 0 1 0 7.07"/>
      </svg>
      {{-- Volume OFF icon --}}
      <svg class="bgm-icon-off" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
        <line x1="23" y1="9" x2="17" y2="15"/>
        <line x1="17" y1="9" x2="23" y2="15"/>
      </svg>
    </button>

    {{-- Messenger --}}
    <a href="https://m.me/nguyensonarsenal.10" target="_blank" rel="noopener noreferrer"
       class="floating-contact__btn floating-contact__btn--messenger" title="Chat Messenger">
      <svg viewBox="0 0 28 28" width="28" height="28">
        <path d="M14 0C6.268 0 0 5.758 0 13.09c0 3.882 1.614 7.342 4.244 9.79.22.206.352.49.364.794l.074 2.48c.024.796.846 1.316 1.57.992l2.77-1.222a1.07 1.07 0 0 1 .722-.046c1.378.38 2.842.582 4.356.582C21.73 26.46 28 20.702 28 13.37S21.73.28 14 .28V0Z" fill="url(#msng-grad)"/>
        <path d="M5.6 16.94l4.12-6.536a2.1 2.1 0 0 1 3.038-.56l3.276 2.458a.84.84 0 0 0 1.014-.002l4.424-3.358c.59-.448 1.362.252.966.876l-4.12 6.534a2.1 2.1 0 0 1-3.038.56l-3.276-2.458a.84.84 0 0 0-1.014.002l-4.424 3.358c-.59.45-1.362-.25-.966-.874Z" fill="#fff"/>
        <defs>
          <linearGradient id="msng-grad" x1="14" y1="26.46" x2="14" y2="0" gradientUnits="userSpaceOnUse">
            <stop stop-color="#0099FF"/>
            <stop offset="1" stop-color="#A033FF"/>
          </linearGradient>
        </defs>
      </svg>
    </a>
  </div>

  {{-- Hidden audio element --}}
  <audio id="bgMusicAudio" loop preload="auto">
    <source src="/frontend/audio/ambient.mp3" type="audio/mpeg">
  </audio>

  {{-- Scroll to Top --}}
  <button class="floating-contact__btn floating-contact__scroll-top" id="scrollToTopBtn" title="Lên đầu trang" aria-label="Scroll to top" style="display:none">
    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#4f7af8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <polyline points="18 15 12 9 6 15"/>
    </svg>
  </button>

  {{-- Toggle Button --}}
  <button class="floating-contact__toggle" id="floatingContactToggle" title="Liên hệ" aria-label="Toggle contact icons">
    {{-- Chat icon (open state) --}}
    <svg class="floating-contact__icon-chat" viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z"/>
    </svg>
    {{-- Close icon (close state) --}}
    <svg class="floating-contact__icon-close" viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <line x1="18" y1="6" x2="6" y2="18"/>
      <line x1="6" y1="6" x2="18" y2="18"/>
    </svg>
  </button>
</div>

<style>
/* ═══ Floating Contact Widget ═══ */
.floating-contact {
  position: fixed;
  bottom: 28px;
  right: 24px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

/* ── Icon list ── */
.floating-contact__icons {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  transition: opacity .3s ease, transform .3s ease;
}
.floating-contact.is-collapsed .floating-contact__icons {
  opacity: 0;
  transform: translateY(16px) scale(0.8);
  pointer-events: none;
}

/* ── Each icon button ── */
.floating-contact__btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: #fff;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.18), 0 1px 4px rgba(0, 0, 0, 0.1);
  text-decoration: none;
  transition: transform .25s ease, box-shadow .25s ease;
  animation: floatContactBounceIn .5s ease both;
}
.floating-contact__btn:nth-child(1) { animation-delay: .1s; }
.floating-contact__btn:nth-child(2) { animation-delay: .2s; }
.floating-contact__btn:nth-child(3) { animation-delay: .3s; }

.floating-contact__btn:hover {
  transform: scale(1.12);
  box-shadow: 0 6px 24px rgba(0, 0, 0, 0.25), 0 2px 8px rgba(0, 0, 0, 0.15);
}
.floating-contact__btn:active {
  transform: scale(0.95);
}

/* ── Zalo specific ── */
.floating-contact__btn--zalo {
  border: 2px solid #0068FF;
}

/* ── Messenger specific ── */
.floating-contact__btn--messenger {
  border: 2px solid #0099FF;
}

/* ── Music toggle ── */
.floating-contact__btn--music {
  background: linear-gradient(135deg, #7c3aed, #a855f7);
  border: 2px solid #a855f7;
  cursor: pointer;
  outline: none;
  position: relative;
  overflow: hidden;
}
.floating-contact__btn--music:hover {
  box-shadow: 0 6px 24px rgba(168, 85, 247, 0.45), 0 2px 8px rgba(0, 0, 0, 0.15);
}

/* Icons inside music button */
.floating-contact__btn--music .bgm-icon-on,
.floating-contact__btn--music .bgm-icon-off {
  position: absolute;
  transition: opacity .3s ease, transform .3s ease;
}
.floating-contact__btn--music .bgm-icon-on {
  opacity: 0;
  transform: scale(0.6);
}
.floating-contact__btn--music .bgm-icon-off {
  opacity: 1;
  transform: scale(1);
}
/* When playing */
.floating-contact__btn--music.is-playing .bgm-icon-on {
  opacity: 1;
  transform: scale(1);
}
.floating-contact__btn--music.is-playing .bgm-icon-off {
  opacity: 0;
  transform: scale(0.6);
}

/* Glow pulse when playing */
.floating-contact__btn--music.is-playing {
  box-shadow: 0 0 12px rgba(168, 85, 247, 0.5), 0 0 24px rgba(168, 85, 247, 0.25);
  animation: bgmGlowPulse 2s ease-in-out infinite;
}
@keyframes bgmGlowPulse {
  0%, 100% { box-shadow: 0 0 12px rgba(168, 85, 247, 0.5), 0 0 24px rgba(168, 85, 247, 0.25); }
  50% { box-shadow: 0 0 20px rgba(168, 85, 247, 0.7), 0 0 40px rgba(168, 85, 247, 0.35); }
}

/* ── Scroll to Top ── */
.floating-contact__scroll-top {
  border: 2px solid var(--blue, #4f7af8);
  opacity: 0;
  transform: translateY(10px);
  transition: opacity .3s ease, transform .25s ease, box-shadow .25s ease, border-color .25s ease;
  pointer-events: none;
  cursor: pointer;
  outline: none;
}
.floating-contact__scroll-top.is-visible {
  opacity: 1;
  transform: translateY(0) scale(1);
  pointer-events: auto;
}
.floating-contact__scroll-top.is-visible:hover {
  transform: translateY(0) scale(1.12);
  box-shadow: 0 6px 24px rgba(79, 122, 248, 0.35), 0 2px 8px rgba(0, 0, 0, 0.15);
  border-color: #3a64e0;
}
.floating-contact__scroll-top.is-visible:hover svg {
  stroke: #3a64e0;
}
.floating-contact__scroll-top.is-visible:active {
  transform: translateY(0) scale(0.95);
}
.floating-contact.is-collapsed .floating-contact__scroll-top {
  opacity: 0;
  transform: translateY(16px) scale(0.8);
  pointer-events: none;
}

/* ── Toggle button ── */
.floating-contact__toggle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 52px;
  height: 52px;
  border-radius: 50%;
  border: none;
  cursor: pointer;
  background: linear-gradient(135deg, #FF6B35, #F74A25);
  box-shadow: 0 4px 16px rgba(247, 74, 37, 0.4), 0 1px 4px rgba(0, 0, 0, 0.1);
  transition: transform .25s ease, box-shadow .25s ease, background .3s ease;
}
.floating-contact__toggle:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 24px rgba(247, 74, 37, 0.55), 0 2px 8px rgba(0, 0, 0, 0.15);
}
.floating-contact__toggle:active {
  transform: scale(0.92);
}

/* ── Toggle icon states ── */
.floating-contact__icon-chat,
.floating-contact__icon-close {
  position: absolute;
  transition: opacity .25s ease, transform .25s ease;
}
.floating-contact__icon-close {
  opacity: 0;
  transform: rotate(-90deg) scale(0.6);
}
.floating-contact.is-collapsed .floating-contact__icon-chat {
  opacity: 0;
  transform: rotate(90deg) scale(0.6);
}
.floating-contact.is-collapsed .floating-contact__icon-close {
  opacity: 1;
  transform: rotate(0) scale(1);
}

/* When collapsed, show chat icon (bubble), hide X */
/* When expanded, show X, hide chat icon */
/* Default = expanded = X visible. After toggle = collapsed = chat bubble visible */
/* Actually let's flip: default is expanded (icons visible), toggle collapses */

/* ── Animations ── */
@keyframes floatContactBounceIn {
  0% {
    opacity: 0;
    transform: translateY(20px) scale(0.6);
  }
  60% {
    transform: translateY(-4px) scale(1.05);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* ── Pulse animation on toggle ── */
.floating-contact__toggle::after {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: inherit;
  animation: floatContactPulse 2s ease-in-out infinite;
  z-index: -1;
}
@keyframes floatContactPulse {
  0%, 100% { transform: scale(1); opacity: 0.6; }
  50% { transform: scale(1.25); opacity: 0; }
}

/* ── Mobile responsive ── */
@media (max-width: 600px) {
  .floating-contact {
    bottom: 16px;
    right: 14px;
    gap: 10px;
  }
  .floating-contact__btn,
  .floating-contact__toggle,
  .floating-contact__scroll-top {
    width: 46px;
    height: 46px;
  }
  .floating-contact__btn svg,
  .floating-contact__toggle svg,
  .floating-contact__scroll-top svg {
    width: 22px;
    height: 22px;
  }
}
</style>

<script>
(function() {
  const widget = document.getElementById('floatingContact');
  const toggle = document.getElementById('floatingContactToggle');
  const scrollBtn = document.getElementById('scrollToTopBtn');

  toggle.addEventListener('click', function() {
    widget.classList.toggle('is-collapsed');
  });

  // Scroll to top
  scrollBtn.addEventListener('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // Show/hide scroll-to-top based on scroll position
  let ticking = false;
  window.addEventListener('scroll', function() {
    if (!ticking) {
      window.requestAnimationFrame(function() {
        if (window.scrollY > 300) {
          scrollBtn.style.display = 'flex';
          requestAnimationFrame(function() { scrollBtn.classList.add('is-visible'); });
        } else {
          scrollBtn.classList.remove('is-visible');
          setTimeout(function() {
            if (!scrollBtn.classList.contains('is-visible')) scrollBtn.style.display = 'none';
          }, 300);
        }
        ticking = false;
      });
      ticking = true;
    }
  });

  // ═══ Background Music Logic ═══
  const musicBtn = document.getElementById('bgMusicToggle');
  const audio = document.getElementById('bgMusicAudio');
  const STORAGE_KEY = 'bgm_enabled';

  if (musicBtn && audio) {
    audio.volume = 0.3;
    var autoPlayTriggered = false;

    function setPlaying(playing) {
      if (playing) {
        musicBtn.classList.add('is-playing');
        musicBtn.title = 'Tắt nhạc nền';
      } else {
        musicBtn.classList.remove('is-playing');
        musicBtn.title = 'Bật nhạc nền';
      }
    }

    function tryPlay() {
      var playPromise = audio.play();
      if (playPromise !== undefined) {
        playPromise.then(function() {
          setPlaying(true);
          localStorage.setItem(STORAGE_KEY, 'true');
        }).catch(function() {
          // Autoplay blocked – will retry on next user interaction
          setPlaying(false);
        });
      }
    }

    // Toggle on click
    musicBtn.addEventListener('click', function() {
      if (audio.paused) {
        tryPlay();
      } else {
        audio.pause();
        setPlaying(false);
        localStorage.setItem(STORAGE_KEY, 'false');
      }
    });

    // ── Auto-play on first user interaction ──
    // Trình duyệt chặn autoplay, nhưng cho phép play sau khi user tương tác
    // → Đợi user scroll/click/chạm bất kỳ đâu → tự động phát nhạc
    function autoPlayOnInteraction() {
      if (autoPlayTriggered) return;
      // Nếu user đã tắt nhạc trước đó → tôn trọng, không tự phát
      if (localStorage.getItem(STORAGE_KEY) === 'false') return;
      autoPlayTriggered = true;
      tryPlay();
      // Gỡ listener sau khi đã trigger
      document.removeEventListener('scroll', autoPlayOnInteraction);
      document.removeEventListener('click', autoPlayOnInteraction);
      document.removeEventListener('touchstart', autoPlayOnInteraction);
    }

    // Thử play ngay (sẽ thành công nếu user đã tương tác trước đó / reload)
    if (localStorage.getItem(STORAGE_KEY) !== 'false') {
      var directPlay = audio.play();
      if (directPlay !== undefined) {
        directPlay.then(function() {
          autoPlayTriggered = true;
          setPlaying(true);
          localStorage.setItem(STORAGE_KEY, 'true');
        }).catch(function() {
          // Bị chặn → đăng ký listener chờ user tương tác
          document.addEventListener('scroll', autoPlayOnInteraction, { once: false, passive: true });
          document.addEventListener('click', autoPlayOnInteraction, { once: true });
          document.addEventListener('touchstart', autoPlayOnInteraction, { once: true });
        });
      }
    }

    // Listen for audio events to keep icon in sync
    audio.addEventListener('play', function() { setPlaying(true); });
    audio.addEventListener('pause', function() { setPlaying(false); });
  }
})();
</script>
