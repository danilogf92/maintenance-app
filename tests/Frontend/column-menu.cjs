const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const root = path.resolve(__dirname, '../..');
const source = fs.readFileSync(path.join(root,
    'resources/views/components/record-editor/column-header.blade.php'), 'utf8');
const expression = source.match(/x-data="([\s\S]*?)"\s+@resize/)[1];
const context = {window: {innerWidth: 1600, innerHeight: 900}};
const menu = vm.runInNewContext('(' + expression + ')', context);
let focused = 0;
menu.$nextTick = callback => callback();
menu.$refs = {columnMenu: {querySelector: () => ({focus: () => focused++})}};
const trigger = (left, bottom) => ({currentTarget: {
    getBoundingClientRect: () => ({left, bottom}),
}});
menu.showMenu(trigger(1500, 880));
assert.equal(menu.open, true);
assert.equal(focused, 1, 'Opening the menu focuses its first control');
assert.ok(menu.left + 280 <= context.window.innerWidth);
assert.ok(menu.top + 320 <= context.window.innerHeight);
menu.showMenu(trigger(1500, 880));
assert.equal(menu.open, false, 'Second click closes the same menu');
context.window.innerWidth = 320;
context.window.innerHeight = 240;
menu.showMenu(trigger(0, 200));
assert.equal(menu.left, 8);
assert.equal(menu.top, 8);
assert.match(source, /maxHeight:Math.max/);
assert.match(source, /x-teleport="body"/, 'Table scrolling must not clip the menu');
assert.match(source, /\$wire.filterColumn/);
assert.match(source, /\$wire.sortColumn/);
assert.match(source, /\$wire.groupColumn/);
console.log('PASS: header menu toggle, viewport bounds, scroll-safe placement and column actions.');
