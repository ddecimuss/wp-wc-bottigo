console.log("Bottigo JS loaded");
// Скрытие блока с преимуществами при клике на кнопку "Закрыть"
document.addEventListener("DOMContentLoaded", function () {
  const closeBtn = document.querySelector(".js-close-btn");
  const block = document.getElementById("block-advantages");

  if (closeBtn && block) {
    closeBtn.addEventListener("click", () => {
      block.classList.add("hide");
      // Больше ничего не нужно — всё будет плавно уходить и соседние блоки подвинутся
    });
  }
});
