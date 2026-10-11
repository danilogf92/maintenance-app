// Run from the project root: node tests/Frontend/drawer-resize.cjs
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

const root = process.argv[2] || path.resolve(__dirname, '../..');
const source = fs.readFileSync(path.join(root,
    'resources/views/components/record-editor/drawer-workspace.blade.php'), 'utf8');
const expression = source.match(/x-data="([\s\S]*?)"\s+:class/)[1];
const storage = new Map();
const body = {style: {cursor: '', userSelect: ''}};
let observed;
const context = {
    window: {innerWidth: 1832}, document: {body},
    localStorage: {
        getItem: key => storage.get(key),
        setItem: (key, value) => storage.set(key, value),
    },
    ResizeObserver: class {
        constructor(callback) { this.callback = callback; }
        observe(element) { observed = element; }
        disconnect() {}
    },
    cancelAnimationFrame() {},
};

function controller(width = 1832) {
    const workspace = {
        clientWidth: width, pointer: null,
        setPointerCapture(id) { this.pointer = id; },
        hasPointerCapture(id) { return this.pointer === id; },
        releasePointerCapture() { this.pointer = null; },
    };
    const data = vm.runInNewContext('(' + expression + ')', context);
    data.$el = workspace;
    data.$wire = {editingId: null, formOpen: false};
    data.$watch = () => {};
    data.$nextTick = callback => callback();
    data.init();
    assert.equal(observed, workspace);
    return {data, workspace};
}

const {data, workspace} = controller();
// Alpine resolves $el to the descendant executing the expression.
const grip = {clientWidth: 36, setPointerCapture() { assert.fail('Grip captured'); }};
data.$el = grip;
data.beginResize({pointerType: 'mouse', button: 0, clientX: 1336,
    pointerId: 7, currentTarget: grip});
assert.equal(data.drawerWidth, 520);
assert.equal(workspace.pointer, 7);
data.resizeTo({pointerId: 8, clientX: 900});
assert.equal(data.drawerWidth, 520, 'Other pointers must be ignored');
data.resizeTo({pointerId: 7, clientX: 1000});
assert.equal(data.drawerWidth, 856);
data.$el = {clientWidth: 856}; // Panel style expression, another descendant.
assert.equal(data.clampWidth(data.drawerWidth), 856, 'Must not collapse to 420');
data.refreshLayout();
assert.equal(data.drawerWidth, 856, 'Observer must retain the chosen width');
data.resizeTo({pointerId: 7, clientX: 1300});
assert.equal(data.drawerWidth, 556, 'Move back right during the same drag');
data.finishResize();
assert.equal(data.drawerWidth, 556, 'Release must not reset width');
assert.equal(workspace.pointer, null);
assert.equal(body.style.cursor, '');
assert.equal(body.style.userSelect, '');
assert.equal(storage.get(data.storageKey), '556');
assert.equal(controller().data.drawerWidth, 556, 'Reload must restore width');
data.adjustWidth(-5000);
assert.equal(data.drawerWidth, 420);
data.adjustWidth(5000);
assert.equal(data.drawerWidth, 1432, 'Leave 400 pixels for the table');
data.resetWidth();
assert.equal(data.drawerWidth, 520);
workspace.clientWidth = 700;
data.refreshLayout();
assert.equal(data.isMobile, true);
data.beginResize({pointerType: 'mouse', button: 0});
assert.equal(data.resizing, false);
workspace.clientWidth = 1832;
data.refreshLayout();
assert.equal(data.isMobile, false);
assert.equal(data.drawerWidth, 520);
// Sidebar collapse changes available width while the record editor is closed.
// Alpine object style bindings update these keys without replacing display.
const style = {display: 'none'};
for (const available of [1500, 700, 1832, 900, 700]) {
    workspace.clientWidth = available;
    data.refreshLayout();
    const layout = data.panelStyle();
    assert.equal(Object.hasOwn(layout, 'display'), false);
    Object.assign(style, layout);
    assert.equal(style.display, 'none', 'Resizing must not reveal a closed editor');
    assert.equal(style.position, available < 820 ? 'absolute' : 'relative');
}
workspace.clientWidth = 1832;
data.refreshLayout();
Object.assign(style, data.panelStyle());
assert.equal(style.inset, 'auto', 'Mobile positioning must reset on desktop');
assert.equal(style['z-index'], 'auto');
assert.equal(style.width, '500px', 'Keep the clamped width after the narrow desktop layout');
data.destroy();

for (const module of ['user', 'institution']) {
    const form = fs.readFileSync(path.join(root,
        ...(module === 'institution' ? ['app/Modules/Institutions/UI/Views/editor.blade.php'] : ['resources/views/livewire', module, 'form.blade.php'])), 'utf8');
    assert.match(form, /<aside wire:ignore\.self/,
        'Livewire must preserve Alpine width while updating form children');
    assert.match(form, /x-show\.important="\$wire\.formOpen"/);
    assert.match(form, /:style="panelStyle\(\)"/,
        'Both modules must use the object style binding');
}
console.log('PASS: drag both directions, scoped $el, release, persistence, limits, mobile, morph protection and closed-panel visibility.');
