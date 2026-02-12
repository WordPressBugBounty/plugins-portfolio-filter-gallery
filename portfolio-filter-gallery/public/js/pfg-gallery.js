/**
 * Portfolio Filter Gallery - Core JavaScript
 * Vanilla JS - No jQuery dependency
 * Supports multi-filter selection and AND/OR logic
 *
 * @package Portfolio_Filter_Gallery
 * @version 2.1.0
 */

(function () {
  "use strict";

  /**
   * Gallery class for handling filtering and interactions
   */
  class PFGGallery {
    constructor(container) {
      this.container = container;
      this.galleryId = container.dataset.galleryId;
      this.grid = container.querySelector(".pfg-grid");
      this.filtersContainer = container.querySelector(".pfg-filters");
      this.filters = container.querySelectorAll(".pfg-filter");
      this.items = container.querySelectorAll(".pfg-item");
      this.searchInput = container.querySelector(".pfg-search-input");
      this.logicToggle = container.querySelector(".pfg-logic-toggle");

      // Multi-filter settings
      this.multiSelect = container.dataset.multiSelect === "true";
      this.filterLogic = container.dataset.filterLogic || "or"; // 'and' or 'or'
      this.activeFilters = new Set();
      this.searchTerm = "";

      // Deep linking settings
      this.deepLinking = container.dataset.deepLinking === "true";
      this.urlParam = container.dataset.urlParam || "filter";
      this.defaultFilter = container.dataset.defaultFilter || "";

      // Filter hierarchy (parent slug => array of child slugs)
      this.filterHierarchy = {};
      try {
        this.filterHierarchy = JSON.parse(
          container.dataset.filterHierarchy || "{}"
        );
      } catch (e) {
        console.warn("PFG: Failed to parse filter hierarchy", e);
      }

      this.init();
    }

    init() {
      this.bindFilters();
      this.bindSearch();
      this.bindLogicToggle();
      this.bindCascadingDropdowns();
      this.initLazyLoading();
      this.assignStaggerDelays();
      this.initDeepLinking();
      this.initPackedLayout();
      this.initPreloader();
    }

    /**
     * Initialize preloader - hide after images load
     */
    initPreloader() {
      // Check if all images are already loaded (cached)
      const images = Array.from(this.container.querySelectorAll('.pfg-grid img'));
      const allLoaded = images.every(img => img.complete && img.naturalHeight !== 0);
      
      if (allLoaded) {
        // Images already loaded, remove preloader immediately
        this.removePreloader();
        return;
      }
      
      // Wait for images to load
      this.waitForImagesMain().then(() => {
        this.removePreloader();
      });
      
      // Fallback: always hide preloader after 3 seconds max
      setTimeout(() => {
        this.removePreloader();
      }, 3000);
    }

    /**
     * Wait for images in main grid
     */
    waitForImagesMain() {
      const images = Array.from(this.container.querySelectorAll('.pfg-grid img'));
      const promises = images.map((img) => {
        if (img.complete) {
          return Promise.resolve();
        }
        return new Promise((resolve) => {
          img.addEventListener('load', resolve, { once: true });
          img.addEventListener('error', resolve, { once: true });
          // Timeout fallback
          setTimeout(resolve, 5000);
        });
      });
      return Promise.all(promises);
    }

    /**
     * Remove preloader and show gallery
     */
    removePreloader() {
      this.container.classList.remove('pfg-loading');
      this.container.classList.add('pfg-loaded');
    }

    /**
     * Initialize URL filter and default filter
     * URL param ALWAYS works, Deep Linking only controls URL updates on click
     */
    initDeepLinking() {
      // Check URL param first (highest priority, always works)
      const urlParams = new URLSearchParams(window.location.search);
      const urlFilter = urlParams.get(this.urlParam);

      if (urlFilter) {
        this.activateFilterBySlug(urlFilter);
        return;
      }

      // Otherwise, activate default filter if set
      if (this.defaultFilter) {
        this.activateFilterBySlug(this.defaultFilter);
      } else {
        // No URL filter and no default filter - activate "All" button
        const allBtn = this.container.querySelector('[data-filter="*"]');
        if (allBtn) {
          allBtn.classList.add("pfg-filter--active");
        }
      }
    }

    /**
     * Activate a filter by its slug
     */
    activateFilterBySlug(slug) {
      const filterBtn = this.container.querySelector(
        `.pfg-filter[data-filter="${slug}"]`
      );
      if (filterBtn) {
        this.setSingleFilter(slug, filterBtn);
        this.filterItems();
      }
    }

    /**
     * Update URL with current filter (deep linking)
     */
    updateUrl(filter) {
      if (!this.deepLinking) return;

      const url = new URL(window.location.href);

      if (filter === "*" || !filter) {
        url.searchParams.delete(this.urlParam);
      } else {
        url.searchParams.set(this.urlParam, filter);
      }

      window.history.replaceState({}, "", url.toString());
    }

    /**
     * Assign stagger delays to items for animation
     */
    assignStaggerDelays() {
      this.items.forEach((item, index) => {
        item.dataset.delay = (index % 6) + 1;
      });
    }

    /**
     * Bind filter button click events
     */
    bindFilters() {
      this.filters.forEach((filter) => {
        filter.addEventListener("click", (e) => {
          e.preventDefault();
          const filterValue = filter.dataset.filter;

          if (this.multiSelect && filterValue !== "*") {
            // Multi-select mode: toggle filter
            this.toggleFilter(filterValue, filter);
          } else {
            // Single-select mode or "All" clicked
            this.setSingleFilter(filterValue, filter);
          }

          this.filterItems();
        });
      });
    }

    /**
     * Toggle a filter in multi-select mode
     */
    toggleFilter(filter, button) {
      if (this.activeFilters.has(filter)) {
        this.activeFilters.delete(filter);
        button.classList.remove("pfg-filter--selected", "pfg-filter--active");
      } else {
        this.activeFilters.add(filter);
        button.classList.add("pfg-filter--selected", "pfg-filter--active");
      }

      // Update "All" button state
      const allBtn = this.container.querySelector('[data-filter="*"]');
      if (allBtn) {
        if (this.activeFilters.size === 0) {
          allBtn.classList.add("pfg-filter--active");
        } else {
          allBtn.classList.remove("pfg-filter--active");
        }
      }
    }

    /**
     * Set single active filter (clears others)
     */
    setSingleFilter(filter, button) {
      this.activeFilters.clear();

      if (filter !== "*") {
        this.activeFilters.add(filter);
      }

      // Update button states
      this.filters.forEach((btn) => {
        btn.classList.remove("pfg-filter--active", "pfg-filter--selected");
      });
      button.classList.add("pfg-filter--active");

      // Update URL for deep linking
      this.updateUrl(filter);
    }

    /**
     * Bind search input events
     */
    bindSearch() {
      if (!this.searchInput) return;

      let debounceTimer;
      this.searchInput.addEventListener("input", (e) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
          this.searchTerm = e.target.value.toLowerCase().trim();
          this.filterItems();
        }, 300);
      });
    }

    /**
     * Bind AND/OR logic toggle
     */
    bindLogicToggle() {
      if (!this.logicToggle) return;

      const buttons = this.logicToggle.querySelectorAll(".pfg-logic-btn");
      buttons.forEach((btn) => {
        btn.addEventListener("click", () => {
          this.filterLogic = btn.dataset.logic;
          
          // Update container dataset so AJAX picks up the current logic
          this.container.dataset.filterLogic = this.filterLogic;

          // Update button states
          buttons.forEach((b) => b.classList.remove("pfg-logic-btn--active"));
          btn.classList.add("pfg-logic-btn--active");

          this.filterItems();
        });
      });
    }

    /**
     * Bind cascading dropdown events (single-level for free version)
     */
    bindCascadingDropdowns() {
      const dropdownContainer = this.container.querySelector('.pfg-cascading-dropdowns');
      if (!dropdownContainer) return;

      const level1Select = dropdownContainer.querySelector('.pfg-level1-select');
      if (!level1Select) return;

      // Level 1 dropdown change
      level1Select.addEventListener('change', () => {
        const level1Value = level1Select.value;

        // Clear active filters and set new one
        this.activeFilters.clear();
        if (level1Value !== '*') {
          this.activeFilters.add(level1Value);
        }

        // Update URL if deep linking enabled
        if (this.deepLinking && level1Value !== '*') {
          this.updateUrl(level1Value);
        } else if (this.deepLinking) {
          this.updateUrl('*');
        }

        this.filterItems();
      });
    }

    /**
     * Filter gallery items with smooth animation
     * Uses masonry-aware repositioning for masonry layouts,
     * FLIP animation for other layouts
     */
    filterItems() {
      // Re-query items from DOM to include any dynamically loaded items (Load More)
      this.items = this.container.querySelectorAll(".pfg-item");
      
      // Check if masonry layout — needs different animation strategy
      const isMasonry = this.grid && this.grid.classList.contains("pfg-grid--masonry");
      
      if (isMasonry) {
        this._filterItemsMasonry();
      } else {
        this._filterItemsFLIP();
      }

      // Dispatch custom event for other scripts
      let visibleCount = 0;
      this.items.forEach((item) => {
        if (!item.classList.contains("pfg-item--hidden") && !item.classList.contains("pfg-item--hiding")) {
          visibleCount++;
        }
      });
      this.container.dispatchEvent(
        new CustomEvent("pfg:filtered", {
          bubbles: true,
          detail: {
            filters: Array.from(this.activeFilters),
            logic: this.filterLogic,
            search: this.searchTerm,
            visibleCount: visibleCount,
          },
        })
      );

      // Update numbered pagination after filtering
      this.updateNumberedPaginationAfterFilter();
      
      // Reset Load More button visibility when filter changes
      this.resetLoadMoreButton();
    }

    /**
     * Masonry-aware filtering: hide/show items then recalculate all positions.
     * CSS transitions on left/top handle smooth repositioning of remaining items.
     */
    _filterItemsMasonry() {
      const itemsToShow = [];
      const itemsToHide = [];

      this.items.forEach((item) => {
        const matchesFilter = this.itemMatchesFilter(item);
        const matchesSearch = this.itemMatchesSearch(item);
        const wasHidden = item.classList.contains("pfg-item--hidden");

        if (matchesFilter && matchesSearch) {
          if (wasHidden) {
            itemsToShow.push(item);
          }
        } else if (!wasHidden) {
          itemsToHide.push(item);
        }
      });

      // Phase 1: Fade out items that need hiding
      itemsToHide.forEach((item) => {
        item.classList.remove("pfg-item--visible", "pfg-item--hidden");
        item.classList.add("pfg-item--hiding");
      });

      // Phase 2: After fade-out completes, collapse hidden items and recalculate layout
      setTimeout(() => {
        // Mark hidden items
        itemsToHide.forEach((item) => {
          item.classList.remove("pfg-item--hiding");
          item.classList.add("pfg-item--hidden");
          item.classList.remove("pfg-item--positioned");
        });

        // Prepare items to show (they'll be positioned by applyMosaicLayout)
        itemsToShow.forEach((item) => {
          item.classList.remove("pfg-item--hidden", "pfg-item--hiding");
          item.classList.add("pfg-item--visible");
          item.classList.remove("pfg-item--positioned");
        });

        // Recalculate all masonry positions
        this.applyMosaicLayout();

      }, 250); // Wait for hide (opacity) animation
    }

    /**
     * FLIP-based filtering for non-masonry layouts
     * FLIP = First, Last, Invert, Play - for smooth position animations
     */
    _filterItemsFLIP() {
      // FLIP Step 1: FIRST - Record current positions of ALL items
      const firstPositions = new Map();
      this.items.forEach((item) => {
        if (!item.classList.contains("pfg-item--hidden")) {
          const rect = item.getBoundingClientRect();
          firstPositions.set(item, { x: rect.left, y: rect.top });
        }
      });
      
      let visibleIndex = 0;
      const itemsToShow = [];
      const itemsToHide = [];
      const itemsToAnimate = [];

      this.items.forEach((item) => {
        const matchesFilter = this.itemMatchesFilter(item);
        const matchesSearch = this.itemMatchesSearch(item);
        const wasVisible = !item.classList.contains("pfg-item--hidden");

        if (matchesFilter && matchesSearch) {
          if (wasVisible) {
            itemsToAnimate.push(item);
          } else {
            itemsToShow.push({ item, index: visibleIndex });
          }
          visibleIndex++;
        } else if (wasVisible) {
          itemsToHide.push(item);
        }
      });

      itemsToHide.forEach((item) => {
        item.classList.remove("pfg-item--visible", "pfg-item--hidden");
        item.classList.add("pfg-item--hiding");
      });

      setTimeout(() => {
        itemsToHide.forEach((item) => {
          item.classList.remove("pfg-item--hiding");
          item.classList.add("pfg-item--hidden");
        });

        itemsToShow.forEach(({ item }) => {
          item.classList.remove("pfg-item--hidden", "pfg-item--hiding");
          item.style.opacity = "0";
          item.style.transform = "scale(0.9)";
          item.style.transition = "none";
        });

        void this.container.offsetHeight;

        const lastPositions = new Map();
        [...itemsToAnimate, ...itemsToShow.map(i => i.item)].forEach((item) => {
          const rect = item.getBoundingClientRect();
          lastPositions.set(item, { x: rect.left, y: rect.top });
        });

        itemsToAnimate.forEach((item) => {
          const first = firstPositions.get(item);
          const last = lastPositions.get(item);
          item.style.opacity = "1";
          
          if (first && last) {
            const deltaX = first.x - last.x;
            const deltaY = first.y - last.y;
            const didMove = Math.abs(deltaX) > 1 || Math.abs(deltaY) > 1;
            
            item.style.transition = "none";
            if (didMove) {
              item.style.transform = `translate(${deltaX}px, ${deltaY}px) scale(0.97)`;
            } else {
              item.style.transform = "scale(0.97)";
            }
            void item.offsetHeight;
            item.style.transition = "transform 0.35s cubic-bezier(0.4, 0, 0.2, 1)";
            item.style.transform = "translate(0, 0) scale(1)";
          } else {
            item.style.transition = "none";
            item.style.transform = "scale(0.97)";
            void item.offsetHeight;
            item.style.transition = "transform 0.35s cubic-bezier(0.4, 0, 0.2, 1)";
            item.style.transform = "scale(1)";
          }
        });

        itemsToShow.forEach(({ item, index }) => {
          const delay = index * 50;
          setTimeout(() => {
            item.style.transition = "opacity 0.3s ease-out, transform 0.3s ease-out";
            item.style.opacity = "1";
            item.style.transform = "scale(1)";
          }, delay);
        });

        setTimeout(() => {
          itemsToAnimate.forEach((item) => {
            item.style.transition = "";
            item.style.transform = "";
            item.style.opacity = "";
          });
          itemsToShow.forEach(({ item }) => {
            item.style.transition = "";
            item.style.transform = "";
            item.style.opacity = "";
            item.classList.add("pfg-item--visible");
          });
        }, 500);

      }, 280);
    }
    
    /**
     * Reset Load More button visibility when filter changes
     * Shows the button if there are more items to load for the current filter
     */
    resetLoadMoreButton() {
      const gallery = this.container.closest(".pfg-gallery-wrapper");
      if (!gallery) return;
      
      const btn = gallery.querySelector(".pfg-load-more");
      if (!btn) return;
      
      const isPaginationEnabled = gallery.dataset.pagination === "true";
      if (!isPaginationEnabled) return;
      
      // Get current active filter(s)
      const isAllFilter = this.activeFilters.size === 0 || this.activeFilters.has("*");
      
      let filterTotal = 0;
      let loadedMatching = 0;
      
      if (isAllFilter) {
        // For "All" filter, use global total
        filterTotal = parseInt(gallery.dataset.totalItems) || 0;
        loadedMatching = gallery.querySelectorAll(".pfg-item").length;
      } else {
        // For specific filter, get count from filter button
        const activeFilterSlug = Array.from(this.activeFilters)[0];
        const filterBtn = this.container.querySelector(`.pfg-filter[data-filter="${activeFilterSlug}"]`);
        
        if (filterBtn) {
          // Try to get count from button text (e.g., "Wedding Portraits (2)")
          const countMatch = filterBtn.textContent.match(/\((\d+)\)/);
          if (countMatch) {
            filterTotal = parseInt(countMatch[1]) || 0;
          }
        }
        
        // Count how many items matching this filter are already in DOM
        // Use itemMatchesFilter to properly handle parent/child filter relationships
        const allItems = gallery.querySelectorAll(".pfg-item");
        allItems.forEach(item => {
          if (this.itemMatchesFilter(item)) {
            loadedMatching++;
          }
        });
      }
      
      const remaining = filterTotal - loadedMatching;
      
      if (remaining > 0) {
        // Show button with accurate remaining count
        btn.style.display = "";
        const countEl = btn.querySelector(".pfg-load-more-count");
        if (countEl) {
          countEl.textContent = `(${remaining} remaining)`;
          countEl.style.display = ""; // Ensure count is visible (may have been hidden by AJAX response)
        }
      } else {
        // No more items for this filter - hide button
        btn.style.display = "none";
      }
    }
    
    /**
     * Apply mosaic layout with FLIP animation for smooth repositioning
     */
    applyMosaicLayoutWithFLIP(firstPositions) {
      // Apply new layout (this sets new --pfg-x and --pfg-y values)
      this.applyMosaicLayout();
      
      // FLIP Step 2 & 3: After layout applied, calculate inverse transforms
      // The CSS will handle the smooth animation via transition
    }

    /**
     * Check if item matches current filter(s)
     */
    itemMatchesFilter(item) {
      // No filters active = show all
      if (this.activeFilters.size === 0) return true;

      const itemFilters = this.getItemFilters(item);

      // Expand active filters to include their children
      const expandedFilters = this.expandFiltersWithChildren([
        ...this.activeFilters,
      ]);

      if (this.filterLogic === "and") {
        // AND logic: item must match ALL active filters (or their children)
        const result = [...this.activeFilters].every((filter) => {
          const filterAndChildren = [
            filter,
            ...(this.filterHierarchy[filter] || []),
          ];
          const matches = filterAndChildren.some((f) => itemFilters.includes(f));
          return matches;
        });
        return result;
      } else {
        // OR logic: item must match ANY active filter (or their children)
        return expandedFilters.some((filter) => itemFilters.includes(filter));
      }
    }

    /**
     * Expand filter list to include all child filters
     */
    expandFiltersWithChildren(filters) {
      const expanded = new Set(filters);
      filters.forEach((filter) => {
        if (this.filterHierarchy[filter]) {
          this.filterHierarchy[filter].forEach((child) => expanded.add(child));
        }
      });
      return [...expanded];
    }

    /**
     * Get all filter slugs for an item
     */
    getItemFilters(item) {
      const classes = Array.from(item.classList);
      return classes
        .filter((c) => c.startsWith("pfg-filter-"))
        .map((c) => c.replace("pfg-filter-", ""));
    }

    /**
     * Check if item matches search term
     */
    itemMatchesSearch(item) {
      if (!this.searchTerm) return true;

      const title = item.querySelector(".pfg-item-title");
      const alt = item.querySelector(".pfg-item-image")?.alt || "";

      const searchableText = (title?.textContent || "") + " " + alt;
      return searchableText.toLowerCase().includes(this.searchTerm);
    }

    /**
     * Show an item with smooth animation
     */
    showItem(item, index) {
      // Remove hidden class
      item.classList.remove("pfg-item--hidden");

      // Remove any previous visible class and re-add for animation
      item.classList.remove("pfg-item--visible");

      // Force reflow to restart animation
      void item.offsetWidth;

      // Add visible class with stagger delay for smooth sequential animation
      const delay = (index % 8) * 50; // 50ms stagger between items
      setTimeout(() => {
        item.classList.add("pfg-item--visible");
      }, delay);

      // Reset inline styles
      item.style.maxHeight = "";
      item.style.margin = "";
      item.style.padding = "";
    }

    /**
     * Hide an item with smooth animation
     */
    hideItem(item) {
      // Just add hidden class - CSS handles animation
      item.classList.add("pfg-item--hidden");
    }

    /**
     * Initialize lazy loading for images
     */
    initLazyLoading() {
      if ("loading" in HTMLImageElement.prototype) {
        // Native lazy loading supported
        return;
      }

      // Fallback for browsers without native lazy loading
      if ("IntersectionObserver" in window) {
        const imageObserver = new IntersectionObserver(
          (entries, observer) => {
            entries.forEach((entry) => {
              if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                  img.src = img.dataset.src;
                  img.removeAttribute("data-src");
                }
                observer.unobserve(img);
              }
            });
          },
          {
            rootMargin: "50px 0px",
          }
        );

        this.items.forEach((item) => {
          const img = item.querySelector("img[data-src]");
          if (img) {
            imageObserver.observe(img);
          }
        });
      }
    }

    /**
     * Initialize JS-positioned layouts (packed-cards and masonry)
     * Packed-cards: mosaic grid with captions below
     * Masonry: horizontal fill, shortest-column-first (Pinterest-style)
     */
    initPackedLayout() {
      // Check if this is a packed-cards or masonry layout (both need JS positioning)
      const packedCardsGrid = this.grid?.classList.contains(
        "pfg-grid--packed-cards"
      );
      const masonryGrid = this.grid?.classList.contains("pfg-grid--masonry");

      if (!packedCardsGrid && !masonryGrid) {
        return;
      }

      // Mark that this gallery uses mosaic layout
      this.usesMosaicLayout = true;

      // Wait for all images to load before calculating layout
      this.waitForImages().then(() => {
        this.applyMosaicLayout();
      });

      // Re-apply on resize with debounce
      let resizeTimeout;
      window.addEventListener("resize", () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
          if (this.usesMosaicLayout) {
            this.applyMosaicLayout();
          }
        }, 200);
      });
    }

    /**
     * Wait for all images to load
     */
    waitForImages() {
      const images = Array.from(this.grid.querySelectorAll("img"));
      const promises = images.map((img) => {
        if (img.complete) {
          return Promise.resolve();
        }
        return new Promise((resolve) => {
          img.addEventListener("load", resolve, { once: true });
          img.addEventListener("error", resolve, { once: true });
        });
      });
      return Promise.all(promises);
    }

    /**
     * Apply mosaic/packed/masonry layout with absolute positioning
     * Uses requestAnimationFrame for reliable caption height measurement
     * Masonry: places items horizontally (shortest-column-first) for left-to-right ordering
     */
    applyMosaicLayout() {
      if (!this.grid) return;
      
      const isMasonry = this.grid.classList.contains("pfg-grid--masonry");

      const items = Array.from(
        this.grid.querySelectorAll(".pfg-item:not(.pfg-item--hidden)")
      );
      if (items.length === 0) {
        this.grid.style.height = "0px";
        return;
      }

      const containerWidth = this.grid.offsetWidth;
      const gap =
        parseInt(getComputedStyle(this.grid).getPropertyValue("--pfg-gap")) ||
        10;

      let cols;
      if (isMasonry) {
        const w = window.innerWidth;
        const styles = getComputedStyle(this.grid);
        if (w >= 1200) {
          cols = parseInt(styles.getPropertyValue("--pfg-cols-xl")) || 4;
        } else if (w >= 992) {
          cols = parseInt(styles.getPropertyValue("--pfg-cols-lg")) || 3;
        } else if (w >= 768) {
          cols = parseInt(styles.getPropertyValue("--pfg-cols-md")) || 2;
        } else {
          cols = parseInt(styles.getPropertyValue("--pfg-cols-sm")) || 1;
        }
      } else {
        const minSize =
          parseInt(
            getComputedStyle(this.grid).getPropertyValue("--pfg-packed-min")
          ) || 200;
        cols = Math.max(2, Math.floor(containerWidth / (minSize + gap)));
      }
      
      const colWidth = (containerWidth - gap * (cols - 1)) / cols;

      const newItems = [];
      const itemData = [];

      items.forEach((item, index) => {
        const img = item.querySelector("img");
        const imgLink = item.querySelector(".pfg-item-link");
        const caption = item.querySelector(".pfg-item-caption");

        let aspectRatio = 1;
        if (img && img.naturalWidth && img.naturalHeight) {
          aspectRatio = img.naturalWidth / img.naturalHeight;
        }

        let itemCols = 1;
        if (!isMasonry && aspectRatio > 1.5 && cols >= 3 && index % 4 === 0) {
          itemCols = 2;
        }

        const itemWidth = colWidth * itemCols + gap * (itemCols - 1);
        const imgHeight = Math.round(itemWidth / aspectRatio);

        const isNewItem = !item.classList.contains("pfg-item--positioned");

        if (isNewItem) {
          item.style.transition = "none";
          item.style.opacity = "0";
          item.style.transform = "scale(0.92)";
        }

        item.style.position = "absolute";
        item.style.width = itemWidth + "px";

        if (isMasonry) {
          if (imgLink) {
            imgLink.style.display = "block";
            imgLink.style.width = "100%";
            imgLink.style.height = "auto";
            imgLink.style.overflow = "hidden";
          }
          if (img) {
            img.style.width = "100%";
            img.style.height = "auto";
            img.style.objectFit = "";
          }
        } else {
          if (imgLink) {
            imgLink.style.display = "block";
            imgLink.style.width = "100%";
            imgLink.style.height = imgHeight + "px";
            imgLink.style.overflow = "hidden";
          }
          if (img) {
            img.style.width = "100%";
            img.style.height = "100%";
            img.style.objectFit = "cover";
          }
        }

        if (isNewItem) {
          newItems.push(item);
        }

        itemData.push({
          item,
          itemCols,
          imgHeight,
          itemWidth,
          caption,
        });
      });

      setTimeout(() => {
        const colHeights = new Array(cols).fill(0);
        let visibleIndex = 0;

        itemData.forEach((data) => {
          const { item, itemCols, imgHeight, caption, itemWidth } = data;

          if (item.classList.contains("pfg-item--hidden")) {
            return;
          }

          let itemHeight;
          if (isMasonry) {
            itemHeight = Math.ceil(item.offsetHeight);
            if (itemHeight <= 0) {
              const img = item.querySelector("img");
              if (img && img.naturalWidth && img.naturalHeight) {
                itemHeight = Math.ceil(itemWidth / (img.naturalWidth / img.naturalHeight));
              } else {
                itemHeight = Math.ceil(itemWidth);
              }
            }
          } else {
            let captionHeight = 0;
            if (caption) {
              captionHeight = Math.ceil(caption.getBoundingClientRect().height);
              if (captionHeight === 0) {
                captionHeight = 50;
              }
            }
            itemHeight = Math.ceil(imgHeight + captionHeight);
          }

          let bestCol;
          if (isMasonry) {
            bestCol = 0;
            for (let c = 1; c < cols; c++) {
              if (colHeights[c] < colHeights[bestCol]) {
                bestCol = c;
              }
            }
          } else {
            bestCol = visibleIndex % cols;
            if (itemCols > 1) {
              bestCol = Math.min(bestCol, cols - itemCols);
            }
          }
          
          let minHeight = colHeights[bestCol];
          if (itemCols > 1) {
            minHeight = Math.max(...colHeights.slice(bestCol, bestCol + itemCols));
          }

          const x = Math.round(bestCol * (colWidth + gap));
          const y = Math.round(minHeight);

          if (isMasonry) {
            item.style.left = x + "px";
            item.style.top = y + "px";
          } else {
            item.style.setProperty("--pfg-x", x + "px");
            item.style.setProperty("--pfg-y", y + "px");
          }

          if (!item.classList.contains("pfg-item--positioned")) {
            void item.offsetHeight;
            item.classList.add("pfg-item--positioned");
          }

          for (let c = bestCol; c < bestCol + itemCols && c < cols; c++) {
            colHeights[c] = y + itemHeight + gap;
          }

          visibleIndex++;
        });

        const maxHeight = Math.max(...colHeights, 0);
        this.grid.style.position = "relative";
        this.grid.style.height = Math.ceil(maxHeight) + "px";

        if (newItems.length > 0) {
          void this.grid.offsetHeight;

          newItems.forEach((item, i) => {
            const delay = i * 40;
            setTimeout(() => {
              item.style.transition = "opacity 0.35s ease-out, transform 0.35s ease-out";
              item.style.opacity = "1";
              item.style.transform = "scale(1)";

              setTimeout(() => {
                item.style.transition = "";
                item.style.opacity = "";
                item.style.transform = "";
              }, 400);
            }, delay);
          });
        }
      }, 50);
    }

    /**
     * Public method to re-apply mosaic layout (called after Load More)
     */
    refreshMosaicLayout() {
      if (this.usesMosaicLayout) {
        // Update items reference
        this.items = this.container.querySelectorAll(".pfg-item");

        this.waitForImages().then(() => {
          this.applyMosaicLayout();
        });
      }
    }

    /**
     * Public API: Set filter logic programmatically
     */
    setLogic(logic) {
      if (logic === "and" || logic === "or") {
        this.filterLogic = logic;
        this.filterItems();
      }
    }

    /**
     * Public API: Add a filter programmatically
     */
    addFilter(filter) {
      this.activeFilters.add(filter);
      this.filterItems();
    }

    /**
     * Public API: Clear all filters
     */
    clearFilters() {
      this.activeFilters.clear();
      this.filters.forEach((btn) => {
        btn.classList.remove("pfg-filter--active", "pfg-filter--selected");
      });
      const allBtn = this.container.querySelector('[data-filter="*"]');
      if (allBtn) allBtn.classList.add("pfg-filter--active");
      this.filterItems();
    }

    /**
     * Update numbered pagination after filtering
     * Re-paginates only the visible (filtered) items
     */
    updateNumberedPaginationAfterFilter() {
      const paginationWrap = this.container.querySelector(
        ".pfg-numbered-pagination"
      );
      if (!paginationWrap) return;

      const itemsPerPage = parseInt(this.container.dataset.itemsPerPage) || 12;

      // Get only visible (non-filtered-hidden) items
      const visibleItems = Array.from(
        this.container.querySelectorAll(".pfg-item:not(.pfg-item--hidden)")
      );
      const totalVisible = visibleItems.length;
      const totalPages = Math.ceil(totalVisible / itemsPerPage);

      // Show first page of filtered items, hide rest
      visibleItems.forEach((item, index) => {
        if (index < itemsPerPage) {
          item.classList.remove("pfg-item--paginated-hidden");
        } else {
          item.classList.add("pfg-item--paginated-hidden");
        }
      });

      // Rebuild pagination buttons
      paginationWrap.innerHTML = "";
      for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "pfg-pagination-btn" + (i === 1 ? " active" : "");
        btn.dataset.page = i;
        btn.textContent = i;
        paginationWrap.appendChild(btn);
      }

      // Hide pagination if only one page
      paginationWrap.style.display = totalPages <= 1 ? "none" : "";
    }
  }

  /**
   * Pagination handler class
   */
  class PFGPagination {
    constructor() {
      this.init();
    }

    init() {
      this.bindLoadMore();
      this.bindNumberedPagination();
      this.initInfiniteScroll();
      this.bindFilterReset();
    }

    /**
     * Listen for filter changes to reset pagination
     * NOTE: Disabled - items loaded via Load More now persist until page reload
     * Client-side filtering handles show/hide without AJAX reset
     */
    bindFilterReset() {
      // Removed: AJAX reset on filter change
      // Items loaded via Load More now stay in DOM and are filtered client-side
      // This provides better UX - users don't lose loaded items when filtering
      
      // document.addEventListener("pfg:filtered", (e) => {
      //   const gallery = e.target.closest(".pfg-gallery-wrapper");
      //   if (gallery && gallery.dataset.pagination === "true") {
      //     this.resetPagination(gallery);
      //   }
      // });
    }

    /**
     * Bind Load More button clicks
     */
    bindLoadMore() {
      document.addEventListener("click", (e) => {
        const btn = e.target.closest(".pfg-load-more");
        if (!btn) return;

        const gallery = btn.closest(".pfg-gallery-wrapper");
        this.loadMoreItems(gallery, btn);
      });
    }

    /**
     * Bind numbered pagination clicks
     */
    bindNumberedPagination() {
      document.addEventListener("click", (e) => {
        const btn = e.target.closest(".pfg-pagination-btn");
        if (!btn || btn.disabled || btn.classList.contains("active")) return;

        const gallery = btn.closest(".pfg-gallery-wrapper");
        const page = parseInt(btn.dataset.page);

        this.goToPage(gallery, page);
      });
    }

    /**
     * Navigate to specific page (numbered pagination)
     */
    goToPage(gallery, page) {
      const items = gallery.querySelectorAll(
        ".pfg-item:not(.pfg-item--hidden)"
      );
      const itemsPerPage = parseInt(gallery.dataset.itemsPerPage) || 12;
      const startIndex = (page - 1) * itemsPerPage;
      const endIndex = startIndex + itemsPerPage;

      items.forEach((item, index) => {
        if (index >= startIndex && index < endIndex) {
          item.classList.remove("pfg-item--paginated-hidden");
        } else {
          item.classList.add("pfg-item--paginated-hidden");
        }
      });

      // Update pagination buttons
      gallery.querySelectorAll(".pfg-pagination-btn").forEach((btn) => {
        btn.classList.toggle("active", parseInt(btn.dataset.page) === page);
      });

      // Scroll to gallery
      gallery.scrollIntoView({ behavior: "smooth", block: "start" });
    }

    /**
     * Load more items via AJAX
     * NOTE: Always loads ALL items regardless of current filter
     * Client-side filtering handles show/hide after loading
     */
    async loadMoreItems(gallery, btn) {
      const galleryId = gallery.dataset.galleryId;
      const itemsPerPage = parseInt(gallery.dataset.itemsPerPage) || 12;
      const currentPage = parseInt(gallery.dataset.currentPage) || 1;
      const nextPage = currentPage + 1;
      
      // Get current active filter to load matching items
      const pfgGallery = gallery.pfgGallery;
      const activeFilters = pfgGallery ? pfgGallery.activeFilters : new Set();
      const isMultiSelect = gallery.dataset.multiSelect === "true";
      
      // Build filter parameter based on active filters
      let filter = "*";
      if (activeFilters.size > 0 && !activeFilters.has("*")) {
        filter = Array.from(activeFilters).join(",");
      }
      
      // Use total items count (not filtered count)
      const totalItems = parseInt(gallery.dataset.totalItems) || 0;

      // Calculate how many are already loaded
      const loadedCount = gallery.querySelectorAll(".pfg-item").length;

      // Check if all items are already loaded
      if (loadedCount >= totalItems) {
        if (btn) btn.style.display = "none";
        return;
      }

      if (btn) {
        btn.classList.add("loading");
        const spinner = btn.querySelector(".pfg-load-more-spinner");
        const text = btn.querySelector(".pfg-load-more-text");
        if (spinner) spinner.style.display = "inline-block";
        if (text) text.textContent = "Loading...";
      }

      try {
        // Track the filter used for this load (so resetPagination knows if we need to reload for "All")
        gallery.dataset.lastLoadFilter = filter;
        
        // Get IDs of already loaded items to avoid duplicates
        const existingItems = gallery.querySelectorAll(".pfg-item");
        const excludeIds = Array.from(existingItems)
          .map(item => item.dataset.id)
          .filter(id => id);
        
        // Calculate offset based on items already in DOM
        const offset = existingItems.length;
        
        // Make AJAX request
        const formData = new FormData();
        formData.append("action", "pfg_load_more");
        formData.append("gallery_id", galleryId);
        formData.append("page", nextPage);
        formData.append("items_per_page", itemsPerPage);
        formData.append("filter", filter);
        formData.append("filter_logic", gallery.dataset.filterLogic || "or");
        formData.append("nonce", window.pfgData?.nonce || "");
        formData.append("offset", offset);
        formData.append("exclude_ids", excludeIds.join(","));

        const response = await fetch(
          window.pfgData?.ajaxUrl || "/wp-admin/admin-ajax.php",
          {
            method: "POST",
            body: formData,
          }
        );

        const data = await response.json();

        if (data.success && data.data.html) {
          // Append new items to grid
          const grid = gallery.querySelector(".pfg-grid");
          if (grid) {
            // Check if using mosaic layout (packed-cards OR masonry)
            const isMosaicLayout = grid.classList.contains("pfg-grid--packed-cards") ||
              grid.classList.contains("pfg-grid--masonry");

            // Create temp container to parse HTML
            const temp = document.createElement("div");
            temp.innerHTML = data.data.html;

            // Note: Items are pre-filtered by server based on current filter
            // No client-side filter check needed - just animate all received items

            if (isMosaicLayout) {
              // For mosaic/masonry layout:
              // Append items fully hidden, wait for images, then position + fade in
              const newItems = Array.from(temp.children);
              newItems.forEach((item) => {
                item.classList.add("pfg-item--visible");
                grid.appendChild(item);
              });

              // Wait for images to load, then recalculate layout
              if (
                gallery.pfgGallery &&
                gallery.pfgGallery.refreshMosaicLayout
              ) {
                gallery.pfgGallery.refreshMosaicLayout();
              }
            } else {
              // For other layouts: animate each new item
              // Items are already filtered by server, so all loaded items should be shown
              Array.from(temp.children).forEach((item, i) => {
                // Add visible class for filtering state
                item.classList.add("pfg-item--visible");
                
                // Start hidden for animation
                item.style.opacity = "0";
                item.style.transform = "translateY(20px) scale(0.95)";
                item.style.transition = "none";
                grid.appendChild(item);

                // Trigger animation with stagger
                requestAnimationFrame(() => {
                  setTimeout(() => {
                    item.style.transition =
                      "opacity 0.3s ease, transform 0.3s ease";
                    item.style.opacity = "1";
                    item.style.transform = "translateY(0) scale(1)";
                    
                    // Clean up inline styles after animation
                    setTimeout(() => {
                      item.style.transition = "";
                      item.style.opacity = "";
                      item.style.transform = "";
                    }, 350);
                  }, i * 50);
                });
              });
            }
          }

          // Update state
          gallery.dataset.currentPage = nextPage;

          // Update remaining count
          this.updateLoadMoreCount(gallery, data.data.remaining);


          // Note: Items are now pre-filtered during load, no need for separate filterItems call

          // Hide button if no more items
          if (!data.data.has_more && btn) {
            btn.style.display = "none";
          }
        }
      } catch (error) {
        console.error("PFG Load More Error:", error);
      }

      if (btn) {
        btn.classList.remove("loading");
        const spinner = btn.querySelector(".pfg-load-more-spinner");
        const text = btn.querySelector(".pfg-load-more-text");
        if (spinner) spinner.style.display = "none";
        if (text) text.textContent = btn.dataset.loadMoreText || "Load More";
      }
    }

    /**
     * Reset pagination when filters change (AJAX version)
     */
    async resetPagination(gallery) {
      const galleryId = gallery.dataset.galleryId;
      const itemsPerPage = parseInt(gallery.dataset.itemsPerPage) || 12;

      // Reset page counter
      gallery.dataset.currentPage = 1;

      // Check if using multi-select mode
      const isMultiSelect = gallery.dataset.multiSelect === "true";
      
      // Get all active/selected filters
      const activeFilters = gallery.querySelectorAll(
        ".pfg-filter--active, .pfg-filter--selected"
      );
      
      // Determine if we're showing "All" (no filters or just the * filter)
      let isShowingAll = activeFilters.length === 0;
      if (activeFilters.length === 1 && activeFilters[0].dataset.filter === "*") {
        isShowingAll = true;
      }
      
      // Build filter string (comma-separated for multi-select)
      let filter = "*";
      if (!isShowingAll) {
        if (isMultiSelect) {
          const selectedFilters = gallery.querySelectorAll(".pfg-filter--selected");
          const filterSlugs = Array.from(selectedFilters)
            .map(f => f.dataset.filter)
            .filter(f => f && f !== "*");
          filter = filterSlugs.length > 0 ? filterSlugs.join(",") : "*";
        } else {
          const activeFilter = gallery.querySelector(".pfg-filter--active");
          filter = activeFilter ? activeFilter.dataset.filter || "*" : "*";
        }
      }

      // Check pagination type
      const paginationType = gallery.dataset.paginationType;
      const isNumberedPagination = paginationType === "numbered";

      // For numbered pagination, items are already rendered - just update display
      // Don't use AJAX, filtering is done client-side
      if (isNumberedPagination) {
        const visibleItems = gallery.querySelectorAll(
          ".pfg-item:not(.pfg-item--hidden)"
        );
        this.updateNumberedPagination(
          gallery,
          visibleItems.length,
          itemsPerPage
        );
        this.goToPage(gallery, 1);
        return;
      }

      // If showing "All" (*), check if we need to reload
      // We need to reload if:
      // 1. We previously loaded items with a filter (stored in data-last-load-filter)
      // 2. Current page > 1 (we've done Load More before)
      if (isShowingAll) {
        const lastLoadFilter = gallery.dataset.lastLoadFilter || "*";
        const currentPage = parseInt(gallery.dataset.currentPage) || 1;
        
        // If we previously loaded with a specific filter, we need to reload "All" from server
        // because the DOM only contains items for that specific filter
        if (lastLoadFilter !== "*" && lastLoadFilter !== "") {
          // Clear the last load filter since we're resetting to "All"
          gallery.dataset.lastLoadFilter = "*";
          
          // Don't return early - continue to AJAX reload below
          // Set isShowingAll to false to trigger the AJAX path
          // but keep filter as "*" for the reload
          filter = "*";
        } else {
          // No filtered loads before - just show what's there
          // Clear filtered total since we're showing all
          delete gallery.dataset.filteredTotal;
          
          // For Load More: show button if there are more items
          const totalItems = parseInt(gallery.dataset.totalItems) || 0;
          const loadedItems = gallery.querySelectorAll(".pfg-item").length;
          const remaining = totalItems - loadedItems;

          this.updateLoadMoreCount(gallery, remaining);
          const loadMoreBtn = gallery.querySelector(".pfg-load-more");
          if (loadMoreBtn) {
            loadMoreBtn.style.display = remaining > 0 ? "" : "none";
          }

          // For Numbered Pagination: update page buttons based on visible items
          const visibleItems = gallery.querySelectorAll(
            ".pfg-item:not(.pfg-item--hidden)"
          );
          this.updateNumberedPagination(
            gallery,
            visibleItems.length,
            itemsPerPage
          );

          // Reset pagination view to page 1
          this.goToPage(gallery, 1);

          return;
        }
      }
      
      // Track the filter we're loading with (for detecting filtered Load More)
      gallery.dataset.lastLoadFilter = filter;

      // For filtered views with AJAX, we need to reload from server
      // Show loading state
      const grid = gallery.querySelector(".pfg-grid");
      const loadMoreBtn = gallery.querySelector(".pfg-load-more");

      if (loadMoreBtn) {
        loadMoreBtn.classList.add("loading");
      }

      try {
        const formData = new FormData();
        formData.append("action", "pfg_load_more");
        formData.append("gallery_id", galleryId);
        formData.append("page", 1);
        formData.append("items_per_page", itemsPerPage);
        formData.append("filter", filter);
        formData.append("filter_logic", gallery.dataset.filterLogic || "or");
        formData.append("nonce", window.pfgData?.nonce || "");

        const response = await fetch(
          window.pfgData?.ajaxUrl || "/wp-admin/admin-ajax.php",
          {
            method: "POST",
            body: formData,
          }
        );

        const data = await response.json();

        if (data.success) {
          // Always update grid content (even if empty - 0 results is valid)
          if (grid) {
            grid.innerHTML = data.data.html || "";

            // Check if using mosaic/packed layout and refresh it (packed-cards OR masonry)
            const isMosaicLayout = grid.classList.contains("pfg-grid--packed-cards") ||
              grid.classList.contains("pfg-grid--masonry");

            if (isMosaicLayout && data.data.html) {
              // Refresh mosaic layout after content replacement
              if (
                gallery.pfgGallery &&
                gallery.pfgGallery.refreshMosaicLayout
              ) {
                setTimeout(() => {
                  gallery.pfgGallery.refreshMosaicLayout();
                }, 50);
              }
            }
          }

          // Update remaining count and button visibility
          this.updateLoadMoreCount(gallery, data.data.remaining);

          if (loadMoreBtn) {
            loadMoreBtn.style.display = data.data.has_more ? "" : "none";
          }

          // Store filtered total for subsequent loads
          gallery.dataset.filteredTotal = data.data.total;

          // For Numbered Pagination: update page buttons based on total filtered items
          // After AJAX, all visible items are the first page of filtered results
          const visibleItems = grid
            ? grid.querySelectorAll(".pfg-item").length
            : 0;
          const totalFiltered = data.data.total || visibleItems;
          this.updateNumberedPagination(gallery, totalFiltered, itemsPerPage);
        }
      } catch (error) {
        console.error("PFG Reset Pagination Error:", error);
      }

      if (loadMoreBtn) {
        loadMoreBtn.classList.remove("loading");
      }
    }

    /**
     * Update Load More count display
     */
    updateLoadMoreCount(gallery, remaining) {
      const countEl = gallery.querySelector(".pfg-load-more-count");
      if (countEl) {
        if (remaining > 0) {
          countEl.textContent = `(${remaining} remaining)`;
          countEl.style.display = "";
        } else {
          countEl.style.display = "none";
        }
      }
    }

    /**
     * Update numbered pagination buttons
     */
    updateNumberedPagination(gallery, totalVisible, itemsPerPage) {
      const paginationWrap = gallery.querySelector(".pfg-numbered-pagination");
      if (!paginationWrap) return;

      const totalPages = Math.ceil(totalVisible / itemsPerPage);

      // Rebuild pagination buttons
      paginationWrap.innerHTML = "";
      for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "pfg-pagination-btn" + (i === 1 ? " active" : "");
        btn.dataset.page = i;
        btn.textContent = i;
        paginationWrap.appendChild(btn);
      }

      // Hide pagination if only one page
      paginationWrap.style.display = totalPages <= 1 ? "none" : "";
    }

    /**
     * Initialize Infinite Scroll (AJAX version)
     */
    initInfiniteScroll() {
      const galleries = document.querySelectorAll(
        '.pfg-gallery-wrapper[data-pagination-type="infinite"]'
      );

      galleries.forEach((gallery) => {
        const trigger = gallery.querySelector(".pfg-scroll-trigger");
        const loader = gallery.querySelector(".pfg-scroll-loader");
        if (!trigger) return;

        let isLoading = false;

        const observer = new IntersectionObserver(
          (entries) => {
            entries.forEach((entry) => {
              if (entry.isIntersecting && !isLoading) {
                const totalItems = parseInt(gallery.dataset.totalItems) || 0;
                const loadedItems =
                  gallery.querySelectorAll(".pfg-item").length;

                if (loadedItems < totalItems) {
                  isLoading = true;
                  if (loader) loader.style.display = "";

                  this.loadMoreItems(gallery, null).then(() => {
                    isLoading = false;
                    if (loader) loader.style.display = "none";

                    const newLoadedItems =
                      gallery.querySelectorAll(".pfg-item").length;
                    if (newLoadedItems >= totalItems) {
                      observer.disconnect();
                      if (loader) loader.remove();
                    }
                  });
                }
              }
            });
          },
          { rootMargin: "100px" }
        );

        observer.observe(trigger);
      });
    }
  }

  /**
   * Initialize galleries when DOM is ready
   */
  function initGalleries() {
    const galleries = document.querySelectorAll(".pfg-gallery-wrapper");

    galleries.forEach((container) => {
      // Skip if already initialized
      if (container.dataset.pfgInitialized) return;

      container.pfgGallery = new PFGGallery(container);
      container.dataset.pfgInitialized = "true";
    });

    // Initialize pagination (once)
    if (!window.pfgPaginationInitialized) {
      new PFGPagination();
      window.pfgPaginationInitialized = true;
    }
  }

  // Initialize on DOM ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initGalleries);
  } else {
    initGalleries();
  }

  // Re-initialize for dynamically added content
  if (typeof MutationObserver !== "undefined") {
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
          if (node.nodeType === 1) {
            if (node.classList?.contains("pfg-gallery-wrapper")) {
              node.pfgGallery = new PFGGallery(node);
            } else {
              const galleries = node.querySelectorAll?.(
                ".pfg-gallery-wrapper:not([data-pfg-initialized])"
              );
              galleries?.forEach((g) => {
                g.pfgGallery = new PFGGallery(g);
              });
            }
          }
        });
      });
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true,
    });
  }

  // Expose to global scope for external use
  window.PFGGallery = PFGGallery;
  window.pfgInitGalleries = initGalleries;
})();
