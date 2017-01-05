<p><?php printf( __( 'To enquire for this restaurant <strong>email your details to</strong> <a class="restaurant_application_email" href="mailto:%1$s%2$s">%1$s</a>', 'wp-review-restaurant' ), $enquire->email, '?subject=' . rawurlencode( $enquire->subject ) ); ?></p>

<p>
	<?php _e( 'Enquire using webmail: ', 'wp-review-restaurant' ); ?>

	<a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?php echo $enquire->email; ?>&su=<?php echo urlencode( $enquire->subject ); ?>" target="_blank" class="restaurant_application_email">Gmail</a> / 
	
	<a href="http://webmail.aol.com/Mail/ComposeMessage.aspx?to=<?php echo $enquire->email; ?>&subject=<?php echo urlencode( $enquire->subject ); ?>" target="_blank" class="restaurant_application_email">AOL</a> / 
	
	<a href="http://compose.mail.yahoo.com/?to=<?php echo $enquire->email; ?>&subject=<?php echo urlencode( $enquire->subject ); ?>" target="_blank" class="restaurant_application_email">Yahoo</a> / 
	
	<a href="http://mail.live.com/mail/EditMessageLight.aspx?n=&to=<?php echo $enquire->email; ?>&subject=<?php echo urlencode( $enquire->subject ); ?>" target="_blank" class="restaurant_application_email">Outlook</a>

</p>