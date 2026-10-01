

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const navigation = document.getElementById('navigation');
if (navigation) {
    const wide = window.matchMedia('(min-width: 768px)');
    const adapt = () => { navigation.open = wide.matches; };
    adapt();
    wide.addEventListener('change', adapt);
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !wide.matches) {
            navigation.open = false;
            navigation.querySelector('summary').focus();
        }
    });
}
