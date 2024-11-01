(function ($) {
    $('.inquiries-form').on('submit', function (event) {
        event.preventDefault();

        let form = $(this);
        let name = form.find('input[name="inquiry_name"]');
        let email = form.find('input[name="inquiry_email"]');
        let message = form.find('textarea[name="inquiry_message"]');
        let submit = form.find('input[type="submit"]');
        let send = submit.val();
        let sending = submit.data('sending');
        let data = {
            'action': 'inquiries_ajax_action',
            'nonce': inquiries_ajax_object.ajax_nonce,
            'name': name.val(),
            'email': email.val(),
            'message': message.val()
        };

        name.attr('disabled', 'disabled');
        email.attr('disabled', 'disabled');
        message.attr('disabled', 'disabled');
        submit.val(sending).attr('disabled', 'disabled');

        $.post(inquiries_ajax_object.ajax_url, data, function (response) {
            name.val('').removeAttr('disabled');
            email.val('').removeAttr('disabled');
            message.val('').removeAttr('disabled');
            submit.val(send).removeAttr('disabled');

            form.find('.response').remove();
            form.prepend('<p class="response ' + response.type + '">' + response.message + '</p>');
        });
    }).delegate('.response', 'click touchstart', function () {
        $(this).fadeOut(function () {
            $(this).remove();
        });
    });
})(jQuery);