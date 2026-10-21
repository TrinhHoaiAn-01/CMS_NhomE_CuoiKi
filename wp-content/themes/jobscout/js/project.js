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
    window.matchMedia('(max-width: 780.98px)').addEventListener('change', closeMenu);
}

document.querySelectorAll('.jsc-sort-form select').forEach(select => select.addEventListener('change', () => select.form.requestSubmit()));


document.querySelectorAll('.jsc-share-button').forEach(button => {
    button.addEventListener('click', async () => {
        const url = button.dataset.shareUrl;
        const status = button.parentElement.querySelector('.jsc-share-status');
        const fallback = button.parentElement.querySelector('.jsc-share-fallback');
        try {
            if (navigator.share) {
                await navigator.share({title: button.dataset.shareTitle, url});
                status.textContent = 'Sharing complete.';
            } else if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(url);
                status.textContent = 'Page link copied.';
                button.textContent = 'Link Copied';
            } else {
                fallback.hidden = false;
                fallback.querySelector('input').focus();
                fallback.querySelector('input').select();
                status.textContent = 'Copy the selected page link to share.';
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                fallback.hidden = false;
                fallback.querySelector('input').focus();
                fallback.querySelector('input').select();
                status.textContent = 'Copy this page link to share.';
            }
        }
    });
});



const photoDialog = document.querySelector('.jsc-gallery-dialog');
const photoOpener = document.querySelector('.jsc-gallery-open');
if (photoDialog && photoOpener) {
    photoOpener.addEventListener('click', () => photoDialog.showModal());
    photoDialog.querySelector('.jsc-gallery-close').addEventListener('click', () => photoDialog.close());
    photoDialog.addEventListener('click', event => { if (event.target === photoDialog) photoDialog.close(); });
    photoDialog.addEventListener('close', () => photoOpener.focus());
    const thumbnails = Array.from(photoDialog.querySelectorAll('[data-photo]'));
    let photoIndex = 0;
    function showPhoto(index) {
        photoIndex = (index + thumbnails.length) % thumbnails.length;
        const target = thumbnails[photoIndex];
        photoDialog.querySelector('.jsc-gallery-large').src = target.dataset.photo;
        photoDialog.querySelector('.jsc-gallery-large').alt = target.dataset.alt;
        thumbnails.forEach((item, i) => item.setAttribute('aria-pressed', String(i === photoIndex)));
    }
    thumbnails.forEach((item, i) => item.addEventListener('click', () => showPhoto(i)));
    photoDialog.addEventListener('keydown', event => {
        if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
            event.preventDefault();showPhoto(photoIndex + (event.key === 'ArrowRight' ? 1 : -1));
        }
    });
    showPhoto(0);
}

