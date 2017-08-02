(function($){
	$(document).ready(function(){
		
		 $('#save').click(function() {
            var self = this;
            var data = {
                'action': 'mxp_settings',
                'nonce': MXP_FB2WP.nonce,
                //'key': $(this).data().key,
                'method':'get'
            };
            $.post(ajaxurl, data, function(res) {
                if (res.success) {
                    $('#'+$(self).data().key).hide();
                } else {
                    //Error? That's my problem...
                }
            });
        });
	});
})(jQuery)