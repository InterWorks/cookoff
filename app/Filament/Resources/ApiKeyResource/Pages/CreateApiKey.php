<?php

namespace App\Filament\Resources\ApiKeyResource\Pages;

use App\Filament\Resources\ApiKeyResource;
use App\Models\ApiKey;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateApiKey extends CreateRecord
{
    protected static string $resource = ApiKeyResource::class;

    protected function handleRecordCreation(array $data): ApiKey
    {
        $apiKey = ApiKey::generate(
            $data['name'],
            $data['description'] ?? null,
            $data['permissions'] ?? [],
            $data['expires_at'] ?? null
        );

        $apiKey->update([
            'is_active' => $data['is_active'] ?? true,
        ]);

        // Show notification with the generated key
        Notification::make()
            ->title('API Key Created Successfully')
            ->body("Key: {$apiKey->key}")
            ->success()
            ->duration(10000)
            ->send();

        return $apiKey;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
