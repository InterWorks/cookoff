<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContestResource\Pages;
use App\Filament\Resources\ContestResource\RelationManagers;
use App\Models\Contest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContestResource extends Resource
{
    protected static ?string $model = Contest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * Form fields for creating and updating records.
     *
     * @param Form $form Form object for the resource.
     *
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->columnSpanFull()
                    ->required(),

            ]);
    }

    /**
     * Table columns and filters.
     *
     * @param Table $table Table object for the resource.
     *
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(100)
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Returns relation manager relations
     *
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            RelationManagers\EntriesRelationManager::class,
            RelationManagers\RatingFactorsRelationManager::class,
        ];
    }

    /**
     * Returns the pages available for the resource
     *
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListContests::route('/'),
            'create' => Pages\CreateContest::route('/create'),
            'edit'   => Pages\EditContest::route('/{record}/edit'),
        ];
    }
}
