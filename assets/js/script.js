
document.querySelectorAll('.delete-btn').forEach(btn => {
  btn.addEventListener('click', function (e) {
    if (!confirm("Are you sure you want to delete this task?")) {
      e.preventDefault();
    }
  });
});

window.addEventListener("DOMContentLoaded", () => {
  const urlParams = new URLSearchParams(window.location.search);
  const toastMsg = urlParams.get('toast');
  if (toastMsg) {
    Toastify({
      text: decodeURIComponent(toastMsg),
      duration: 3000,
      gravity: "top",
      position: "right",
      backgroundColor: "#28a745",
    }).showToast();
  }
});

const { createApp } = Vue;

createApp({
  data() {
    return {
      message: '',
      visible: false
    };
  },
  mounted() {
    const urlParams = new URLSearchParams(window.location.search);
    const toastMsg = urlParams.get('toast');
    if (toastMsg) {
      this.message = decodeURIComponent(toastMsg);
      this.visible = true;
      setTimeout(() => this.visible = false, 4000);
    }
  }
}).mount('#toast');


