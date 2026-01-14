composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

i use Filament Admin for admin dashboard and Laravel Jetstream for login register it will auto work on multiple user




for asdd new permission :)


php artisan tinker

use Spatie\Permission\Models\Permission;
Permission::create(['name' => 'delete posts']);

use Spatie\Permission\Models\Role;
$role = Role::findByName('agent');
$role->givePermissionTo('delete posts');
