<?php

namespace MKWebDesign\FilamentWatchdog\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use MKWebDesign\FilamentWatchdog\Models\SecurityAlert;
use MKWebDesign\FilamentWatchdog\Resources\SecurityAlertResource\Pages;
use MKWebDesign\FilamentWatchdog\Resources\SecurityAlertResource\RelationManagers;
use MKWebDesign\FilamentWatchdog\Traits\ConfiguresWatchdogNavigation;

class SecurityAlertResource extends Resource
{
    use ConfiguresWatchdogNavigation;  // <- Deze regel moet erin!

    protected static function getNavigationVisibility(): string
    {
        return 'conditional'; // <- Deze moet 'conditional' zijn, niet 'always'
    }

    protected static function getDefaultSecuritySort(): int
    {
        return 4;
    }


    protected static ?string $model = SecurityAlert::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'Security';
    protected static ?string $navigationLabel = 'Security Alerts';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('alert_type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('severity')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ])
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'new' => 'New',
                        'acknowledged' => 'Acknowledged',
                        'resolved' => 'Resolved',
                        'false_positive' => 'False Positive',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('metadata')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('alert_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('severity')
                    ->colors([
                        'success' => 'low',
                        'info' => 'medium',
                        'warning' => 'high',
                        'danger' => 'critical',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'new',
                        'info' => 'acknowledged',
                        'success' => 'resolved',
                        'secondary' => 'false_positive',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('severity')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'New',
                        'acknowledged' => 'Acknowledged',
                        'resolved' => 'Resolved',
                        'false_positive' => 'False Positive',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSecurityAlerts::route('/'),
            'create' => Pages\CreateSecurityAlert::route('/create'),
            'view' => Pages\ViewSecurityAlert::route('/{record}'),
            'edit' => Pages\EditSecurityAlert::route('/{record}/edit'),
        ];
    }
}
