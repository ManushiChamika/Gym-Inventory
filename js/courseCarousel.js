const scrollAmount = 500; // Adjust the scroll amount as needed

function scrollCardsLeft() {
  const container = document.querySelector(".project-container");
  container.scrollBy({
    left: -scrollAmount,
    behavior: "smooth",
  });
}

function scrollCardsRight() {
  const container = document.querySelector(".project-container");
  container.scrollBy({
    left: scrollAmount,
    behavior: "smooth",
  });
}
