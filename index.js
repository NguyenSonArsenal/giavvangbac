$(function () {

    /* ── Mock data ─────────────────────────────────────── */
    const VND = 25450; // tỷ giá tham khảo

    const brandDataVang = [
        {
            id: 'sjc', name: 'SJC', site: 'sjc.com.vn',
            color: '#f5c518', bg: 'linear-gradient(135deg,#b8860b,#8b6914)', icon: '🏅',
            items: [
                { type: 'Vàng miếng SJC 1L, 10L, 1KG', unit: 'nghìn/chỉ', buy: 89500, sell: 91800, change: +200 },
                { type: 'Vàng nhẫn SJC 99.99', unit: 'nghìn/chỉ', buy: 87600, sell: 89400, change: +150 },
                { type: 'Vàng trang sức 18K', unit: 'nghìn/chỉ', buy: 64200, sell: 68500, change: +100 },
            ]
        },
        {
            id: 'doji', name: 'DOJI', site: 'doji.vn',
            color: '#a855f7', bg: 'linear-gradient(135deg,#6b21a8,#4c1d95)', icon: '💎',
            items: [
                { type: 'Vàng miếng SJC', unit: 'nghìn/chỉ', buy: 89450, sell: 91750, change: +180 },
                { type: 'Vàng nhẫn 24K DOJI', unit: 'nghìn/chỉ', buy: 87800, sell: 89600, change: +200 },
                { type: 'Vàng trang sức 18K', unit: 'nghìn/chỉ', buy: 63800, sell: 68200, change: +80  },
            ]
        },
        {
            id: 'pnj', name: 'PNJ', site: 'pnj.com.vn',
            color: '#ec4899', bg: 'linear-gradient(135deg,#9d174d,#6d1436)', icon: '💍',
            items: [
                { type: 'Vàng miếng SJC', unit: 'nghìn/chỉ', buy: 89400, sell: 91700, change: +160 },
                { type: 'Vàng nhẫn PNJ TPCM', unit: 'nghìn/chỉ', buy: 87200, sell: 89000, change: +120 },
                { type: 'Vàng 18K PNJ', unit: 'nghìn/chỉ', buy: 63500, sell: 67800, change: +60  },
            ]
        },
        {
            id: 'btmc', name: 'Bảo Tín Minh Châu', site: 'baotinminhchau.com',
            color: '#22d3ee', bg: 'linear-gradient(135deg,#0e7490,#064e63)', icon: '⭐',
            items: [
                { type: 'Vàng 9999 BTMC', unit: 'nghìn/chỉ', buy: 87500, sell: 89300, change: +130 },
                { type: 'Vàng trang sức 18K', unit: 'nghìn/chỉ', buy: 63000, sell: 67600, change: +70  },
                { type: 'Vàng miếng SJC 1 chỉ', unit: 'nghìn/chỉ', buy: 89300, sell: 91600, change: +100 },
            ]
        },
        {
            id: 'mihong', name: 'Mi Hồng', site: 'mihong.vn',
            color: '#f97316', bg: 'linear-gradient(135deg,#c2410c,#92400e)', icon: '🔶',
            items: [
                { type: 'Vàng nhẫn 24K', unit: 'nghìn/chỉ', buy: 87100, sell: 89200, change: -50  },
                { type: 'Vàng trang sức 18K', unit: 'nghìn/chỉ', buy: 62800, sell: 67300, change: 0   },
                { type: 'Vàng miếng SJC', unit: 'nghìn/chỉ', buy: 89200, sell: 91500, change: +120 },
            ]
        },
        {
            id: 'baotin', name: 'Bảo Tín Mạnh Hải', site: 'baotin.com',
            color: '#10b981', bg: 'linear-gradient(135deg,#065f46,#064e3b)', icon: '🟢',
            items: [
                { type: 'Vàng 9999', unit: 'nghìn/chỉ', buy: 87300, sell: 89100, change: +100 },
                { type: 'Vàng trang sức 18K', unit: 'nghìn/chỉ', buy: 62500, sell: 67000, change: +50  },
                { type: 'Vàng miếng SJC', unit: 'nghìn/chỉ', buy: 89100, sell: 91400, change: +80  },
            ]
        },
    ];

    const brandDataBac = [
        {
            id: 'sjc-bac', name: 'SJC', site: 'sjc.com.vn',
            color: '#b0bec5', bg: 'linear-gradient(135deg,#546e7a,#37474f)', icon: '⚪',
            items: [
                { type: 'Bạc miếng SJC', unit: 'nghìn/chỉ', buy: 950, sell: 1050, change: +10 },
                { type: 'Bạc nguyên liệu', unit: 'nghìn/chỉ', buy: 880, sell: 980, change: +5  },
            ]
        },
        {
            id: 'doji-bac', name: 'DOJI', site: 'doji.vn',
            color: '#a855f7', bg: 'linear-gradient(135deg,#6b21a8,#4c1d95)', icon: '⚪',
            items: [
                { type: 'Bạc 99.9', unit: 'nghìn/chỉ', buy: 945, sell: 1045, change: +8  },
                { type: 'Bạc 925 trang sức', unit: 'nghìn/chỉ', buy: 820, sell: 920, change: +5  },
            ]
        },
        {
            id: 'pnj-bac', name: 'PNJ', site: 'pnj.com.vn',
            color: '#ec4899', bg: 'linear-gradient(135deg,#9d174d,#6d1436)', icon: '⚪',
            items: [
                { type: 'Bạc nguyên liệu 99.9', unit: 'nghìn/chỉ', buy: 940, sell: 1040, change: +6  },
                { type: 'Bạc trang sức 925', unit: 'nghìn/chỉ', buy: 810, sell: 910, change: 0   },
            ]
        },
    ];

    const worldData = [
        { metal: '🥇 Vàng (XAU/USD)',   usd: 2940.5, chg: -4.80, pct: -0.16 },
        { metal: '⚪ Bạc (XAG/USD)',    usd: 33.48,  chg: +0.24, pct: +0.72 },
        { metal: '⬜ Bạch Kim (XPT/USD)', usd: 1010.2, chg: +3.10, pct: +0.31 },
        { metal: '🟤 Paladi (XPD/USD)', usd: 954.0,  chg: -8.20, pct: -0.85 },
    ];

    /* ── Helpers ────────────────────────────────────────── */
    const fmt = n => n.toLocaleString('vi-VN');
    const fmtUsd = n => '$' + n.toLocaleString('en-US', {minimumFractionDigits:2,maximumFractionDigits:2});
    const fmtVND = usd => fmt(Math.round(usd * VND / 1000) * 1000);

    function changeHtml(val) {
        if (val > 0) return `<span class="change ch-up">▲ +${fmt(val)}</span>`;
        if (val < 0) return `<span class="change ch-dn">▼ ${fmt(val)}</span>`;
        return `<span class="change ch-nc">– 0</span>`;
    }

    /* ── Render brand cards ─────────────────────────────── */
    function renderBrandGrid(brands, containerId, isSilver) {
        const $grid = $('#' + containerId).empty();
        brands.forEach((b, bi) => {
            const rows = b.items.map(it => `
        <tr class="${isSilver ? 'silver' : ''}">
          <td><div class="type-name">${it.type}</div><div class="type-unit">${it.unit}</div></td>
          <td><span class="buy-price">${fmt(it.buy)}</span></td>
          <td><span class="sell-price">${fmt(it.sell)}</span></td>
          <td>${changeHtml(it.change)}</td>
        </tr>
      `).join('');

            const card = $(`
        <div class="brand-card">
          <div class="brand-head">
            <div class="brand-logo" style="background:${b.bg}">${b.icon}</div>
            <div class="brand-info">
              <h3>${b.name}</h3>
              <div class="site">🔗 ${b.site}</div>
            </div>
            <div class="brand-updated">${getUpdatedStr()}</div>
          </div>
          <table class="price-table">
            <thead>
              <tr>
                <th>Loại</th>
                <th>Mua vào</th>
                <th>Bán ra</th>
                <th>+/–</th>
              </tr>
            </thead>
            <tbody>${rows}</tbody>
          </table>
        </div>
      `);
            $grid.append(card);
        });
    }

    /* ── World table ────────────────────────────────────── */
    function renderWorld() {
        const $tb = $('#world-tbody').empty();
        worldData.forEach(w => {
            const sign = w.chg >= 0 ? '+' : '';
            const cls  = w.chg >= 0 ? 'ch-up' : 'ch-dn';
            $tb.append(`<tr>
        <td style="font-weight:600">${w.metal}</td>
        <td style="font-weight:700;color:var(--gold2)">${fmtUsd(w.usd)}</td>
        <td style="color:var(--muted)">${fmtVND(w.usd)} ₫</td>
        <td class="${cls}" style="font-weight:600">${sign}${fmtUsd(w.chg)}</td>
        <td class="${cls}" style="font-weight:600">${sign}${w.pct}%</td>
      </tr>`);
        });
    }

    /* ── Ticker ─────────────────────────────────────────── */
    function buildTicker() {
        const items = [
            ...brandDataVang.map(b => ({ name: b.name + ' (SJC)', price: b.items[0].sell, chg: b.items[0].change })),
            ...brandDataBac.map(b => ({ name: b.name + ' Bạc', price: b.items[0].sell, chg: b.items[0].change })),
        ];
        const html = items.map(it => {
            const chgHtml = it.chg > 0 ? `<span class="ticker-change ch-up">▲+${fmt(it.chg)}</span>`
                : it.chg < 0 ? `<span class="ticker-change ch-dn">▼${fmt(it.chg)}</span>`
                    : `<span class="ticker-change ch-nc">–</span>`;
            return `<span class="ticker-item"><span class="ticker-name">${it.name}</span><span class="ticker-price">${fmt(it.price)}</span>${chgHtml}</span>`;
        }).join('');
        // Duplicate for seamless loop
        $('#ticker-inner').html(html + html);
    }

    /* ── Clock & timestamp ──────────────────────────────── */
    function getUpdatedStr() {
        const now = new Date();
        return now.getHours().toString().padStart(2,'0') + ':' +
            now.getMinutes().toString().padStart(2,'0');
    }

    function updateClock() {
        const now = new Date();
        const h = now.getHours().toString().padStart(2,'0');
        const m = now.getMinutes().toString().padStart(2,'0');
        const s = now.getSeconds().toString().padStart(2,'0');
        $('#live-clock').text(h + ':' + m + ':' + s);
    }
    setInterval(updateClock, 1000);
    updateClock();

    function updateLastUpdated() {
        const now = new Date();
        const str = now.toLocaleString('vi-VN', {weekday:'long', year:'numeric', month:'long', day:'numeric', hour:'2-digit', minute:'2-digit'});
        $('#last-updated').text('Cập nhật lúc: ' + str);
    }

    /* ── Toast ──────────────────────────────────────────── */
    function showToast(msg) {
        $('#toast-msg').text(msg);
        $('#toast').addClass('show');
        setTimeout(() => $('#toast').removeClass('show'), 3000);
    }

    /* ── Refresh button ─────────────────────────────────── */
    $('#btn-refresh').on('click', function () {
        const $btn = $(this);
        $btn.prop('disabled', true).css('opacity', 0.6);
        $btn.find('svg').css('animation', 'spin 0.6s linear infinite');

        // Simulate fetch delay
        setTimeout(() => {
            renderBrandGrid(brandDataVang, 'brand-grid-vang', false);
            renderBrandGrid(brandDataBac, 'brand-grid-bac', true);
            renderWorld();
            updateLastUpdated();
            $btn.prop('disabled', false).css('opacity', '');
            $btn.find('svg').css('animation', '');
            showToast('Đã cập nhật dữ liệu mới nhất!');
        }, 1200);
    });

    /* ── Tabs ───────────────────────────────────────────── */
    $('.tab').on('click', function () {
        const tab = $(this).data('tab');
        $('.tab').removeClass('active');
        $(this).addClass('active');
        $('#section-vang, #section-bac, #section-thegioi').hide();
        $('#section-' + tab).fadeIn(250);
    });

    /* ── Add spin keyframe dynamically ─────────────────── */
    $('<style>@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}</style>').appendTo('head');

    /* ── Initial render ─────────────────────────────────── */
    renderBrandGrid(brandDataVang, 'brand-grid-vang', false);
    renderBrandGrid(brandDataBac, 'brand-grid-bac', true);
    renderWorld();
    buildTicker();
    updateLastUpdated();

    /* ── Auto-refresh every 5 min ───────────────────────── */
    setInterval(function () {
        renderBrandGrid(brandDataVang, 'brand-grid-vang', false);
        updateLastUpdated();
        showToast('Dữ liệu được cập nhật tự động.');
    }, 5 * 60 * 1000);
});