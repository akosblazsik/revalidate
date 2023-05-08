jQuery(document).ready(function ($) {
    const nonce = $('#revalidate_nonce_field').val();

    function displayAdminNotice(type, message) {
        const notice = $(`<div class="notice notice-${type} is-dismissible"><p>${message}</p></div>`);
    
        const pageTitleAction = $('a.page-title-action');
        
        if (pageTitleAction.length) {
            pageTitleAction.last().after(notice);
        } else {
            $('.wrap h1').after(notice);
        }
    
        setTimeout(() => {
            notice.fadeOut('slow', () => notice.remove());
        }, 3000);
    }
    

    function revalidate() {
        console.log("revalidate", revalidate_params);

        $.ajax({
            url: revalidate_params.ajax_url,
            type: 'POST',
            data: {
                action: 'revalidate',
                security: revalidate_params.nonce,
                nonce: revalidate_params.nonce,
                slug: revalidate_params.slug,
            },
            success: function (response) {
                // Handle the success response
                console.log(response);
                if (response.success) {
                    displayAdminNotice('success', response.data.message);
                } else {
                    displayAdminNotice('error', response.data.message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // Handle the error
                console.error(textStatus, errorThrown);
                displayAdminNotice('error', 'An error occurred: ' + errorThrown);
            },
        });
    }

    revalidate();
});
