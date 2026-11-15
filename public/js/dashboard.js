(function () {
  'use strict';
  const canvas = document.getElementById('hargaChart');
  if (!canvas) return;

  const select = document.getElementById('komoditasSelect');
  const info = document.getElementById('chartInfo');
  const buttons = document.querySelectorAll('[data-periode]');
  const baseUrl = canvas.dataset.url;
  let periode = 'harian';
  let chart = null;

  const rupiah = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');

  function render(data) {
    if (chart) chart.destroy();
    chart = new Chart(canvas, {
      type: 'line',
      data: {
        labels: data.labels,
        datasets: [{
          label: data.nama + ' (Rp/' + data.satuan + ')',
          data: data.values,
          borderColor: '#0f766e',
          backgroundColor: 'rgba(15,118,110,.15)',
          fill: true, tension: 0.25, pointRadius: 2,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { tooltip: { callbacks: { label: (c) => rupiah(c.parsed.y) } } },
        scales: { y: { ticks: { callback: (v) => rupiah(v) } } },
      },
    });
  }

  async function load() {
    info.textContent = 'Memuat data...';
    try {
      const url = baseUrl + '/' + encodeURIComponent(select.value) + '?periode=' + encodeURIComponent(periode);
      const res = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();
      render(data);
      // textContent (bukan innerHTML) → aman dari XSS
      info.textContent = data.labels.length ? '' : 'Belum ada data untuk periode ini. Jalankan: php artisan fetch:sembako';
    } catch (err) {
      info.textContent = 'Gagal memuat grafik.';
    }
  }

  buttons.forEach((b) => b.addEventListener('click', () => {
    periode = b.dataset.periode;
    buttons.forEach((x) => x.classList.toggle('active', x === b));
    load();
  }));
  select.addEventListener('change', load);
  load();
})();