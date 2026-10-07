/* Shared progressive enhancements. Content and navigation work without JS. */
document.documentElement.classList.add('jsc-js');
const menuToggle = document.querySelector('.jsc-menu-toggle');
const navigation = document.querySelector('.jsc-navigation');
if (menuToggle && navigation) {
    menuToggle.addEventListener('click', () => {
        const expanded = menuToggle.getAttribute('aria-expanded') === 'true';
        menuToggle.setAttribute('aria-expanded', String(!expanded));
        navigation.classList.toggle('is-open', !expanded);
    });
    navigation.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            navigation.classList.remove('is-open');
            menuToggle.setAttribute('aria-expanded', 'false');
            menuToggle.focus();
        }
    });
}
