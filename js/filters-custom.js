/**
 * Custom WooCommerce Product Filters with Submit Button
 * Replaces auto-update functionality with manual submit
 */
(function($) {
    'use strict';

    class BottigoFilters {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
            this.disableAutoUpdate();
        }

        bindEvents() {
            // Wait for DOM to be ready and filters to be loaded
            $(document).ready(() => {
                this.setupSubmitButton();
            });

            // Handle dynamic content loading
            $(document).on('click', '.bottigo-filters-submit', (e) => {
                e.preventDefault();
                this.submitFilters();
            });

            // Prevent auto-navigation on filter changes
            this.preventAutoNavigation();
        }

        setupSubmitButton() {
            // Wait a bit for WooCommerce blocks to initialize
            setTimeout(() => {
                const filtersContent = document.querySelector('.wc-block-product-filters__overlay-content');
                const existingSubmit = document.querySelector('.bottigo-filters-submit-container');
                
                if (filtersContent && !existingSubmit) {
                    this.addSubmitButton(filtersContent);
                }
            }, 500);
        }

        addSubmitButton(container) {
            const submitContainer = document.createElement('div');
            submitContainer.className = 'bottigo-filters-submit-container';
            
            const submitButton = document.createElement('button');
            submitButton.className = 'bottigo-filters-submit';
            submitButton.textContent = 'Применить фильтры';
            submitButton.type = 'button';
            
            // Add accessibility attributes
            submitButton.setAttribute('aria-label', 'Применить выбранные фильтры товаров');
            
            submitContainer.appendChild(submitButton);
            container.appendChild(submitContainer);
        }

        disableAutoUpdate() {
            // Override WooCommerce Interactivity API navigation
            $(document).on('change input', '.wc-block-product-filters input, .wc-block-product-filters select', (e) => {
                // Prevent the default navigation behavior
                e.stopPropagation();
            });

            // Disable range slider auto-navigation
            $(document).on('mouseup keyup touchend', '.wc-block-product-filter-price-slider input[type="range"]', (e) => {
                e.stopPropagation();
            });
        }

        preventAutoNavigation() {
            // Intercept and prevent automatic navigation
            const originalPushState = history.pushState;
            const originalReplaceState = history.replaceState;
            
            // Track if navigation was triggered by our submit button
            let manualSubmit = false;
            
            history.pushState = function(...args) {
                if (!manualSubmit && args[2] && args[2].includes('filter')) {
                    // Prevent automatic filter navigation
                    return;
                }
                return originalPushState.apply(history, args);
            };
            
            history.replaceState = function(...args) {
                if (!manualSubmit && args[2] && args[2].includes('filter')) {
                    // Prevent automatic filter navigation
                    return;
                }
                return originalReplaceState.apply(history, args);
            };
            
            // Store reference for submit method
            this.setManualSubmit = (value) => {
                manualSubmit = value;
            };
        }

        submitFilters() {
            const submitButton = document.querySelector('.bottigo-filters-submit');
            if (!submitButton) return;

            // Show loading state
            submitButton.disabled = true;
            submitButton.textContent = 'Применение...';
            
            // Add loading class to filters container
            const filtersContainer = document.querySelector('.wc-block-product-filters');
            if (filtersContainer) {
                filtersContainer.classList.add('bottigo-filters-loading');
            }

            // Collect all filter values
            const filterData = this.collectFilterData();
            
            // Build URL with filters
            const baseUrl = bottigoFilters.shop_url || window.location.pathname;
            const url = this.buildFilterUrl(baseUrl, filterData);
            
            // Enable manual navigation
            if (this.setManualSubmit) {
                this.setManualSubmit(true);
            }
            
            // Navigate to filtered results
            window.location.href = url;
        }

        collectFilterData() {
            const data = {};
            
            // Collect price filter
            const minPriceInput = document.querySelector('.wc-block-product-filter-price-slider input.min');
            const maxPriceInput = document.querySelector('.wc-block-product-filter-price-slider input.max');
            
            if (minPriceInput && maxPriceInput) {
                const minPrice = minPriceInput.value;
                const maxPrice = maxPriceInput.value;
                const minRange = minPriceInput.getAttribute('min');
                const maxRange = maxPriceInput.getAttribute('max');
                
                // Only add price filter if values differ from defaults
                if (minPrice !== minRange || maxPrice !== maxRange) {
                    data.min_price = minPrice;
                    data.max_price = maxPrice;
                }
            }
            
            // Collect attribute filters
            const attributeCheckboxes = document.querySelectorAll('.wc-block-product-filter-checkbox-list input[type="checkbox"]:checked');
            attributeCheckboxes.forEach(checkbox => {
                const value = checkbox.value;
                const context = checkbox.getAttribute('data-wp-context');
                
                if (context) {
                    try {
                        const contextData = JSON.parse(context);
                        const filterType = contextData.item?.type;
                        
                        if (filterType && filterType.startsWith('attribute/')) {
                            const attributeName = filterType.replace('attribute/', '');
                            
                            if (!data[`filter_${attributeName}`]) {
                                data[`filter_${attributeName}`] = [];
                            }
                            data[`filter_${attributeName}`].push(value);
                        }
                    } catch (e) {
                        console.warn('Could not parse filter context:', e);
                    }
                }
            });
            
            return data;
        }

        buildFilterUrl(baseUrl, filterData) {
            const url = new URL(baseUrl, window.location.origin);
            
            // Add filter parameters
            Object.keys(filterData).forEach(key => {
                const value = filterData[key];
                if (Array.isArray(value)) {
                    url.searchParams.set(key, value.join(','));
                } else {
                    url.searchParams.set(key, value);
                }
            });
            
            return url.toString();
        }
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        new BottigoFilters();
    });

    // Also initialize on AJAX complete (for dynamic content)
    $(document).ajaxComplete(function() {
        // Small delay to ensure content is rendered
        setTimeout(() => {
            if (!window.bottigoFiltersInstance) {
                window.bottigoFiltersInstance = new BottigoFilters();
            }
        }, 100);
    });

})(jQuery);