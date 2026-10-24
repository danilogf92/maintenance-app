# Planificación — Relaciones implementadas

Los nombres corresponden a las tablas reales. Las flechas no muestran campos.

```mermaid
erDiagram
    direction TB
    equipment ||--o{ maintenance_plans : tiene
    users |o--o{ maintenance_plans : tecnico_previsto
    maintenance_plans ||--o{ maintenance_visits : programa
    users |o--o{ maintenance_visits : tecnico_previsto
    maintenance_visits |o--o| tickets : puede_generar
    equipment ||--o{ tickets : registra
    users ||--o{ tickets : tecnico_asignado
    contacts |o--o{ tickets : solicitante_opcional
    tickets ||--o{ services : origina
```

Un ticket puede existir sin visita y sin contacto solicitante. Cada visita genera como máximo un ticket. La referencia de contrato del plan es texto; la entidad de Contratos queda pendiente.
