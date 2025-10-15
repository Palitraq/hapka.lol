document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('daily-listing');
    const titleDate = document.getElementById('daily-date');
    const titleCount = document.getElementById('daily-count');
    const content = document.getElementById('daily-content');

    function formatBytes(bytes) {
        if (bytes >= 1024*1024*1024) return (bytes/1024/1024/1024).toFixed(1) + ' GB';
        if (bytes >= 1024*1024) return (bytes/1024/1024).toFixed(1) + ' MB';
        if (bytes >= 1024) return (bytes/1024).toFixed(1) + ' KB';
        return bytes + ' B';
    }

    async function fetchByDate(dateStr) {
        const res = await fetch(`./admin.php?list_by_date=${encodeURIComponent(dateStr)}`, { credentials: 'same-origin' });
        if (!res.ok) throw new Error('Failed to load list');
        return res.json();
    }

    async function deleteByCode(code) {
        const form = new FormData();
        form.append('delete_code', code);
        // Get CSRF token from the page
        const csrfInput = document.querySelector('input[name="csrf_token"]');
        if (csrfInput) {
            form.append('csrf_token', csrfInput.value);
        }
        const res = await fetch('./admin.php', { method: 'POST', body: form, credentials: 'same-origin' });
        if (!res.ok) throw new Error('Delete failed');
        return res.json();
    }

    function renderList(dateStr, items) {
        titleDate.textContent = dateStr;
        titleCount.textContent = items.length;
        content.innerHTML = '';
        if (!items.length) {
            content.innerHTML = '<div style="color:#b9bbbe">No uploads on this day.</div>';
            container.style.display = 'block';
            return;
        }
        items.forEach(item => {
            const row = document.createElement('div');
            row.style.display = 'grid';
            row.style.gridTemplateColumns = '1fr auto auto auto';
            row.style.gap = '10px';
            row.style.alignItems = 'center';
            row.style.padding = '10px';
            row.style.border = '1px solid rgba(255,255,255,0.08)';
            row.style.borderRadius = '8px';
            row.style.background = 'rgba(30,30,50,0.6)';

            const name = document.createElement('div');
            name.innerHTML = `<a href="/${encodeURIComponent(item.code)}" target="_blank" style="color:#8ab4f8; text-decoration:underline;">${escapeHtml(item.orig || item.saved)}</a>`;

            const code = document.createElement('div');
            code.style.color = '#bbb';
            code.textContent = item.code;

            const size = document.createElement('div');
            size.style.color = '#bbb';
            size.textContent = formatBytes(item.size || 0);

            const del = document.createElement('button');
            del.textContent = '🗑 Delete';
            del.style.padding = '6px 10px';
            del.style.borderRadius = '8px';
            del.style.border = 'none';
            del.style.cursor = 'pointer';
            del.style.background = 'linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%)';
            del.style.color = '#fff';
            del.addEventListener('click', async () => {
                if (!confirm(`Delete ${item.code}?`)) return;
                del.disabled = true;
                try {
                    const r = await deleteByCode(item.code);
                    if (r.status === 'ok') {
                        row.remove();
                        titleCount.textContent = parseInt(titleCount.textContent, 10) - 1;
                    } else {
                        alert('Delete error');
                        del.disabled = false;
                    }
                } catch (e) {
                    alert('Delete failed');
                    del.disabled = false;
                }
            });

            row.appendChild(name);
            row.appendChild(code);
            row.appendChild(size);
            row.appendChild(del);
            content.appendChild(row);
        });
        container.style.display = 'block';
    }

    function escapeHtml(str) {
        return (str || '').replace(/[&<>"]/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[s]));
    }

    document.querySelectorAll('.activity-cell').forEach(cell => {
        const dateStr = cell.getAttribute('data-date');
        const count = parseInt(cell.getAttribute('data-count') || '0', 10);
        if (!dateStr) return;
        if (count <= 0) return;
        cell.addEventListener('click', async () => {
            try {
                const data = await fetchByDate(dateStr);
                if (data.status === 'ok') {
                    renderList(data.date, data.items || []);
                } else {
                    alert('Failed to load');
                }
            } catch (e) {
                alert('Failed to load');
            }
        });
    });
});


