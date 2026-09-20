// ======================================================
// TheSecond Admin — JS dùng chung
// ======================================================

// ---------- Sidebar mobile toggle ----------
const hamburgerBtn = document.getElementById('hamburgerBtn');
if (hamburgerBtn) {
  hamburgerBtn.addEventListener('click', () => {
    document.getElementById('sidebar')?.classList.toggle('open');
  });
}

// ---------- Modal ----------
function openModal(id) {
  document.getElementById(id)?.classList.add('show');
}
function closeModal(id) {
  document.getElementById(id)?.classList.remove('show');
}

document.querySelectorAll('.overlay').forEach(ov => {
  ov.addEventListener('click', e => {
    if (e.target === ov) ov.classList.remove('show');
  });
});

// ---------- Biểu đồ cột thuần CSS ----------
function renderBars(containerId, data) {
  const el = document.getElementById(containerId);
  if (!el || !Array.isArray(data) || data.length === 0) return;

  const max = Math.max(...data.map(d => d.v), 0.01);
  el.innerHTML = data.map(d => `
    <div class="bar-col">
      <div class="bar" style="height:${(d.v / max * 100)}%"></div>
      <div class="bar-label">${d.l}</div>
    </div>`).join('');
}

if (window.revenueData) {
  renderBars('revenueChart', window.revenueData);
}

// ---------- Thông báo (chuông) ----------
function toggleNotif(e) {
  e.stopPropagation();
  document.getElementById('notifPanel')?.classList.toggle('show');
}
document.addEventListener('click', function (e) {
  const wrap = document.querySelector('.notif-wrap');
  if (wrap && !wrap.contains(e.target)) {
    document.getElementById('notifPanel')?.classList.remove('show');
  }
});
