<?php
namespace App\Modules\DataManagement\Configuration;
class HistoryIssues
{
    public static function message(string $code): string
    {
        return match ($code) {
            'date_missing' => 'Sin fecha: se conserva el historial. Solo se vincula si las asignaciones activas coinciden en un único destino.',
            'date_invalid' => 'Fecha no reconocida. Se requiere una fecha ISO válida; no se deduce su unidad ni formato.',
            'equipment_reference_pending' => 'La referencia del equipo está ausente, es ambigua o no existe entre los equipos recibidos.',
            'parent_reference_pending' => 'No se pudo resolver el cliente o centro con su identificador de origen.',
            'reverse_reference_mismatch' => 'El historial y el equipo no confirman la misma relación en ambos sentidos.',
            'source_conflict' => 'El origen cambió respecto al historial conservado. No se reemplazaron sus valores.',
            'existing_link_conflict' => 'El equipo ya tiene otro destino vinculado. Se conserva para revisión.',
            'history_references_incomplete' => 'Las referencias del equipo y las del paquete no coinciden o faltan historiales.',
            default => 'No se puede determinar una asignación actual: revisa referencias, fechas, empates o asignaciones futuras.',
        };
    }
}
