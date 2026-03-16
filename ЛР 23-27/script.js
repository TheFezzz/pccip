// ==== Данные о людях ====
const people = [
  {
    name: "Тамара Малюк",
    age: "85 лет",
    bio: "Пережила ужасы нацистской оккупации и рабства",
    photo: "people1.jpg",
    quotes: [
      "— Знаете, мои старшие братья и сестры удивлялись тому, как много я запомнила с тех лет — больше, чем они, — немного помолчав, 85-летняя Тамара Алексеевна Малюк добавляет: — Наверное, даже больше, чем мне бы хотелось..."
    ]
  },
  {
    name: "Раиса Корзун",
    age: "84 года",
    bio: "Бывшая узница концентрационного лагеря «Озаричи»",
    photo: "people2.jpg",
    quotes: [
      "– Родилась я в 1938 году. Мне было пять лет, когда меня с бабушкой и дедушкой, маминой младшей сестрой и ее шестимесячным ребенком вывезли в концлагерь «Озаричи». Очень много было населенных пунктов, которые мы прошли и откуда собирали людей. Детей, стариков, женщин… Лагерь «Озаричи» был обнесен колючей проволокой и кругом заминирован."
    ]
  },
  {
    name: "Мария Алексеевна Дубоделова",
    age: "84 года",
    bio: "Оказалась в Восточной Пруссии, в огороженном колючей проволокой в поле лагере, где было много бараков и откуда по утрам за ворота выводили взрослых на работу",
    photo: "people3.jpg",
    quotes: [
      "…Ей было суждено выжить уже хотя бы потому, что в оккупированном селе, где немецкий штаб был рядом с домом ее родителей, разъяренный фашист случайно не убил ее мать."
    ]
  }
];

let currentPersonIndex = 0;

function updatePersonInfo(index) {
  const person = people[index];
  document.getElementById("person-name").textContent = person.name;
  document.getElementById("person-age").innerHTML = `<strong>Возраст:</strong> ${person.age}`;
  document.getElementById("person-bio").innerHTML = `<strong>Биография:</strong> ${person.bio}`;
  document.getElementById("profile-pic").src = person.photo;
  document.getElementById("quote1").textContent = person.quotes[0];
  document.getElementById("moreLink").href = `index.php#person${index + 1}`;
}

// ==== Данные об образовательных статьях ====
const articles = [
  {
    title: "Шталаг-352: людей давили танками, применяли извращенные казни",
    content: "С июля 1941-го по июнь 1944 г. Лагерь военнопленных, находившийся на территории современного микрорайона Масюковщина в Минске. Мученическую смерть здесь приняли более 80 тысяч человек.  Людей содержали в сараях с земляным полом, в жуткой вони и грязи, темноте. Хлипкие нары под телами людей ломались, и часто они оказывались придушенными или раздавленными. С особым цинизмом относились к проштрафившимся: на несколько дней их садили в миниатюрные клетки с холодным полом и колючей проволокой вместо крыши, встать в полный рост было невозможно. Мало кто это выдерживал. Бушевали болезни, в частности дизентерия. Изможденные люди при этом тяжело трудились. Умирали сотнями ежедневно. Кто своей смертью, а кого пристреливали, когда бросался к ограждению за гнилой картошкой. Посудой в лагере, в которую наливали несъедобную жижу, иногда служили шапки и даже ладони.  Применялись порка, повешение, мучительные казни. Например, подвешивали на крюки за подбородок. По сведениям местных жителей, места захоронения расстрелянных укатывали танком, но даже после этого земля продолжала шевелиться. ",
    image: "https://www.sb.by/upload/iblock/bba/bbaa8bdd9761ab42cb727d053ffd2ba1.jpg"
  },
  {
    title: "Холокост — трагедия еврейского народа",
    content: "Во время Второй мировой войны нацистская Германия организовала систематическое уничтожение еврейского населения Европы. Этот ужасный период истории вошёл в историю под названием Холокост. С 1941 по 1945 год около шести миллионов евреев были убиты в гетто, концентрационных лагерях и на расстрельных полигонах. Жестокость Холокоста выражалась не только в масштабах, но и в методах — газовые камеры, медицинские эксперименты, массовые депортации. Помимо евреов, жертвами стали цыгане, инвалиды, славяне, представители ЛГБТ-сообщества и другие группы. Холокост стал символом крайнего проявления ненависти и расизма.",
    image: "https://avatars.mds.yandex.net/i?id=7eb2a025e888968a41c3247ba044becf6b9f6b27-7909006-images-thumbs&n=13"
  },
  {
    title: "Почему важно помнить о геноцидах",
    content: "Геноцид — это не только трагедия прошлого, но и предупреждение на будущее. Знание истории массовых уничтожений, таких как Холокост, геноцид армян, события в Камбодже и Дарфуре, формирует у общества иммунитет против ненависти и насилия. Память о геноциде помогает обществу распознавать ранние признаки нетерпимости: расизм, национализм, пропаганду вражды. Она призывает к уважению прав человека и к защите уязвимых групп. Забвение — почва для повторения. Поэтому важно говорить, изучать и напоминать: „Никогда снова“ — это не лозунг, а призыв к действию.",
    image: "https://i.ytimg.com/vi/oJ87WehXwv0/maxresdefault.jpg"
  }
];

