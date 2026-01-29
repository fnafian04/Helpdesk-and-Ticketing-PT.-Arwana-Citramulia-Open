<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Blade;

class BladeHelpers
{
    /**
     * Register custom Blade directives
     */
    public static function register()
    {
        // @auth_token
        Blade::directive('auth_token', function () {
            return "<?php echo session('auth_token'); ?>";
        });

        // @user
        Blade::directive('user', function ($expression) {
            return "<?php echo session('user.{$expression}'); ?>";
        });

        // @auth_check
        Blade::directive('auth_check', function () {
            return "<?php if (session('auth_token')): ?>";
        });

        // @endauth_check
        Blade::directive('endauth_check', function () {
            return "<?php endif; ?>";
        });

        // @role('technician')
        Blade::directive('role', function ($expression) {
            return "<?php if (in_array($expression, session('roles', []))): ?>";
        });

        // @endrole
        Blade::directive('endrole', function () {
            return "<?php endif; ?>";
        });

        // @permission('ticket.view')
        Blade::directive('permission', function ($expression) {
            return "<?php if (in_array($expression, session('permissions', []))): ?>";
        });

        // @endpermission
        Blade::directive('endpermission', function () {
            return "<?php endif; ?>";
        });
    }
}
