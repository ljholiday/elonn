(function () {
    function openModal(name) {
        var modal = document.querySelector('[data-auth-modal="' + name + '"]');
        if (!modal) {
            return false;
        }

        document.querySelectorAll('[data-auth-modal]').forEach(function (item) {
            item.hidden = true;
        });

        modal.hidden = false;
        return true;
    }

    function closeModals() {
        document.querySelectorAll('[data-auth-modal]').forEach(function (item) {
            item.hidden = true;
        });
    }

    document.addEventListener('click', function (event) {
        var opener = event.target.closest('[data-auth-open]');
        if (opener && openModal(opener.getAttribute('data-auth-open'))) {
            event.preventDefault();
            return;
        }

        if (event.target.closest('[data-auth-close]')) {
            closeModals();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModals();
        }
    });
}());
