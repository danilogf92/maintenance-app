<?php
namespace App\Modules\Contacts\Models;

class Contact extends \Illuminate\Database\Eloquent\Model
{
    protected $guarded = ['id'];
    protected function casts(): array { return ['active' => 'boolean', 'lock_version' => 'integer']; }
    public function links() { return $this->hasMany(ContactLink::class); }
}
