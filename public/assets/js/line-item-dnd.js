/**
 * Line Item Drag-and-Drop Reordering & Auto-Indexing
 * Automatically enables drag-and-drop row reordering and re-indexes visible
 * serial numbers and form field names (e.g., items[0][...], designs[0][...]).
 */
(function (window, document) {
    'use strict';

    const INDEX_SPAN_CLASSES = ['index-column', 'row-number', 'row-sno'];

    function isDraggableRow(tr) {
        if (!tr || tr.tagName !== 'TR') return false;
        if (tr.id === 'emptyRow' || tr.classList.contains('no-dnd')) return false;
        const firstTd = tr.querySelector('td:first-child');
        if (!firstTd || firstTd.hasAttribute('colspan')) return false;
        return true;
    }

    function enhanceRow(tr) {
        if (!isDraggableRow(tr)) return;

        const firstTd = tr.querySelector('td:first-child');
        if (!firstTd) return;

        firstTd.classList.add('dnd-drag-cell');
        if (!firstTd.getAttribute('title')) {
            firstTd.setAttribute('title', 'Drag to reorder');
        }

        if (firstTd.querySelector('.dnd-drag-handle')) return;

        const handle = document.createElement('i');
        handle.className = 'fas fa-grip-vertical dnd-drag-handle me-1';
        handle.setAttribute('aria-hidden', 'true');

        // Customer Orders has .design-no-badge + hidden .design-no-val inside first <td>
        const designBadge = firstTd.querySelector('.design-no-badge');
        if (designBadge) {
            firstTd.insertBefore(handle, designBadge);
            return;
        }

        // Standard forms put .index-column / .row-number / .row-sno on the <td> itself.
        // Move that class onto an inner <span> so existing JS updating .textContent won't wipe the grip icon.
        let matchedClass = null;
        for (const cls of INDEX_SPAN_CLASSES) {
            if (firstTd.classList.contains(cls)) {
                matchedClass = cls;
                firstTd.classList.remove(cls);
                break;
            }
        }

        const existingText = firstTd.textContent.trim();
        firstTd.textContent = '';

        const span = document.createElement('span');
        span.className = 'row-index-text' + (matchedClass ? ' ' + matchedClass : '');
        span.textContent = existingText;

        firstTd.appendChild(handle);
        firstTd.appendChild(span);
    }

    function reindexLineItemRows(tbody) {
        if (!tbody) return;
        const rows = Array.from(tbody.children).filter(isDraggableRow);

        rows.forEach((tr, idx) => {
            enhanceRow(tr);

            const num = idx + 1;
            const indexSpan = tr.querySelector('.row-index-text, .design-no-badge');
            if (indexSpan) {
                indexSpan.textContent = String(num);
            }

            const designVal = tr.querySelector('.design-no-val');
            if (designVal) {
                designVal.value = String(num);
            }

            if (tr.hasAttribute('data-index')) {
                tr.setAttribute('data-index', String(idx));
            }

            const fields = tr.querySelectorAll('input[name], select[name], textarea[name]');
            fields.forEach((field) => {
                const name = field.getAttribute('name');
                if (!name) return;
                const updated = name.replace(/^([a-zA-Z0-9_]+)\[\d+\](.*)$/, '$1[' + idx + ']$2');
                if (updated !== name) {
                    field.setAttribute('name', updated);
                }
            });
        });
    }

    function initLineItemDnd(tbodyOrSelector) {
        const tbody = typeof tbodyOrSelector === 'string'
            ? document.querySelector(tbodyOrSelector)
            : tbodyOrSelector;

        if (!tbody || tbody.dataset.dndInit === '1') return;
        tbody.dataset.dndInit = '1';

        let draggedRow = null;
        let isReordering = false;

        reindexLineItemRows(tbody);

        // Enable draggable only when grabbing the drag cell/handle so text selection inside inputs works normally
        tbody.addEventListener('mousedown', function (e) {
            const cell = e.target.closest('.dnd-drag-cell, .dnd-drag-handle');
            if (!cell) return;
            const tr = cell.closest('tr');
            if (tr && tr.parentNode === tbody && isDraggableRow(tr)) {
                tr.setAttribute('draggable', 'true');
            }
        });

        document.addEventListener('mouseup', function () {
            if (draggedRow) return;
            tbody.querySelectorAll('tr[draggable="true"]').forEach((tr) => {
                tr.removeAttribute('draggable');
            });
        });

        tbody.addEventListener('dragstart', function (e) {
            const tr = e.target.closest('tr');
            if (!tr || tr.parentNode !== tbody || !isDraggableRow(tr) || tr.getAttribute('draggable') !== 'true') {
                e.preventDefault();
                return;
            }

            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                try {
                    window.jQuery(tbody).find('select.select2-hidden-accessible').select2('close');
                } catch (_) {}
            }

            draggedRow = tr;
            if (e.dataTransfer) {
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', '');
            }
            window.requestAnimationFrame(function () {
                if (draggedRow) draggedRow.classList.add('dnd-dragging');
            });
        });

        tbody.addEventListener('dragover', function (e) {
            if (!draggedRow || draggedRow.parentNode !== tbody) return;
            e.preventDefault();
            if (e.dataTransfer) {
                e.dataTransfer.dropEffect = 'move';
            }

            const targetRow = e.target.closest('tr');
            if (!targetRow || targetRow === draggedRow || targetRow.parentNode !== tbody || !isDraggableRow(targetRow)) {
                return;
            }

            const rect = targetRow.getBoundingClientRect();
            const isAfter = (e.clientY - rect.top) > (rect.height / 2);

            isReordering = true;
            try {
                if (isAfter) {
                    if (targetRow.nextSibling !== draggedRow) {
                        tbody.insertBefore(draggedRow, targetRow.nextSibling);
                    }
                } else {
                    if (draggedRow.nextSibling !== targetRow) {
                        tbody.insertBefore(draggedRow, targetRow);
                    }
                }
            } finally {
                isReordering = false;
            }

            reindexLineItemRows(tbody);
        });

        tbody.addEventListener('drop', function (e) {
            if (draggedRow) {
                e.preventDefault();
            }
        });

        tbody.addEventListener('dragend', function () {
            if (draggedRow) {
                draggedRow.classList.remove('dnd-dragging');
                draggedRow.removeAttribute('draggable');
                draggedRow = null;
            }
            reindexLineItemRows(tbody);
            tbody.dispatchEvent(new CustomEvent('lineitems:reordered', { bubbles: true }));
        });

        // Automatically enhance and re-index whenever rows are added or removed
        const observer = new MutationObserver(function () {
            if (isReordering) return;
            reindexLineItemRows(tbody);
        });
        observer.observe(tbody, { childList: true });
    }

    window.initLineItemDnd = initLineItemDnd;
    window.reindexLineItemRows = reindexLineItemRows;

    document.addEventListener('DOMContentLoaded', function () {
        ['#itemsBody', '#designsBody', '#scannedItems'].forEach(function (selector) {
            document.querySelectorAll(selector).forEach(function (tbody) {
                initLineItemDnd(tbody);
            });
        });
    });
})(window, document);
