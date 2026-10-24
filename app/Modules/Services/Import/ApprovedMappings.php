<?php
namespace App\Modules\Services\Import;
use App\Modules\Tickets\Models\Ticket;
use App\Models\User;
class ApprovedMappings
{
    public function read(?string $path): array
    {
        if (!$path) { return ['tickets' => [], 'technicians' => []]; }
        $map = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($map) || array_diff(array_keys($map), ['tickets', 'technicians'])) {
            throw new \RuntimeException('El mapa solo admite tickets y technicians.');
        }
        foreach (['tickets' => Ticket::class, 'technicians' => User::class] as $group => $model) {
            if (!is_array($map[$group] ?? [])) { throw new \RuntimeException('Grupo de equivalencias inválido.'); }
            foreach ($map[$group] ?? [] as $key => $id) {
                if (!is_int($id) || $id < 1 || !$model::whereKey($id)->exists()) {
                    throw new \RuntimeException('Una equivalencia apunta a un registro inexistente.');
                }
            }
        }
        return ['tickets' => $map['tickets'] ?? [], 'technicians' => $map['technicians'] ?? []];
    }
    public function values(array $record, array $map): array
    {
        return ['ticket_id' => $map['tickets'][(string)($record['tickets_associated'] ?? '')] ?? null,
            'technician_id' => $map['technicians'][$record['engineer_assigned'] ?? ''] ?? null];
    }
}
