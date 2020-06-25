<?php

class GCPCExport {
    public static function gcpc_do_export() {
        check_ajax_referer( 'arc_download' );
        $filepath = 'C:\Users\JorieSieck\Local Sites\newscratch\app\public\wp-content\plugins\gc-prac-code\assets\lib\testtext.txt';
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=\"$filepath\"");
        header("Content-Type: text/plain; charset=UTF-8");
        $buffer_length = ob_get_length(); //length or false if no buffer
		if ( $buffer_length > 1 ) {
			ob_clean();
        }
        readfile($filepath);
        exit;
        // // start buffer
        // ob_start();

        // // set headers, depending on type of export
        // $filepath = 'C:\Users\JorieSieck\Local Sites\newscratch\app\public\wp-content\plugins\gc-prac-code\assets\lib\testtext.txt';
        // if(file_exists($filepath)) {
        //     if(!headers_sent()) {
        //         header("Content-Description: File Transfer");
        //         header("Content-Disposition: attachment; filename=\"$filepath\"");
        //         header("Content-Type: text/plain; charset=UTF-8");
        //         header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        //         header("Cache-Control: post-check=0, pre-check=0", false);
        //         header("Pragma: no-cache");
        
        //         // flush output - ob_end_clean() is the same as ob_flush() + flush()
        //         ob_end_clean();
        
        //         // get the data
        //         readfile($filepath);

        //         ob_end_clean();
        
        //         // return headers_list();
        //         exit;
        //     } else {
        //         return headers_list();
        //     }
        // } else {
        //     return "$filepath does not exist, for some reason";
        // }
		
        // die;
    }

    public static function gcpc_export_page() {
        ?>
        <script type="text/javascript">
            ( function( $ ){

            $('#download-button').click( function(){

                event.preventDefault();
                const comp_num = document.querySelector('#arc-select').value;
                var url = admin_url( 'admin-ajax.php' ) + '?action=gcpc_do_export&_wpnonce=<?php echo wp_create_nonce( 'arc_download' ); ?>';
				document.location.href = url;
                // $.ajax({
                //     type : 'post',
                //     dataType : 'json',
                //     url : arc_globals.ajax_url,
                //     data : {
                //         action: 'gcpc_do_export',
                //         comp_num,
                //         _ajax_nonce: arc_globals.nonce
                //     },
                //     error: function(response) {
                //         console.log(response);
                //     },
                //     success: function( response ) {
                //         console.log(response);
                //         // if( 'success' == response.type ) {
                //         //     // alert('yay!');
                //         //     console.log(response['data']);
                //         // }
                //         // else {
                //         //     alert( 'Something went wrong!' );
                //         // }
                //     }
                // })

            } );

            } )( jQuery );
        </script>
        <style>
            button {
            background-color: #333;
            border: 0;
            cursor: pointer;
            padding: 16px 24px;
            white-space: normal;
            width: auto;
            text-decoration: none;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            }
        </style>
        <body>
            <div class="wrap">
                <h2><?php esc_html_e( 'ARC Assessment Data Export', 'arc-jquery-ajax' ); ?></h2>
            <h3>Select which competency you would like to export:</h3>
            <select name="arc-select" id="arc-select">
            <option value="all">All</option>
            <option value="1">Competency 1</option>
            <option value="2">Competency 2</option>
            <option value="3">Competency 3</option>
            <option value="4">Competency 4</option>
            <option value="5">Competency 5</option>
            <option value="6">Competency 6</option>
            <option value="7">Competency 7</option>
            <option value="8">Competency 8</option>
            <option value="9">Competency 9</option>
            <option value="10">Competency 10</option>
            <option value="11">Competency 11</option>
            <option value="12">Competency 12</option>
            </select>
            <button id="download-button" class="arc-download">Download</button>
            </div>
        </body>
        <?php
    }
}