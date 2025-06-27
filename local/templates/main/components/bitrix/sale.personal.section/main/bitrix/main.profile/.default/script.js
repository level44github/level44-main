$(function () {
    $(document).on('blur', '.js-form__email, .js-form__phone, .js-form__birthdate', function () {
        $(this).siblings('.invalid-feedback').text(!$(this).val() ? window.fieldRequiredMes : window.fieldIncorrectMes)
    })

    $(document).on('submit', '.profile-form', function (e) {
        $(this).find('.js-form__email, .js-form__phone, .js-form__birthdate').blur();

        if ($(this).find('.is-invalid').length) {
            e.preventDefault();
        }
    })
})