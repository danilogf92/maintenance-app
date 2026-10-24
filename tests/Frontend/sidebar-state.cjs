// Run from the project root: node tests/Frontend/sidebar-state.cjs
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const root = path.resolve(__dirname, '../..');
const layout = fs.readFileSync(path.join(root,
    'resources/views/components/layouts/app.blade.php'), 'utf8');
const source = fs.readFileSync(path.join(root,
    'resources/views/components/layouts/sidebar-state.blade.php'), 'utf8');
const startup = source.match(/<script>([\s\S]*?)<\/script>/)[1];
const expression = layout.match(/<div x-data="(\{mobileOpen:false[^\n]*?)"/)[1];
const storage = new Map();
let unavailable = false;
function visit() {
    const context = {
        document: {documentElement: {dataset: {}}},
        localStorage: {
            getItem(key) { if (unavailable) throw Error('blocked'); return storage.get(key); },
            setItem(key, value) { if (unavailable) throw Error('blocked'); storage.set(key, value); },
        },
    };
    vm.runInNewContext(startup, context);
    const firstPaint = context.document.documentElement.dataset.sidebarCollapsed;
    const controller = vm.runInNewContext('(' + expression + ')', context);
    assert.equal(controller.collapsed, firstPaint === '1', 'Alpine must match the initial layout');
    return {context, firstPaint, controller};
}
assert.equal(visit().firstPaint, '0');
storage.set('maintenance-sidebar-collapsed', '1');
for (let i = 0; i < 5; i++) {
    assert.equal(visit().firstPaint, '1', 'Every destination must start collapsed');
}
const page = visit();
page.controller.collapsed = false;
page.controller.saveCollapse();
assert.equal(page.context.document.documentElement.dataset.sidebarCollapsed, '0');
assert.equal(visit().firstPaint, '0');
page.controller.collapsed = true;
page.controller.saveCollapse();
assert.equal(visit().firstPaint, '1');
unavailable = true;
const fallback = visit();
assert.equal(fallback.firstPaint, '0', 'Blocked storage must not break initialization');
fallback.controller.collapsed = true;
fallback.controller.saveCollapse();
assert.equal(fallback.context.document.documentElement.dataset.sidebarCollapsed, '1');
assert.ok(layout.indexOf("@include('components.layouts.sidebar-state')") < layout.indexOf('@vite('));
assert.ok(layout.indexOf("@include('components.layouts.sidebar-state')") < layout.indexOf('<body'));
assert.match(source, /#app-sidebar \{ width:var\(--sidebar-width\)!important; transition:none!important/);
assert.match(source, /\.app-shell-content \{ padding-left:var\(--sidebar-width\)!important/);
const navigation = fs.readFileSync(path.join(root,
    'resources/views/components/layouts/operations-navigation.blade.php'), 'utf8');
assert.match(layout, /@include\('components.layouts.operations-navigation'\)/);
assert.match(navigation, /class="sidebar-label"/);
console.log('PASS: collapsed/expanded state before paint, navigation persistence, toggle, blocked storage and matching layout.');
