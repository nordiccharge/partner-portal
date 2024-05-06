import preset from './vendor/filament/support/tailwind.config.preset'
const colors = require('tailwindcss/colors')
export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './resources/views/installer/**/*.blade.php',
        './resources/views/installer/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                ...colors
            }
        }
    }
}
