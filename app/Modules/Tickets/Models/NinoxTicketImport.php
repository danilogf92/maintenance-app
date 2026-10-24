<?php
namespace App\Modules\Tickets\Models;
use Illuminate\Database\Eloquent\Model;
class NinoxTicketImport extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['payload'];
    protected function casts(): array { return ['payload'=>'encrypted:array']; }
    public function ticket() { return $this->belongsTo(Ticket::class); }
}
