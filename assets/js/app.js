import 'bootstrap';

document.addEventListener("DOMContentLoaded", () => {
    // search on the index page
    const form = document.getElementById('search-form');

    form?.addEventListener('submit', async (e) => {
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

    // open search result details modal
    const modalElement = document.getElementById('resultDetailsModal');
    const modalContent = document.getElementById('resultDetailsModalContent');

    modalElement?.addEventListener('show.bs.modal', (e) => {
        const button = e.relatedTarget;
        const id = button.getAttribute('data-product-id');

        modalContent.innerHTML = '<div class="modal-body text-center p-5"><div class="spinner-border text-primary" role="status"></div></div>';

        fetch(`/product/details/${id}`)
            .then(response => response.text())
            .then(html => {
                modalContent.innerHTML = html;
            })
            .catch(err => {
                modalContent.innerHTML = '<div class="modal-body">Error loading details.</div>';
            });
    });
});