<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:mail {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test mail configuration by sending a test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? config('mail.from.address');

        $this->info("Sending test email to: {$email}");

        try {
            Mail::raw('Ceci est un email de test pour vérifier la configuration SMTP Gmail.', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Test de configuration mail - Gestion Réseau MikroTik');
            });

            $this->info('✅ Email envoyé avec succès !');
            $this->info("Vérifiez votre boîte mail: {$email}");

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de l\'envoi du mail:');
            $this->error($e->getMessage());
        }
    }
}
