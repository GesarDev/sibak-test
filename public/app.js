(() => {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

  const registerForm = document.getElementById('registerForm');
  const feedbackForm = document.getElementById('feedbackForm');
  const usersList = document.getElementById('usersList');
  const feedbackList = document.getElementById('feedbackList');

  const q = (root, sel) => root.querySelector(sel);

  const escapeHtml = (s) => String(s)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

  const clearErrors = (form) => {
    form.querySelectorAll('[data-err-for]').forEach(el => (el.textContent = ''));
    form.querySelectorAll('input,textarea').forEach(el => el.removeAttribute('aria-invalid'));
  };

  const setFieldError = (form, field, message) => {
    const target = q(form, `[data-err-for="${field}"]`) || q(form, '[data-err-for="_global"]');
    if (target) target.textContent = message;

    const input = form.elements[field];
    if (input) input.setAttribute('aria-invalid', 'true');
  };

  const applyErrors = (form, errors) => {
    if (!errors) return;
    for (const [field, message] of Object.entries(errors)) {
      setFieldError(form, field, message);
    }
  };

  const isEmail = (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
  const isPhone = (v) => /^\+?[0-9\-\s()]{7,20}$/.test(v);

  const formToObject = (form) => {
    const fd = new FormData(form);
    const obj = {};
    for (const [k, v] of fd.entries()) obj[k] = String(v).trim();
    return obj;
  };

  class ApiError extends Error {
    constructor(status, payload) {
      super(`API error ${status}`);
      this.status = status;
      this.payload = payload;
    }
  }

  const apiPost = async (url, body) => {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      body: JSON.stringify(body),
    });

    const payload = await res.json().catch(() => ({}));
    if (!res.ok) throw new ApiError(res.status, payload);
    return payload;
  };

  // TODO: добавить debounce для валидации
  const validateRegister = (p) => {
    const errors = {};
    if (!p.name || p.name.length < 2) errors.name = 'Имя минимум 2 символа';
    if (!p.email || !isEmail(p.email)) errors.email = 'Некорректный email';
    if (!p.phone || !isPhone(p.phone)) errors.phone = 'Некорректный телефон';
    if (!p.password || p.password.length < 6) errors.password = 'Пароль минимум 6 символов';
    if (p.password !== p.password_repeat) errors.password_repeat = 'Пароли не совпадают';
    return errors;
  };

  const validateFeedback = (p) => {
    const errors = {};
    if (!p.email || !isEmail(p.email)) errors.email = 'Некорректный email';
    if (!p.message || p.message.length < 5) errors.message = 'Сообщение минимум 5 символов';
    return errors;
  };

  const prependLi = (list, html) => {
    if (!list) return;
    const muted = list.querySelector('.muted');
    if (muted) list.innerHTML = '';

    const li = document.createElement('li');
    li.innerHTML = html;
    list.prepend(li);
  };

  // Обработчик формы регистрации
  if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      clearErrors(registerForm);

      const payload = formToObject(registerForm);
      const errors = validateRegister(payload);
      if (Object.keys(errors).length) {
        applyErrors(registerForm, errors);
        return;
      }

      try {
        const data = await apiPost('/api/register', payload);
        const u = data.user;

        prependLi(usersList, `<b>${escapeHtml(u.name)}</b> — ${escapeHtml(u.email)} — ${escapeHtml(u.phone)}`);
        registerForm.reset();
      } catch (err) {
        if (err instanceof ApiError) {
          if (err.status === 422 && err.payload?.errors) {
            applyErrors(registerForm, err.payload.errors);
            return;
          }
          if (err.status === 419) {
            applyErrors(registerForm, { _global: 'Сессия/CSRF устарели. Обнови страницу.' });
            return;
          }
        }
        applyErrors(registerForm, { _global: 'Ошибка сервера. Попробуй ещё раз.' });
      }
    });
  }

  if (feedbackForm) {
    feedbackForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      clearErrors(feedbackForm);

      const payload = formToObject(feedbackForm);
      const errors = validateFeedback(payload);
      if (Object.keys(errors).length) {
        applyErrors(feedbackForm, errors);
        return;
      }

      try {
        const data = await apiPost('/api/feedback', payload);
        const f = data.feedback;

        prependLi(feedbackList, `<b>${escapeHtml(f.author)}</b>: ${escapeHtml(f.message)}`);
        feedbackForm.reset();
      } catch (err) {
        if (err instanceof ApiError) {
          if (err.status === 422 && err.payload?.errors) {
            applyErrors(feedbackForm, err.payload.errors);
            return;
          }
          if (err.status === 419) {
            applyErrors(feedbackForm, { _global: 'Сессия/CSRF устарели. Обнови страницу.' });
            return;
          }
        }
        applyErrors(feedbackForm, { _global: 'Ошибка сервера. Попробуй ещё раз.' });
      }
    });
  }
})();
