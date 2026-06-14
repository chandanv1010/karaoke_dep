(function () {
    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-home-design-tab]');
        if (!button) return;

        var section = button.closest('.home-designs');
        if (!section) return;

        var target = button.getAttribute('data-home-design-tab');
        section.querySelectorAll('[data-home-design-tab]').forEach(function (tab) {
            tab.classList.toggle('active', tab === button);
        });
        section.querySelectorAll('[data-home-design-panel]').forEach(function (panel) {
            panel.classList.toggle('active', panel.getAttribute('data-home-design-panel') === target);
        });
    });
})();
