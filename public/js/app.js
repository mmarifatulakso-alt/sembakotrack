document.addEventListener('DOMContentLoaded', () => {
    const ICON_EYE_OPEN = `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
    const ICON_EYE_OFF  = `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;
    const ICON_SEARCH   = `<svg class="table-search-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>`;
    const ICON_PDF      = `<svg class="btn-svg-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><polyline points="9 15 12 18 15 15"></polyline></svg>`;
    const ICON_CSV      = `<svg class="btn-svg-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="3" y1="15" x2="21" y2="15"></line><line x1="9" y1="9" x2="9" y2="21"></line><line x1="15" y1="9" x2="15" y2="21"></line></svg>`;

    function formatRupiah(num) {
        return 'Rp ' + Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // 1. TOMBOL MATA PASSWORD GAYA EDLINK
    document.querySelectorAll('input[type="password"]').forEach((input) => {
        if (!input.parentElement.classList.contains('pw-input-wrap')) {
            const wrap = document.createElement('div');
            wrap.className = 'pw-input-wrap';
            input.parentNode.insertBefore(wrap, input);
            wrap.appendChild(input);

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pw-toggle-btn';
            btn.setAttribute('aria-label', 'Tampilkan password');
            btn.setAttribute('aria-pressed', 'false');
            btn.innerHTML = ICON_EYE_OFF;
            wrap.appendChild(btn);
        } else {
            const existingBtn = input.parentElement.querySelector('.pw-toggle-btn');
            if (existingBtn && !existingBtn.querySelector('svg')) {
                existingBtn.innerHTML = ICON_EYE_OFF;
            }
        }
    });

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.pw-toggle-btn');
        if (!btn) return;

        const targetId = btn.getAttribute('data-target');
        const wrap = btn.closest('.pw-input-wrap');
        const input = targetId ? document.getElementById(targetId) : (wrap ? wrap.querySelector('input') : null);
        if (!input) return;

        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = ICON_EYE_OPEN;
            btn.setAttribute('aria-label', 'Sembunyikan password');
            btn.setAttribute('aria-pressed', 'true');
        } else {
            input.type = 'password';
            btn.innerHTML = ICON_EYE_OFF;
            btn.setAttribute('aria-label', 'Tampilkan password');
            btn.setAttribute('aria-pressed', 'false');
        }
    });

    // 2. PENANDA TOMBOL AKTIF PERIODE GRAFIK (TANPA MEMINDAHKAN ELEMEN DOM)
    document.querySelectorAll('.card .toolbar').forEach((toolbar) => {
        const periodBtns = Array.from(toolbar.querySelectorAll('button, .btn')).filter((b) =>
            /Harian|Mingguan|Tahunan/i.test(b.textContent)
        );
        if (periodBtns.length >= 2) {
            periodBtns.forEach((btn, idx) => {
                if (idx === 0) {
                    btn.classList.remove('period-inactive');
                } else {
                    btn.classList.add('period-inactive');
                }
                btn.addEventListener('click', () => {
                    periodBtns.forEach((b) => b.classList.add('period-inactive'));
                    btn.classList.remove('period-inactive');
                });
            });
        }
    });

    // 3. PENCARIAN CEPAT & TOMBOL PINTAS "+ PANTAU" DI TABEL DASHBOARD HARGA
    document.querySelectorAll('table').forEach((table) => {
        const headers = Array.from(table.querySelectorAll('thead th')).map((th) => th.textContent.trim().toUpperCase());
        const isDashboardTable = headers.includes('KOMODITAS') && headers.includes('INDIKATOR');

        if (isDashboardTable) {
            const headRow = table.querySelector('thead tr');
            if (!document.querySelector('.navbar').textContent.includes('Admin') && headRow && !headRow.innerHTML.includes('AKSI')) {
                const thAksi = document.createElement('th');
                thAksi.textContent = 'AKSI';
                headRow.appendChild(thAksi);

                table.querySelectorAll('tbody tr').forEach((row) => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length >= 2) {
                        const namaKomoditas = cells[0].textContent.trim();
                        const teksHarga     = cells[1].textContent.replace(/[^\d]/g, '');
                        const hargaNum      = parseInt(teksHarga || '0', 10);
                        const potongan      = Math.floor(hargaNum / 50);
                        const saranTarget   = hargaNum > 1000 ? Math.round((hargaNum - potongan) / 100) * 100 : hargaNum;

                        const tdAksi = document.createElement('td');
                        tdAksi.className = 'action-cell';
                        const url = `/watchlist?komoditas=${encodeURIComponent(namaKomoditas)}&target=${saranTarget}#formWatchlistCard`;
                        tdAksi.innerHTML = `<a href="${url}" class="btn btn-sm btn-outline">+ Pantau</a>`;
                        row.appendChild(tdAksi);
                    }
                });
            }

            if (!table.parentElement.querySelector('.table-search-wrap')) {
                const bar = document.createElement('div');
                bar.className = 'table-header-bar';
                bar.innerHTML = `
                    ${!window.IS_ADMIN ? '<div class="muted">Klik <strong>+ Pantau</strong> untuk menambahkan komoditas ke Watchlist Anda</div>' : ''}
                    <div class="table-search-wrap">
                        ${ICON_SEARCH}
                        <input type="search" id="quickSearchKomoditas" placeholder="Cari komoditas (misal: Beras, Cabai)..." aria-label="Cari komoditas pangan">
                    </div>
                `;
                table.parentNode.insertBefore(bar, table);

                const searchInput = bar.querySelector('#quickSearchKomoditas');
                const tbody = table.querySelector('tbody');

                searchInput.addEventListener('input', () => {
                    const q = searchInput.value.trim().toLowerCase();
                    let visibleCount = 0;

                    Array.from(tbody.querySelectorAll('tr:not(.empty-search-row)')).forEach((row) => {
                        const match = row.textContent.toLowerCase().includes(q);
                        row.style.display = match ? '' : 'none';
                        if (match) visibleCount++;
                    });

                    let emptyRow = tbody.querySelector('.empty-search-row');
                    if (visibleCount === 0) {
                        if (!emptyRow) {
                            emptyRow = document.createElement('tr');
                            emptyRow.className = 'empty-search-row';
                            emptyRow.innerHTML = `<td colspan="5" class="muted">Tidak ditemukan komoditas yang cocok dengan pencarian Anda.</td>`;
                            tbody.appendChild(emptyRow);
                        }
                    } else if (emptyRow) {
                        emptyRow.remove();
                    }
                });
            }
        }
    });

    // Warnai otomatis kolom PERUBAHAN & INDIKATOR di Dashboard
    document.querySelectorAll('table tbody td').forEach((td) => {
        if (td.querySelector('button, a, input, select, form')) return;
        const txt = td.textContent.trim();

        if (/▲\s*Naik|^Naik$/i.test(txt)) {
            td.innerHTML = `<span class="ind-naik">${txt}</span>`;
        } else if (/▼\s*Turun|^Turun$/i.test(txt)) {
            td.innerHTML = `<span class="ind-turun">${txt}</span>`;
        } else if (/^Stabil$|^Tetap$/i.test(txt)) {
            td.innerHTML = `<span class="ind-stabil">${txt}</span>`;
        } else if (/^\+[\d.,]+\s*\([\d.,+-]+%\)$/.test(txt)) {
            td.classList.add('val-naik');
        } else if (/^-[\d.,]+\s*\([\d.,+-]+%\)$/.test(txt)) {
            td.classList.add('val-turun');
        }
    });

    // 4. KALKULATOR KUANTITAS (QTY) & STATUS REAL-TIME DI HALAMAN WATCHLIST
    const watchlistRows = document.querySelectorAll('tr.watchlist-item-row');
    if (watchlistRows.length > 0) {
        const storageKey = 'sembako_qty_map';
        let qtyMap = {};
        try {
            qtyMap = JSON.parse(localStorage.getItem(storageKey) || '{}');
        } catch (e) {
            qtyMap = {};
        }

        function recalculateWatchlistTotals() {
            let totalMarket = 0;
            let totalTarget = 0;
            let hitCount    = 0;
            let waitCount   = 0;

            watchlistRows.forEach((row) => {
                const id     = row.getAttribute('data-watch-id');
                const harga  = parseInt(row.getAttribute('data-harga') || '0', 10);
                const target = parseInt(row.getAttribute('data-target') || '0', 10);
                const input  = row.querySelector('.qty-input');
                const qty    = Math.max(1, parseInt(input?.value || '1', 10));

                if (id) qtyMap[id] = qty;

                // Harga Saat Ini dikalikan Kuantitas, sedangkan Target Harga tetap sesuai batas anggaran user
                const subMarket = Math.imul(harga, qty);
                const subTarget = target;
                totalMarket += subMarket;
                totalTarget += subTarget;

                const marketEl = row.querySelector('.row-market-display');
                const targetEl = row.querySelector('.row-target-display');
                if (marketEl && harga > 0) marketEl.textContent = formatRupiah(subMarket);
                if (targetEl && target > 0) targetEl.textContent = formatRupiah(subTarget);

                // Update Badge STATUS pada baris ini secara real-time
                const statusBadge = row.querySelector('.status-hit, .status-wait');
                if (statusBadge && harga > 0) {
                    if (subMarket <= subTarget) {
                        statusBadge.className = 'status-hit';
                        statusBadge.textContent = 'Target Tercapai';
                        hitCount++;
                    } else {
                        statusBadge.className = 'status-wait';
                        statusBadge.textContent = 'Di Atas Target';
                        waitCount++;
                    }
                }
            });

            localStorage.setItem(storageKey, JSON.stringify(qtyMap));

            // Update Kartu Ringkasan Atas
            const sumMarketVal = document.getElementById('sumMarketVal');
            const sumTargetVal = document.getElementById('sumTargetVal');
            const sumDiffWrap  = document.getElementById('sumDiffBadgeWrap');

            if (sumMarketVal) sumMarketVal.textContent = formatRupiah(totalMarket);
            if (sumTargetVal) sumTargetVal.textContent = formatRupiah(totalTarget);
            if (sumDiffWrap) {
                const isHemat = totalMarket <= totalTarget;
                const selisih = Math.abs(totalMarket - totalTarget);
                if (isHemat) {
                    sumDiffWrap.innerHTML = `<span class="status-hit">Hemat ${formatRupiah(selisih)} dari target</span>`;
                } else {
                    sumDiffWrap.innerHTML = `<span class="status-wait">Selisih +${formatRupiah(selisih)} di atas target</span>`;
                }
            }

            // Update Kartu ke-3 (Progres Pencapaian Target) & Tombol Filter
            const summaryCards = document.querySelectorAll('#watchlistSummaryCards .summary-val');
            if (summaryCards.length >= 3) {
                summaryCards[2].textContent = `${hitCount} / ${watchlistRows.length} Komoditas`;
            }

            const filterPills = document.querySelectorAll('.filter-pills .filter-pill');
            if (filterPills.length >= 3) {
                filterPills[0].textContent = `Semua (${watchlistRows.length})`;
                filterPills[1].textContent = `Tercapai (${hitCount})`;
                filterPills[2].textContent = `Di Atas Target (${waitCount})`;
            }
        }

        watchlistRows.forEach((row) => {
            const id    = row.getAttribute('data-watch-id');
            const input = row.querySelector('.qty-input');
            if (input) {
                if (id && qtyMap[id]) {
                    input.value = qtyMap[id];
                }
                input.addEventListener('input', recalculateWatchlistTotals);
            }
        });

        recalculateWatchlistTotals();
    }

    // 5. KARTU METRIK SEMANTIK & TOMBOL EKSPOR DI PANEL ADMIN
    document.querySelectorAll('.card').forEach((card) => {
        if (card.children.length <= 2 && card.textContent.trim().length < 65) {
            const rawText = card.textContent.trim();
            const match = rawText.match(/^(\d+)\s*([A-Za-z].*)$/);
            if (match && !card.querySelector('table, form, input, select, button')) {
                const label = match[2];
                card.innerHTML = `<span class="admin-stat-num">${match[1]}</span><span class="admin-stat-label">${label}</span>`;

                if (/IDOR|RBAC|ditolak|diblokir/i.test(label)) {
                    card.classList.add('stat-card-danger');
                } else if (/gagal|Rate limit/i.test(label)) {
                    card.classList.add('stat-card-warning');
                } else {
                    card.classList.add('stat-card-info');
                }
            }
        }
    });

    document.querySelectorAll('a.btn, button.btn').forEach((btn) => {
        const label = btn.textContent.trim();
        if (/^Ekspor PDF$/i.test(label) && !btn.querySelector('svg')) {
            btn.innerHTML = `${ICON_PDF}<span>Ekspor PDF</span>`;
        } else if (/^Ekspor CSV$/i.test(label) && !btn.querySelector('svg')) {
            btn.classList.add('btn-outline');
            btn.innerHTML = `${ICON_CSV}<span>Ekspor CSV</span>`;
        }
    });

    // 6. KONFIRMASI SEBELUM HAPUS ITEM WATCHLIST / AKUN USER
    document.querySelectorAll('form.inline-form, form.form-delete-confirm').forEach((form) => {
        const methodInput = form.querySelector('input[name="_method"][value="DELETE"]');
        if (methodInput) {
            form.addEventListener('submit', (e) => {
                if (!window.confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                    e.preventDefault();
                }
            });
        }
    });

    // 7. LONCENG NOTIFIKASI WEB DALAM BROWSER
    const root = document.getElementById('webNotifRoot');
    if (!root) return;

    document.addEventListener('click', (e) => {
        if (root.hasAttribute('open') && !root.contains(e.target)) {
            root.removeAttribute('open');
        }
    });

    let notifData = [];
    try {
        notifData = JSON.parse(root.getAttribute('data-notif') || '[]');
    } catch (e) {
        notifData = [];
    }

    function playNotifSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(1320, ctx.currentTime + 0.15);
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.4);
        } catch (e) {}
    }

    function showToastBanner(judul, pesan) {
        const toast = document.createElement('div');
        toast.className = 'toast-notif';
        toast.innerHTML = `<div class="toast-notif-title">${judul}</div><div>${pesan}</div>`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 6000);
    }

    function triggerBrowserNotification() {
        playNotifSound();

        let judul = 'Notifikasi SembakoTrack';
        let isi   = 'Sistem notifikasi web aktif! Anda akan diberitahu saat harga sembako turun mencapai target.';

        if (notifData.length > 0) {
            const item = notifData[0];
            judul = 'Target Harga Sembako Tercapai';
            isi   = `${item.nama} turun ke Rp ${item.harga}/${item.satuan} (Target Anda: Rp ${item.target}).`;
        }

        showToastBanner(judul, isi);

        if ('Notification' in window) {
            if (Notification.permission === 'granted') {
                new Notification(judul, { body: isi });
            } else if (Notification.permission !== 'denied') {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        new Notification(judul, { body: isi });
                    }
                });
            }
        }
    }

    const btnTest = document.getElementById('btnTestWebNotif');
    if (btnTest) {
        btnTest.addEventListener('click', () => triggerBrowserNotification());
    }
});