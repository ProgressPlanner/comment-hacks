/* global chUserSearch */

jQuery( document ).ready( function( $ ) {
	var $search = $( "#comment_notification_recipient_search" );
	var $hidden = $( "#comment_notification_recipient" );
	var $results = $( "#comment_notification_recipient_results" );
	var $clear = $( "#comment_notification_recipient_clear" );
	var searchTimer = null;

	$search.on( "input", function() {
		clearTimeout( searchTimer );
		var query = $search.val();

		if ( query.length < 2 ) {
			$results.empty().hide();
			return;
		}

		searchTimer = setTimeout( function() {
			$.ajax( {
				url: chUserSearch.ajax_url,
				type: "POST",
				data: {
					action: "ch_search_users",
					search: query,
					nonce: chUserSearch.nonce,
				},
				/**
				 * Handle the AJAX response.
				 *
				 * @param {Object} response The response object.
				 * @param {boolean} response.success Indicates if the request was successful.
				 * @param {Array} response.data The response data.
				 *
				 * @returns {void}
				 */
				success: function( response ) {
					if ( response.success && response.data.length ) {
						$results.empty();
						$.each( response.data, function( i, user ) {
							$results.append(
								$( "<li>" )
									.text( user.name )
									.attr( "data-id", user.id )
									.on( "click", function() {
										$hidden.val( user.id );
										$search.val( user.name );
										$results.empty().hide();
										$clear.show();
									} )
							);
						} );
						$results.show();
					} else {
						$results.empty().hide();
					}
				},
			} );
		}, 300 );
	} );

	$clear.on( "click", function( e ) {
		e.preventDefault();
		$hidden.val( "0" );
		$search.val( "" );
		$clear.hide();
	} );

	// Hide results when clicking outside.
	$( document ).on( "click", function( e ) {
		if ( ! $( e.target ).closest( "#comment_notification_recipient_search, #comment_notification_recipient_results" ).length ) {
			$results.hide();
		}
	} );
} );
