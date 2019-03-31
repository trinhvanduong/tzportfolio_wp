<div class="tp-admin-metabox">
	<?php $role = $object['data'];
	$this->__construct( array(
		'class'		=> 'tp-role-edit',
		'prefix_id'	=> 'role',
		'fields' => array(
			array(
				'id'		    => 'edit_tp_posts',
				'type'		    => 'checkbox',
				'label'		    => __( 'Allowed To edit Posts', 'tz-portfolio' ),
				'description'	=> __( 'Users in this group is allowed to edit own Posts on the site.', 'tz-portfolio' ),
				'value'		    => ! empty( $role['edit_tp_posts'] ) ? $role['edit_tp_posts'] : 0,
			),
			array(
				'id'		    => 'edit_others_tp_posts',
				'type'		    => 'checkbox',
				'label'		    => __( 'Allowed To edit others Posts', 'tz-portfolio' ),
				'description'	=> __( 'Users in this group is allowed to edit other posts on the site.', 'tz-portfolio' ),
				'value'		    => ! empty( $role['edit_others_tp_posts'] ) ? $role['edit_others_tp_posts'] : 0,
			),
			array(
				'id'		    => 'edit_private_tp_posts',
				'type'		    => 'checkbox',
				'label'		    => __( 'Allowed To edit private posts', 'tz-portfolio' ),
				'description'	=> __( 'Users in this group is allowed to edit private posts on the site.', 'tz-portfolio' ),
				'value'		    => ! empty( $role['edit_private_tp_posts'] ) ? $role['edit_private_tp_posts'] : 0,
			),
			array(
				'id'		    => 'edit_published_tp_posts',
				'type'		    => 'checkbox',
				'label'		    => __( 'Allowed To edit published posts', 'tz-portfolio' ),
				'description'	=> __( 'Users in this group is allowed to edit published posts on the site.', 'tz-portfolio' ),
				'value'		    => ! empty( $role['edit_published_tp_posts'] ) ? $role['edit_published_tp_posts'] : 0,
			),
			array(
				'id'		    => 'publish_tp_posts',
				'type'		    => 'checkbox',
				'label'		    => __( 'Allowed To publish posts', 'tz-portfolio' ),
				'description'	=> __( 'Users in this group is allowed to publish post on the site.', 'tz-portfolio' ),
				'value'		    => ! empty( $role['publish_tp_posts'] ) ? $role['publish_tp_posts'] : 0,
			),
			array(
				'id'		    => 'read_private_tp_posts',
				'type'		    => 'checkbox',
				'label'		    => __( 'Allowed To read private posts', 'tz-portfolio' ),
				'description'	=> __( 'Users in this group is allowed to read private posts on the site.', 'tz-portfolio' ),
				'value'		    => ! empty( $role['read_private_tp_posts'] ) ? $role['read_private_tp_posts'] : 0,
			),
			array(
				'id'		    => 'delete_tp_posts',
				'type'		    => 'checkbox',
				'label'		    => __( 'Allowed To delete Posts', 'tz-portfolio' ),
				'description'	=> __( 'Users in this group is allowed to delete own posts on the site.', 'tz-portfolio' ),
				'value'		    => ! empty( $role['delete_tp_posts'] ) ? $role['delete_tp_posts'] : 0,
			),
			array(
				'id'		    => 'delete_private_tp_posts',
				'type'		    => 'checkbox',
				'label'		    => __( 'Allowed To delete private posts', 'tz-portfolio' ),
				'description'	=> __( 'Users in this group is allowed to delete private posts on the site.', 'tz-portfolio' ),
				'value'		    => ! empty( $role['delete_private_tp_posts'] ) ? $role['delete_private_tp_posts'] : 0,
			),
			array(
				'id'		    => 'delete_published_tp_posts',
				'type'		    => 'checkbox',
				'label'		    => __( 'Allowed To delete published posts', 'tz-portfolio' ),
				'description'	=> __( 'Users in this group is allowed to published posts on the site.', 'tz-portfolio' ),
				'value'		    => ! empty( $role['delete_published_tp_posts'] ) ? $role['delete_published_tp_posts'] : 0,
			),
			array(
				'id'		    => 'delete_others_tp_posts',
				'type'		    => 'checkbox',
				'label'		    => __( 'Allowed To edit delete others posts', 'tz-portfolio' ),
				'description'	=> __( 'Users in this group is allowed to delete others posts on the site.', 'tz-portfolio' ),
				'value'		    => ! empty( $role['delete_others_tp_posts'] ) ? $role['delete_others_tp_posts'] : 0,
			),
		)
	) );
	$this->render_form();
	?>
</div>