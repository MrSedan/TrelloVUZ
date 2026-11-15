window.onload = () => {
  const message = document.querySelector('#message');

  document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();

      const response = await fetch(form.action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(Object.fromEntries(new FormData(form))),
      });
      const result = await response.json();

      if (response.ok) {
        location.reload();
        return;
      }

      const details = Object.entries(result.errors ?? {})
        .map(([field, errors]) => `${field}: ${errors.join(', ')}`)
        .join('; ');
      message.textContent = `${result.error}${details ? ' (' + details + ')' : ''}`;
    });
  });
};
