// Simplified JavaScript for BOTTIGO theme
(function() {
  'use strict';

  // Скрытие блока с преимуществами при клике на кнопку "Закрыть"
  const initAdvantagesBlock = () => {
    const closeBtn = document.querySelector(".js-close-btn");
    const block = document.getElementById("block-advantages");

    if (!closeBtn || !block) return;

    // Accessibility: Handle both click and keyboard events
    const hideBlock = () => {
      block.classList.add("hide");
    };

    closeBtn.addEventListener("click", hideBlock);

    // Keyboard accessibility
    closeBtn.addEventListener("keydown", (e) => {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        hideBlock();
      }
    });
  };

  // Инициализация при готовности DOM
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdvantagesBlock);
  } else {
    initAdvantagesBlock();
  }

  // Dev log
  if (window.console && typeof console.log === 'function') {
    console.log('BOTTIGO theme scripts loaded');
  }
})();
