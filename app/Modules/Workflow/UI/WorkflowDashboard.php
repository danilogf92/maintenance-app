<?php

namespace App\Modules\Workflow\UI;

class WorkflowDashboard extends \Filament\Pages\Page
{
    protected string $view = 'workflow.dashboard';
    protected static ?string $title = 'Flujo de trabajo';
    protected static ?string $slug = '';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 0;
}