let currentArticleIndex = 0;

function showArticle(index) {
  const article = articles[index];
  document.getElementById('articleTitle').textContent = article.title;
  document.getElementById('articleContent').textContent = article.content;
  document.getElementById('articleImage').src = article.image;
}

function nextArticle() {
  currentArticleIndex = (currentArticleIndex + 1) % articles.length;
  showArticle(currentArticleIndex);
}

function prevArticle() {
  currentArticleIndex = (currentArticleIndex - 1 + articles.length) % articles.length;
  showArticle(currentArticleIndex);
}

document.addEventListener('DOMContentLoaded', () => {
  showArticle(currentArticleIndex);
});
let originalOrder = [];

// Сохраняем оригинальный порядок при загрузке
document.addEventListener('DOMContentLoaded', function() {
    const list = document.getElementById("dictionaryList");
    const items = Array.from(list.getElementsByTagName("li"));
    originalOrder = items.map(item => item.cloneNode(true));
    
    // Скрыть дополнительные термины при загрузке
    const extraTerms = document.querySelectorAll('.extra-term');
    extraTerms.forEach(term => {
        term.style.display = 'none';
    });
    
    // Обработчик для очистки подсветки при пустом поиске
    const searchInput = document.getElementById("searchInput");
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            if (this.value.trim() === "") {
                const terms = document.querySelectorAll('#dictionaryList strong');
                terms.forEach(term => {
                    const originalText = term.textContent;
                    term.innerHTML = originalText;
                });
                // При пустом поиске применяем текущую сортировку
                applySorting();
            }
        });
    }
    
    // Применяем сортировку по умолчанию
    applySorting();
});

function searchDictionary() {
    const input = document.getElementById("searchInput");
    const filter = input.value.trim().toLowerCase();
    const list = document.getElementById("dictionaryList");
    const items = list.getElementsByTagName("li");
    const noResultsMsg = document.getElementById("noResultsMessage");
    let foundCount = 0;

    // Сначала скрываем сообщение
    if (noResultsMsg) {
        noResultsMsg.style.display = "none";
    }

    // Если поле поиска пустое, показываем все элементы
    if (filter === "") {
        for (let i = 0; i < items.length; i++) {
            items[i].style.display = "";
        }
        return;
    }

    // Ищем совпадения
    for (let i = 0; i < items.length; i++) {
        const termElement = items[i].querySelector("strong");
        
        if (!termElement) {
            items[i].style.display = "none";
            continue;
        }
        
        const term = termElement.textContent.trim().toLowerCase();
        const category = items[i].getAttribute('data-category') || '';
        const description = items[i].textContent.toLowerCase();
        
        // Ищем в термине, категории и описании
        const isMatch = term.includes(filter) || 
                       category.includes(filter) || 
                       description.includes(filter);
        
        if (isMatch) {
            items[i].style.display = "";
            foundCount++;
            
            // Подсветка найденного текста в термине
            const originalText = termElement.textContent;
            if (filter && term.includes(filter)) {
                const regex = new RegExp(`(${filter})`, 'gi');
                termElement.innerHTML = originalText.replace(regex, '<mark>$1</mark>');
            }
        } else {
            items[i].style.display = "none";
        }
    }
    
    // Показываем сообщение, если ничего не найдено
    if (noResultsMsg && foundCount === 0) {
        noResultsMsg.style.display = "block";
    }
}

