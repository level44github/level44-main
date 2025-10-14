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

    BX.Vue.event.$on('BXmakerAuthuserphoneSimpleAjaxResponse', (data) => {
        const response = data.result?.response;
        const loginModal = $('#login-modal');
        const backurl = loginModal.data('backurl')

        if (response?.type === 'AUTH' && backurl) {
            loginModal.modal('hide');
            window.location.href = backurl;
        }
    });



    // Динамическое применение sticky для product__info
    const productInfo = $('.product__info');

    if (productInfo.length) {
        let isSticky = false;

        // Убираем sticky по умолчанию
        productInfo.css('position', 'relative');
        productInfo.css('top', '0px');

        // Сохраняем начальную позицию и высоту блока (до применения sticky)
        /*const initialOffset = productInfo.offset().top;
        const initialHeight = productInfo.outerHeight();
        const initialBottom = initialOffset + initialHeight;

        $(window).on('scroll', function() {
            const scrollTop = $(window).scrollTop();

            // Sticky применяется когда нижняя граница блока достигла позиции sticky (top: 60px)
            if (!isSticky && scrollTop + 60 >= initialBottom) {
                isSticky = true;
                productInfo.css({
                    'position': 'sticky',
                    'top': '83px'
                });
            }
            // Когда скроллим обратно вверх, убираем sticky с небольшим буфером
            else if (isSticky && scrollTop + 60 < initialBottom - 100) {
                isSticky = false;
                productInfo.css('position', 'relative');
                productInfo.css('top', '0px');
            }
        });*/
    }
})

