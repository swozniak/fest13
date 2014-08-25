<?php

add_action( 'widgets_init', 'vfb_pro_register_widgets' );

function vfb_pro_register_widgets() {
	register_widget( 'VisualFormBuilder_Pro_Widget' );
}

/**
 * Class that builds our Import page
 *
 * @since 1.7
 */
class VisualFormBuilder_Pro_Widget extends WP_Widget {
	public function __construct(){
		global $wpdb;

		/* Setup global database table names */
		$this->field_table_name 	= $wpdb->prefix . 'vfb_pro_fields';
		$this->form_table_name 		= $wpdb->prefix . 'vfb_pro_forms';
		$this->entries_table_name 	= $wpdb->prefix . 'vfb_pro_entries';

		$args = array(
			'classname' 	=> 'vfb_pro_widget_class',
			'description' 	=> 'Visual Form Builder Pro widget'
		);

		$this->WP_Widget( 'vfb_widget', 'Visual Form Builder Pro', $args );
	}

	public function form( $instance ) {
		global $wpdb;

		// Query to get all forms
		$order = sanitize_sql_orderby( 'form_id ASC' );
		$where = apply_filters( 'vfb_pre_get_forms_widget', '' );
		$forms = $wpdb->get_results( "SELECT * FROM $this->form_table_name WHERE form_status != 'trash' $where ORDER BY $order" );

		$instance = wp_parse_args( (array) $instance );
		$title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'id' ); ?>"><?php _e( 'Form to display:', 'visual-form-builder' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'id' ); ?>" name="<?php echo $this->get_field_name( 'id' ); ?>" class="widefat">
		<?php
			foreach ( $forms as $form ) {
				echo sprintf(
					'<option value="%1$d" id="%2$s"%3$s>%1$d - %4$s</option>',
					absint( $form->form_id ),
					esc_html( $form->form_key ),
					selected( $form->form_id, $instance['id'], 1 ),
					wp_specialchars_decode( esc_html( stripslashes( $form->form_title ) ), ENT_QUOTES )
				);
			}
		?>
		</select>
		<?php
	}

	public function widget( $args, $instance ) {
		extract( $args );

		$form_id = absint( $instance['id'] );

		echo $before_widget;

		// Title
		if ( !empty( $instance['title'] ) )
			echo $args['before_title'] . $instance['title'] . $args['after_title'];

		// Print the output
		echo do_action( 'vfb_pro', array( 'id' => $form_id ) );

		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['id'] = !empty( $new_instance['id'] ) ? absint( $new_instance['id'] ) : '';
		$instance['title'] = !empty( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}
