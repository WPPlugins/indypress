<?php

/**
 * get_taxonomy_from_term
 *
 * @param int $term_id
 * @access public
 * @return string name of the taxonomy the term belongs to
 * @global $wpdb
 */
function get_taxonomy_from_term( $term_id ) {
	global $wpdb;
	$res = $wpdb->get_var($wpdb->prepare("SELECT taxonomy FROM $wpdb->term_taxonomy WHERE term_id=%d", $term_id));
	return $res;
}

/**
 * get_taxonomies_from_terms
 *
 * @param array $term_ids array of id
 * @access public
 * @return array every element is a string: a taxonomy name such that there is an element in $term_ids
 * 				 that belongs to it
 * @global $wpdb
 */
function get_taxonomies_from_terms( $term_ids ) {
	global $wpdb;
	$res = $wpdb->get_col("SELECT taxonomy FROM $wpdb->term_taxonomy WHERE term_id IN ( " . implode( ',', $term_ids ) . ' )', 0);
	return $res;
}

?>
