// Run from the project root: node tests/Frontend/automatic-save.cjs
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.resolve(__dirname,
    '../../resources/views/components/record-editor/record-form.blade.php'), 'utf8');
const expression = source.match(/x-data="([\s\S]*?)"\s+@input/)[1];
const timers = new Map();
let timerId = 0;
function setup() {
    const data = vm.runInNewContext('(' + expression + ')', {
        setTimeout: callback => { timers.set(++timerId, callback); return timerId; },
        clearTimeout: id => timers.delete(id),
    });
    data.$watch = () => {};
    data.$wire = {editingId: null, editorSession: 'draft-a', formOpen: true};
    data.init();
    return data;
}
(async () => {
    const data = setup(), requests = [];
    data.$wire.autosaveDraft = async session => {
        requests.push(['create', session]); data.$wire.editingId = 42; return 'saved';
    };
    data.$wire.autosaveRecord = async (id, session) => {
        requests.push(['update', id, session]); return 'saved';
    };
    data.queueAutosave(); await data.saveChanges();
    assert.deepEqual(requests, [['create', 'draft-a']]);
    assert.equal(data.saveState, 'saved', 'ID assignment must keep the saved check');
    data.queueAutosave(); await data.saveChanges();
    assert.deepEqual(requests[1], ['update', 42, 'draft-a']);
    const invalid = setup();
    invalid.$wire.autosaveDraft = async () => 'invalid';
    invalid.queueAutosave(); await invalid.saveChanges();
    assert.equal(invalid.saveState, 'error');
    assert.equal(invalid.$wire.editingId, null);
    const pending = setup();
    let release, count = 0;
    pending.$wire.autosaveDraft = async () => {
        count++; await new Promise(resolve => { release = resolve; });
        pending.$wire.editingId = 5; return 'saved';
    };
    pending.queueAutosave();
    const request = pending.saveChanges();
    pending.queueAutosave(); await pending.saveChanges();
    assert.equal(count, 1, 'Do not overlap creation requests');
    release(); await request;
    assert.equal(pending.savePending, true, 'Edits typed while creating must remain pending');
    assert.equal(pending.saveBusy, false);
    data.destroy(); pending.destroy(); invalid.destroy();
    console.log('PASS: automatic creation, same-record updates, validation, pending edits and request serialization.');
})().catch(error => { console.error(error); process.exitCode = 1; });
