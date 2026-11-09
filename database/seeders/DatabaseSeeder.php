<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Schedule;
use App\Models\ShiftTemplate;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Файлы аватаров лежат в public/assets/img; в БД хранится только публичный путь.
        $employees = [
            ['first_name' => 'Алина', 'last_name' => 'Кузнецова', 'position' => 'агент', 'avatar_url' => '/assets/img/avatar-alina-kuznetsova.png'],
            ['first_name' => 'Тимур', 'last_name' => 'Волков', 'position' => 'агент', 'avatar_url' => '/assets/img/avatar-timur-volkov.png'],
            ['first_name' => 'Виктория', 'last_name' => 'Морозова', 'position' => 'агент', 'avatar_url' => '/assets/img/avatar-victoria-morozova.png'],
            ['first_name' => 'Артём', 'last_name' => 'Соколов', 'position' => 'агент', 'avatar_url' => '/assets/img/avatar-artem-sokolov.png'],
            ['first_name' => 'Полина', 'last_name' => 'Лебедева', 'position' => 'агент', 'avatar_url' => '/assets/img/avatar-polina-lebedeva.png'],
            ['first_name' => 'Кирилл', 'last_name' => 'Новиков', 'position' => 'агент', 'avatar_url' => '/assets/img/avatar-kirill-novikov.png'],
            ['first_name' => 'Дарья', 'last_name' => 'Ерохина', 'position' => 'агент', 'avatar_url' => '/assets/img/avatar-darya-erokhina.png'],
            ['first_name' => 'Егор', 'last_name' => 'Гаврилов', 'position' => 'агент', 'avatar_url' => '/assets/img/avatar-egor-gavrilov.png'],
        ];

        foreach ($employees as $employee) {
            Employee::query()->create($employee);
        }

        ShiftTemplate::query()->insert([
            [
                'name' => 'Утро',
                'start_time' => '09:00',
                'end_time' => '16:00',
                'color' => 'cornflower',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'День',
                'start_time' => '12:00',
                'end_time' => '18:00',
                'color' => 'mint',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Вечер',
                'start_time' => '16:00',
                'end_time' => '22:00',
                'color' => 'lavanda',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Schedule::query()->create([
            'title' => 'ТЦ Метрополис',
            'address' => 'Ходынский б-р, 4',
            'status' => 'published',
            'employees_count' => 24,
            'progress' => 91,
            'period_end' => '2024-08-30',
        ]);

        Schedule::query()->create([
            'title' => 'ТЦ Авиапарк',
            'address' => 'Ходынский б-р, 4',
            'status' => 'draft',
            'employees_count' => 18,
            'progress' => 45,
            'period_end' => '2024-09-15',
        ]);

        Schedule::query()->create([
            'title' => 'ТЦ Европейский',
            'address' => 'пл. Киевского Вокзала, 2',
            'status' => 'draft',
            'employees_count' => 12,
            'progress' => 20,
            'period_end' => '2024-09-20',
        ]);
    }
}
