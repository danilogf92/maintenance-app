// Run from the project root: node tests/Frontend/record-navigation.cjs
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const root = path.resolve(__dirname, '../..');
const source = fs.readFileSync(path.join(root,
    'resources/views/components/record-editor/record-navigation.blade.php'), 'utf8');
const expression = source.match(/x-data="([\s\S]*?)">/)[1];
const data = vm.runInNewContext('(' + expression + ')');
const requests = [];
let pending;
data.$el = {closest: () => ({querySelectorAll: () =>
    [11, 12, 13].map(id => ({dataset: {recordId: String(id)}}))})};
data.$wire = {
    editingId: 11, formOpen: true,
    closeForm() { assert.fail('Navigation must not close and reopen the editor'); },
    async edit(id) {
        requests.push(id);
        if (pending) await pending;
        this.editingId = id;
        this.formOpen = true;
    },
};
(async () => {
    await data.openRecord('next');
    assert.deepEqual(requests, [12], 'One request per record change');
    assert.equal(data.$wire.formOpen, true);
    await data.openRecord('previous');
    assert.equal(data.$wire.editingId, 11);
    data.$wire.formOpen = false;
    await data.openRecord('last');
    assert.equal(data.$wire.editingId, 13);
    assert.equal(data.$wire.formOpen, true);
    const count = requests.length;
    await data.openRecord('next');
    assert.equal(requests.length, count, 'Last record must not request itself');
    let release;
    pending = new Promise(resolve => { release = resolve; });
    const first = data.openRecord('first');
    await data.openRecord('previous');
    assert.equal(requests.length, count + 1, 'Rapid clicks must not overlap');
    release();
    await first;
    assert.equal(data.navigationBusy, false);
    assert.equal(data.$wire.editingId, 11);
    // An invalid pending edit keeps the original record open.
    data.$wire.edit = async () => {};
    await data.openRecord('next');
    assert.equal(data.$wire.editingId, 11);
    assert.equal(data.$wire.formOpen, true);
    console.log('PASS: single request, first/last, boundaries, rapid clicks, validation and open panel.');
})().catch(error => { console.error(error); process.exitCode = 1; });
