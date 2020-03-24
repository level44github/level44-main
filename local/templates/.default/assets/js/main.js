$(function () {
    $(document).on("submit", ".js-search__line", function (event) {
        if ($(this).find("[name='q']").val().length <= 0) {
            event.preventDefault();
        }
    })

    let vh = window.innerHeight * 0.01;
    // Then we set the value in the --vh custom property to the root of the document
    document.documentElement.style.setProperty('--vh', `${vh}px`);
})

