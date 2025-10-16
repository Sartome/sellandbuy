// JavaScript principal

// Countdown for auction
(function(){
  const endsEl = document.getElementById('auction-ends-at');
  const countdownEl = document.getElementById('countdown');
  if (!endsEl || !countdownEl) return;
  const endsAt = new Date(endsEl.dataset.ends.replace(' ', 'T'));
  function leftPad(n){ return n.toString().padStart(2,'0'); }
  function tick(){
    const now = new Date();
    let diff = Math.max(0, Math.floor((endsAt - now) / 1000));
    const d = Math.floor(diff / 86400); diff -= d*86400;
    const h = Math.floor(diff / 3600); diff -= h*3600;
    const m = Math.floor(diff / 60); diff -= m*60;
    const s = diff;
    countdownEl.textContent = d+"j "+leftPad(h)+":"+leftPad(m)+":"+leftPad(s);
    if ((endsAt - now) <= 0) {
      countdownEl.textContent = 'Terminée';
      clearInterval(timer);
    }
  }
  tick();
  const timer = setInterval(tick, 1000);
})();

// Auto-dismiss flash messages
(function(){
  const alerts = document.querySelectorAll('.alert');
  if (!alerts.length) return;
  setTimeout(() => alerts.forEach(a => a.style.display = 'none'), 4000);
})();

// Confirm destructive actions
(function(){
  document.addEventListener('click', function(e){
    const t = e.target.closest('[data-confirm]');
    if (t) {
      if (!confirm(t.getAttribute('data-confirm'))) {
        e.preventDefault();
      }
    }
  });
})();