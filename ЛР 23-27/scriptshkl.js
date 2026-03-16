$(document).ready(function () {
    var mySwiper = new Swiper(".swiper", {
      autoHeight: true,
      autoplay: {
        delay: 5000,
        disableOnInteraction: false
      },
      speed: 500,
      direction: "horizontal",
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev"
      },
      pagination: {
        el: ".swiper-pagination",
        type: "progressbar"
      },
      loop: false,
      effect: "slide",
      spaceBetween: 30,
      on: {
        init: function () {
          updateCustomPagination(0);
          updateProgressBar(0, this.slides.length);
        },
        slideChange: function () {
          updateCustomPagination(this.realIndex);
          updateProgressBar(this.realIndex, this.slides.length);
        }
      }
    });
  
    function updateProgressBar(index, total) {
      const percent = (index) / (total - 1) * 100;
      $(".swiper-pagination-progressbar-fill").css("width", `${percent}%`);
    }
  
    function updateCustomPagination(activeIndex) {
      $(".swiper-pagination-custom .swiper-pagination-switch").removeClass("active");
      $(".swiper-pagination-custom .swiper-pagination-switch").eq(activeIndex).addClass("active");
    }
  
    $(".swiper-pagination-custom .swiper-pagination-switch").click(function () {
      const index = $(this).index();
      mySwiper.slideTo(index);
      updateCustomPagination(index);
      updateProgressBar(index, mySwiper.slides.length);
    });
  });
  