const form = document.getElementById("filterForm");
const genre = document.getElementById("genre");
const author = document.getElementById("author");
const title = document.getElementById("title");
const sortField = document.getElementById("sortField");
const sortDirection = document.getElementById("sortDirection");
const books = document.getElementById("books");
const importSection = document.getElementById("importSection");
const importButton = document.getElementById("import");
const limit = document.getElementById("limit");
let importOffset = 0;

if (importSection) importSection.hidden = true;

async function loadBooks() {
    if (!genre || !author || !title || !sortField || !sortDirection || !books) return;

    const params = new URLSearchParams({
        genre: genre.value,
        author: author.value,
        title: title.value,
        sortField: sortField.value,
        sortDirection: sortDirection.value
    });

    const response = await fetch("../scripts/books.php?" + params);
    const data = await response.json();

    books.innerHTML = "";

    if (!data.length) {
        books.innerHTML = "<p>Книги не найдены</p>";
        return;
    }

    data.forEach(book => {
        books.innerHTML += `
        <div>
            ${book.cover_path ? `<img src="${book.cover_path}" width="150">` : ""}
            <h2><a href="book.php?id=${book.book_id}">${book.title}</a></h2>
            <p>Автор: ${book.authors ?? "Нет"}</p>
            <p>Жанр: ${book.genres ?? "Нет"}</p>
            <p>Цена: ${book.price} ₸</p>
            <hr>
        </div>
        `;
    });
}

loadBooks();

if (form) {
    form.addEventListener("submit", e => {
        e.preventDefault();
        loadBooks();

        if (importSection && genre.value !== "") {
            importSection.hidden = false;
        }
    });
}

if (sortField) sortField.addEventListener("change", loadBooks);
if (sortDirection) sortDirection.addEventListener("change", loadBooks);

if (genre) {
    genre.addEventListener("change", () => {
        importOffset = 0;
        loadBooks();
    });
}

if (limit) {
    limit.addEventListener("input", () => {
        if (limit.value > 50) limit.value = 50;
        if (limit.value < 1) limit.value = 1;
    });
}

async function importBooks() {
    if (!genre || !limit) return;

    importButton.disabled = true;

    try {
        const response = await fetch("../scripts/import.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
                genre: genre.value,
                limit: limit.value,
                offset: importOffset
            })
        });

        const result = await response.json();

        alert(result.message);

        if (result.success) {
            importOffset += Number(limit.value);
            loadBooks();
        }

    } catch (error) {
        alert("Ошибка импорта.");
        console.error(error);
    } finally {
        importButton.disabled = false;
    }
}

if (importButton) {
    importButton.addEventListener("click", importBooks);
}

const phoneInput = document.getElementById("phone");

if (phoneInput) {
    IMask(phoneInput, {
        mask: "+{7} (000) 000-00-00",
        lazy: false
    });
}

const orderForm = document.getElementById("orderForm");

if (orderForm) {
    orderForm.addEventListener("submit", orderBook);
}

async function orderBook(event) {
    event.preventDefault();

    const button = orderForm.querySelector("button");
    button.disabled = true;

    try {
        const formData = new FormData(orderForm);

        const response = await fetch("../scripts/order.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (response.status === 201) {
            alert(`Уважаемый ${result.firstname}, ваш заказ успешно оформлен.`);
            orderForm.reset();
        } else {
            alert(result.message);
        }

    } catch (error) {
        alert("Ошибка соединения с сервером.");
        console.error(error);
    } finally {
        button.disabled = false;
    }
}

Fancybox.bind("[data-fancybox]", {});

const registrationForm = document.getElementById("registrationForm");

if (registrationForm) {
    registrationForm.addEventListener("submit", registerUser);
}

async function registerUser(event) {
    event.preventDefault();

    const button = registrationForm.querySelector("button");
    button.disabled = true;

    try {
        const formData = new FormData(registrationForm);

        const response = await fetch("./scripts/registration.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (response.status === 201) {
            alert(result.message);
            registrationForm.reset();
        } else {
            alert(result.message);
        }

    } catch (error) {
        alert("Ошибка регистрации.");
        console.error(error);
    } finally {
        button.disabled = false;
    }
}