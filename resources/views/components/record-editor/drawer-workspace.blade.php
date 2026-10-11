@once
    @include('components.record-editor.drawer-styles')
@endonce
<div class="ops-drawer-workspace" x-data="{
 drawerWidth: 520,
 minimumDrawerWidth: 420,
 minimumTableWidth: 400,
 layoutObserver: null,
 workspaceElement: null,
 resizing: false,
 isMobile: false,
 storageKey: 'maintenance-app:operation-drawer-width',
 resizeOrigin: null,
 selectionFrame: null,
 originalCursor: '',
 originalSelection: '',
 init() {
  // $el changes inside descendant Alpine expressions: retain the workspace.
  this.workspaceElement = this.$el;
  try { this.drawerWidth = Number(localStorage.getItem(this.storageKey)) || 520; } catch (error) {}
  this.refreshLayout();
  this.layoutObserver = new ResizeObserver(() => this.refreshLayout());
  this.layoutObserver.observe(this.workspaceElement);
  // Watch only selection changes, never animation bookkeeping.
  this.$watch('$wire.editingId', () => this.queueSelection(this.$wire.editingId, this.$wire.formOpen));
  this.$watch('$wire.formOpen', () => this.queueSelection(this.$wire.editingId, this.$wire.formOpen));
  this.$nextTick(() => this.queueSelection(this.$wire.editingId, this.$wire.formOpen));
 },
 refreshLayout() {
  const available = this.workspaceElement.clientWidth || window.innerWidth;
  this.isMobile = available < this.minimumDrawerWidth + this.minimumTableWidth;
  if (this.isMobile) this.finishResize();
  else this.drawerWidth = this.clampWidth(this.drawerWidth);
 },
 clampWidth(value) {
  const available = this.workspaceElement.clientWidth || window.innerWidth;
  const minimum = Math.min(this.minimumDrawerWidth, available);
  const maximum = Math.max(minimum, available - this.minimumTableWidth);
  return Math.round(Math.min(maximum, Math.max(minimum, Number(value) || 520)));
 },
 panelStyle() {
  // Bind individual properties so a layout refresh cannot remove x-show's display.
  const width = this.isMobile ? '100%' : this.clampWidth(this.drawerWidth) + 'px';
  return {
   position: this.isMobile ? 'absolute' : 'relative',
   inset: this.isMobile ? '0' : 'auto',
   'z-index': this.isMobile ? '20' : 'auto',
   width,
   'min-width': this.isMobile ? '0' : width,
   'max-width': width,
   flex: this.isMobile ? 'none' : '0 0 ' + width
  };
 },
 beginResize(event) {
  if (this.isMobile || (event.pointerType === 'mouse' && event.button !== 0)) return;
  this.drawerWidth = this.clampWidth(this.drawerWidth);
  this.resizeOrigin = {x: event.clientX, width: this.drawerWidth, id: event.pointerId};
  this.resizing = true;
  this.originalCursor = document.body.style.cursor;
  this.originalSelection = document.body.style.userSelect;
  document.body.style.cursor = 'col-resize';
  document.body.style.userSelect = 'none';
  // Capture on the stationary workspace, not the moving grip.
  this.workspaceElement.setPointerCapture?.(event.pointerId);
 },
 resizeTo(event) {
  if (!this.resizing || event.pointerId !== this.resizeOrigin.id) return;
  this.drawerWidth = this.clampWidth(this.resizeOrigin.width + this.resizeOrigin.x - event.clientX);
  this.rememberWidth();
 },
 rememberWidth() {
  try { localStorage.setItem(this.storageKey, String(this.drawerWidth)); } catch (error) {}
 },
 finishResize() {
  if (!this.resizing) return;
  this.resizing = false;
  const pointerId = this.resizeOrigin.id;
  if (this.workspaceElement.hasPointerCapture?.(pointerId)) {
   this.workspaceElement.releasePointerCapture(pointerId);
  }
  document.body.style.cursor = this.originalCursor;
  document.body.style.userSelect = this.originalSelection;
  this.resizeOrigin = null;
  this.rememberWidth();
 },
 adjustWidth(amount) {
  this.drawerWidth = this.clampWidth(this.drawerWidth + amount);
  this.rememberWidth();
 },
 resetWidth() {
  this.drawerWidth = this.clampWidth(520);
  this.rememberWidth();
 },
 syncWidth(event) {
  if (event.key === this.storageKey && !this.resizing) {
   this.drawerWidth = this.clampWidth(event.newValue);
  }
 },
 queueSelection(recordId, formOpen) {
  if (this.selectionFrame !== null) cancelAnimationFrame(this.selectionFrame);
  this.selectionFrame = null;
  if (!formOpen || !recordId) return;
  this.$nextTick(() => {
   this.selectionFrame = requestAnimationFrame(() => {
    this.selectionFrame = null;
    this.revealRecord(recordId);
   });
  });
 },
 revealRecord(recordId) {
  const table = this.$refs.records;
  const row = table?.querySelector('[data-record-id=&quot;' + Number(recordId) + '&quot;]');
  if (!row) return;
  const viewport = table.getBoundingClientRect();
  const bounds = row.getBoundingClientRect();
  const header = table.querySelector('thead')?.getBoundingClientRect().height || 0;
  const footer = table.querySelector('.ops-record-count')?.getBoundingClientRect().height || 0;
  const top = viewport.top + header;
  const bottom = viewport.top + table.clientHeight - footer;
  if (bounds.top < top) table.scrollTop += bounds.top - top;
  else if (bounds.bottom > bottom) table.scrollTop += bounds.bottom - bottom;
 },
 destroy() {
  this.finishResize();
  this.layoutObserver?.disconnect();
  if (this.selectionFrame !== null) cancelAnimationFrame(this.selectionFrame);
 }
}"
    :class="{'ops-drawer-resizing': resizing, 'ops-drawer-mobile': isMobile}"
    @resize.window="refreshLayout()"
    @pointermove.window="resizeTo($event)"
    @pointerup.window="finishResize()"
    @pointercancel.window="finishResize()"
    @lostpointercapture="if ($event.target === workspaceElement) finishResize()"
    @blur.window="finishResize()"
    @storage.window="syncWidth($event)">
    {{ $slot }}
</div>
