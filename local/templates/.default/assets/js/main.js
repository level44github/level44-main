$(function () {
    $(document).on("submit", ".js-search__line", function (event) {
        if ($(this).find("[name='q']").val().length <= 0) {
            event.preventDefault();
        }
    })

    let vh = window.innerHeight * 0.01;
    // Then we set the value in the --vh custom property to the root of the document
    document.documentElement.style.setProperty('--vh', `${vh}px`);

    $(document).on('click', '.nav-item .has-submenu', function (e) {
        e.preventDefault();
        $(this).siblings('.nav.submenu').slideToggle();
    });

    $('.form-radio-group [name="sort"]').on('change', function (e) {
        const sortCookieName = $(this).attr('data-sort-cookie-name');

        document.cookie = `${sortCookieName}=${encodeURIComponent(e.target.value)}; path=/`;
        window.location.reload();
    })

    const dropdownItem = $('[data-dropdown] [name="sort"]:checked + span');
    if (dropdownItem?.length) {
        $('[data-dropdown] [name="sort"]').closest('[data-dropdown]').find('.dropdown__title').text(dropdownItem.text());
    }
})

