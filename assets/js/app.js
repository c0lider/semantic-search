import 'bootstrap';

document.addEventListener("DOMContentLoaded", () => {
    // search on the index page
    const form = document.getElementById('search-form');

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();

        const searchResultDisplay = document.getElementById('search-results');
        searchResultDisplay.innerHTML = '<div class="spinner-border text-primary m-auto" role="status">\n </div>';

        const typeCheckBoxes = document.querySelectorAll('.form-check-input:checked');

        const typesSelected = Array.from(typeCheckBoxes).map(input => input.value);
        const query = document.getElementById('search-input').value;

        const params = new URLSearchParams();
        params.append('q', query);
        typesSelected.forEach(type => params.append('t[]', type));

        fetch(`/search?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                const searchInfo = document.getElementById('search-info');

                if (data.error) {
                    searchInfo.toggleAttribute('hidden', false);
                    searchInfo.children[0].textContent = data.error;
                    searchResultDisplay.innerHTML = '';


                    return;
                }

                if (data.html !== 0) {
                    searchInfo.toggleAttribute('hidden', false);
                    searchInfo.children[0].textContent = `${data.totalCount} results for '${query}' in ${data.totalDuration}ms`;

                    searchResultDisplay.innerHTML = '';

                    for (const result of data.results) {
                        searchResultDisplay.insertAdjacentHTML('beforeend', result.html);
                    }
                }
            })
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