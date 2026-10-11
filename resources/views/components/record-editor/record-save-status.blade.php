@once
<style>
.ops-save-status { display:inline-flex; align-items:center; justify-content:center; gap:6px; min-width:32px; height:32px; margin-right:12px; font-size:12px; color:#64748b; }
.ops-save-status svg { width:22px; height:22px; flex:none; }
.ops-save-status .ops-save-check { color:#16a34a; }
.ops-save-status .ops-save-error { color:#dc2626; }
.ops-save-spinner { animation:ops-save-spin 1s linear infinite; }
@keyframes ops-save-spin { to { transform:rotate(360deg); } }
@media(prefers-reduced-motion:reduce) { .ops-save-spinner { animation:none; } }
.dark .ops-save-status { color:#94a3b8; }.dark .ops-save-status .ops-save-check { color:#4ade80; }
</style>
@endonce
<div class="ops-save-status" x-show="saveState !== 'idle'" x-cloak role="status" aria-live="polite"
    :title="saveState === 'saved' ? 'Cambios guardados' : saveState === 'error' ? 'No se guardó. Revisa los campos o la conexión.' : saveState === 'new' ? 'Completa los campos obligatorios; se guarda automáticamente' : 'Guardando cambios'">
    <svg x-show="saveState === 'saved'" class="ops-save-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
    <svg x-show="saveState === 'saving' || saveState === 'pending'" class="ops-save-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9" opacity=".2"/><path d="M12 3a9 9 0 0 1 9 9"/></svg>
    <svg x-show="saveState === 'error' || saveState === 'new'" class="ops-save-error" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v6m0 3v1"/></svg>
    <span class="sr-only" x-text="saveState === 'saved' ? 'Cambios guardados' : saveState === 'error' ? 'Cambios no guardados' : saveState === 'new' ? 'Registro nuevo sin guardar' : 'Guardando cambios'"></span>
</div>
