<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApiKeyResource\Pages;
use App\Models\ApiKey;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class ApiKeyResource extends Resource
{
    protected static ?string $model = ApiKey::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'System';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('API Key Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('A descriptive name for this API key'),

                        Textarea::make('description')
                            ->rows(3)
                            ->helperText('Optional description of what this key is used for'),

                        Toggle::make('is_active')
                            ->default(true)
                            ->helperText('Inactive keys cannot be used for API access'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Permissions & Access')
                    ->schema([
                        Select::make('permissions')
                            ->multiple()
                            ->options(ApiKey::getAvailablePermissions())
                            ->helperText('Select specific permissions or leave empty to allow all endpoints'),

                        DateTimePicker::make('expires_at')
                            ->helperText('Optional expiration date. Leave empty for no expiration')
                            ->seconds(false),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('masked_key')
                    ->label('API Key')
                    ->copyable()
                    ->copyMessage('Key copied to clipboard')
                    ->copyMessageDuration(1500)
                    ->fontFamily('mono'),

                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger' => 'expired',
                    ]),

                TextColumn::make('last_used_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never used'),

                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('No expiration'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),

                Tables\Filters\Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->where('expires_at', '<', now()))
                    ->toggle(),
            ])
            ->actions([
                Action::make('regenerate')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Regenerate API Key')
                    ->modalDescription('This will generate a new API key. The old key will no longer work. Are you sure?')
                    ->action(function (ApiKey $record) {
                        // Generate new key and hash
                        $newKey = 'ck_' . \Illuminate\Support\Str::random(60);
                        $newKeyHash = hash('sha256', $newKey);

                        // Update the record with new key and hash
                        $record->update([
                            'key' => $newKey,
                            'key_hash' => $newKeyHash,
                        ]);

                        Notification::make()
                            ->title('API Key Regenerated')
                            ->body("New Key: {$newKey}")
                            ->success()
                            ->duration(10000)
                            ->send();
                    }),

                Action::make('show_key')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading('API Key')
                    ->modalContent(fn (ApiKey $record) => 
                        $record->key 
                            ? new \Illuminate\Support\HtmlString(
                                '<div style="font-family: monospace; word-break: break-all; padding: 12px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">' . 
                                htmlspecialchars($record->key) . 
                                '</div><p class="mt-4 text-sm text-gray-600">Copy this key now - it won\'t be shown again for security reasons.</p>'
                            )
                            : new \Illuminate\Support\HtmlString('Key not available. This may happen if the key was not properly generated.')
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApiKeys::route('/'),
            'create' => Pages\CreateApiKey::route('/create'),
            'edit' => Pages\EditApiKey::route('/{record}/edit'),
        ];
    }
}
