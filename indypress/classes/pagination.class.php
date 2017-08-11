<?php

class pagination {

	var $per_page;
	var $page_num;

	function pagination($per_page = 20) {
		$this->per_page = $per_page;
		if( empty( $_GET['paged'] ) )
			$this->page_num = 1;
		else
			$this->page_num = $_GET['paged'];
	}

	function paging( $query, $target, $page) {
		global $wpdb;
		
		$target = $target . '?page=' . $page;
		
		// If the query variable for paging is set and more then one, then use it to determine the page, else it's page 1
		$my_paging = ( isset( $_GET['paged'] ) && $_GET['paged'] > 1 ) ? intval( $_GET['paged'] ) : 1;

		// If page is more than one, create the offset, per page divided by page number(minus one), else no offset.
		$offset = ( $my_paging > 1 ) ? ( $this->per_page * ( $my_paging - 1 ) ) : 0;

		// Your query, with necessary offsets
		$sql = $query;

		//$posts_date = $wpdb->get_results($query);
		$posts_date = mysql_num_rows(mysql_query($sql));

		// Ensures you have a result from the query
		if( !$posts_date || empty( $posts_date ) ) return 0;

		// If the count of the results in the query is more then per page
		if( $posts_date > $this->per_page ) {
		   // Round up the count divided by the amount to show per page
		   $total_pages = ceil( $posts_date / $this->per_page );
		}
		else {
		   // Else it's less or equal to, so there can only be one page (there's not enough to have another page)
		   $total_pages = 1;
		}
		
		if( $total_pages > 1 ) {

			$pn = 2;
	
			if( $this->page_num > $pn+1 ) {
				echo "<a class=\"page-numbers\" href=\"$target&paged=1\">1</a>";
				echo "<span class=\"page-numbers dots\">...</span>";
			}
				
			for( $i=$this->page_num-$pn; $i<=$this->page_num+$pn; $i++) {
				if( ($i)>0 && $i<=$total_pages ) {
					if( $this->page_num==$i )
						echo "<span class=\"page-numbers current\">$i</span>";
					else
						echo "<a class=\"page-numbers\" href=\"$target&paged=$i\">$i</a>";
				}
			}

			if( $this->page_num+$pn+1 <= $total_pages ) {
				echo "<span class=\"page-numbers dots\">...</span>";
				echo "<a class=\"page-numbers\" href=\"$target&paged=$total_pages\">$total_pages</a>";
			}
		}
	}
}

?>

