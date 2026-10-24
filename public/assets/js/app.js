document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', event => {
      if (!window.confirm(form.dataset.confirm || 'Are you sure?')) event.preventDefault();
    });
  });
  document.querySelectorAll('img').forEach(img => {
    img.addEventListener('error', () => {
      img.classList.add('image-failed');
      img.alt = img.alt || 'Product image unavailable';
    }, { once: true });
  });
  const search = document.querySelector('.searchbar input[type="search"]');
  if (search) search.addEventListener('keydown', event => {
    if (event.key === 'Escape') search.value = '';
  });
});
