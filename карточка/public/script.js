// Простая проверка e-mail через регулярное выражение
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const form = document.getElementById("surveyForm");
const formValuesBlock = document.getElementById("formValues");
const cookieValuesBlock = document.getElementById("cookieValues");
const jsonOutput = document.getElementById("jsonOutput");
const cancelBtn = document.getElementById("cancelBtn");
const clearCookieBtn = document.getElementById("clearCookieBtn");

function getFormData() {
  const data = {
    firstName: document.getElementById("firstName").value.trim(),
    lastName: document.getElementById("lastName").value.trim(),
    email: document.getElementById("email").value.trim(),
    comment: document.getElementById("comment").value.trim(),
  };

  return data;
}

function validateData(data) {
  const errors = [];

  if (!data.firstName) errors.push("Поле «Имя» не должно быть пустым.");
  if (!data.lastName) errors.push("Поле «Фамилия» не должно быть пустым.");
  if (!data.email) {
    errors.push("Поле «E-mail» не должно быть пустым.");
  } else if (!emailRegex.test(data.email)) {
    errors.push("Поле «E-mail» не соответствует формату электронной почты.");
  }
  if (!data.comment) errors.push("Поле «Комментарий» не должно быть пустым.");

  return errors;
}

function showFormValues(data) {
  formValuesBlock.textContent =
    `Имя: ${data.firstName}\n` +
    `Фамилия: ${data.lastName}\n` +
    `E-mail: ${data.email}\n` +
    `Комментарий: ${data.comment}`;
}

// Работа с cookie
function setCookie(name, value, days = 7) {
  const date = new Date();
  date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
  const expires = "expires=" + date.toUTCString();
  document.cookie = `${encodeURIComponent(name)}=${encodeURIComponent(
    value
  )};${expires};path=/`;
}

function getCookie(name) {
  const nameEQ = encodeURIComponent(name) + "=";
  const ca = document.cookie.split(";");
  for (let c of ca) {
    while (c.charAt(0) === " ") c = c.substring(1, c.length);
    if (c.indexOf(nameEQ) === 0) {
      return decodeURIComponent(c.substring(nameEQ.length, c.length));
    }
  }
  return null;
}

function showCookieValues() {
  const cookie = getCookie("formData");
  if (!cookie) {
    cookieValuesBlock.textContent = "Cookie пока не создана.";
    return;
  }

  try {
    const data = JSON.parse(cookie);
    cookieValuesBlock.textContent =
      `Имя: ${data.firstName}\n` +
      `Фамилия: ${data.lastName}\n` +
      `E-mail: ${data.email}\n` +
      `Комментарий: ${data.comment}`;
  } catch {
    cookieValuesBlock.textContent = "Ошибка чтения данных из cookie.";
  }
}

function clearCookies() {
  // Удаляем только нашу cookie с данными формы
  setCookie("formData", "", -1);

  // Дополнительно очищаем сохранённый JSON
  try {
    window.localStorage.removeItem("formDataJSON");
  } catch {
    // игнорируем
  }

  cookieValuesBlock.textContent = "Cookie очищены.";
}

// JSON-представление
function showJson(data) {
  const json = JSON.stringify(data, null, 2);
  jsonOutput.textContent = json;
  // Дополнительно сохраняем JSON в Local Storage
  try {
    window.localStorage.setItem("formDataJSON", json);
  } catch {
    // игнорируем ошибки (например, запрет доступа к localStorage)
  }
}

form.addEventListener("submit", (event) => {
  event.preventDefault();

  const data = getFormData();
  const errors = validateData(data);

  if (errors.length > 0) {
    alert(errors.join("\n"));
    return;
  }

  // 2.1 Вывести полученные значения полей формы на текущую страницу
  showFormValues(data);

  // 2.2 Сохранить значения полей в cookie-файл
  setCookie("formData", JSON.stringify(data), 7);
  showCookieValues();

  // 4. Сохранить данные в JSON-формате
  showJson(data);
});

cancelBtn.addEventListener("click", () => {
  formValuesBlock.textContent = "";
  jsonOutput.textContent = "";
});

clearCookieBtn.addEventListener("click", () => {
  clearCookies();
});

// При загрузке страницы пробуем вывести данные из cookie и Local Storage
document.addEventListener("DOMContentLoaded", () => {
  showCookieValues();

  // Восстановление JSON из Local Storage (не обязательно, но демонстрация)
  try {
    const json = window.localStorage.getItem("formDataJSON");
    if (json) {
      jsonOutput.textContent = json;
    }
  } catch {
    // игнорируем
  }
});

