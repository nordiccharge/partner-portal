import preset from './vendor/filament/support/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './resources/**/*.blade.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './app/Filament/**/*.php',
        './resources/views/filament/forms/components/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ]
}
