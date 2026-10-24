# Maintenance APP — Relaciones entre entidades

Los modelos PHP y las tablas de la base de datos están en inglés. Por ejemplo, el modelo `User` corresponde a `users` y `Institution` a `institutions`. En estos diagramas cada entidad usa el **nombre exacto de su tabla**, respetando minúsculas, plural y guiones bajos. Los títulos y las etiquetas de las relaciones se mantienen en español.

Relaciones funcionales verificadas en `maintenance-app(5).zip` (v11.50.0), que se conservan en v11.50.3. No se muestran campos ni detalles de interfaz.

## Operación y equipos

```mermaid
erDiagram
    direction TB

    manufacturers |o--o{ brands : agrupa
    brands |o--o{ equipment_models : tiene
    brands |o--o{ equipment : identifica
    equipment_models |o--o{ equipment : clasifica

    clients ||--o{ equipment : posee
    institutions ||--o{ equipment : tiene
    institutions ||--o{ users : agrupa

    equipment ||--o{ tickets : registra
    users ||--o{ tickets : atiende
    tickets ||--o{ services : origina
    users ||--o{ services : realiza

    services ||--o{ activities : contiene
    services ||--o{ technical_reports : genera
```

## Contactos

Una persona puede vincularse con varios clientes e instituciones. Cada vínculo corresponde a un cliente **o** a una institución. Los contactos están separados de las cuentas de usuario que acceden al sistema.

```mermaid
erDiagram
    direction TB

    contacts ||--o{ contact_links : tiene
    clients |o--o{ contact_links : relaciona
    institutions |o--o{ contact_links : relaciona
```

## Plantillas e informes

```mermaid
erDiagram
    direction TB

    report_templates ||--o{ report_template_versions : conserva
    report_templates ||--o{ technical_reports : define_formato
    report_templates ||--o{ report_generations : utiliza

    technical_reports |o--o{ report_generations : produce
    users ||--o{ report_generations : solicita
    users |o--o{ report_template_versions : crea
```

## Usuarios y acceso

```mermaid
erDiagram
    direction TB

    institutions ||--o{ users : agrupa
    users ||--o{ model_has_roles : recibe
    roles ||--o{ model_has_roles : asigna

    roles ||--o{ role_has_permissions : contiene
    permissions ||--o{ role_has_permissions : integra

    users ||--o{ model_has_permissions : recibe_directamente
    permissions ||--o{ model_has_permissions : asigna
    users ||--o| google_accounts : vincula
```

Las tablas `model_has_roles` y `model_has_permissions` utilizan relaciones polimórficas. En este diagrama se muestra su uso para el modelo `User`. `role_has_permissions` es la tabla intermedia entre roles y permisos.

## Cómo leer las relaciones

| Notación | Significado |
| --- | --- |
| `\|\|` | Uno obligatorio |
| `\|o` u `o\|` | Cero o uno |
| `o{` o `}o` | Cero o muchos |

Por ejemplo, `equipment ||--o{ tickets` indica que un equipo puede tener cero o muchos tickets y que cada ticket pertenece a un equipo obligatorio.

Los bloques Mermaid pueden visualizarse en un editor Markdown compatible con Mermaid.
