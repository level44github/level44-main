$(function () {
    $(document).on("submit", ".js-search__line", function (event) {
        if ($(this).find("[name='q']").val().length <= 0) {
            event.preventDefault();
        }
    })
})
