(function($) {
	$.fn.navDropdown = function(itemWidth) {
		/*
		function updateDropdown($nav) {
			if($('.dropdown-item.active', $nav).length > 0)
				$('.dropdown .nav-link', $nav).addClass('active');
			else if($('.dropdown .nav-link', $nav).hasClass('active'))
				$('.dropdown .nav-link', $nav).removeClass('active');
		}
		*/
		
		function permute($nav) {
			var activeDropdownItem = $('.dropdown-item.active', $nav);
			if(activeDropdownItem.length === 0)
				return;
			var lastNavItem = $('.nav-item', $nav).not('.dropdown').last(),
				oldNavLink = $('a.nav-link', lastNavItem).remove();
			activeDropdownItem.removeClass('dropdown-item').addClass('nav-link');
			lastNavItem.append(activeDropdownItem);
			oldNavLink.addClass('dropdown-item').removeClass('nav-link');
			$('.dropdown-menu', $nav).prepend(oldNavLink);
		}
		
		function process($nav, itemWidth) {
			if($nav.length < 1 || $nav.hasClass('nav') === false)
				return $nav;

			var width = $nav.width(),
				navItems = $('.nav-item', $nav).not('.dropdown'),
				dropdown = $('.dropdown', $nav),
				hasDropdown = dropdown.length,
				nbNavItems = navItems.length,
				std = false,
				stn = false,
				showDropdown = false,
				dataWidth = parseInt($nav.attr('data-width'), 10);
			if(hasDropdown > 0)
				width -= dropdown.outerWidth(true);
			if(dataWidth != undefined && dataWidth > 0)
				itemWidth = dataWidth;
			
			var	nbPossibleNavItems = parseInt(width / itemWidth, 10);
			
			if(hasDropdown === 0) {
				$nav.append('<li class="nav-item dropdown sr-only" style="max-width: 40px;"><a class="nav-link dropdown-toggle text-left" role="button" '
					+ 'aria-expanded="false" aria-haspopup="true" id="dropdownNavButton" data-toggle="dropdown" href="#">' 
					+ '<div class="dropdown-menu dropdown-menu-right"></div></a></li>');
				dropdown = $('.dropdown', $nav);
				hasDropdown = 1;
				$('.dropdown-menu', $nav).on('click', '.dropdown-item', function(e) {
					if(/http/.test(e.currentTarget.href))
						window.location.href = e.currentTarget.href;
					if($(this).attr('role') == 'tab') {
						var activeTabPaneId = $('.active', $nav).removeClass('active').attr('href');
						$(activeTabPaneId).removeClass('show active');
						var newTabPaneId = $(this).attr('href');
						$(this).addClass('active');
						$(newTabPaneId).addClass('show active');
					}
					permute($nav);
					//updateDropdown($nav);
				});
				$nav.on('click', '.nav-link', function(e) {
					if($(this).attr('role') != 'tab')
						return;
					if($(this).parent().hasClass('dropdown'))
						return;
					if($(this).hasClass('nav-link') === false)
						return;
					if($('.dropdown-item', $nav).hasClass('active')) {
						var activeTabPaneId = $('.dropdown-item.active', $nav).removeClass('active').attr('href');
						$(activeTabPaneId).removeClass('show active');
					}
				});
			
				$('.dropdown', $nav).on('shown.bs.dropdown', function(e) {
					$(this).addClass('dropup');
				});
				
				$('.dropdown', $nav).on('hidden.bs.dropdown', function() {
					$(this).removeClass('dropup');
					//updateDropdown($nav);
				});
			}
			
			var dropdownItems = $('.dropdown-item', $nav),
				dropdownItemsLength = dropdownItems.length,
				nbPotentialSwapItems = nbPossibleNavItems - nbNavItems;
					
			if(nbPotentialSwapItems >= 0) {
				if(dropdownItemsLength > 0) 
					stn = true;
			}
			else if( nbNavItems > 1) {
				nbPotentialSwapItems = Math.abs(nbPotentialSwapItems);
				showDropdown = true;
				std = true;
			}

			if(stn) {
				var	navLinks = dropdownItems.filter(function(index, element) {
					return index < nbPotentialSwapItems;
				}).remove();
				
				navLinks.removeClass('dropdown-item').addClass('nav-link').insertBefore(dropdown)
					.wrap('<li class="nav-item"></li>');			
			}
			
			if(std) {
				var newDropdownItems = navItems.filter(function(index, element) {
					return index >= nbPossibleNavItems;
				}).remove();	
				$('a', newDropdownItems).removeClass('nav-link').addClass('dropdown-item').prependTo($('.dropdown-menu', $nav));
			}
					
			if($('.dropdown-item', $nav).length === 0)
				showDropdown = false;
			else
				showDropdown = true;
			
			if(showDropdown) 
				dropdown.removeClass('sr-only');
			else
				dropdown.addClass('sr-only');
			permute($nav);
			//updateDropdown($nav);
		}
		
		this.each(function() {
			$nav = $(this);
			process($nav, itemWidth);
			$(window).on('resize', function() {
				process($nav, itemWidth);
			});
		});					
		
		return this;
	};
})(jQuery);