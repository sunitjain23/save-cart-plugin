jQuery(document).ready( function($) {
    $('.generate_cart').click( function(e) {
       e.preventDefault();
       const user_id = $('#save_user_id').val();
       $.ajax({
            type : 'post',
            dataType : "json",
            url : saveCartParams.ajaxUrl,
            data : {action: "save_cart_ajx", user_id : user_id, nonce: saveCartParams.nonce},
            success: function(response) {
                $('#show_url').html(response.message);
            }
        })  
    });
});
