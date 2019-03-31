<div class="wrap">
	<h2>
		<?php _e( 'ACL Manager', 'tz-portfolio' ) ?>
	</h2>

	<?php if ( ! empty( $_GET['msg'] ) ) {
		switch( $_GET['msg'] ) {
			case 'd':
				echo '<div id="message" class="updated fade"><p>' . __( 'User Role <strong>Deleted</strong> Successfully.', 'tz-portfolio' ) . '</p></div>';
				break;
		}
	} ?>

	<form action="" method="get" name="um-roles" id="um-roles" style="float: left;margin-right: 10px;">
		<input type="hidden" name="page" value="mp_roles" />
		<?php $this->list_table->display(); ?>
	</form>
</div>