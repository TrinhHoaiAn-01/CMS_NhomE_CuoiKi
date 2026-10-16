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
// Keep the disclosure state consistent across breakpoints and pointer dismissal.
if (menuToggle && navigation) {
    const closeMenu = () => {
        menuToggle.setAttribute('aria-expanded', 'false');
        navigation.classList.remove('is-open');
    };
    document.addEventListener('click', event => {
        if (!navigation.contains(event.target) && !menuToggle.contains(event.target)) closeMenu();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && menuToggle.getAttribute('aria-expanded') === 'true') {
            closeMenu();
            menuToggle.focus();
        }
    });
    window.matchMedia('(max-width: 700px)').addEventListener('change', closeMenu);
}

document.querySelectorAll('.jsc-sort-form select').forEach(select => select.addEventListener('change', () => select.form.requestSubmit()));
