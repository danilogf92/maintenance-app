const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const assert = require('node:assert/strict');
const source = fs.readFileSync(path.resolve(__dirname,
    '../../app/Modules/Institutions/UI/Views/editor.blade.php'), 'utf8');
let expression = source.match(/x-data="([\s\S]*?)"\s+@keydown/)[1];
expression = expression.replace(/@js\(array_key_first\(\$editorTabs\)\)/g, "'general'")
    .replace(/relations:@js\([^\n]+/, 'relations:{general:null,contact:null,users:"users"},');
const data = vm.runInNewContext('(' + expression + ')');
let changedRecord;
const calls = [];
data.$wire = {editorSession: 'record-a', formOpen: true,
    loadEditorRelation: async relation => calls.push(relation)};
data.$watch = (key, callback) => { changedRecord = callback; };
data.init();
(async () => {
    await data.selectTab('contact');
    assert.equal(data.activeTab, 'contact');
    assert.equal(data.isRelationTab(), false);
    assert.equal(calls.length, 0, 'Field tabs must not send requests or remount fields');
    assert.equal(data.$wire.editorSession, 'record-a');
    await data.selectTab('users');
    assert.equal(data.isRelationTab(), true);
    assert.deepEqual(calls, ['users']);
    assert.equal(data.$wire.formOpen, true);
    changedRecord();
    assert.equal(data.activeTab, 'general', 'A different item starts on the first tab');
    assert.match(source, /x-show="activeTab===/);
    assert.match(source, /!document.querySelector\('\.institution-user-dialog\[open\]'\)/,
        'Escape in a related editor must not close the institution');
    console.log('PASS: field tabs preserve session, lazy relations, new-item reset and nested-dialog Escape.');
})().catch(error => { console.error(error); process.exitCode = 1; });
