(function () {
  const password = document.getElementById('password-input');
  const repeat = document.getElementById('repeat-password-input');
  const form = document.querySelector('.wrapper form');
  if (form && repeat) {
    form.addEventListener('submit', function (event) {
      if (password && password.value !== repeat.value) {
        event.preventDefault();
        repeat.setCustomValidity('Passwords do not match.');
        repeat.reportValidity();
      } else if (repeat) {
        repeat.setCustomValidity('');
      }
    });
    repeat.addEventListener('input', function () { repeat.setCustomValidity(''); });
  }
})();
