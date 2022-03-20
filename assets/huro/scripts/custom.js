$(document).ready(function () {
    $(document).on('click', 'a.delete', function () {
        $(this).closest('.message').fadeOut();
    });
});
