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

            // Handle clear button click
            $(document).on('click', '.wc-block-product-filter-clear-button button', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.clearAllFilters();
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
            // Comprehensive auto-update prevention for all filter types
            
            // 1. Prevent all input/change events on filter elements
            $(document).on('change input click', '.wc-block-product-filters input, .wc-block-product-filters select, .wc-block-product-filters button', (e) => {
                // Allow our custom submit button
                if (e.target.classList.contains('bottigo-filters-submit')) {
                    return;
                }
                
                // Allow clear button functionality but prevent navigation
                if (e.target.hasAttribute('data-wp-on--click') && 
                    e.target.getAttribute('data-wp-on--click').includes('removeAllActiveFilters')) {
                    e.stopPropagation();
                    e.preventDefault();
                    // Handle clear manually without navigation
                    this.clearAllFilters();
                    return false;
                }
                
                // Prevent all other automatic navigation
                e.stopPropagation();
                e.stopImmediatePropagation();
            });

            // 2. Disable range slider auto-navigation (price filters)
            $(document).on('mouseup keyup touchend input change', '.wc-block-product-filter-price-slider input[type="range"]', (e) => {
                e.stopPropagation();
                e.stopImmediatePropagation();
            });

            // 3. Disable checkbox auto-navigation (attribute filters)
            $(document).on('change click', '.wc-block-product-filter-checkbox-list input[type="checkbox"]', (e) => {
                e.stopPropagation();
                e.stopImmediatePropagation();
            });

            // 4. Disable category filter auto-navigation
            $(document).on('change click', '.wc-block-product-filter-taxonomy input, .wc-block-product-filter-taxonomy select', (e) => {
                e.stopPropagation();
                e.stopImmediatePropagation();
            });

            // 5. Disable any dropdown/select filters
            $(document).on('change', '.wc-block-product-filters select', (e) => {
                e.stopPropagation();
                e.stopImmediatePropagation();
            });

            // 6. Override any WordPress Interactivity API actions
            this.overrideInteractivityActions();
        }

        overrideInteractivityActions() {
            // Override WordPress Interactivity API to prevent automatic navigation
            const originalAddEventListener = EventTarget.prototype.addEventListener;
            
            EventTarget.prototype.addEventListener = function(type, listener, options) {
                // Check if this is a WooCommerce filter element
                if (this.closest && this.closest('.wc-block-product-filters')) {
                    // Allow our custom events
                    if (listener.toString().includes('bottigo-filters') || 
                        this.classList.contains('bottigo-filters-submit')) {
                        return originalAddEventListener.call(this, type, listener, options);
                    }
                    
                    // Block navigation-related events
                    if (type === 'change' || type === 'input' || type === 'click') {
                        if (listener.toString().includes('navigate') || 
                            listener.toString().includes('actions.')) {
                            // Don't add the listener that causes navigation
                            return;
                        }
                    }
                }
                
                return originalAddEventListener.call(this, type, listener, options);
            };
        }

        clearAllFilters() {
            // Custom clear filters implementation without navigation
            const filterElements = document.querySelectorAll('.wc-block-product-filters input, .wc-block-product-filters select');
            
            filterElements.forEach(element => {
                if (element.type === 'checkbox' || element.type === 'radio') {
                    element.checked = false;
                } else if (element.type === 'range') {
                    // Reset range inputs to their default values
                    const min = element.getAttribute('min');
                    const max = element.getAttribute('max');
                    if (element.classList.contains('min')) {
                        element.value = min;
                    } else if (element.classList.contains('max')) {
                        element.value = max;
                    }
                } else if (element.tagName === 'SELECT') {
                    element.selectedIndex = 0;
                } else if (element.type === 'text') {
                    // Reset price text inputs
                    const rangeInput = element.parentNode.querySelector('input[type="range"]');
                    if (rangeInput) {
                        if (element.classList.contains('min')) {
                            element.value = rangeInput.getAttribute('min') + ' ₸';
                        } else if (element.classList.contains('max')) {
                            element.value = rangeInput.getAttribute('max') + ' ₸';
                        }
                    }
                }
            });
            
            // Update visual state without navigation
            this.updateFilterVisualState();
        }

        updateFilterVisualState() {
            // Update any visual indicators without triggering navigation
            const activeFiltersContainer = document.querySelector('.wc-block-product-filter-active');
            if (activeFiltersContainer) {
                activeFiltersContainer.style.display = 'none';
            }
            
            // Update price display
            const priceSliders = document.querySelectorAll('.wc-block-product-filter-price-slider');
            priceSliders.forEach(slider => {
                const minInput = slider.querySelector('input[type="range"].min');
                const maxInput = slider.querySelector('input[type="range"].max');
                const minText = slider.querySelector('input[type="text"].min');
                const maxText = slider.querySelector('input[type="text"].max');
                
                if (minInput && maxInput && minText && maxText) {
                    const minValue = minInput.getAttribute('min');
                    const maxValue = maxInput.getAttribute('max');
                    
                    minInput.value = minValue;
                    maxInput.value = maxValue;
                    minText.value = minValue + ' ₸';
                    maxText.value = maxValue + ' ₸';
                }
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
            
            // 1. Collect price filter
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
            
            // 2. Collect attribute filters (размер обуви, цвет и т.д.)
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
                        console.warn('Could not parse attribute filter context:', e);
                    }
                }
            });
            
            // 3. Collect category filters
            const categoryCheckboxes = document.querySelectorAll('.wc-block-product-filter-taxonomy input[type="checkbox"]:checked');
            categoryCheckboxes.forEach(checkbox => {
                const value = checkbox.value;
                const context = checkbox.getAttribute('data-wp-context');
                
                if (context) {
                    try {
                        const contextData = JSON.parse(context);
                        const filterType = contextData.item?.type;
                        
                        if (filterType === 'taxonomy/product_cat') {
                            if (!data.filter_product_cat) {
                                data.filter_product_cat = [];
                            }
                            data.filter_product_cat.push(value);
                        } else if (filterType === 'taxonomy/product_tag') {
                            if (!data.filter_product_tag) {
                                data.filter_product_tag = [];
                            }
                            data.filter_product_tag.push(value);
                        }
                    } catch (e) {
                        console.warn('Could not parse taxonomy filter context:', e);
                    }
                }
            });
            
            // 4. Collect dropdown/select filters
            const selectFilters = document.querySelectorAll('.wc-block-product-filters select');
            selectFilters.forEach(select => {
                if (select.value && select.value !== '' && select.selectedIndex > 0) {
                    const name = select.name || select.getAttribute('data-filter-name');
                    if (name) {
                        data[name] = select.value;
                    }
                }
            });
            
            // 5. Collect radio button filters
            const radioFilters = document.querySelectorAll('.wc-block-product-filters input[type="radio"]:checked');
            radioFilters.forEach(radio => {
                const name = radio.name;
                const value = radio.value;
                if (name && value) {
                    data[name] = value;
                }
            });
            
            // 6. Collect any other custom filters
            const customFilters = document.querySelectorAll('.wc-block-product-filters [data-filter-type]');
            customFilters.forEach(filter => {
                const filterType = filter.getAttribute('data-filter-type');
                const filterValue = filter.value || filter.getAttribute('data-filter-value');
                
                if (filterType && filterValue) {
                    if (!data[filterType]) {
                        data[filterType] = [];
                    }
                    if (Array.isArray(data[filterType])) {
                        data[filterType].push(filterValue);
                    } else {
                        data[filterType] = filterValue;
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