document.addEventListener("DOMContentLoaded", () => {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("visible");
      } else {
        entry.target.classList.remove("visible");
      }
    });
  }, { threshold: 0.2 }); // triggers when 20% is visible

  const targets = document.querySelectorAll(".grid-section, .five-day");
  targets.forEach((target) => {
    target.classList.add("scroll-appear");
    observer.observe(target);
  });
});