function applySorting() {
    const select = document.getElementById("sortSelect");
    const mode = select.value;
    const list = document.getElementById("dictionaryList");
    const items = Array.from(list.getElementsByTagName("li"));
    
    switch(mode) {
        case 'alphabet':
            // А-Я (по возрастанию)
            items.sort((a, b) => {
                const textA = a.querySelector("strong")?.textContent || a.textContent;
                const textB = b.querySelector("strong")?.textContent || b.textContent;
                return textA.localeCompare(textB, 'ru');
            });
            break;
            
        case 'reverse':
            // Я-А (по убыванию)
            items.sort((a, b) => {
                const textA = a.querySelector("strong")?.textContent || a.textContent;
                const textB = b.querySelector("strong")?.textContent || b.textContent;
                return textB.localeCompare(textA, 'ru');
            });
            break;
            
        case 'length-asc':
            // По длине термина (от короткого к длинному)
            items.sort((a, b) => {
                const textA = a.querySelector("strong")?.textContent || a.textContent;
                const textB = b.querySelector("strong")?.textContent || b.textContent;
                return textA.length - textB.length;
            });
            break;
            
        case 'length-desc':
            // По длине термина (от длинного к короткому)
            items.sort((a, b) => {
                const textA = a.querySelector("strong")?.textContent || a.textContent;
                const textB = b.querySelector("strong")?.textContent || b.textContent;
                return textB.length - textA.length;
            });
            break;
            
        case 'category':
            // По категориям
            items.sort((a, b) => {
                const catA = a.getAttribute('data-category') || '';
                const catB = b.getAttribute('data-category') || '';
                if (catA === catB) {
                    const textA = a.querySelector("strong")?.textContent || a.textContent;
                    const textB = b.querySelector("strong")?.textContent || b.textContent;
                    return textA.localeCompare(textB, 'ru');
                }
                return catA.localeCompare(catB, 'ru');
            });
            break;
            
        case 'date-added':
            // По дате добавления (новые сверху)
            items.sort((a, b) => {
                const dateA = parseInt(a.getAttribute('data-date') || '0');
                const dateB = parseInt(b.getAttribute('data-date') || '0');
                return dateB - dateA;
            });
            break;
            
        case 'shuffle':
            // Перемешать случайным образом
            for (let i = items.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [items[i], items[j]] = [items[j], items[i]];
            }
            break;
            
        case 'popularity':
            // По популярности (популярные сверху)
            items.sort((a, b) => {
                const popA = parseInt(a.getAttribute('data-popularity') || '0');
                const popB = parseInt(b.getAttribute('data-popularity') || '0');
                return popB - popA;
            });
            break;
            
        default:
            // По умолчанию - алфавитная сортировка
            items.sort((a, b) => {
                const textA = a.querySelector("strong")?.textContent || a.textContent;
                const textB = b.querySelector("strong")?.textContent || b.textContent;
                return textA.localeCompare(textB, 'ru');
            });
    }
    
    // Очищаем список и добавляем отсортированные элементы
    list.innerHTML = "";
    items.forEach(item => list.appendChild(item));
}

function toggleAdditionalTerms() {
    const extraTerms = document.querySelectorAll('.extra-term');
    const showMoreBtn = document.getElementById('showMoreBtn');
    
    const isHidden = extraTerms[0].style.display === 'none' || 
                    extraTerms[0].style.display === '';
    
    extraTerms.forEach(term => {
        term.style.display = isHidden ? 'list-item' : 'none';
    });
    
    showMoreBtn.textContent = isHidden ? 'Скрыть' : 'Показать больше';
}

    // JS логика кнопки "Наверх"
    window.onscroll = function() {
        const btn = document.getElementById("scrollTopBtn");
        if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
            btn.style.display = "block";
        } else {
            btn.style.display = "none";
        }
    };

    function scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
document.querySelectorAll('.timeline-marker').forEach(marker => {
    marker.addEventListener('click', e => {
      e.stopPropagation();
      const timelineItem = marker.parentElement;

      document.querySelectorAll('.timeline-item').forEach(item => {
        if (item !== timelineItem) {
          item.classList.remove('active');
          item.querySelector('.timeline-details').classList.remove('visible');
        }
      });

      timelineItem.classList.toggle('active');
      timelineItem.querySelector('.timeline-details').classList.toggle('visible');
    });
  });

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
      }
    });
  }, {
    threshold: 0.1
  });

  document.querySelectorAll('.animate-on-scroll').forEach(section => {
    observer.observe(section);
  });
function toggleAdditionalTerms() {
  const extraTerms = document.querySelectorAll('.extra-term');
  const showMoreBtn = document.getElementById('showMoreBtn');

  extraTerms.forEach(term => {
    term.style.display = (term.style.display === 'none' || term.style.display === '') ? 'list-item' : 'none';
  });

  showMoreBtn.innerHTML = (showMoreBtn.innerHTML === 'Показать больше') ? 'Скрыть' : 'Показать больше';
}

// ==== Инициализация после загрузки страницы ====
window.addEventListener('DOMContentLoaded', () => {
  updatePersonInfo(currentPersonIndex);
  showArticle(currentArticleIndex);
});

// ==== Навешиваем обработчики переключения людей ====
document.getElementById("nextBtn").addEventListener("click", () => {
  currentPersonIndex = (currentPersonIndex + 1) % people.length;
  updatePersonInfo(currentPersonIndex);
});

document.getElementById("prevBtn").addEventListener("click", () => {
  currentPersonIndex = (currentPersonIndex - 1 + people.length) % people.length;
  updatePersonInfo(currentPersonIndex);
});

function toggleAccessibility() {
  document.body.classList.toggle('accessibility');
  const btn = document.getElementById('accessibilityBtn');
  if (document.body.classList.contains('accessibility')) {
    btn.textContent = 'Обычная версия';
  } else {
    btn.textContent = 'Версия для слабовидящих';
  }
}