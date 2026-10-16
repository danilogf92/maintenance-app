<?php

namespace App\Modules\ReportTemplates\UI;

use App\Modules\ReportTemplates\Actions\DuplicateReportTemplate;
use App\Modules\ReportTemplates\Configuration\Permissions;
use App\Modules\ReportTemplates\Models\ReportTemplate;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportTemplateResource extends Resource
{
    protected static ?string $model = ReportTemplate::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $modelLabel = 'plantilla de informe';
    protected static ?string $pluralModelLabel = 'Plantillas de informes';
    protected static ?string $slug = 'report-templates';

    public static function canViewAny(): bool
    {
        return Permissions::allows(auth()->user());
    }

    public static function canCreate(): bool { return static::canViewAny(); }
    public static function canEdit($record): bool { return static::canViewAny(); }
    public static function canDelete($record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([TextInput::make('name')->label('Nombre')->required()->maxLength(120),
            Textarea::make('description')->label('Descripción')->maxLength(1000)]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Plantilla')->searchable()->sortable(),
            TextColumn::make('published_version_id')->label('Estado')->formatStateUsing(fn ($state) => $state ? 'Publicada' : 'Borrador')->placeholder('Borrador'),
            TextColumn::make('updated_at')->label('Modificada')->dateTime('d/m/Y H:i')->sortable(),
        ])->recordActions([EditAction::make(), Action::make('duplicate')->label('Duplicar')
            ->icon('heroicon-o-document-duplicate')->action(function (ReportTemplate $record) {
                $copy = app(DuplicateReportTemplate::class)->execute($record);
                return redirect(static::getUrl('edit', ['record' => $copy->id]));
            })])->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListReportTemplates::route('/'),
            'create' => Pages\CreateReportTemplate::route('/create'),
            'edit' => Pages\EditReportTemplate::route('/{record}/edit')];
    }
}
