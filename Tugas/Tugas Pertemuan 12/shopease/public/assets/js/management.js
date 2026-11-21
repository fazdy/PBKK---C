document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', event => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});
const picker = document.getElementById('images');
if (picker) {
    const preview = document.getElementById('image-preview');
    let objectURLs = [];
    picker.addEventListener('change', () => {
        objectURLs.forEach(url => URL.revokeObjectURL(url));
        objectURLs = [];
        preview.replaceChildren();
        for (const file of picker.files) {
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) continue;
            const figure = document.createElement('figure');
            const img = document.createElement('img');
            const caption = document.createElement('figcaption');
            img.alt = 'Pratinjau ' + file.name;
            img.src = URL.createObjectURL(file);
            objectURLs.push(img.src);
            caption.textContent = file.name;
            figure.append(img, caption);
            preview.append(figure);
        }
    });
}
