<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample events
        Event::create([
            'name' => 'Conferência de Tecnologia 2025',
            'description' => 'Uma conferência anual sobre as últimas tendências em tecnologia, desenvolvimento de software e inovação digital.',
            'event_date' => now()->addMonths(2),
            'is_active' => true,
        ]);

        Event::create([
            'name' => 'Workshop de Laravel Avançado',
            'description' => 'Workshop intensivo de 2 dias sobre técnicas avançadas de desenvolvimento com Laravel, incluindo performance, arquitetura e boas práticas.',
            'event_date' => now()->addMonths(1)->addDays(15),
            'is_active' => true,
        ]);

        Event::create([
            'name' => 'Meetup de Desenvolvedores',
            'description' => 'Encontro mensal da comunidade de desenvolvedores para networking, palestras relâmpago e troca de experiências.',
            'event_date' => now()->addDays(20),
            'is_active' => true,
        ]);

        Event::create([
            'name' => 'Hackathon 2025',
            'description' => 'Competição de 48 horas para desenvolver soluções inovadoras para problemas reais. Prêmios para os melhores projetos.',
            'event_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        Event::create([
            'name' => 'Evento Passado (Inativo)',
            'description' => 'Este é um evento de exemplo que já ocorreu e está inativo.',
            'event_date' => now()->subMonths(1),
            'is_active' => false,
        ]);
    }
}
