<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@motonline.local'],
            [
                'name'     => 'admin',
                'username' => 'admin',
                'email'    => 'admin@motonline.local',
                'password' => Hash::make('Admin12345!'),
                'is_admin' => true,
            ]
        );

        $this->command->info('Admin kullanıcısı oluşturuldu:');
        $this->command->info('  E-posta : admin@motonline.local');
        $this->command->info('  Şifre   : Admin12345!');
        $this->command->warn('Canlı ortama geçmeden önce şifreyi değiştirin!');
    }
}
