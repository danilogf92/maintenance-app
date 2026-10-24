<div class="ops-record-navigation" style="display:flex;align-items:center;gap:3px;"
    x-data="{
        navigationBusy: false,
        async openRecord(direction) {
            if (this.navigationBusy) return;
            const page = this.$el.closest('.ops-page');
            const ids = Array.from(page.querySelectorAll('.ops-table-wrap tr[data-record-id]'))
                .map(row => Number(row.dataset.recordId));
            if (!ids.length) return;
            const current = ids.indexOf(Number(this.$wire.editingId));
            let index = 0;
            if (direction === 'last') index = ids.length - 1;
            else if (direction === 'next') index = current < 0 ? 0 : Math.min(current + 1, ids.length - 1);
            else if (direction === 'previous') index = current < 0 ? ids.length - 1 : Math.max(current - 1, 0);
            const targetId = ids[index];
            if (this.$wire.formOpen && Number(this.$wire.editingId) === targetId) return;
            const selectedTabId = this.$wire.formOpen
                ? page.querySelector('.ops-detail-panel [role=tab][aria-selected=true]')?.id
                : null;
            this.navigationBusy = true;
            try {
                // edit() saves and validates the current record in one request.
                await this.$wire.edit(targetId);
                if (selectedTabId && this.$wire.formOpen && Number(this.$wire.editingId) === targetId) {
                    await this.$nextTick();
                    const tabs = page.querySelectorAll('.ops-detail-panel [role=tab]');
                    const tab = Array.from(tabs).find(tab => tab.id === selectedTabId);
                    if (tab) {
                        tab.dispatchEvent(new CustomEvent('record-navigation-select'));
                        if (tab.dataset.recordRelation) await this.$wire.loadEditorRelation(tab.dataset.recordRelation);
                    }
                }
            } finally {
                this.navigationBusy = false;
            }
        }
    }">
    <button type="button" @click="openRecord('first')" :disabled="navigationBusy"
        title="Abrir primer registro" aria-label="Abrir primer registro"><x-ui.icon name="skip-back" /></button>
    <button type="button" @click="openRecord('previous')" :disabled="navigationBusy"
        title="Abrir registro anterior" aria-label="Abrir registro anterior"><x-ui.icon name="play-back" /></button>
    <button type="button" @click="openRecord('next')" :disabled="navigationBusy"
        title="Abrir registro siguiente" aria-label="Abrir registro siguiente"><x-ui.icon name="play-forward" /></button>
    <button type="button" @click="openRecord('last')" :disabled="navigationBusy"
        title="Abrir último registro" aria-label="Abrir último registro"><x-ui.icon name="skip-forward" /></button>
</div>
