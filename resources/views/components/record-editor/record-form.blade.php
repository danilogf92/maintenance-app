<form {{ $attributes->merge(['class' => 'flex h-full min-h-0 flex-col']) }}
    @submit.prevent="queueAutosave(); saveChanges()"
    x-data="{
        saveState: 'idle',
        saveTimer: null,
        saveBusy: false,
        savePending: false,
        saveRevision: 0,
        init() {
            this.$watch('$wire.editorSession', () => this.resetSaveStatus());
            this.$watch('$wire.formOpen', () => this.resetSaveStatus());
        },
        resetSaveStatus() {
            clearTimeout(this.saveTimer);
            this.saveRevision++;
            this.savePending = false;
            this.saveState = 'idle';
        },
        queueAutosave() {
            this.saveRevision++;
            clearTimeout(this.saveTimer);
            this.savePending = true;
            this.saveState = 'pending';
            this.saveTimer = setTimeout(() => this.saveChanges(), 700);
        },
        async saveChanges() {
            if (this.saveBusy || !this.savePending || !this.$wire.formOpen) return;
            const recordId = Number(this.$wire.editingId);
            const session = this.$wire.editorSession;
            if (!session) return;
            const revision = this.saveRevision;
            this.savePending = false;
            this.saveBusy = true;
            this.saveState = 'saving';
            try {
                const result = recordId ? await this.$wire.autosaveRecord(recordId, session) : await this.$wire.autosaveDraft(session);
                if (revision === this.saveRevision && this.$wire.editorSession === session && this.$wire.formOpen) {
                    this.saveState = result === 'saved' ? 'saved' : result === 'invalid' ? 'error' : 'idle';
                }
            } catch (error) {
                if (revision === this.saveRevision) this.saveState = 'error';
            } finally {
                this.saveBusy = false;
                if (this.savePending && this.$wire.formOpen) {
                    clearTimeout(this.saveTimer);
                    this.saveTimer = setTimeout(() => this.saveChanges(), 700);
                }
            }
        },
        destroy() { clearTimeout(this.saveTimer); }
    }"
    @input="queueAutosave()"
    @change="queueAutosave()">
    {{ $slot }}
</form>
