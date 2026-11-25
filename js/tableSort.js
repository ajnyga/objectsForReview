/**
 * @file plugins/generic/objectsForReview/js/tableSort.js
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Sort the public objects for review table by clicking a column header
 */
(function () {
	function sortTable(table, th, column) {
		var direction = th.getAttribute('aria-sort') === 'ascending' ? 'descending' : 'ascending';
		var headerRow = th.parentNode;
		var rows = Array.prototype.filter.call(table.rows, function (row) {
			return row !== headerRow;
		});
		var collator = new Intl.Collator(document.documentElement.lang || undefined, {numeric: true, sensitivity: 'base'});
		var value = function (row) {
			var cell = row.cells[column];
			return cell ? (cell.getAttribute('data-sort') || cell.textContent).trim() : '';
		};

		rows.sort(function (a, b) {
			var result = collator.compare(value(a), value(b));
			return direction === 'ascending' ? result : -result;
		});
		rows.forEach(function (row) {
			row.parentNode.appendChild(row);
		});

		Array.prototype.forEach.call(headerRow.cells, function (cell) {
			cell.removeAttribute('aria-sort');
		});
		th.setAttribute('aria-sort', direction);
	}

	document.addEventListener('DOMContentLoaded', function () {
		var table = document.getElementById('objects-for-review-table');
		if (!table || !table.rows.length) {
			return;
		}
		Array.prototype.forEach.call(table.rows[0].cells, function (th, column) {
			if (th.classList.contains('no-sort')) {
				return;
			}
			th.setAttribute('role', 'columnheader');
			th.setAttribute('tabindex', '0');
			th.addEventListener('click', function () {
				sortTable(table, th, column);
			});
			th.addEventListener('keydown', function (event) {
				if (event.key === 'Enter' || event.key === ' ') {
					event.preventDefault();
					sortTable(table, th, column);
				}
			});
		});
	});
})();
