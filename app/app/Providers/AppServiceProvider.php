<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;


class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //treść maila
       ResetPassword::toMailUsing(function ($notifiable, string $token) {

            $notifiableLocale = method_exists($notifiable, 'preferredLocale')
            ? $notifiable->preferredLocale()
            : null;

            app()->setLocale($notifiableLocale ?: 'pl');

            $frontend = rtrim(config('app.frontend_url'), '/');

            // ✅ Link bezpośrednio do Nuxta (bez backendu)
            $url = $frontend . '/auth/reset-password?' . http_build_query([
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);

            return (new MailMessage)
                // ✅ nagłówek
                ->greeting(__('notifications.reset_password_greeting'))

                // ✅ temat
                ->subject(__('notifications.reset_password_subject'))

                // ✅ treść
                ->line(__('notifications.reset_password_line_1'))

                // ✅ przycisk
                ->action(__('notifications.reset_password_action'), $url)

                // ✅ info o wygaśnięciu
                ->line(__('notifications.reset_password_line_2', [
                    'count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire'),
                ]))

                // ✅ disclaimer
                ->line(__('notifications.reset_password_line_3'))

                // ✅ stopka
                ->salutation(__('notifications.reset_password_salutation'))

                // ✅ fallback link (to usuwa angielski tekst!)
                ->with([
                    'actionText' => __('notifications.reset_password_action'),
                    'actionUrl'  => $url,
                ]);
        });
        //tworzenie super admina
        if (app()->runningInConsole()) {
            Event::listen(MigrationsEnded::class, function () {
                $this->createSuperAdminIfNotExists();
            });
        }
    }

    protected function createSuperAdminIfNotExists(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $name = env('ADMIN_NAME', 'System Administrator');
        $password = env('ADMIN_PASSWORD', 'admin123');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            // rola super-admin
            $role = Role::firstOrCreate(['name' => 'super-admin']);
            $user->assignRole($role);

            $this->app['log']->info("✅ Super admin utworzony: {$email}");
        } else {
            $this->app['log']->info("ℹ️ Super admin już istnieje: {$email}");
        }

        // token Sanctum (tworzymy, jeśli brak o danej nazwie)
        $this->ensureSuperAdminToken($user);
    }

    protected function ensureSuperAdminToken(User $user): void
    {
        $tokenName = env('ADMIN_TOKEN_NAME', 'superadmin-token');
        $abilitiesEnv = (string) env('ADMIN_TOKEN_ABILITIES', '*');

        // parsowanie abilities z .env (np. "orders:read,orders:create")
        $abilities = array_filter(array_map('trim', explode(',', $abilitiesEnv)));
        if (empty($abilities)) {
            $abilities = ['*'];
        }

        $exists = $user->tokens()
            ->where('name', $tokenName)
            ->exists();

        if (!$exists) {
            $plain = $user->createToken($tokenName, $abilities)->plainTextToken;

            // zapisz token do pliku w storage/app/
            $path = storage_path("app/{$tokenName}.txt");
            File::put($path, $plain);

            $this->app['log']->info("🔐 Token superadmina utworzony i zapisany do: storage/app/{$tokenName}.txt");
        } else {
            $this->app['log']->info("🔑 Token '{$tokenName}' już istnieje dla superadmina – nie tworzę nowego.");
        }
    }
}
