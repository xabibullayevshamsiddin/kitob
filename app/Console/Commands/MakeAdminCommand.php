<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MakeAdminCommand extends Command
{
    protected $signature = 'user:admin {email}';
    protected $description = 'Foydalanuvchiga toliq admin huquqini beradi';

    public function handle(): int
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->orWhere('username', $email)->first();

        if (!$user) {
            $this->error("Foydalanuvchi topilmadi: {$email}");
            return self::FAILURE;
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        $user->syncRoles(['admin']);
        $user->role = 'admin';
        $user->save();

        $this->info("Foydalanuvchi {$user->name} ({$user->email}) muvaffaqiyatli ADMIN qilindi!");
        return self::SUCCESS;
    }
}
