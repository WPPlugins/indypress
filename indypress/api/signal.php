<?php

function indypress_signal_post() {
	$indypress_signal = new indypress_signal();
	add_action( 'indypress_signal_post', array( $indypress_signal, 'signal_post' ) );
}

function indypress_signal_comment() {
	$indypress_signal = new indypress_signal();
	add_action( 'indypress_signal_comment', array( $indypress_signal, 'signal_comment' ) );
}

?>
