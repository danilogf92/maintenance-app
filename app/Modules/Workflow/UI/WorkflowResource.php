<?php

namespace App\Modules\Workflow\UI;

use App\Modules\Workflow\Configuration\Modules;
use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Configuration\TableColumns;
use App\Modules\Workflow\Queries\VisibleRecords;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

abstract class WorkflowResource extends Resource
{
    protected static string $module;
    protected static ?string $recordTitleAttribute = 'code';
    public static function module(): string { return static::$module; }
    public static function getNavigationLabel(): string { return Modules::get(static::$module)['label']; }
    public static function getModelLabel(): string
    {
        return ['clients' => 'Cliente', 'equipment' => 'Equipo', 'tickets' => 'Ticket', 'services' => 'Servicio',
            'activities' => 'Actividad', 'technical_reports' => 'Informe técnico'][static::$module];
    }
    public static function getPluralModelLabel(): string { return static::getNavigationLabel(); }
    public static function getNavigationIcon(): string { return Modules::get(static::$module)['icon']; }
    public static function canViewAny(): bool { return Permissions::allows(static::$module); }
    public static function canCreate(): bool { return Permissions::allows(static::$module, 'manage'); }
    public static function canView($record): bool { return static::canViewAny() && VisibleRecords::query(static::$module)->whereKey($record->id)->exists(); }
    public static function canEdit($record): bool { return static::canCreate() && static::canView($record); }
    public static function canDelete($record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }

    public static function getEloquentQuery(): Builder
    {
        $query = VisibleRecords::query(static::$module);
        if (static::$module === 'technical_reports') { $query->with('lastGeneration')->select(['id', 'code', 'service_id', 'report_template_id', 'title', 'revision', 'lock_version', 'created_by', 'created_at', 'updated_at']); }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Hidden::make('lock_version')->default(0), ...Modules::configuration(static::$module, 'Fields')::make()])->columns(2);
    }

    public static function table(Table $table): Table
    {
        $fields = Modules::configuration(static::$module, 'Fields');
        $filters = method_exists($fields, 'statuses') ? [SelectFilter::make('status')->label('Estado')->options($fields::statuses())] : [];
        $parent = ['equipment' => ['client_id', 'clients'], 'tickets' => ['equipment_id', 'equipment'], 'services' => ['ticket_id', 'tickets'],
            'activities' => ['service_id', 'services'], 'technical_reports' => ['service_id', 'services']][static::$module] ?? null;
        if ($parent && Permissions::allows($parent[1])) {
            $filters[] = SelectFilter::make($parent[0])->label('Registro relacionado')->searchable()
                ->options(fn () => VisibleRecords::query($parent[1])->orderByDesc('id')->limit(100)->pluck('code', 'id')->all())
                ->default(fn () => request()->integer($parent[0]) ?: null);
        }
        $table->columns(TableColumns::make(static::$module))->filters($filters)->defaultSort('id', 'desc')
            ->recordActions([ViewAction::make(), EditAction::make(), ActionGroup::make(RecordActions::linked(static::$module))->label('Acciones')])
            ->recordUrl(fn ($record) => static::getUrl('view', ['record' => $record->id]));
        if (static::$module === 'technical_reports') { $table->poll('5s'); }

        return $table;
    }
}
