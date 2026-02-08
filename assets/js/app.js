import 'bootstrap';

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById('search-form');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const query = document.getElementById('search-input').value;
        const response = await fetch(`/search?q=${encodeURIComponent(query)}`);
        const data = await response.json();

        if (data.error) {
            return alert(data.error);
        }

        if (data.html !== 0) {
            document.getElementById('search-results').innerHTML = data.html;

            const searchInfo = document.getElementById('search-info');
            searchInfo.toggleAttribute('hidden', false);
            searchInfo.children[0].textContent = `${data.count} Ergebnisse für "${query}" in ${data.duration}ms`;
        }
    });
});