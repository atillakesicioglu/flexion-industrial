document.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('home-sections-list');
    if (!list) return;

    Sortable.create(list, {
        handle: '.drag-handle',
        animation: 150,
    });
});

