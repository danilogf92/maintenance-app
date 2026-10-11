<style>
.ops-modal .ops-column-toolbar{display:flex;flex-wrap:wrap;gap:10px;padding:16px 0}
.ops-modal .ops-column-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:40px;padding:9px 14px;border:1px solid #cbd5e1;border-radius:9px;background:#fff;color:#334155;font-size:14px;font-weight:600;cursor:pointer;transition:background .15s,color .15s,border-color .15s,box-shadow .15s}
.ops-modal .ops-column-button:hover:not(:disabled){background:#2563eb;border-color:#2563eb;color:#fff;box-shadow:0 3px 8px #2563eb26}
.ops-modal .ops-column-actions{display:flex;gap:6px;flex-shrink:0}
.ops-modal .ops-column-actions button{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;padding:0;border:1px solid #bfdbfe;border-radius:8px;background:#eff6ff;color:#1d4ed8;cursor:pointer;transition:background .15s,color .15s,border-color .15s}
.ops-modal .ops-column-actions button:hover:not(:disabled){background:#2563eb;border-color:#2563eb;color:#fff}
.ops-modal .ops-column-button.ops-column-danger,.ops-modal .ops-column-actions .ops-column-danger{background:#fff1f2;border-color:#fecdd3;color:#e11d48}
.ops-modal .ops-column-button.ops-column-danger:hover:not(:disabled),.ops-modal .ops-column-actions .ops-column-danger:hover:not(:disabled){background:#dc2626;border-color:#dc2626;color:#fff}
.ops-modal .ops-column-actions button:disabled,.ops-modal .ops-column-button:disabled{opacity:.3;cursor:not-allowed}
.ops-modal .ops-column-actions button:focus-visible,.ops-modal .ops-column-button:focus-visible,.ops-modal .ops-add-column:focus-visible{outline:3px solid #93c5fd;outline-offset:3px}
.ops-modal .ops-column-button svg,.ops-modal .ops-column-actions svg{width:19px;height:19px;flex:none;stroke-width:2}
.ops-modal .ops-favorite-button{margin-bottom:16px}
.ops-modal .ops-favorite-button.is-favorite{background:#fffbeb;border-color:#fbbf24;color:#92400e}
.ops-modal .ops-favorite-button.is-favorite svg{fill:#fbbf24;stroke:#92400e}
.ops-modal .ops-add-column:hover{background:#eff6ff;border-color:#60a5fa;color:#1d4ed8}
.ops-view-tab{display:inline-flex;align-items:center;gap:7px}
.ops-tab-star{color:#d97706;fill:#fbbf24;width:16px;height:16px}
.dark .ops-modal .ops-column-button{background:#1e293b;border-color:#475569;color:#e2e8f0}
.dark .ops-modal .ops-column-actions button{background:#1e3a5f;border-color:#3b82f6;color:#bfdbfe}
.dark .ops-modal .ops-column-button:hover:not(:disabled),.dark .ops-modal .ops-column-actions button:hover:not(:disabled){background:#2563eb;color:#fff}
.dark .ops-modal .ops-column-button.ops-column-danger,.dark .ops-modal .ops-column-actions .ops-column-danger{background:#4c0519;border-color:#9f1239;color:#fda4af}
.dark .ops-modal .ops-column-button.ops-column-danger:hover:not(:disabled),.dark .ops-modal .ops-column-actions .ops-column-danger:hover:not(:disabled){background:#dc2626;color:#fff}
.dark .ops-modal .ops-favorite-button.is-favorite{background:#422006;border-color:#d97706;color:#fde68a}
</style>
