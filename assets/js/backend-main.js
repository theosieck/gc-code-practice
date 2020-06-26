( function( $ ){

    $('#download-button').click( function(){

        event.preventDefault();
        const comp_num = document.querySelector('#arc-select').value;
        $.ajax({
            type : 'post',
            dataType : 'json',
            url : arc_globals.ajax_url,
            data : {
                action: 'gcac_do_export',
                comp_num,
                _ajax_nonce: arc_globals.nonce
            },
            error: function(response) {
                console.log('error: ');
                console.log(response);
            },
            success: function( response ) {
                console.log(response);
                // if( 'success' == response.type ) {
                //     // alert('yay!');
                //     console.log(response['data']);
                // }
                // else {
                //     alert( 'Something went wrong!' );
                // }
            }
        })

    } );

} )( jQuery );