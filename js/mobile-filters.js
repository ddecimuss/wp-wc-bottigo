/**
 * Mobile Filters Button Handler
 * 
 * This script handles the mobile filters button functionality by:
 * 1. Listening for clicks on the mobile filters button
 * 2. Finding the original WooCommerce filters button in the sidebar
 * 3. Programmatically triggering a click on the original button
 * 4. This ensures compatibility with WordPress Interactivity API
 */

document.addEventListener('DOMContentLoaded', function() {
    // Find the mobile filters button
    const mobileFiltersButton = document.querySelector('.mobile-filters-button');
    
    if (!mobileFiltersButton) {
        return; // No mobile button found, exit
    }
    
    // Function to remove overlay footer
    function removeOverlayFooter() {
        const overlayFooter = document.querySelector('.wc-block-product-filters__overlay-footer');
        if (overlayFooter) {
            overlayFooter.remove();
            console.log('Overlay footer removed');
        }
    }
    
    // Add click event listener to mobile button
    mobileFiltersButton.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Find the original WooCommerce filters button in the sidebar
        const originalFiltersButton = document.querySelector('.wc-block-product-filters__open-overlay');
        
        if (originalFiltersButton) {
            // Programmatically trigger click on the original button
            // This ensures the WordPress Interactivity API works correctly
            originalFiltersButton.click();
            
            // Remove overlay footer after a short delay to ensure overlay is loaded
            setTimeout(removeOverlayFooter, 100);
            
            // Optional: Add visual feedback
            mobileFiltersButton.classList.add('clicked');
            setTimeout(() => {
                mobileFiltersButton.classList.remove('clicked');
            }, 200);
        } else {
            // Fallback: if no WooCommerce filters found, show a message
            console.warn('WooCommerce product filters not found. Make sure the Product Filters block is added to the sidebar.');
            
            // Optional: Show user-friendly message
            alert('Фильтры товаров недоступны. Пожалуйста, обратитесь к администратору сайта.');
        }
    });
    
    // Optional: Handle keyboard navigation (Enter and Space keys)
    mobileFiltersButton.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            mobileFiltersButton.click();
        }
    });
    
    // Also remove footer when overlay is opened by original button
    const originalButton = document.querySelector('.wc-block-product-filters__open-overlay');
    if (originalButton) {
        originalButton.addEventListener('click', function() {
            setTimeout(removeOverlayFooter, 100);
        });
    }
    
    // Use MutationObserver to watch for overlay footer being added to DOM
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    // Check if the added node is the overlay footer or contains it
                    if (node.classList && node.classList.contains('wc-block-product-filters__overlay-footer')) {
                        node.remove();
                        console.log('Overlay footer removed via MutationObserver');
                    } else {
                        // Check if overlay footer was added inside this node
                        const overlayFooter = node.querySelector && node.querySelector('.wc-block-product-filters__overlay-footer');
                        if (overlayFooter) {
                            overlayFooter.remove();
                            console.log('Overlay footer removed via MutationObserver (nested)');
                        }
                    }
                }
            });
        });
    });
    
    // Start observing
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});

/**
 * Optional: Handle window resize to ensure proper visibility
 * This ensures the mobile button is properly hidden/shown when window is resized
 */
window.addEventListener('resize', function() {
    const mobileButton = document.querySelector('.mobile-filters-button-wrapper');
    if (mobileButton) {
        // Force re-evaluation of CSS media queries
        mobileButton.style.display = '';
    }
});