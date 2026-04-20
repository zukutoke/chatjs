(function () {
  const slides = Array.from(document.querySelectorAll(".slide"));
  const total = slides.length;
  const currentEl = document.getElementById("current");
  const totalEl = document.getElementById("total");
  const progressBar = document.getElementById("progressBar");
  const prevBtn = document.getElementById("prev");
  const nextBtn = document.getElementById("next");

  totalEl.textContent = String(total);

  function readHash() {
    const n = parseInt(location.hash.replace("#", ""), 10);
    if (Number.isFinite(n) && n >= 1 && n <= total) return n;
    return 1;
  }

  let idx = readHash();

  function render() {
    slides.forEach((s, i) => s.classList.toggle("is-active", i === idx - 1));
    currentEl.textContent = String(idx);
    progressBar.style.width = ((idx / total) * 100).toFixed(2) + "%";
    if (location.hash !== "#" + idx) {
      history.replaceState(null, "", "#" + idx);
    }
  }

  function go(delta) {
    const next = Math.min(total, Math.max(1, idx + delta));
    if (next !== idx) {
      idx = next;
      render();
    }
  }

  function goTo(n) {
    const next = Math.min(total, Math.max(1, n));
    if (next !== idx) {
      idx = next;
      render();
    }
  }

  prevBtn.addEventListener("click", () => go(-1));
  nextBtn.addEventListener("click", () => go(+1));

  document.addEventListener("keydown", (e) => {
    if (e.target && (e.target.tagName === "INPUT" || e.target.tagName === "TEXTAREA")) return;
    switch (e.key) {
      case "ArrowRight":
      case "PageDown":
      case " ":
        e.preventDefault();
        go(+1);
        break;
      case "ArrowLeft":
      case "PageUp":
        e.preventDefault();
        go(-1);
        break;
      case "Home":
        e.preventDefault();
        goTo(1);
        break;
      case "End":
        e.preventDefault();
        goTo(total);
        break;
      default:
        if (/^[0-9]$/.test(e.key)) {
          const digit = parseInt(e.key, 10);
          if (digit === 0) goTo(10);
          else goTo(digit);
        }
    }
  });

  let touchX = null;
  document.addEventListener("touchstart", (e) => {
    if (e.touches.length === 1) touchX = e.touches[0].clientX;
  }, { passive: true });
  document.addEventListener("touchend", (e) => {
    if (touchX == null) return;
    const dx = (e.changedTouches[0]?.clientX ?? touchX) - touchX;
    if (Math.abs(dx) > 50) go(dx < 0 ? +1 : -1);
    touchX = null;
  });

  window.addEventListener("hashchange", () => {
    const n = readHash();
    if (n !== idx) {
      idx = n;
      render();
    }
  });

  render();
})();
