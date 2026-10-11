<div x-show.important="!isMobile" class="ops-resize-handle" role="separator" tabindex="0"
    aria-orientation="vertical" aria-label="Cambiar ancho del panel"
    :aria-valuenow="drawerWidth" :aria-valuemin="clampWidth(1)" :aria-valuemax="clampWidth(100000)"
    title="Arrastra para cambiar el ancho. Doble clic para restaurar."
    @pointerdown.prevent.stop="beginResize($event)"
    @keydown.left.prevent="adjustWidth(32)"
    @keydown.right.prevent="adjustWidth(-32)"
    @dblclick.prevent="resetWidth()">
    <span class="ops-resize-grip" aria-hidden="true">
        <svg viewBox="0 0 16 30" fill="currentColor">
            <circle cx="5" cy="7" r="1.5"/><circle cx="11" cy="7" r="1.5"/>
            <circle cx="5" cy="15" r="1.5"/><circle cx="11" cy="15" r="1.5"/>
            <circle cx="5" cy="23" r="1.5"/><circle cx="11" cy="23" r="1.5"/>
        </svg>
    </span>
</div>
