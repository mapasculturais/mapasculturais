(($) => {
    $(() => {
        $('.js-locked-fields input').on('change', () => {
            const $form = $('.js-locked-fields')
            let fields = $(".js-locked-fields input:checkbox:checked").map(function() {
                return $(this).val();
            }).get(); // <----

            $('#locked-fields-input').editable('setValue', fields.length ? fields : '')

        })
    })
})(jQuery)
