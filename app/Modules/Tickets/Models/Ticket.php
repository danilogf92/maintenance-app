<?php

namespace App\Modules\Tickets\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'tickets';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['opened_at' => 'datetime'];
    }

    public function fieldEngineers() { return $this->belongsToMany(\App\Modules\FieldEngineers\Models\FieldEngineer::class,'ticket_field_engineer')->withPivot('source_assignment_id'); }

    public function ninoxImport() { return $this->hasOne(NinoxTicketImport::class); }

    public function maintenanceVisit() { return $this->belongsTo(\App\Modules\MaintenancePlans\Models\MaintenanceVisit::class); }
    public function requester() { return $this->belongsTo(\App\Modules\Contacts\Models\Contact::class, 'requester_contact_id'); }

    public function equipment()
    {
        return $this->belongsTo(\App\Modules\Equipment\Models\Equipment::class, 'equipment_id');
    }

    public function technician()
    {
        return $this->belongsTo(\App\Models\User::class, 'technician_id');
    }

    public function services()
    {
        return $this->hasMany(\App\Modules\Services\Models\Service::class, 'ticket_id');
    }

}
