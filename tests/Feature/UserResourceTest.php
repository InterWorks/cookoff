<?php

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

beforeEach(function () {
    $this->artisan('migrate:fresh');

    $this->adminUser = User::factory()->create([
        'email' => 'admin@interworks.com',
    ]);
    $this->actingAs($this->adminUser);
});

test('can render user list page', function () {
    $users = User::factory()->count(3)->create();

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords($users)
        ->assertSuccessful();
});

test('can search users by name', function () {
    $user1 = User::factory()->create(['name' => 'John Doe']);
    $user2 = User::factory()->create(['name' => 'Jane Smith']);

    Livewire::test(ListUsers::class)
        ->searchTable('John')
        ->assertCanSeeTableRecords([$user1])
        ->assertCanNotSeeTableRecords([$user2]);
});

test('can search users by email', function () {
    $user1 = User::factory()->create(['email' => 'john@example.com']);
    $user2 = User::factory()->create(['email' => 'jane@example.com']);

    Livewire::test(ListUsers::class)
        ->searchTable('john@example.com')
        ->assertCanSeeTableRecords([$user1])
        ->assertCanNotSeeTableRecords([$user2]);
});

test('can create user', function () {
    $userData = [
        'name'     => 'Test User',
        'email'    => 'test@interworks.com',
        'password' => 'password123',
        'timezone' => 'America/New_York',
    ];

    Livewire::test(CreateUser::class)
        ->fillForm($userData)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'name'     => 'Test User',
        'email'    => 'test@interworks.com',
        'timezone' => 'America/New_York',
    ]);

    $user = User::where('email', 'test@interworks.com')->first();
    expect($user->password)->not->toBe('password123');
    expect(password_verify('password123', $user->password))->toBeTrue();
});

test('can edit user', function () {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm([
            'name'     => 'Updated Name',
            'email'    => $user->email,
            'timezone' => 'America/Chicago',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh())
        ->name->toBe('Updated Name')
        ->timezone->toBe('America/Chicago');
});

test('can edit user without changing password', function () {
    $user = User::factory()->create();
    $originalPassword = $user->password;

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm([
            'name'     => 'Updated Name',
            'email'    => $user->email,
            'password' => '',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh())
        ->name->toBe('Updated Name')
        ->password->toBe($originalPassword);
});

test('can reset user password', function () {
    $user = User::factory()->create();
    $originalPassword = $user->password;

    Livewire::test(ListUsers::class)
        ->callTableAction('resetPassword', $user)
        ->assertNotified();

    expect($user->fresh()->password)->not->toBe($originalPassword);
});

test('validates required fields on create', function () {
    Livewire::test(CreateUser::class)
        ->fillForm([
            'name'  => '',
            'email' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required', 'email' => 'required', 'password' => 'required']);
});

test('validates email uniqueness', function () {
    $existingUser = User::factory()->create();

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name'     => 'Test User',
            'email'    => $existingUser->email,
            'password' => 'password123',
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique']);
});

test('validates password minimum length', function () {
    Livewire::test(CreateUser::class)
        ->fillForm([
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => '123',
        ])
        ->call('create')
        ->assertHasFormErrors(['password' => 'min']);
});

test('can filter by email verification status', function () {
    $verifiedUser = User::factory()->create(['email_verified_at' => now()]);
    $unverifiedUser = User::factory()->create(['email_verified_at' => null]);

    Livewire::test(ListUsers::class)
        ->filterTable('email_verified_at', true)
        ->assertCanSeeTableRecords([$verifiedUser])
        ->assertCanNotSeeTableRecords([$unverifiedUser]);
});

test('can delete user', function () {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->callAction(DeleteAction::class)
        ->assertSuccessful();

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('unauthenticated user cannot access user creation page', function () {
    auth()->logout();

    $this->get(UserResource::getUrl('create'))
        ->assertRedirect();
});

test('unauthenticated user cannot access user list page', function () {
    auth()->logout();

    $this->get(UserResource::getUrl('index'))
        ->assertRedirect();
});

test('unauthenticated user cannot access user edit page', function () {
    $user = User::factory()->create();
    auth()->logout();

    $this->get(UserResource::getUrl('edit', ['record' => $user]))
        ->assertRedirect();
});

test('authenticated user can access user management pages', function () {
    $user = User::factory()->create();

    $this->get(UserResource::getUrl('index'))
        ->assertSuccessful();

    $this->get(UserResource::getUrl('create'))
        ->assertSuccessful();

    $this->get(UserResource::getUrl('edit', ['record' => $user]))
        ->assertSuccessful();
});
