<?php

namespace MKWebDesign\FilamentWatchdog\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use MKWebDesign\FilamentWatchdog\Models\FileIntegrityCheck;
use MKWebDesign\FilamentWatchdog\Resources\FileIntegrityResource\Pages;
use MKWebDesign\FilamentWatchdog\Resources\FileIntegrityResource\RelationManagers;

class FileIntegrityResource extends Resource
{
    protected static ?string $model = FileIntegrityCheck::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Security';
    protected static ?string $navigationLabel = 'File Integrity';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('file_path')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('file_hash')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('file_size')
                    ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('last_modified')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'clean' => 'Clean',
                        'modified' => 'Modified',
                        'deleted' => 'Deleted',
                        'new' => 'New',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('changes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('file_path')
                    ->searchable(),
                Tables\Columns\TextColumn::make('file_hash')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('file_size')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_modified')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'clean',
                        'warning' => 'modified',
                        'danger' => 'deleted',
                        'info' => 'new',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'clean' => 'Clean',
                        'modified' => 'Modified',
                        'deleted' => 'Deleted',
                        'new' => 'New',
                    ]),
            ])
            ->actions([
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
            'index' => Pages\ListFileIntegrityChecks::route('/'),
            'create' => Pages\CreateFileIntegrityCheck::route('/create'),
            'edit' => Pages\EditFileIntegrityCheck::route('/{record}/edit'),
        ];
    }
}
