<?php

namespace Tests\Feature;

use App\Livewire\SecretArchive\Index;
use App\Models\SecretArchiveFolder;
use App\Models\User;
use App\Support\MenuPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecretArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_manager_can_create_a_pin_folder_and_upload_private_files(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $this->actingAs($this->userWithPermissions([
            'view accounts maktoom',
            'view secret archive maktoom',
            'manage secret archive maktoom',
        ]));

        $component = Livewire::test(Index::class)
            ->set('folderName', 'Personnel records')
            ->set('folderPin', '0042')
            ->call('createFolder')
            ->assertHasNoErrors();

        $folder = SecretArchiveFolder::query()->where('name', 'Personnel records')->firstOrFail();
        $this->assertNotSame('0042', $folder->secret_encrypted);
        $this->assertSame('0042', Crypt::decryptString($folder->secret_encrypted));
        $component->assertSet('activeFolderId', $folder->id);

        $component
            ->set('archiveFiles', [UploadedFile::fake()->createWithContent('contract.txt', 'private archive content')])
            ->call('uploadFiles')
            ->assertHasNoErrors();

        $file = $folder->files()->firstOrFail();
        Storage::disk('local')->assertExists($file->path);
        $this->assertStringStartsWith('secret-archive/'.$folder->id.'/', $file->path);
        $this->assertFalse(Storage::disk('public')->exists($file->path));
    }

    public function test_private_file_requires_the_folder_to_be_unlocked(): void
    {
        Storage::fake('local');
        User::factory()->create(['email' => 'secret-archive-super-admin@example.test']);
        $user = User::factory()->create(['email' => 'secret-archive-viewer@example.test']);
        $user->givePermissionTo([
            Permission::findOrCreate('view accounts maktoom', 'web'),
            Permission::findOrCreate('view secret archive maktoom', 'web'),
        ]);
        $this->assertGreaterThan(1, $user->id);
        $this->actingAs($user);

        $folder = $this->makeFolder('Private documents', '2468');
        $file = $folder->files()->create([
            'path' => 'secret-archive/'.$folder->id.'/private.txt',
            'original_name' => 'private.txt',
            'mime' => 'text/plain',
            'size' => 15,
        ]);
        Storage::disk('local')->put($file->path, 'private content');
        session()->forget('secret_archive.unlocks.'.$folder->id);
        $this->assertNull(session('secret_archive.unlocks.'.$folder->id));

        $this->get(route('secret-archive-files.view', $file->id))->assertStatus(423);

        session()->put('secret_archive.unlocks.'.$folder->id, $folder->password_version);

        $this->get(route('secret-archive-files.view', $file->id))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function test_folder_pin_must_match_before_contents_are_shown(): void
    {
        Storage::fake('local');
        $this->actingAs($this->userWithPermissions([
            'view accounts maktoom',
            'view secret archive maktoom',
        ]));

        $folder = $this->makeFolder('PIN protected', '2468');
        $file = $folder->files()->create([
            'path' => 'secret-archive/'.$folder->id.'/visible-after-unlock.txt',
            'original_name' => 'visible-after-unlock.txt',
            'mime' => 'text/plain',
            'size' => 14,
        ]);
        Storage::disk('local')->put($file->path, 'private content');

        Livewire::test(Index::class)
            ->call('openFolder', $folder->id)
            ->set('unlockPin', '0000')
            ->call('unlockFolder')
            ->assertHasErrors('unlockPin')
            ->set('unlockPin', '2468')
            ->call('unlockFolder')
            ->assertHasNoErrors()
            ->assertSee('visible-after-unlock.txt');
    }

    public function test_archive_branch_permissions_do_not_cross_between_avani_and_maktoom(): void
    {
        $this->actingAs($this->userWithPermissions([
            'view accounts avani',
            'view secret archive avani',
        ]));

        $this->assertTrue(MenuPermissions::canSee((object) ['slug' => 'secret-archive']));

        Livewire::test(Index::class)
            ->assertSet('account', 'avani')
            ->set('account', 'maktoom')
            ->assertForbidden();
    }

    public function test_user_id_one_can_view_private_files_without_unlocking(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['email' => 'secret-archive-root@example.test']);
        $this->assertSame(1, $admin->id);
        $this->actingAs($admin);

        $folder = $this->makeFolder('Root archive', '8642');
        $file = $folder->files()->create([
            'path' => 'secret-archive/'.$folder->id.'/root.txt',
            'original_name' => 'root.txt',
            'mime' => 'text/plain',
            'size' => 12,
        ]);
        Storage::disk('local')->put($file->path, 'root content');

        $this->get(route('secret-archive-files.view', $file->id))->assertOk();
    }

    public function test_user_id_one_can_reveal_and_change_a_folder_pin_without_entering_it(): void
    {
        $admin = $this->userWithPermissions([], 1);
        $this->actingAs($admin);
        $folder = $this->makeFolder('Admin archive', '1357');

        Livewire::test(Index::class)
            ->call('revealFolderPin', $folder->id)
            ->assertSet('revealedFolderPin', '1357')
            ->set('newFolderPin', '9753')
            ->call('changeFolderPin', $folder->id)
            ->assertHasNoErrors();

        $this->assertSame('9753', Crypt::decryptString($folder->fresh()->secret_encrypted));
        $this->assertSame(2, $folder->fresh()->password_version);
    }

    public function test_other_admin_accounts_cannot_reveal_a_folder_pin_without_id_one(): void
    {
        $user = $this->userWithPermissions([
            'view accounts maktoom',
            'view secret archive maktoom',
            'manage secret archive maktoom',
        ]);
        $user->setRelation('roles', collect([new Role(['name' => 'Admin', 'guard_name' => 'web'])]));
        $this->actingAs($user);
        $folder = $this->makeFolder('Managed archive', '1122');

        Livewire::test(Index::class)
            ->call('revealFolderPin', $folder->id)
            ->assertForbidden();
    }

    private function makeFolder(string $name, string $pin): SecretArchiveFolder
    {
        return SecretArchiveFolder::query()->create([
            'account' => 'maktoom',
            'name' => $name,
            'secret_encrypted' => Crypt::encryptString($pin),
            'password_version' => 1,
        ]);
    }

    private function userWithPermissions(array $permissions, ?int $id = null): User
    {
        $user = User::factory()->make();
        $user->setRelation('roles', collect());

        if ($id !== null) {
            $user->setAttribute('id', $id);
        }

        Gate::before(fn ($authenticatedUser, $ability) => $authenticatedUser === $user && in_array($ability, $permissions, true)
            ? true
            : null);

        return $user;
    }
}
