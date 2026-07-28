/**
 * MDB Configurator — Single Product Wizard
 *
 * Enhances CPO's rendered form with step headers, a live order-summary
 * sidebar, and custom Calculate / Add-to-Cart buttons.
 *
 * Architecture — "transform in place":
 *   CPO's <form class="cart"> stays fully rendered and functional.
 *   We prepend step headers INSIDE each .uni-module wrapper so CPO's own
 *   slideUp / slideDown on the wrapper automatically shows/hides our headers.
 *   All CPO show/hide rules, validation, and price calculations fire natively.
 *   The sidebar is a separate component that reads CPO's events.
 */
( function ( $ ) {
	'use strict';

	// ─────────────────────────────────────────────────────────────────────────
	// Bootstrap
	// ─────────────────────────────────────────────────────────────────────────

	var initialized = false;

	$( function () {
		/**
		 * Listen for CPO's ready event (primary path).
		 * CPO fires this inside its own $(document).ready handler which runs
		 * before ours because uni-cpo-frontend is listed as a dependency and
		 * therefore output earlier in the HTML.
		 */
		$( document.body ).on( 'uni_cpo_frontend_is_ready', function () {
			initConfigurator();
		} );

		/**
		 * Fallback: by the time our ready handler executes, CPO may have already
		 * finished its initialisation and fired its event. Check the known
		 * marker (unicpoAllOptions is set via wp_localize_script before any JS
		 * runs, so it is always available when CPO is active on this page).
		 */
		if ( typeof unicpoAllOptions !== 'undefined' && $( '#uni_cpo_options' ).length ) {
			initConfigurator();
		}
	} );

	function initConfigurator() {
		if ( initialized ) { return; }
		if ( ! $( '#uni_cpo_options' ).length ) { return; }
		initialized = true;
		MdbConfigurator.init();
	}

	// ─────────────────────────────────────────────────────────────────────────
	// MdbConfigurator
	// ─────────────────────────────────────────────────────────────────────────

	var MdbConfigurator = {

		$form: null,
		userTouched: {},
		desktopSummaryExpanded: false,
		summaryHydrated: false,
		summarySettleTimer: null,
		summaryFallbackTimer: null,

		init: function () {
			this.$form = $( 'form.cart' );
			if ( ! this.$form.length ) { return; }
			this.userTouched = {};

			this.initSummaryHydration();
			this.setOptionImages();
			this.injectStepHeaders();
			this.classifyGridColumns();
			this.classifyImageShapes();
			this.buildSidebarList();
			this.redirectPriceSelector();
			this.bindEvents();
			this.initMobileDrawer();
			this.initSummaryToggle();
		},

		// ── Grid column classification ─────────────────────────────────────────

		/**
		 * Count the option labels inside each radio image-mode module.
		 * Modules with 4+ choices get class .mdb-grid--4col, which switches
		 * the CSS grid from repeat(3,1fr) to repeat(4,1fr).
		 * Must run after injectStepHeaders so the step header is in the DOM,
		 * but we only count .uni-cpo-option-label (actual choices, not headers).
		 */
		classifyGridColumns : function () {
			$( '#uni_cpo_options' ).find( '.uni-module-radio-image-mode' ).each( function () {
				var count = $( this ).find( '.uni-cpo-option-label' ).length;
				if ( count >= 4 ) {
					$( this ).addClass( 'mdb-grid--4col' );
				}
			} );
		},

		// ── Image shape classification ────────────────────────────────────────

		/**
		 * PHP exports mdbCpoShapes = { slug: 'circle'|'square', ... } via
		 * wp_add_inline_script. For each slug whose geom is 'circle', add the
		 * .mdb-shape--circle class to the matching .uni-module wrapper so the
		 * SCSS can apply border-radius: 50% instead of the default 0.
		 */
		classifyImageShapes : function () {
			if ( typeof mdbCpoShapes === 'undefined' ) { return; }

			// Circles are the default — CPO’s own product CSS already applies
			// border-radius:100% for those. We only add a class for rectangular
			// options so the theme CSS can override to border-radius:0.
			Object.keys( mdbCpoShapes ).forEach( function ( slug ) {
				if ( 'circle' === mdbCpoShapes[ slug ] ) { return; }
				var $module = $( '#' + slug );
				if ( $module.hasClass( 'uni-module-radio-image-mode' ) ) {
					$module.addClass( 'mdb-shape--rect' );
				}
			} );
		},

		// ── Mobile bottom drawer ──────────────────────────────────────────────

		/**
		 * On mobile the sidebar becomes a fixed bottom sheet.
		 * Tapping the handle bar toggles it open/closed.
		 * A backdrop is added to body when open; clicking it closes the drawer.
		 */
		initMobileDrawer : function () {
			var $handle  = $( '#mdb-sidebar-handle' );
			var $sidebar = $( '.mdb-configurator__sidebar' );

			if ( ! $handle.length ) { return; }

			$handle.on( 'click', function ( e ) {
				e.stopPropagation();
				var opening = ! $sidebar.hasClass( 'is-open' );
				$sidebar.toggleClass( 'is-open', opening );
				$handle.attr( 'aria-expanded', opening ? 'true' : 'false' );
				$( 'body' ).toggleClass( 'mdb-drawer-open', opening );
			} );

			// Clicking the backdrop (body::after pseudo-element is not clickable,
			// so we listen on document for clicks outside the sidebar).
			$( document ).on( 'click.mdbDrawer', function ( e ) {
				if ( $sidebar.hasClass( 'is-open' ) &&
				     ! $( e.target ).closest( '.mdb-configurator__sidebar' ).length ) {
					$sidebar.removeClass( 'is-open' );
					$handle.attr( 'aria-expanded', 'false' );
					$( 'body' ).removeClass( 'mdb-drawer-open' );
				}
			} );
		},

		// ── Summary hydration (initial defaults settle) ───────────────────────

		/**
		 * Start in a loading state so users do not see transient summary counts
		 * while Uni CPO defaults and conditional logic are still settling.
		 */
		initSummaryHydration : function () {
			var $wrapper = $( '#mdb-summary-options-wrapper' );
			var $btn = $( '#mdb-summary-options-toggle' );

			this.summaryHydrated = false;
			this.desktopSummaryExpanded = false;
			this.clearSummaryHydrationTimers();

			if ( $wrapper.length ) {
				$wrapper.addClass( 'is-loading' ).attr( 'aria-busy', 'true' );
			}

			if ( $btn.length ) {
				$btn
					.addClass( 'is-loading' )
					.prop( 'disabled', true )
					.attr( 'aria-disabled', 'true' )
					.attr( 'aria-expanded', 'false' );
			}

			// Safety net in case conditional event timing differs by product setup.
			this.summaryFallbackTimer = setTimeout( function () {
				this.finalizeSummaryHydration();
			}.bind( this ), 1500 );
		},

		/**
		 * Called after Uni CPO conditional/price events. Debounced so the list is
		 * revealed once after defaults settle.
		 */
		scheduleSummaryHydrationFinalize : function () {
			if ( this.summaryHydrated ) { return; }

			if ( this.summarySettleTimer ) {
				clearTimeout( this.summarySettleTimer );
			}

			this.summarySettleTimer = setTimeout( function () {
				this.finalizeSummaryHydration();
			}.bind( this ), 120 );
		},

		/**
		 * Reveal real summary items and remove loading state.
		 */
		finalizeSummaryHydration : function () {
			var $wrapper = $( '#mdb-summary-options-wrapper' );
			var $btn = $( '#mdb-summary-options-toggle' );

			if ( this.summaryHydrated ) { return; }

			this.summaryHydrated = true;
			this.clearSummaryHydrationTimers();

			// Rebuild from settled DOM so first visible state is final defaults.
			this.buildSidebarList();
			this.syncSidebarVisibility();
			this.renumberSteps();
			this.syncDrawerPrice();

			if ( $wrapper.length ) {
				$wrapper.removeClass( 'is-loading' ).attr( 'aria-busy', 'false' );
			}

			if ( $btn.length ) {
				$btn
					.removeClass( 'is-loading' )
					.prop( 'disabled', false )
					.removeAttr( 'aria-disabled' );
			}

			this.applyDesktopSummaryCollapse();
		},

		clearSummaryHydrationTimers : function () {
			if ( this.summarySettleTimer ) {
				clearTimeout( this.summarySettleTimer );
				this.summarySettleTimer = null;
			}

			if ( this.summaryFallbackTimer ) {
				clearTimeout( this.summaryFallbackTimer );
				this.summaryFallbackTimer = null;
			}
		},

		/**
		 * Collapsible selected-options section for summary panel.
		 * Matches cart/checkout "View/Hide Configuration" behavior.
		 */
		initSummaryToggle : function () {
			var $btn = $( '#mdb-summary-options-toggle' );
			var $wrapper = $( '#mdb-summary-options-wrapper' );
			var $label = $btn.find( '.mdb-options-toggle-btn__label' );

			if ( ! $btn.length || ! $wrapper.length ) { return; }

			$btn.removeClass( 'active is-desktop-overflow' ).attr( 'aria-expanded', 'false' );
			if ( $label.length ) {
				$label.text( 'View Configuration' );
			}

			$( document ).off( 'click.mdbSummaryToggle', '#mdb-summary-options-toggle' ).on( 'click.mdbSummaryToggle', '#mdb-summary-options-toggle', function ( e ) {
				e.preventDefault();
				e.stopPropagation();

				if ( ! this.summaryHydrated ) { return; }

				var $this = $( this );
				var isDesktop = window.matchMedia( '(min-width: 961px)' ).matches;
				var count = $( '#mdb-summary-list .mdb-summary-item' ).length;

				if ( isDesktop ) {
					if ( count <= 5 ) {
						// Desktop with 5 or fewer items: no toggle action.
						return;
					}

					// If currently collapsed, clicking expands; otherwise collapses.
					var expanding = $( '#mdb-summary-list' ).hasClass( 'is-collapsed' );
					this.desktopSummaryExpanded = expanding;
					this.applyDesktopSummaryCollapse();
					return;
				}

				var isOpen = $this.hasClass( 'active' );
				$wrapper.stop( true, true ).slideToggle( 200 );
				$this.toggleClass( 'active', ! isOpen );
				$this.attr( 'aria-expanded', isOpen ? 'false' : 'true' );
				if ( $label.length ) {
					$label.text( isOpen ? 'View Configuration' : 'Hide Configuration' );
				}
			}.bind( this ) );

			$( window ).off( 'resize.mdbSummaryDesktop' ).on( 'resize.mdbSummaryDesktop', function () {
				this.applyDesktopSummaryCollapse();
			}.bind( this ) );

			this.applyDesktopSummaryCollapse();
		},

		/**
		 * Desktop-only: collapse summary items after the first 5 and expose a
		 * compact toggle when more options are selected.
		 */
		applyDesktopSummaryCollapse : function () {
			var $btn = $( '#mdb-summary-options-toggle' );
			var $label = $btn.find( '.mdb-options-toggle-btn__label' );
			var $list = $( '#mdb-summary-list' );
			var $wrapper = $( '#mdb-summary-options-wrapper' );
			var $summaryCard = $( '.mdb-order-summary' );
			var isDesktop = window.matchMedia( '(min-width: 961px)' ).matches;
			var count = $list.find( '.mdb-summary-item' ).length;
			var overflowCount = Math.max( count - 5, 0 );
			var overflowLabel = 'View ' + overflowCount + ' more option' + ( overflowCount === 1 ? '' : 's' );

			$list.removeClass( 'is-collapsed' );

			if ( ! this.summaryHydrated ) {
				$summaryCard.removeClass( 'mdb-order-summary--desktop-scroll-enabled' );
				$btn.removeClass( 'is-desktop-overflow active' ).attr( 'aria-expanded', 'false' );
				return;
			}

			if ( ! isDesktop ) {
				$summaryCard.removeClass( 'mdb-order-summary--desktop-scroll-enabled' );
				// Mobile keeps original behavior and control position above the list.
				if ( ! $btn.prev().is( '#mdb-summary-options-wrapper' ) ) {
					$btn.insertBefore( $wrapper );
				}
				$btn.removeClass( 'is-desktop-overflow active' ).attr( 'aria-expanded', 'false' );
				if ( $label.length && ! isDesktop ) {
					$label.text( 'View Configuration' );
				}
				return;
			}

			// Desktop: keep wrapper visible and place control below the list.
			$wrapper.stop( true, true ).show();
			if ( ! $btn.prev().is( '#mdb-summary-options-wrapper' ) ) {
				$btn.insertAfter( $wrapper );
			}

			if ( count <= 5 ) {
				this.desktopSummaryExpanded = false;
				$summaryCard.removeClass( 'mdb-order-summary--desktop-scroll-enabled' );
				$btn.removeClass( 'is-desktop-overflow active' ).attr( 'aria-expanded', 'false' );
				if ( $label.length ) {
					$label.text( 'View Configuration' );
				}
				return;
			}

			$btn.addClass( 'is-desktop-overflow' );

			if ( this.desktopSummaryExpanded ) {
				$summaryCard.addClass( 'mdb-order-summary--desktop-scroll-enabled' );
				$btn.addClass( 'active' ).attr( 'aria-expanded', 'true' );
				if ( $label.length ) {
					$label.text( 'Hide extra options' );
				}
			} else {
				$list.addClass( 'is-collapsed' );
				$summaryCard.removeClass( 'mdb-order-summary--desktop-scroll-enabled' );
				$btn.removeClass( 'active' ).attr( 'aria-expanded', 'false' );
				if ( $label.length ) {
					$label.text( overflowLabel );
				}
			}
		},

		// ── Sync drawer handle price ──────────────────────────────────────────

		/**
		 * Keep the collapsed drawer handle price in sync with the main price.
		 * Called after CPO fires its price-update event.
		 */
		syncDrawerPrice : function () {
			var $mainPrice = $( '#mdb-total-price' );
			var $handlePrice = $( '#mdb-handle-price' );
			if ( $mainPrice.length && $handlePrice.length ) {
				$handlePrice.html( $mainPrice.html() );
			}
		},

		/**
		 * CPO stores image URLs in data-image on .uni-cpo-option-label__radio.
		 * CSS attr() for url() is not reliably supported, so we set
		 * background-image via JS instead.
		 */
		setOptionImages: function () {
			// Classic/colour-mode: set background-image on the radio span from data-image.
			$( '#uni_cpo_options' ).find( '.uni-cpo-option-label__radio' ).each( function () {
				var img = $( this ).data( 'image' );
				if ( img ) {
					$( this ).css( 'background-image', 'url(' + img + ')' );
				}
			} );

			// Image-mode: inject a visible title span from data-tip on the image-wrap,
			// because image-mode labels have no __text span.
			$( '#uni_cpo_options' ).find( '.uni-cpo-option-label__image-wrap' ).each( function () {
				var title = $( this ).data( 'tip' );
				if ( title && ! $( this ).next( '.mdb-card-title' ).length ) {
					$( '<span class="mdb-card-title">' )
						.text( title )
						.insertAfter( this );
				}
			} );
		},

		// ── Step headers ──────────────────────────────────────────────────────

		/**
		 * Prepend a .mdb-step-header div INSIDE each .uni-module that contains
		 * a CPO input field. Being inside the wrapper means CPO's slideUp/Down
		 * automatically shows/hides our header — no extra JS needed.
		 */
		injectStepHeaders: function () {
			var self = this;

			$( '#uni_cpo_options' ).find( '.uni-module' ).each( function () {
				var $module = $( this );

				// Only enhance modules that contain actual CPO inputs.
				if ( ! $module.find( '.js-uni-cpo-field' ).length ) { return; }

				// Avoid re-injecting on a second init call.
				if ( $module.find( '> .mdb-step-header' ).length ) { return; }

				var label = self.getOptionLabel( this.id, $module );
				if ( ! label ) { return; }

				$module.prepend(
					'<div class="mdb-step-header" aria-hidden="true">' +
						'<span class="mdb-step-label"></span>' +
						'<h3 class="mdb-step-title">' + self.escHtml( label ) + '</h3>' +
					'</div>'
				);
			} );

			this.renumberSteps();
		},

		/**
		 * Re-count visible .uni-module elements and update each STEP N label.
		 * Called after CPO fires its conditional-logic events.
		 */
		renumberSteps: function () {
			var n = 0;
			$( '#uni_cpo_options' ).find( '.uni-module' ).each( function () {
				var $module = $( this );
				if ( ! $module.find( '.js-uni-cpo-field' ).length ) { return; }
				if ( $module.is( ':hidden' ) ) { return; }
				n++;
				$module.find( '> .mdb-step-header .mdb-step-label' ).text( 'STEP ' + n );
			} );
		},

		// ── Sidebar ───────────────────────────────────────────────────────────

		/**
		 * Build the initial order-summary list from all CPO input modules.
		 * Items for hidden modules are created but immediately hidden so they
		 * appear/disappear in sync with CPO's conditional logic.
		 */
		buildSidebarList: function () {
			var $list = $( '#mdb-summary-list' );
			$list.empty();

			var self = this;

			$( '#uni_cpo_options' ).find( '.uni-module' ).each( function () {
				var $module = $( this );
				var slug    = this.id;

				if ( ! slug ) { return; }
				if ( ! $module.find( '.js-uni-cpo-field' ).length ) { return; }

				// Only add items that already have a selected value AND are visible.
				if ( $module.is( ':hidden' ) ) {
					self.syncModuleCompletionState( slug );
					return;
				}

				var value = self.getTrustedDisplayValue( slug );
				self.syncModuleCompletionState( slug );
				$list.append( self.createSidebarItem( slug, value ) );
			} );

			this.applyDesktopSummaryCollapse();
		},

		/**
		 * Build a single <li> for the summary list.
		 */
		createSidebarItem: function ( slug, value ) {
			var label = this.getOptionLabel( slug, $( '#' + slug ) );
			var hasValue = !! value;
			var valueMarkup = hasValue
				? '<strong class="mdb-summary-item__value">' + this.escHtml( value ) + '</strong>'
				: '<button type="button" class="mdb-summary-item__value mdb-summary-item__value--pending mdb-summary-jump" data-slug="' + this.escAttr( slug ) + '">Choose option</button>';
			return $(
				'<li class="mdb-summary-item' + ( hasValue ? '' : ' mdb-summary-item--pending' ) + '" data-slug="' + this.escAttr( slug ) + '">' +
					'<span class="mdb-summary-item__label">' + this.escHtml( label ) + '</span>' +
					valueMarkup +
				'</li>'
			);
		},

		/**
		 * Insert a new summary item in the same order as the CPO modules in the form.
		 */
		insertSidebarItemSorted: function ( slug, $newItem ) {
			var $list = $( '#mdb-summary-list' );
			var allSlugs = [];

			$( '#uni_cpo_options .uni-module' ).each( function () {
				if ( this.id ) { allSlugs.push( this.id ); }
			} );

			var idx = allSlugs.indexOf( slug );

			// Walk backward from this slug to find the nearest preceding list item.
			for ( var i = idx - 1; i >= 0; i-- ) {
				var $prev = $list.find( '[data-slug="' + allSlugs[ i ] + '"]' );
				if ( $prev.length ) {
					$prev.after( $newItem );
					return;
				}
			}

			// No preceding item — prepend.
			$list.prepend( $newItem );
		},

		/**
		 * Add, update, or remove a sidebar item based on whether the option has a value.
		 */
		updateSidebarItem: function ( slug ) {
			var $list  = $( '#mdb-summary-list' );
			var $item  = $list.find( '[data-slug="' + slug + '"]' );
			var value  = this.getTrustedDisplayValue( slug );

			if ( $item.length ) {
				$item.replaceWith( this.createSidebarItem( slug, value ) );
			} else {
				this.insertSidebarItemSorted( slug, this.createSidebarItem( slug, value ) );
			}

			this.syncModuleCompletionState( slug );

			this.applyDesktopSummaryCollapse();
		},

		/**
		 * Mirror summary completion status onto non-radio input modules so select,
		 * text, number, and textarea fields can show a "completed" visual state.
		 */
		syncModuleCompletionState: function ( slug ) {
			var $module = $( '#' + slug );
			if ( ! $module.length ) { return; }

			$module.removeClass( 'mdb-module--completed' );

			if ( $module.is( ':hidden' ) ) { return; }
			if ( $module.hasClass( 'uni-module-radio' ) ) { return; }

			if ( this.getTrustedDisplayValue( slug ) ) {
				$module.addClass( 'mdb-module--completed' );
			}
		},

		/**
		 * Only treat a value as selected if it is either:
		 * 1) a real builder default rendered in HTML, or
		 * 2) explicitly chosen by the user during this session.
		 */
		getTrustedDisplayValue: function ( slug ) {
			var value = this.getCurrentDisplayValue( slug );
			if ( ! value ) { return ''; }

			if ( this.userTouched[ slug ] || this.isCurrentValueBuilderDefault( slug ) ) {
				return value;
			}

			return '';
		},

		/**
		 * Detect whether the current module value comes from CPO builder defaults
		 * in the original HTML (checked/selected/value attributes), not from
		 * runtime auto-selection logic.
		 */
		isCurrentValueBuilderDefault: function ( slug ) {
			var $module = $( '#' + slug );
			if ( ! $module.length ) { return false; }

			var $checked = $module.find( 'input[type="radio"]:checked' );
			if ( $checked.length ) {
				return $checked.is( '[checked]' );
			}

			var $select = $module.find( 'select.js-uni-cpo-field' );
			if ( $select.length ) {
				var $selectedOpt = $select.find( 'option:selected' ).first();
				if ( ! $selectedOpt.length ) { return false; }

				var selectedText = $selectedOpt.text().trim();
				var firstText = $select.find( 'option:first' ).text().trim();
				if ( ! selectedText || selectedText === firstText ) { return false; }

				return $selectedOpt.is( '[selected]' );
			}

			var $input = $module.find( 'input.js-uni-cpo-field' ).first();
			if ( $input.length ) {
				var current = String( $input.val() || '' ).trim();
				var initial = String( $input.attr( 'value' ) || '' ).trim();
				return '' !== current && current === initial;
			}

			var $textarea = $module.find( 'textarea.js-uni-cpo-field' ).first();
			if ( $textarea.length ) {
				var currentText = String( $textarea.val() || '' ).trim();
				var initialText = String( $textarea.text() || '' ).trim();
				return '' !== currentText && currentText === initialText;
			}

			return false;
		},

		jumpToModule: function ( slug ) {
			if ( ! slug ) { return; }
			var $module = $( '#' + slug );
			if ( ! $module.length || $module.is( ':hidden' ) ) { return; }

			var $target = $module.find(
				'select.js-uni-cpo-field:visible, textarea.js-uni-cpo-field:visible, input.js-uni-cpo-field:not([type="radio"]):visible'
			).first();

			if ( ! $target.length ) {
				var $checkedRadio = $module.find( 'input[type="radio"]:checked' ).first();
				if ( $checkedRadio.length ) {
					$target = $module.find( 'label[for="' + $checkedRadio.attr( 'id' ) + '"]' ).first();
				}

				if ( ! $target.length ) {
					$target = $module.find( '.uni-cpo-option-label:visible' ).first();
				}
			}

			var runJump = function () {
				var $scrollTarget = $target.length ? $target : $module;
				var targetTop = Math.max( $scrollTarget.offset().top - 140, 0 );
				$( 'html, body' ).stop( true ).animate( { scrollTop: targetTop }, 320 );

				$( '.mdb-summary-target-field' ).removeClass( 'mdb-summary-target-field' );
				if ( $target.length ) {
					$target.addClass( 'mdb-summary-target-field' );
					setTimeout( function () {
						$target.removeClass( 'mdb-summary-target-field' );
					}, 1200 );

					if ( $target.is( 'label' ) ) {
						var inputId = $target.attr( 'for' );
						if ( inputId ) {
							$( '#' + inputId ).trigger( 'focus' );
						}
					} else {
						$target.trigger( 'focus' );
					}
				}
			};

			var isMobile = window.matchMedia( '(max-width: 960px)' ).matches;
			var $sidebar = $( '.mdb-configurator__sidebar' );
			if ( isMobile && $sidebar.hasClass( 'is-open' ) ) {
				$sidebar.removeClass( 'is-open' );
				$( '#mdb-sidebar-handle' ).attr( 'aria-expanded', 'false' );
				$( 'body' ).removeClass( 'mdb-drawer-open' );

				// Reset mobile drawer internals so collapsed state consistently shows
				// the quick Add to Cart strip after closing from "Choose option".
				$sidebar.find( '.mdb-order-summary' ).scrollTop( 0 );
				$sidebar.find( '.mdb-sidebar-mobile-actions' ).css( 'display', '' );

				setTimeout( runJump, 220 );
				return;
			}

			runJump();
		},

		/**
		 * After CPO's conditional events fire, remove hidden-module items and
		 * ensure visible modules with values are in the list.
		 */
		syncSidebarVisibility: function () {
			var self = this;

			$( '#uni_cpo_options .uni-module' ).each( function () {
				var slug   = this.id;
				if ( ! slug || ! $( this ).find( '.js-uni-cpo-field' ).length ) { return; }

				var $item  = $( '#mdb-summary-list [data-slug="' + slug + '"]' );

				if ( $( this ).is( ':hidden' ) ) {
					// CPO hid this module — remove its summary row.
					$item.remove();
					self.syncModuleCompletionState( slug );
				} else {
					// Module is visible — sync value (adds if needed, removes if empty).
					self.updateSidebarItem( slug );
				}
			} );

			this.applyDesktopSummaryCollapse();
		},

		/**
		 * Read the currently selected/entered human-readable value for an option.
		 */
		getCurrentDisplayValue: function ( slug ) {
			var $module = $( '#' + slug );
			if ( ! $module.length ) { return ''; }
			var isRadioModule = $module.hasClass( 'uni-module-radio' );

			// Radio — find the checked input's label and extract a readable title.
			var $checked = $module.find( 'input[type="radio"]:checked' );
			if ( $checked.length ) {
				var $label = $();
				var checkedId = $checked.attr( 'id' );
				if ( checkedId ) {
					$label = $module.find( 'label[for="' + checkedId + '"]' ).first();
				}

				// Some CPO layouts render input + label as direct siblings.
				if ( ! $label.length ) {
					$label = $checked.next( '.uni-cpo-option-label' ).first();
				}

				// Fallback for wrapped-markup variants where input lives inside label.
				if ( ! $label.length ) {
					$label = $checked.closest( 'label.uni-cpo-option-label' ).first();
				}

				// Image-mode: title is in data-tip on .uni-cpo-option-label__image-wrap,
				// or in the alt attribute of the image, or in the injected .mdb-card-title.
				var imageWrapTip = $label.find( '.uni-cpo-option-label__image-wrap' ).data( 'tip' );
				if ( imageWrapTip ) { return imageWrapTip; }

				// Some CPO layouts store readable title on the radio span itself.
				var radioTip = $label.find( '.uni-cpo-option-label__radio' ).data( 'tip' );
				if ( radioTip ) { return radioTip; }

				// Image-mode fallback: use the JS-injected card title text.
				var injectedTitle = $label.find( '.mdb-card-title' ).first().text().trim();
				if ( injectedTitle ) { return injectedTitle; }

				// Classic/colour-mode: title is in .uni-cpo-option-label__text (strip description).
				var $textSpan = $label.find( '.uni-cpo-option-label__text' );
				if ( $textSpan.length ) {
					var text = $textSpan.clone()
						.find( '.uni-cpo-option-label__description' )
						.remove()
						.end()
						.text()
						.trim();
					if ( text ) { return text; }
				}

				// Generic fallback: readable label text before dropping to raw value.
				var labelText = $label.clone()
					.find( 'img, .uni-cpo-option-label__description, .uni-cpo-option-label__radio' )
					.remove()
					.end()
					.text()
					.replace( /\s+/g, ' ' )
					.trim();
				if ( labelText ) { return labelText; }

				// Never leak raw machine values (e.g. "white") for radios.
				return '';
			}

			// Radios without a checked item are always "not selected".
			if ( isRadioModule ) {
				return '';
			}

			// Select.
			var $select = $module.find( 'select.js-uni-cpo-field' );
			if ( $select.length ) {
				var selected = $select.find( 'option:selected' ).text().trim();
				return ( selected && selected !== $select.find( 'option:first' ).text().trim() )
					? selected
					: '';
			}

			// Text / number.
			var $input = $module.find( 'input.js-uni-cpo-field:not([type="radio"])' );
			if ( $input.length ) {
				return $input.val().trim();
			}

			return '';
		},

		/**
		 * Return the first visible pending option slug from the summary list.
		 * Pending rows represent required selections the customer has not chosen.
		 */
		getFirstPendingSlug: function () {
			var firstSlug = '';

			$( '#mdb-summary-list .mdb-summary-item--pending' ).each( function () {
				var slug = $( this ).attr( 'data-slug' );
				if ( ! slug ) { return; }

				var $module = $( '#' + slug );
				if ( ! $module.length || $module.is( ':hidden' ) ) { return; }

				firstSlug = slug;
				return false;
			} );

			return firstSlug;
		},

		// ── Helpers ───────────────────────────────────────────────────────────

		/**
		 * Resolve human-readable label for a given option slug.
		 * Primary: unicpoAllOptions (localised by CPO's PHP).
		 * Fallback: first rendered CPO label element inside the module.
		 */
		getOptionLabel: function ( slug, $module ) {
			if ( typeof unicpoAllOptions !== 'undefined' && unicpoAllOptions[ slug ] ) {
				var label = unicpoAllOptions[ slug ].label;
				if ( label ) { return label; }
			}
			// Fallback: grab the rendered label text from CPO's own markup.
			return $module.find( '[class*="-label"]' ).first().text().trim();
		},

		// ── Price selector redirect ───────────────────────────────────────────

		/**
		 * Tell CPO to write its calculated price directly into our sidebar element
		 * rather than the default .price element inside .summary (which we removed).
		 * This is set before any AJAX price calculation fires, so CPO picks it up.
		 */
		redirectPriceSelector: function () {
			if ( typeof unicpo !== 'undefined' && unicpo.price_selector !== undefined ) {
				unicpo.price_selector = '#mdb-total-price';
			}
		},

		// ── Events ────────────────────────────────────────────────────────────

		bindEvents: function () {
			var self = this;

			// Any CPO field change → refresh that item in the sidebar.
			this.$form.on( 'change', '.js-uni-cpo-field', function ( event ) {
				var slug = $( this ).closest( '.uni-module' ).attr( 'id' );
				if ( slug ) {
					if ( event && event.originalEvent && event.originalEvent.isTrusted ) {
						self.userTouched[ slug ] = true;
					}
					self.updateSidebarItem( slug );
				}
			} );

			// Text-like fields should reflect completion immediately while typing.
			this.$form.on( 'input', 'input.js-uni-cpo-field:not([type="radio"]), textarea.js-uni-cpo-field', function ( event ) {
				var slug = $( this ).closest( '.uni-module' ).attr( 'id' );
				if ( slug ) {
					if ( event && event.originalEvent && event.originalEvent.isTrusted ) {
						self.userTouched[ slug ] = true;
					}
					self.updateSidebarItem( slug );
				}
			} );

			$( document ).on( 'click', '.mdb-summary-jump', function ( e ) {
				e.preventDefault();
				self.jumpToModule( $( this ).attr( 'data-slug' ) );
			} );

			/**
			 * After CPO's price-calc AJAX completes or after its conditional-logic
			 * events fire, we wait a tick (setTimeout 0) so CPO's own handlers
			 * have finished updating the DOM before we renumber steps and sync
			 * sidebar item visibility.
			 */
			$( document.body ).on(
				'uni_cpo_options_data_ajax_success uni_cpo_options_data_for_conditional',
				function ( event ) {
					setTimeout( function () {
						self.renumberSteps();
						self.syncSidebarVisibility();
						self.syncDrawerPrice();

						if ( ! self.summaryHydrated && event.type === 'uni_cpo_options_data_for_conditional' ) {
							self.scheduleSummaryHydrationFinalize();
						}
					}, 0 );
				}
			);

			/**
			 * "2. Add to Cart" — click WC's hidden submit button so that CPO's
			 * own form-submit handler (which validates and sends the AJAX
			 * add-to-cart request) fires exactly as normal.
			 */
			$( '#mdb-btn-addtocart, #mdb-btn-addtocart-mobile, #mdb-btn-addtocart-mobile-bottom' ).on( 'click', function () {
				var pendingSlug = self.getFirstPendingSlug();
				if ( pendingSlug ) {
					self.jumpToModule( pendingSlug );
					return;
				}

				self.$form.find( '.single_add_to_cart_button' ).trigger( 'click' );
			} );
		},

		// ── String helpers (no dependency on DOMPurify / wp.utils) ───────────

		escHtml: function ( str ) {
			return $( '<span>' ).text( String( str ) ).html();
		},

		escAttr: function ( str ) {
			return String( str )
				.replace( /&/g, '&amp;' )
				.replace( /"/g, '&quot;' )
				.replace( /'/g, '&#39;' )
				.replace( /</g, '&lt;' )
				.replace( />/g, '&gt;' );
		}
	};

} )( jQuery );
