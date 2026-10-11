const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const root = path.resolve(__dirname, '../..');
const source = fs.readFileSync(path.join(root, 'resources/views/components/record-editor/group-body.blade.php'), 'utf8');
const expression = source.match(/x-data="([\s\S]*?)"\s+@table-groups/)[1]
    .replace(/@js\(\$group\['label'\] !== null\)/g, 'true')
    .replace(/@js\(\$group\['items'\]->pluck\('id'\)->all\(\)\)/g, '[2,8,15]');
const group = vm.runInNewContext('(' + expression + ')');
const watchers = {};
group.$watch = (name, callback) => { watchers[name] = callback; };
group.$wire = {formOpen: false, editingId: null};
group.init();
assert.equal(group.expanded, false, 'Grouped tables start collapsed');
group.$wire.formOpen = true;
group.$wire.editingId = 8;
watchers['$wire.editingId']();
assert.equal(group.expanded, true, 'Navigation opens the selected row group');
group.expanded = false;
group.$wire.editingId = 99;
watchers['$wire.editingId']();
assert.equal(group.expanded, false, 'Other groups remain closed');
assert.match(source, /@table-record-reveal=/);
const drawer = fs.readFileSync(path.join(root, 'resources/views/components/record-editor/drawer-workspace.blade.php'), 'utf8');
assert.ok(drawer.indexOf("new CustomEvent('table-record-reveal'") < drawer.indexOf('const viewport = table.getBoundingClientRect()'));
assert.match(drawer, /this\.\$nextTick\(\(\) => \{\s+const viewport/);
console.log('PASS: initially collapsed groups, selective navigation expansion and reveal before scroll measurement.');
