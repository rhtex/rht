/**
 * List UI Enhancements
 * --------------------------------------------------------------
 * Automatically upgrades list tables on index pages with:
 *   - Instant search (with match highlighting) + "/" shortcut
 *   - Click-to-sort columns (text, numbers, ₹ amounts, dates)
 *   - Client-side pagination with rows-per-page selector
 *   - CSV export of the currently filtered rows
 *   - Sticky header, friendly empty state, action-button tooltips
 *   - Clickable rows (opens the row's "view" link)
 *   - Mobile friendly stacked "card" rows
 *
 * Opt-out: add data-no-enhance to a <table> (or any ancestor).
 * Columns can opt out of sorting with class "no-sort".
 */
(function () {
    'use strict';

    var SKIP_PATH = /\/(create|edit|view|print|payment|convert|change_password|calculate)(\/|$)/i;
    var MIN_ROWS_FOR_TOOLBAR = 5;
    var DEFAULT_PAGE_SIZE = 25;
    var uid = 0;

    function txt(el) { return (el.textContent || '').replace(/\s+/g, ' ').trim(); }

    function isListTable(table) {
        if (table.closest('[data-no-enhance], .modal, .lu-done, form .gst-container')) return false;
        if (table.hasAttribute('data-no-enhance')) return false;
        if (table.classList.contains('dataTable') || table.classList.contains('table-borderless')) return false;
        if (window.jQuery && jQuery.fn.dataTable && jQuery.fn.dataTable.isDataTable(table)) return false;
        if (!table.tHead || !table.tBodies.length) return false;
        var headRow = table.tHead.rows[table.tHead.rows.length - 1];
        if (!headRow || headRow.cells.length < 3) return false;
        var body = table.tBodies[0];
        // Skip data-entry tables and complex layouts
        if (body.querySelector('input[type=text], input[type=number], input[type=date], input[type=time], input[type=checkbox], select, textarea')) return false;
        if (body.querySelector('[rowspan]')) return false;
        return true;
    }

    function headerCells(table) {
        return Array.prototype.slice.call(table.tHead.rows[table.tHead.rows.length - 1].cells);
    }

    function isEmptyRow(tr) {
        return tr.cells.length === 1 && tr.cells[0].colSpan > 1;
    }

    function dataRows(body) {
        return Array.prototype.filter.call(body.rows, function (r) {
            return !r.classList.contains('lu-nomatch') && !isEmptyRow(r);
        });
    }

    /* ---------- value parsing for sorting ---------- */
    var MONTHS = { jan: 0, feb: 1, mar: 2, apr: 3, may: 4, jun: 5, jul: 6, aug: 7, sep: 8, oct: 9, nov: 10, dec: 11 };

    function parseDate(s) {
        var m;
        // dd/mm/yyyy or dd-mm-yyyy
        if ((m = s.match(/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{4})/))) return new Date(+m[3], +m[2] - 1, +m[1]).getTime();
        // yyyy-mm-dd
        if ((m = s.match(/^(\d{4})-(\d{1,2})-(\d{1,2})/))) return new Date(+m[1], +m[2] - 1, +m[3]).getTime();
        // dd-Mon-yyyy / dd Mon yyyy
        if ((m = s.match(/^(\d{1,2})[\s\-]([A-Za-z]{3})[a-z]*[\s\-,]+(\d{4})/)) && MONTHS[m[2].toLowerCase()] !== undefined) {
            return new Date(+m[3], MONTHS[m[2].toLowerCase()], +m[1]).getTime();
        }
        return null;
    }

    function parseNumber(s) {
        var clean = s.replace(/[₹$,\s]|Rs\.?|INR/gi, '').replace(/(Dr|Cr|kg|kgs|mtrs?|m|%)$/i, '');
        if (/^-?\d+(\.\d+)?$/.test(clean)) {
            var n = parseFloat(clean);
            if (/Cr$/i.test(s.trim())) n = -n;
            return n;
        }
        return null;
    }

    function sortKey(cell) {
        var s = txt(cell);
        if (cell.hasAttribute('data-sort')) s = cell.getAttribute('data-sort');
        var d = parseDate(s);
        if (d !== null) return { t: 1, v: d };
        var n = parseNumber(s);
        if (n !== null) return { t: 1, v: n };
        return { t: 2, v: s.toLowerCase() };
    }

    /* ---------- highlighting ---------- */
    function clearHighlight(root) {
        var marks = root.querySelectorAll('mark.lu-hit');
        Array.prototype.forEach.call(marks, function (m) {
            var p = m.parentNode;
            p.replaceChild(document.createTextNode(m.textContent), m);
            p.normalize();
        });
    }

    function highlight(root, q) {
        if (!q || q.length < 2) return;
        var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
            acceptNode: function (n) {
                if (!n.nodeValue.trim()) return NodeFilter.FILTER_REJECT;
                var p = n.parentNode;
                if (p.closest('.btn, script, style, mark')) return NodeFilter.FILTER_REJECT;
                return n.nodeValue.toLowerCase().indexOf(q) !== -1 ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
            }
        });
        var nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);
        nodes.forEach(function (node) {
            var val = node.nodeValue, lower = val.toLowerCase(), idx = lower.indexOf(q);
            if (idx === -1) return;
            var frag = document.createDocumentFragment();
            var last = 0;
            while (idx !== -1) {
                frag.appendChild(document.createTextNode(val.slice(last, idx)));
                var mk = document.createElement('mark');
                mk.className = 'lu-hit';
                mk.textContent = val.slice(idx, idx + q.length);
                frag.appendChild(mk);
                last = idx + q.length;
                idx = lower.indexOf(q, last);
            }
            frag.appendChild(document.createTextNode(val.slice(last)));
            node.parentNode.replaceChild(frag, node);
        });
    }

    /* ---------- CSV export ---------- */
    function exportCsv(table, rows, skipCols) {
        var heads = headerCells(table);
        var out = [];
        var line = [];
        heads.forEach(function (h, i) { if (skipCols.indexOf(i) === -1) line.push(txt(h)); });
        out.push(line);
        rows.forEach(function (r) {
            var l = [];
            Array.prototype.forEach.call(r.cells, function (c, i) { if (skipCols.indexOf(i) === -1) l.push(txt(c)); });
            out.push(l);
        });
        var csv = out.map(function (l) {
            return l.map(function (v) { return '"' + String(v).replace(/"/g, '""') + '"'; }).join(',');
        }).join('\r\n');
        var blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
        var a = document.createElement('a');
        var title = (document.querySelector('.app-content-header h1') || {}).textContent || document.title || 'export';
        a.href = URL.createObjectURL(blob);
        a.download = title.trim().replace(/[^\w\-]+/g, '_').replace(/^_+|_+$/g, '').toLowerCase() + '_' + new Date().toISOString().slice(0, 10) + '.csv';
        document.body.appendChild(a);
        a.click();
        setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 200);
    }

    /* ---------- main enhancer ---------- */
    function enhance(table) {
        uid++;
        table.classList.add('lu-table', 'lu-stack', 'lu-done');
        var body = table.tBodies[0];
        var heads = headerCells(table);
        var colCount = heads.length;

        // Columns that are "actions" (no sort / no export)
        var actionCols = [];
        heads.forEach(function (h, i) {
            var t = txt(h).toLowerCase();
            if (/^(actions?|options?|manage|operations?)$/.test(t) || h.classList.contains('no-sort') || t === '') actionCols.push(i);
        });

        // Mobile labels
        function labelRow(tr) {
            if (isEmptyRow(tr)) { tr.classList.add('lu-empty'); return; }
            Array.prototype.forEach.call(tr.cells, function (td, i) {
                if (!td.hasAttribute('data-label') && heads[i]) td.setAttribute('data-label', txt(heads[i]));
            });
            // Clickable row -> first "view" link
            if (!tr.classList.contains('lu-clickable')) {
                var link = tr.querySelector('a[href*="/view/"], a[href*="/view?"], a[href*="/ledger/"], a[href*="/statement/"]');
                if (link) {
                    tr.classList.add('lu-clickable');
                    tr.addEventListener('click', function (e) {
                        if (e.target.closest('a, button, input, select, textarea, label, form, .dropdown, [data-bs-toggle]')) return;
                        if (window.getSelection && String(window.getSelection()).length) return;
                        if (e.ctrlKey || e.metaKey) { window.open(link.href, '_blank'); } else { window.location.href = link.href; }
                    });
                }
            }
            // Tooltips for icon-only buttons
            if (window.bootstrap && bootstrap.Tooltip) {
                Array.prototype.forEach.call(tr.querySelectorAll('.btn[title], a[title]'), function (b) {
                    if (!b.getAttribute('data-bs-toggle') && !bootstrap.Tooltip.getInstance(b)) {
                        try { new bootstrap.Tooltip(b, { trigger: 'hover', container: 'body' }); } catch (e) {}
                    }
                });
            }
        }
        Array.prototype.forEach.call(body.rows, labelRow);

        // Make sure there is a scroll wrapper
        var wrap = table.parentElement;
        if (!wrap.classList.contains('table-responsive')) {
            var w = document.createElement('div');
            w.className = 'table-responsive';
            table.parentNode.insertBefore(w, table);
            w.appendChild(table);
            wrap = w;
        }
        wrap.classList.add('lu-wrap');

        var allRows = dataRows(body);
        var useToolbar = allRows.length >= MIN_ROWS_FOR_TOOLBAR || !!document.getElementById('btnLoadMore');

        var state = { q: '', sortCol: -1, sortDir: 1, page: 1, size: allRows.length > DEFAULT_PAGE_SIZE ? DEFAULT_PAGE_SIZE : 0 };
        var countEl, pagerEl, searchWrap, searchInput, sizeSel;

        // "No match" row
        var noMatch = document.createElement('tr');
        noMatch.className = 'lu-nomatch';
        noMatch.style.display = 'none';
        noMatch.innerHTML = '<td colspan="' + colCount + '"><i class="fas fa-search me-2"></i>No records match your search.</td>';
        body.appendChild(noMatch);

        function render() {
            var rows = dataRows(body);
            clearHighlight(body);
            var q = state.q;
            var matched = rows.filter(function (r) {
                return !q || txt(r).toLowerCase().indexOf(q) !== -1;
            });

            var size = state.size || matched.length || 1;
            var pages = Math.max(1, Math.ceil(matched.length / size));
            if (state.page > pages) state.page = pages;
            var start = (state.page - 1) * size, end = start + size;

            rows.forEach(function (r) { r.style.display = 'none'; });
            matched.forEach(function (r, i) {
                if (i >= start && i < end) {
                    r.style.display = '';
                    if (q) highlight(r, q);
                }
            });
            noMatch.style.display = (rows.length && !matched.length) ? '' : 'none';
            body.appendChild(noMatch);

            if (countEl) {
                countEl.innerHTML = matched.length === rows.length
                    ? '<b>' + rows.length + '</b> record' + (rows.length === 1 ? '' : 's')
                    : '<b>' + matched.length + '</b> of ' + rows.length + ' records';
            }
            if (pagerEl) renderPager(pages, matched.length, start, Math.min(end, matched.length));
            table._luMatched = matched;
        }

        function renderPager(pages, total, from, to) {
            if (!state.size || pages <= 1) { pagerEl.style.display = 'none'; return; }
            pagerEl.style.display = '';
            var p = state.page, html = '';
            function item(label, page, disabled, active) {
                return '<li class="page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '') + '">' +
                    '<a class="page-link" href="#" data-page="' + page + '">' + label + '</a></li>';
            }
            html += item('<i class="fas fa-angle-double-left"></i>', 1, p === 1);
            html += item('<i class="fas fa-angle-left"></i>', p - 1, p === 1);
            var s = Math.max(1, p - 2), e = Math.min(pages, s + 4);
            s = Math.max(1, e - 4);
            for (var i = s; i <= e; i++) html += item(i, i, false, i === p);
            html += item('<i class="fas fa-angle-right"></i>', p + 1, p === pages);
            html += item('<i class="fas fa-angle-double-right"></i>', pages, p === pages);
            pagerEl.innerHTML = '<div class="lu-count">Showing <b>' + (from + 1) + '–' + to + '</b> of ' + total + '</div>' +
                '<ul class="pagination pagination-sm">' + html + '</ul>';
        }

        // ---- Sorting ----
        heads.forEach(function (th, i) {
            if (actionCols.indexOf(i) !== -1) return;
            if (th.querySelector('a[href]')) return; // server-side sort links already present
            if (th.colSpan > 1) return;
            th.classList.add('lu-sortable');
            th.title = th.title || 'Click to sort';
            th.addEventListener('click', function () {
                state.sortDir = (state.sortCol === i) ? -state.sortDir : 1;
                state.sortCol = i;
                heads.forEach(function (h) { h.classList.remove('lu-asc', 'lu-desc'); });
                th.classList.add(state.sortDir === 1 ? 'lu-asc' : 'lu-desc');
                var rows = dataRows(body);
                rows.sort(function (a, b) {
                    var ca = a.cells[i], cb = b.cells[i];
                    if (!ca || !cb) return 0;
                    var ka = sortKey(ca), kb = sortKey(cb);
                    if (ka.t !== kb.t) return (ka.t - kb.t) * state.sortDir;
                    if (ka.v < kb.v) return -1 * state.sortDir;
                    if (ka.v > kb.v) return 1 * state.sortDir;
                    return 0;
                });
                rows.forEach(function (r) { body.appendChild(r); });
                state.page = 1;
                render();
            });
        });

        // ---- Toolbar ----
        if (useToolbar) {
            var tb = document.createElement('div');
            tb.className = 'lu-toolbar';
            var sid = 'luSearch' + uid;
            tb.innerHTML =
                '<div class="lu-left">' +
                    '<div class="lu-search">' +
                        '<i class="fas fa-search"></i>' +
                        '<input type="search" id="' + sid + '" class="form-control form-control-sm" placeholder="Quick search in this list..." autocomplete="off">' +
                        '<button type="button" class="lu-clear" title="Clear"><i class="fas fa-times"></i></button>' +
                    '</div>' +
                    '<span class="lu-kbd d-none d-md-inline" title="Press / to search">/</span>' +
                    '<span class="lu-count"></span>' +
                '</div>' +
                '<div class="lu-right">' +
                    '<select class="form-select form-select-sm lu-size" title="Rows per page">' +
                        '<option value="10">10 / page</option><option value="25">25 / page</option>' +
                        '<option value="50">50 / page</option><option value="100">100 / page</option>' +
                        '<option value="0">Show all</option>' +
                    '</select>' +
                    '<button type="button" class="btn btn-outline-success lu-csv" title="Download visible rows as CSV"><i class="fas fa-file-csv"></i> Export</button>' +
                    '<button type="button" class="btn btn-outline-secondary lu-print" title="Print list"><i class="fas fa-print"></i></button>' +
                '</div>';
            wrap.parentNode.insertBefore(tb, wrap);

            searchWrap = tb.querySelector('.lu-search');
            searchInput = tb.querySelector('input');
            countEl = tb.querySelector('.lu-count');
            sizeSel = tb.querySelector('.lu-size');
            sizeSel.value = String(state.size);

            var timer;
            searchInput.addEventListener('input', function () {
                clearTimeout(timer);
                var v = this.value;
                searchWrap.classList.toggle('has-value', !!v);
                timer = setTimeout(function () {
                    state.q = v.toLowerCase().trim();
                    state.page = 1;
                    render();
                }, 120);
            });
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') { this.value = ''; this.dispatchEvent(new Event('input')); }
            });
            tb.querySelector('.lu-clear').addEventListener('click', function () {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
                searchInput.focus();
            });
            sizeSel.addEventListener('change', function () {
                state.size = parseInt(this.value, 10) || 0;
                state.page = 1;
                render();
            });
            tb.querySelector('.lu-csv').addEventListener('click', function () {
                exportCsv(table, table._luMatched || dataRows(body), actionCols);
            });
            tb.querySelector('.lu-print').addEventListener('click', function () { window.print(); });

            pagerEl = document.createElement('div');
            pagerEl.className = 'lu-pager';
            wrap.parentNode.insertBefore(pagerEl, wrap.nextSibling);
            pagerEl.addEventListener('click', function (e) {
                var a = e.target.closest('a[data-page]');
                if (!a) return;
                e.preventDefault();
                if (a.parentNode.classList.contains('disabled')) return;
                state.page = parseInt(a.getAttribute('data-page'), 10);
                render();
                wrap.scrollTop = 0;
            });
        }

        // Handle rows appended later (e.g. "View More" / AJAX)
        if (window.MutationObserver) {
            var busy = false;
            new MutationObserver(function (muts) {
                if (busy) return;
                var added = false;
                muts.forEach(function (m) {
                    Array.prototype.forEach.call(m.addedNodes, function (n) {
                        if (n.nodeType === 1 && n.tagName === 'TR' && !n.classList.contains('lu-nomatch')) { labelRow(n); added = true; }
                    });
                });
                if (added) {
                    busy = true;
                    // When rows are appended via "load more", show them (switch to show all)
                    if (sizeSel && state.size) { state.size = 0; sizeSel.value = '0'; }
                    render();
                    setTimeout(function () { busy = false; }, 0);
                }
            }).observe(body, { childList: true });
        }

        render();
    }

    function init() {
        if (SKIP_PATH.test(window.location.pathname)) return;
        var tables = document.querySelectorAll('.app-content table.table');
        Array.prototype.forEach.call(tables, function (t) {
            try { if (isListTable(t)) enhance(t); } catch (e) { if (window.console) console.warn('list-ui:', e); }
        });

        // "/" focuses the first quick search
        document.addEventListener('keydown', function (e) {
            if (e.key !== '/' || e.ctrlKey || e.metaKey || e.altKey) return;
            var tag = (document.activeElement && document.activeElement.tagName) || '';
            if (/INPUT|TEXTAREA|SELECT/.test(tag) || document.activeElement.isContentEditable) return;
            var s = document.querySelector('.lu-search input');
            if (s) { e.preventDefault(); s.focus(); }
        });
    }

    // Run after page scripts (e.g. DataTables initialisation) have executed
    if (document.readyState === 'complete') {
        setTimeout(init, 0);
    } else {
        window.addEventListener('load', function () { setTimeout(init, 0); });
    }
})();

