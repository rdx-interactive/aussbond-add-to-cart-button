( function ( $ ) {
	'use strict';

	var config = window.AussbondATC || {};
	var i18n = config.i18n || {};
	var captureAttached = false;

	function initScope( scope ) {
		var $scope = scope ? $( scope ) : $( document );
		var $forms = $scope.find( '.aussbond-atc-form' );

		if ( $scope.hasClass( 'aussbond-atc-form' ) ) {
			$forms = $forms.add( $scope );
		}

		$forms.each( function () {
			initForm( $( this ) );
		} );
	}

	function initForm( $form ) {
		if ( $form.data( 'aussbondAtcReady' ) ) {
			return;
		}

		$form.data( 'aussbondAtcReady', true );

		var $button = getButton( $form );
		$button.data( 'aussbondOriginalText', $.trim( $button.text() ) );
		$button.data( 'aussbondBaseDisabled', $button.prop( 'disabled' ) );

		if ( isVariableForm( $form ) ) {
			$form.on( 'found_variation.aussbondAtc', function ( event, variation ) {
				$form.data( 'aussbondSelectedVariation', variation || null );
				window.setTimeout( function () {
					updateVariableButton( $form, variation || null );
				}, 0 );
			} );

			$form.on( 'reset_data.aussbondAtc hide_variation.aussbondAtc', function () {
				$form.data( 'aussbondSelectedVariation', null );
				updateVariableButton( $form, null );
			} );

			$form.on( 'change.aussbondAtc', '.variations select', function () {
				clearNotices( $form );
			} );

			if ( $.fn.wc_variation_form ) {
				$form.wc_variation_form();
				$form.trigger( 'check_variations' );
			}

			window.setTimeout( function () {
				resolveSelectedVariation( $form );
			}, 0 );
		} else {
			updateSimpleButton( $form );
		}
	}

	function isVariableForm( $form ) {
		return 'variable' === String( $form.data( 'product-type' ) );
	}

	function getButton( $form ) {
		return $form.find( '.aussbond-atc-button' ).first();
	}

	function getNotices( $form ) {
		return $form.find( '.aussbond-atc-notices' ).first();
	}

	function clearNotices( $form ) {
		getNotices( $form ).empty();
	}

	function writeNotices( $form, html ) {
		var $notices = getNotices( $form );

		if ( html ) {
			$notices.html( html );
		}
	}

	function setButtonLoading( $form, isLoading ) {
		var $button = getButton( $form );

		if ( isLoading ) {
			$button.data( 'aussbondTextBeforeLoading', $.trim( $button.text() ) );
			$button.prop( 'disabled', true ).addClass( 'is-loading disabled' ).attr( 'aria-busy', 'true' );
			$button.text( i18n.adding || 'Adding...' );
			return;
		}

		$button.removeClass( 'is-loading' ).attr( 'aria-busy', 'false' );

		if ( isVariableForm( $form ) ) {
			updateVariableButton( $form, $form.data( 'aussbondSelectedVariation' ) || null );
		} else {
			updateSimpleButton( $form );
		}
	}

	function updateSimpleButton( $form ) {
		var $button = getButton( $form );
		var buttonText = String( $button.data( 'button-text' ) || $button.data( 'aussbondOriginalText' ) || 'Add to Cart' );
		var backorderText = String( $button.data( 'backorder-text' ) || 'Back Order' );
		var outOfStockText = String( $button.data( 'out-of-stock-text' ) || i18n.outOfStock || 'Out of Stock' );
		var isAddable = '1' === String( $button.data( 'is-addable' ) );
		var isBackorder = '1' === String( $button.data( 'is-backorder' ) );
		var disabled = ! isAddable || true === $button.data( 'aussbondBaseDisabled' );
		var label = isAddable ? buttonText : outOfStockText;

		if ( isAddable && isBackorder ) {
			label = backorderText;
		}

		$button.text( label );
		$button.prop( 'disabled', disabled ).toggleClass( 'disabled', disabled ).attr( 'aria-disabled', disabled ? 'true' : 'false' );
	}

	function updateVariableButton( $form, variation ) {
		var $button = getButton( $form );
		var buttonText = String( $button.data( 'button-text' ) || 'Add to Cart' );
		var backorderText = String( $button.data( 'backorder-text' ) || 'Back Order' );
		var outOfStockText = String( $button.data( 'out-of-stock-text' ) || i18n.outOfStock || 'Out of Stock' );
		var disabled = true;
		var label = buttonText;

		if ( variation && variation.variation_id ) {
			var stockStatus = String( variation.aussbond_stock_status || ( variation.is_in_stock ? 'instock' : 'outofstock' ) );
			var purchasable = false !== variation.aussbond_is_purchasable && false !== variation.is_purchasable;
			var backordersAllowed = true === variation.aussbond_backorders_allowed;
			var addable = 'undefined' === typeof variation.aussbond_is_addable ? null : true === variation.aussbond_is_addable;
			var inStock = false !== variation.aussbond_is_in_stock && false !== variation.is_in_stock;
			var managingStock = true === variation.aussbond_managing_stock;
			var stockQuantity = null === variation.aussbond_stock_quantity || 'undefined' === typeof variation.aussbond_stock_quantity ? null : Number( variation.aussbond_stock_quantity );

			label = variation.aussbond_button_text || ( 'instock' === stockStatus ? buttonText : backorderText );
			disabled = null === addable ? ( ! purchasable || ( ! inStock && ! backordersAllowed && ( ! managingStock || ! stockQuantity || 0 >= stockQuantity ) ) ) : ! addable;

			if ( disabled ) {
				label = outOfStockText;
			} else if ( backordersAllowed && stockQuantity !== null && 0 >= stockQuantity ) {
				label = backorderText;
			}
		}

		$button.text( label );
		$button.prop( 'disabled', disabled ).toggleClass( 'disabled', disabled ).attr( 'aria-disabled', disabled ? 'true' : 'false' );
	}

	function collectAttributes( $form ) {
		var attributes = {};

		$form.find( '[name^="attribute_"]' ).each( function () {
			var $field = $( this );
			var name = $field.attr( 'name' );

			if ( name ) {
				attributes[ name ] = $field.val();
			}
		} );

		return attributes;
	}

	function getVariationData( $form ) {
		var variations = $form.data( 'product_variations' );

		if ( variations ) {
			return variations;
		}

		var raw = $form.attr( 'data-product_variations' );

		if ( ! raw ) {
			return [];
		}

		try {
			variations = JSON.parse( raw );
			$form.data( 'product_variations', variations );
			return variations;
		} catch ( error ) {
			return [];
		}
	}

	function findMatchingVariation( $form, attributes ) {
		var variations = getVariationData( $form );

		for ( var index = 0; index < variations.length; index++ ) {
			var variation = variations[ index ];
			var variationAttributes = variation.attributes || {};
			var matches = true;

			for ( var name in variationAttributes ) {
				if ( ! Object.prototype.hasOwnProperty.call( variationAttributes, name ) ) {
					continue;
				}

				if ( variationAttributes[ name ] && String( variationAttributes[ name ] ) !== String( attributes[ name ] || '' ) ) {
					matches = false;
					break;
				}
			}

			if ( matches && variation.variation_id ) {
				return variation;
			}
		}

		return null;
	}

	function resolveSelectedVariation( $form ) {
		if ( ! isVariableForm( $form ) ) {
			return null;
		}

		var variationId = $form.find( '[name="variation_id"]' ).first().val();
		var selectedVariation = $form.data( 'aussbondSelectedVariation' ) || null;

		if ( variationId && '0' !== String( variationId ) && selectedVariation ) {
			return selectedVariation;
		}

		var attributes = collectAttributes( $form );
		var variation = findMatchingVariation( $form, attributes );

		if ( variation ) {
			$form.find( '[name="variation_id"]' ).first().val( variation.variation_id );
			$form.data( 'aussbondSelectedVariation', variation );
			updateVariableButton( $form, variation );
			return variation;
		}

		return null;
	}

	function collectPayload( $form ) {
		var productId = $form.find( '[name="product_id"]' ).first().val() || $form.data( 'product-id' ) || 0;
		var variation = resolveSelectedVariation( $form );
		var variationId = $form.find( '[name="variation_id"]' ).first().val() || 0;

		return {
			action: config.action || 'aussbond_atc_add_to_cart',
			nonce: config.nonce || '',
			'add-to-cart': productId,
			product_id: productId,
			variation_id: variation && variation.variation_id ? variation.variation_id : variationId,
			quantity: $form.find( '[name="quantity"]' ).first().val() || 1,
			attributes: collectAttributes( $form )
		};
	}

	function validateBeforeSubmit( $form, payload ) {
		if ( isVariableForm( $form ) && ( ! payload.variation_id || '0' === String( payload.variation_id ) ) ) {
			writeNotices( $form, '<div class="woocommerce-error" role="alert">' + escapeHtml( i18n.chooseOptions || 'Please choose product options before adding this item to your cart.' ) + '</div>' );
			updateVariableButton( $form, null );
			return false;
		}

		return true;
	}

	function handleSubmit( $form ) {
		initForm( $form );

		if ( $form.data( 'aussbondPending' ) ) {
			writeNotices( $form, '<div class="woocommerce-error" role="alert">' + escapeHtml( i18n.duplicateRequest || 'Please wait a moment before submitting again.' ) + '</div>' );
			return;
		}

		clearNotices( $form );
		$form.data( 'aussbondPending', true );
		setButtonLoading( $form, true );

		refreshNonce()
			.always( function () {
				submitPayload( $form );
			} );
	}

	function submitPayload( $form ) {
		var payload = collectPayload( $form );

		if ( ! validateBeforeSubmit( $form, payload ) ) {
			$form.data( 'aussbondPending', false );
			setButtonLoading( $form, false );
			return;
		}

		$.ajax( {
			type: 'POST',
			url: config.ajaxUrl || '',
			data: payload,
			dataType: 'json'
		} )
			.done( function ( response ) {
				if ( response && response.success ) {
					handleSuccess( $form, response.data || {} );
					return;
				}

				handleError( $form, response );
			} )
			.fail( function ( jqXHR ) {
				handleError( $form, jqXHR.responseJSON || null );
			} )
			.always( function () {
				$form.data( 'aussbondPending', false );
				setButtonLoading( $form, false );
			} );
	}

	function refreshNonce() {
		if ( ! config.ajaxUrl || ! config.refreshNonceAction ) {
			return $.Deferred().resolve().promise();
		}

		return $.ajax( {
			type: 'POST',
			url: config.ajaxUrl,
			data: {
				action: config.refreshNonceAction
			},
			dataType: 'json'
		} ).done( function ( response ) {
			if ( response && response.success && response.data && response.data.nonce ) {
				config.nonce = response.data.nonce;
			}
		} );
	}

	function handleSuccess( $form, data ) {
		writeNotices( $form, data.messages || '' );
		updateFragments( data.fragments || {}, data.cart_hash || '', getButton( $form ) );
	}

	function handleError( $form, response ) {
		var data = response && response.data ? response.data : {};
		var messages = data.messages || '<div class="woocommerce-error" role="alert">' + escapeHtml( i18n.genericError || 'Something went wrong. Please try again.' ) + '</div>';

		writeNotices( $form, messages );
	}

	function updateFragments( fragments, cartHash, $button ) {
		if ( fragments ) {
			$.each( fragments, function ( selector, html ) {
				$( selector ).replaceWith( html );
			} );
		}

		$( document.body ).trigger( 'added_to_cart', [ fragments, cartHash, $button ] );
		$( document.body ).trigger( 'wc_fragment_refresh' );
	}

	function escapeHtml( value ) {
		return String( value )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' )
			.replace( /'/g, '&#039;' );
	}

	function attachSubmitCapture() {
		if ( captureAttached || ! document.addEventListener ) {
			return;
		}

		captureAttached = true;

		document.addEventListener(
			'submit',
			function ( event ) {
				var form = event.target && event.target.closest ? event.target.closest( '.aussbond-atc-form' ) : null;

				if ( ! form ) {
					return;
				}

				event.preventDefault();
				event.stopPropagation();

				if ( event.stopImmediatePropagation ) {
					event.stopImmediatePropagation();
				}

				handleSubmit( $( form ) );
			},
			true
		);
	}

	$( function () {
		attachSubmitCapture();
		initScope( document );
	} );

	$( window ).on( 'elementor/frontend/init', function () {
		if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
			window.elementorFrontend.hooks.addAction( 'frontend/element_ready/aussbond_add_to_cart_button.default', function ( $scope ) {
				attachSubmitCapture();
				initScope( $scope );
			} );
		}
	} );
} )( jQuery );
