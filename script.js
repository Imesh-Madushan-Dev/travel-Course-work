// Add debug logs at the start
console.log("Script loaded successfully");

// Mobile Menu Toggle
const mobileMenu = document.querySelector(".mobile-menu");
const nav = document.querySelector("nav");

mobileMenu.addEventListener("click", () => {
  nav.classList.toggle("active");
  mobileMenu.classList.toggle("active");
});

// Smooth Scrolling for Navigation Links
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault();
    const targetId = this.getAttribute("href");
    if (targetId === "#") return;

    const target = document.querySelector(targetId);
    if (target) {
      target.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });

      // Close mobile menu if open
      nav.classList.remove("active");
      mobileMenu.classList.remove("active");
    }
  });
});

// Header Background on Scroll
const header = document.querySelector(".header");
let lastScroll = 0;

window.addEventListener("scroll", () => {
  const currentScroll = window.pageYOffset;

  if (currentScroll <= 0) {
    header.classList.remove("scroll-up");
    return;
  }

  if (currentScroll > lastScroll && !header.classList.contains("scroll-down")) {
    // Scrolling down
    header.classList.remove("scroll-up");
    header.classList.add("scroll-down");
  } else if (
    currentScroll < lastScroll &&
    header.classList.contains("scroll-down")
  ) {
    // Scrolling up
    header.classList.remove("scroll-down");
    header.classList.add("scroll-up");
  }
  lastScroll = currentScroll;
});

// Header scroll effect
window.addEventListener("scroll", () => {
  const header = document.querySelector(".header");
  if (window.scrollY > 50) {
    header.classList.add("scrolled");
  } else {
    header.classList.remove("scrolled");
  }
});

// Destination Search Functionality
const searchInput = document.querySelector(".search-input input");
const searchButton = document.querySelector(".hero-search button");

searchButton.addEventListener("click", () => {
  const searchTerm = searchInput.value.trim();
  if (searchTerm) {
    // Smooth scroll to destinations section
    const destinationsSection = document.querySelector("#destination");
    if (destinationsSection) {
      destinationsSection.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    }

    // Here you would typically implement the actual search functionality
    // For now, we'll just focus on the UI interaction
    searchInput.value = "";
  }
});

// Testimonial slider
const sliderTrack = document.querySelector(".testimonial-track");
const prevBtn = document.querySelector(".slider-nav .prev");
const nextBtn = document.querySelector(".slider-nav .next");

prevBtn.addEventListener("click", () => {
  sliderTrack.scrollBy({
    left: -300,
    behavior: "smooth",
  });
});

nextBtn.addEventListener("click", () => {
  sliderTrack.scrollBy({
    left: 300,
    behavior: "smooth",
  });
});

// Initialize AOS
AOS.init({
  duration: 800,
  offset: 100,
  once: true,
});

// Optimize Destinations and Services sections
document.addEventListener("DOMContentLoaded", () => {
  // Lazy loading for images
  const lazyImages = document.querySelectorAll(
    ".card-image img, .service-card img"
  );
  const imageObserver = new IntersectionObserver(
    (entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src;
          observer.unobserve(img);
        }
      });
    },
    {
      rootMargin: "50px",
    }
  );

  lazyImages.forEach((img) => {
    img.dataset.src = img.src;
    img.src =
      "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"; // Placeholder
    imageObserver.observe(img);
  });

  // Optimize card animations
  const cards = document.querySelectorAll(".destination-card, .service-card");
  const cardObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("card-visible");
          cardObserver.unobserve(entry.target);
        }
      });
    },
    {
      threshold: 0.1,
    }
  );

  cards.forEach((card) => {
    card.style.opacity = "0";
    card.style.transform = "translateY(20px)";
    cardObserver.observe(card);
  });
});

// Throttle function for scroll events
function throttle(func, limit) {
  let inThrottle;
  return function (...args) {
    if (!inThrottle) {
      func.apply(this, args);
      inThrottle = true;
      setTimeout(() => (inThrottle = false), limit);
    }
  };
}

// Optimize hover effects
const destinationCards = document.querySelectorAll(".destination-card");
destinationCards.forEach((card) => {
  card.addEventListener(
    "mouseenter",
    throttle(function () {
      this.style.transform = "translateY(-5px)";
    }, 100)
  );

  card.addEventListener(
    "mouseleave",
    throttle(function () {
      this.style.transform = "translateY(0)";
    }, 100)
  );
});

// Add this at the end of the file
document.addEventListener("DOMContentLoaded", function () {
  console.log("DOM fully loaded");

  // Test if elements are found
  const header = document.querySelector(".header");
  console.log("Header found:", !!header);

  const destinationCards = document.querySelectorAll(".destination-card");
  console.log("Destination cards found:", destinationCards.length);

  const serviceCards = document.querySelectorAll(".service-card");
  console.log("Service cards found:", serviceCards.length);
});
