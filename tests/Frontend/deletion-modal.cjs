// Run from the project root: node tests/Frontend/deletion-modal.cjs
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const file = path.resolve(__dirname, '../../resources/views/components/ui/deletion-modal.blade.php');
const source = fs.readFileSync(file, 'utf8');
const expression = source.match(/x-data="([\s\S]*?)"\s+x-effect/)[1];
function setup(confirmAction = 'deleteConfirmedRecord', cancelAction = 'cancelDelete') {
    const actions = [], events = [];
    const modal = {isConnected: true, open: false,
        showModal() { this.open = true; }, close() { this.open = false; }};
    const trigger = {isConnected: true, focused: false, focus() { this.focused = true; }};
    const code = expression.replace('@js($confirmAction)', JSON.stringify(confirmAction))
        .replace('@js($cancelAction)', JSON.stringify(cancelAction));
    const data = vm.runInNewContext('(' + code + ')', {document: {activeElement: trigger}});
    data.$el = modal;
    data.$dispatch = event => events.push(event);
    data.$wire = {
        async deleteConfirmedRecord() { actions.push('delete'); },
        async cancelDelete() { actions.push('cancel'); },
        async deleteView() { actions.push('view'); },
    };
    data.init(); data.syncModal(true);
    assert.equal(modal.open, true);
    return {data, modal, trigger, actions, events};
}
(async () => {
    const cancel = setup();
    await cancel.data.dismiss();
    assert.deepEqual(cancel.actions, ['cancel']);
    assert.equal(cancel.modal.open, false);
    const confirmed = setup();
    await confirmed.data.accept();
    assert.deepEqual(confirmed.actions, ['delete']);
    assert.equal(confirmed.modal.open, false);
    confirmed.data.destroy();
    assert.equal(confirmed.trigger.focused, true);
    const repeated = setup();
    let release;
    repeated.data.$wire.deleteConfirmedRecord = async () => {
        repeated.actions.push('delete');
        await new Promise(resolve => { release = resolve; });
    };
    const first = repeated.data.accept();
    await repeated.data.accept(); await repeated.data.dismiss();
    assert.deepEqual(repeated.actions, ['delete'], 'Busy dialog must ignore repeated actions');
    release(); await first;
    const failed = setup();
    failed.data.$wire.deleteConfirmedRecord = async () => { throw Error('denied'); };
    await failed.data.accept();
    assert.equal(failed.modal.open, true, 'Request failure must keep confirmation visible');
    assert.ok(failed.data.errorMessage);
    assert.equal(failed.data.busy, false);
    const view = setup('deleteView', null);
    await view.data.dismiss();
    assert.deepEqual(view.actions, []);
    assert.ok(view.events.includes('deletion-dialog-closed'));
    view.data.syncModal(true); await view.data.accept();
    assert.deepEqual(view.actions, ['view']);
    assert.match(source, /@keydown\.escape\.prevent\.stop="dismiss\(\)"/);
    console.log('PASS: real modal lifecycle, cancel, confirm, one request, error, view deletion and focus restoration.');
})().catch(error => { console.error(error); process.exitCode = 1; });
