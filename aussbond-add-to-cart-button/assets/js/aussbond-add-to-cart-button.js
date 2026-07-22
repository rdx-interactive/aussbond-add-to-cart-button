( function ( $ ) {
	'use strict';

	var config = window.AussbondATC || {};
	var i18n = config.i18n || {};
	var captureAttached = false;
	var clickCaptureAttached = false;
	var toastTimer = null;

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
			repairViewCartLinks( $notices );
		}
	}

	function getCartUrl() {
		var cartUrl = String( config.cartUrl || '/cart/' );

		if ( -1 !== cartUrl.indexOf( '/checkout' ) ) {
			cartUrl = '/cart/';
		}

		return cartUrl;
	}

	function forceCartUrlGlobals() {
		var cartUrl = getCartUrl();

		if ( window.wc_add_to_cart_params ) {
			window.wc_add_to_cart_params.cart_url = cartUrl;
			window.wc_add_to_cart_params.cart_redirect_after_add = 'no';
		}

		if ( window.wc_cart_fragments_params ) {
			window.wc_cart_fragments_params.cart_url = cartUrl;
		}
	}

	function repairViewCartLinks( scope ) {
		var cartUrl = getCartUrl();
		var $scope = scope ? $( scope ) : $( document );

		$scope.find( 'a' ).addBack( 'a' ).each( function () {
			var $link = $( this );
			var href = String( $link.attr( 'href' ) || '' );
			var label = $.trim( $link.text() ).toLowerCase();

			if ( -1 !== href.indexOf( '/checkout' ) && ( 'view cart' === label || -1 !== label.indexOf( 'view cart' ) ) ) {
				$link.attr( 'href', cartUrl );
			}
		} );
	}

	function showPopupNotice( $form, message, type ) {
		var $wrapper = $form.closest( '.aussbond-atc' );
		var $toast;
		var cartUrl = getCartUrl();
		var viewCartText = String( $wrapper.data( 'view-cart-text' ) || i18n.viewCart || 'View cart' );

		if ( ! $wrapper.length ) {
			$wrapper = $( document.body );
		}

		$toast = $wrapper.find( '.aussbond-atc-toast' ).first();

		if ( ! $toast.length ) {
			$toast = $( '<div class="aussbond-atc-toast" role="status" aria-live="polite"></div>' );
			$wrapper.append( $toast );
		}

		$toast
			.removeClass( 'aussbond-atc-toast--success aussbond-atc-toast--error is-visible' )
			.addClass( 'aussbond-atc-toast--' + ( type || 'success' ) )
			.empty()
			.append( $( '<span class="aussbond-atc-toast__message"></span>' ).text( message ) )
			.append( $( '<a class="aussbond-atc-toast__link"></a>' ).attr( 'href', cartUrl ).text( viewCartText ) );

		window.setTimeout( function () {
			$toast.addClass( 'is-visible' );
		}, 10 );

		if ( toastTimer ) {
			window.clearTimeout( toastTimer );
		}

		toastTimer = window.setTimeout( function () {
			$toast.removeClass( 'is-visible' );
		}, 3600 );
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
			var maxPurchaseQuantity = 'undefined' === typeof variation.aussbond_max_purchase_quantity ? null : Number( variation.aussbond_max_purchase_quantity );
			var purchaseLimitReached = 0 === maxPurchaseQuantity;

			label = variation.aussbond_button_text || ( 'instock' === stockStatus ? buttonText : backorderText );
			disabled = null === addable ? ( purchaseLimitReached || ! purchasable || ( ! inStock && ! backordersAllowed && ( ! managingStock || ! stockQuantity || 0 >= stockQuantity ) ) ) : ( purchaseLimitReached || ! addable );

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
		var attributes = collectAttributes( $form );
		var payload = {
			action: config.action || 'aussbond_atc_add_to_cart',
			nonce: config.nonce || '',
			'add-to-cart': productId,
			product_id: productId,
			variation_id: variation && variation.variation_id ? variation.variation_id : variationId,
			quantity: $form.find( '[name="quantity"]' ).first().val() || 1,
			aussbond_atc_request: '1',
			attributes: attributes
		};

		$.each( attributes, function ( name, value ) {
			payload[ name ] = value;
		} );

		return payload;
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
			dataType: 'text'
		} )
			.done( function ( responseText, textStatus, jqXHR ) {
				var response = parseJsonResponse( responseText );

				if ( response && response.success ) {
					handleSuccess( $form, response.data || {} );
					return;
				}

				if ( isRedirectedAddToCartSuccess( jqXHR, textStatus, responseText ) ) {
					handleSuccess( $form, {} );
					return;
				}

				handleError( $form, response || null );
			} )
			.fail( function ( jqXHR, textStatus ) {
				if ( isRedirectedAddToCartSuccess( jqXHR, textStatus, jqXHR.responseText || '' ) ) {
					handleSuccess( $form, {} );
					return;
				}

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
		clearNotices( $form );
		showPopupNotice( $form, data.message || i18n.addedToCart || 'Product added to cart.', 'success' );
		updateFragments( data.fragments || {}, data.cart_hash || '', getButton( $form ) );
		repairViewCartLinks( document );
	}

	function parseJsonResponse( responseText ) {
		if ( ! responseText ) {
			return null;
		}

		try {
			return JSON.parse( responseText );
		} catch ( error ) {
			return null;
		}
	}

	function isRedirectedAddToCartSuccess( jqXHR, textStatus, responseText ) {
		var response = responseText ? String( responseText ) : '';
		var responseUrl = jqXHR && jqXHR.responseURL ? String( jqXHR.responseURL ) : '';

		return 200 === Number( jqXHR && jqXHR.status )
			&& ( 'success' === String( textStatus ) || 'parsererror' === String( textStatus ) )
			&& (
				-1 !== responseUrl.indexOf( '/checkout' )
				|| -1 !== response.indexOf( 'Checkout' )
				|| -1 !== response.indexOf( 'woocommerce-checkout' )
			);
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

	function attachButtonClickCapture() {
		if ( clickCaptureAttached || ! document.addEventListener ) {
			return;
		}

		clickCaptureAttached = true;

		document.addEventListener(
			'click',
			function ( event ) {
				var button = event.target && event.target.closest ? event.target.closest( '.aussbond-atc-button' ) : null;

				if ( ! button || button.disabled ) {
					return;
				}

				var form = button.closest ? button.closest( '.aussbond-atc-form' ) : null;

				if ( ! form ) {
					return;
				}

				var $form = $( form );

				event.preventDefault();
				event.stopPropagation();

				if ( event.stopImmediatePropagation ) {
					event.stopImmediatePropagation();
				}

				initForm( $form );
				handleSubmit( $form );
			},
			true
		);
	}

	$( function () {
		forceCartUrlGlobals();
		repairViewCartLinks( document );
		attachSubmitCapture();
		attachButtonClickCapture();
		initScope( document );
	} );

	$( window ).on( 'elementor/frontend/init', function () {
		if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
			window.elementorFrontend.hooks.addAction( 'frontend/element_ready/aussbond_add_to_cart_button.default', function ( $scope ) {
				forceCartUrlGlobals();
				repairViewCartLinks( document );
				attachSubmitCapture();
				attachButtonClickCapture();
				initScope( $scope );
			} );
		}
	} );
} )( jQuery );
