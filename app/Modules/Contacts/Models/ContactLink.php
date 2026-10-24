<?php
namespace App\Modules\Contacts\Models;

class ContactLink extends \Illuminate\Database\Eloquent\Model
{
    protected $guarded = ['id'];
    protected function casts(): array { return ['active' => 'boolean', 'is_primary' => 'boolean', 'lock_version' => 'integer']; }
    public function contact() { return $this->belongsTo(Contact::class); }
    public function client() { return $this->belongsTo(\App\Modules\Clients\Models\Client::class); }
    public function institution() { return $this->belongsTo(\App\Models\Institution::class); }
    public function parentType(): string { return $this->client_id ? 'client' : 'institution'; }
    public function parentId(): int { return (int)($this->client_id ?: $this->institution_id); }
}
