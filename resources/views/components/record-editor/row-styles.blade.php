<style>
/* Shared table grid: odd rows white, even rows softly shaded. */
.ops-table { --row-white:#fff; --row-even:#f1f5f9; --cell-line:#dbe3ed; }
.ops-table tbody tr:nth-child(odd of :where(.ops-record-row)), .ops-table tbody tr:nth-child(odd of :where(.ops-record-row)) > td { background-color:var(--row-white)!important; }
.ops-table tbody tr:nth-child(even of :where(.ops-record-row)), .ops-table tbody tr:nth-child(even of :where(.ops-record-row)) > td { background-color:var(--row-even)!important; }
.ops-table th, .ops-table td { border-bottom:1px solid var(--cell-line)!important; }
@media(min-width:761px) {
 .ops-table th:not(:last-child), .ops-table td:not(:last-child) { border-right:1px solid var(--cell-line)!important; }
}
.dark .ops-table { --row-white:#0f172a; --row-even:#182335; --cell-line:#334155; }
.ops-table tbody tr.ops-record-row:hover > td { background-color: var(--ui-wash) !important; }
.ops-table tbody tr.ops-record-selected > td, .ops-table tbody tr.ops-record-selected:hover > td { background-color: var(--ui-wash) !important; }
.ops-table tbody tr.ops-record-selected > td:first-child { box-shadow: inset 3px 0 0 var(--ui-primary); }
.dark .ops-table tbody tr.ops-record-row:hover > td { background-color: #1e293b !important; }
.dark .ops-table tbody tr.ops-record-selected > td, .dark .ops-table tbody tr.ops-record-selected:hover > td { background-color: #1e3a5f !important; }
</style>
