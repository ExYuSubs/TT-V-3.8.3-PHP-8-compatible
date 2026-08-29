document.addEventListener("DOMContentLoaded", function() {
  const modal = document.getElementById("trailerModal");
  const btn   = document.getElementById("openTrailer");
  const span  = modal ? modal.querySelector(".close") : null;
  const iframe = modal ? modal.querySelector("iframe") : null;

  if (!modal || !btn || !span) {
    console.warn("Modal elements not found");
    return;
  }

  btn.onclick = () => {
    modal.style.display = "flex";
    if (iframe) {
      // връщаме оригиналния линк от data-src
      iframe.src = iframe.dataset.src || iframe.src;
    }
  };

  function closeModal() {
    modal.style.display = "none";
    if (iframe) {
      // зануляваме src, за да спре звука
      iframe.src = "";
    }
  }

  span.onclick = closeModal;
  window.onclick = (event) => {
    if (event.target === modal) {
      closeModal();
    }
  };
});